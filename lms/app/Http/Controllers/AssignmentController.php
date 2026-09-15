<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Subject;
use App\Models\Section;
use App\Models\AcademicYear;
use App\Models\Semester;
use App\Services\TeacherClassAssignmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;

class AssignmentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            if (!in_array(auth()->user()->role_name, ['Admin', 'Teacher'])) {
                abort(403, 'Only teachers and administrators can manage assignments.');
            }
            return $next($request);
        });
    }

    /**
     * Check if user can create assignments (only teachers)
     */
    private function canCreateAssignment()
    {
        if (auth()->user()->role_name === 'Admin') {
            return false; // Admins cannot create assignments
        }
        return auth()->user()->role_name === 'Teacher';
    }

    /**
     * Display a listing of assignments
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = Assignment::with(['teacher', 'subject', 'section', 'academicYear', 'semester'])
            ->withCount('submissions');

        // Filter by teacher if not admin
        if ($user->role_name === 'Teacher') {
            $teacher = $user->teacher;
            if ($teacher) {
                $query->where('teacher_id', $teacher->id);
            }
        }

        // Apply filters
        if ($request->filled('subject_id')) {
            $query->where('subject_id', $request->subject_id);
        }

        if ($request->filled('section_id')) {
            $query->where('section_id', $request->section_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $stats = [
            'total' => (clone $query)->count(),
            'published' => (clone $query)->where('status', 'published')->count(),
            'draft' => (clone $query)->where('status', 'draft')->count(),
            'due_soon' => (clone $query)->where('status', 'published')
                ->whereDate('due_date', '>=', now()->toDateString())
                ->whereDate('due_date', '<=', now()->addDays(7)->toDateString())
                ->count(),
        ];

        $assignments = $query->orderBy('created_at', 'desc')->paginate(15);
        $subjects = Cache::remember('lookup.subjects.all', 300, fn () => Subject::query()->orderBy('subject_name')->get());
        $sections = Cache::remember('lookup.sections.all', 300, fn () => Section::query()->orderBy('name')->get());

        return view('assignments.index', compact('assignments', 'subjects', 'sections', 'stats'));
    }

    /**
     * Show the form for creating a new assignment
     */
    public function create()
    {
        if (!$this->canCreateAssignment()) {
            abort(403, 'Only teachers can create assignments.');
        }

        $teacher = Auth::user()->teacher;
        if (!$teacher) {
            return redirect()->route('assignments.index')
                ->with('error', 'Teacher profile not found.');
        }

        $assignmentOptions = app(TeacherClassAssignmentService::class)->optionsFor($teacher);
        $academicYears = AcademicYear::orderByDesc('id')->get();
        $semesters = Semester::orderBy('name')->get();

        return view('assignments.create', array_merge($assignmentOptions, [
            'academicYears' => $academicYears,
            'semesters' => $semesters,
        ]));
    }

    /**
     * Store a newly created assignment
     */
    public function store(Request $request)
    {
        if (!$this->canCreateAssignment()) {
            abort(403, 'Only teachers can create assignments.');
        }

        $teacher = Auth::user()->teacher;
        if (!$teacher) {
            return redirect()->back()->with('error', 'Teacher profile not found.');
        }

        $allowed = app(TeacherClassAssignmentService::class)->optionsFor($teacher);
        $allowedSubjectIds = $allowed['subjects']->pluck('id')->all();
        $allowedSectionIds = $allowed['sections']->pluck('id')->all();
        $subjectsBySection = $allowed['subjectsBySection'];

        $validator = Validator::make($request->all(), [
            'section_id' => ['required', Rule::in($allowedSectionIds)],
            'subject_id' => ['required', Rule::in($allowedSubjectIds)],
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'academic_year_id' => 'required|exists:academic_years,id',
            'semester_id' => 'required|exists:semesters,id',
            'due_date' => 'required|date|after_or_equal:today',
            'due_time' => ['nullable', 'regex:/^\d{2}:\d{2}(:\d{2})?$/'],
            'max_score' => 'required|numeric|min:0|max:1000',
            'late_submission_penalty' => 'nullable|numeric|min:0|max:100',
            'submission_instructions' => 'nullable|string',
            'allowed_file_types' => 'nullable|array',
            'max_file_size' => 'nullable|numeric|min:1|max:50',
        ], [
            'subject_id.in' => 'Select a subject assigned to you by Admin.',
            'section_id.in' => 'Select a section assigned to you by Admin.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $subjectId = (int) $request->subject_id;
        $sectionId = (int) $request->section_id;
        $validSubjects = array_map('intval', $subjectsBySection[$sectionId] ?? []);
        if (! in_array($subjectId, $validSubjects, true)) {
            return redirect()->back()->withInput()->withErrors([
                'subject_id' => 'That subject is not linked to the selected section in your teaching assignment.',
            ]);
        }

        $data = $request->only([
            'title',
            'description',
            'subject_id',
            'section_id',
            'academic_year_id',
            'semester_id',
            'due_date',
            'due_time',
            'max_score',
            'late_submission_penalty',
            'submission_instructions',
            'allowed_file_types',
            'max_file_size',
        ]);
        $data['teacher_id'] = $teacher->id;
        $data['allows_late_submission'] = $request->has('allows_late_submission');
        $data['requires_file_upload'] = $request->has('requires_file_upload');
        $data['status'] = 'published';
        $data['is_active'] = true;

        if ($data['requires_file_upload']) {
            $types = array_values(array_filter((array) $request->input('allowed_file_types', [])));
            $types = array_map(static fn ($t) => $t === 'doc' ? 'docx' : $t, $types);
            $types = array_values(array_unique(array_diff($types, ['doc'])));
            $data['allowed_file_types'] = $types !== [] ? $types : ['pdf', 'docx'];
            $data['max_file_size'] = (int) ($request->input('max_file_size') ?: 10);
        } else {
            $data['allowed_file_types'] = null;
            $data['max_file_size'] = 10;
        }

        if ($request->filled('due_time') && strlen($data['due_time']) > 5) {
            $data['due_time'] = substr($data['due_time'], 0, 5);
        }

        try {
            Assignment::create($data);
            return redirect()->route('assignments.create')->with('success', 'Assignment created successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['error' => 'Failed to create assignment: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Display the specified assignment
     */
    public function show(Assignment $assignment)
    {
        $this->authorizeAssignment($assignment);
        
        $assignment->load(['teacher', 'subject', 'section', 'academicYear', 'semester', 'submissions.student']);
        
        return view('assignments.show', compact('assignment'));
    }

    /**
     * Show the form for editing the specified assignment
     */
    public function edit(Assignment $assignment)
    {
        $this->authorizeAssignment($assignment);
        
        $subjects = Subject::all();
        $sections = Section::all();
        $academicYears = AcademicYear::all();
        $semesters = Semester::all();
        
        return view('assignments.edit', compact('assignment', 'subjects', 'sections', 'academicYears', 'semesters'));
    }

    /**
     * Update the specified assignment
     */
    public function update(Request $request, Assignment $assignment)
    {
        $this->authorizeAssignment($assignment);

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'subject_id' => 'required|exists:subjects,id',
            'section_id' => 'required|exists:sections,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'semester_id' => 'required|exists:semesters,id',
            'due_date' => 'required|date',
            'due_time' => ['nullable', 'regex:/^\d{2}:\d{2}(:\d{2})?$/'],
            'max_score' => 'required|numeric|min:0|max:1000',
            'late_submission_penalty' => 'nullable|numeric|min:0|max:100',
            'submission_instructions' => 'nullable|string',
            'allowed_file_types' => 'nullable|array',
            'max_file_size' => 'nullable|numeric|min:1|max:50',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $data = $request->only([
            'title',
            'description',
            'subject_id',
            'section_id',
            'academic_year_id',
            'semester_id',
            'due_date',
            'due_time',
            'max_score',
            'late_submission_penalty',
            'submission_instructions',
            'allowed_file_types',
            'max_file_size',
        ]);
        $data['allows_late_submission'] = $request->has('allows_late_submission');
        $data['requires_file_upload'] = $request->has('requires_file_upload');

        if ($data['requires_file_upload']) {
            $types = array_values(array_filter((array) $request->input('allowed_file_types', [])));
            $types = array_map(static fn ($t) => $t === 'doc' ? 'docx' : $t, $types);
            $types = array_values(array_unique(array_diff($types, ['doc'])));
            $data['allowed_file_types'] = $types !== [] ? $types : ['pdf', 'docx'];
            $data['max_file_size'] = (int) ($request->input('max_file_size') ?: 10);
        } else {
            $data['allowed_file_types'] = null;
            $data['max_file_size'] = 10;
        }

        if ($request->filled('due_time') && strlen((string) ($data['due_time'] ?? '')) > 5) {
            $data['due_time'] = substr($data['due_time'], 0, 5);
        }

        $assignment->update($data);

        return redirect()->route('assignments.show', $assignment)->with('success', 'Assignment updated successfully.');
    }

    /**
     * Remove the specified assignment
     */
    public function destroy(Assignment $assignment)
    {
        $this->authorizeAssignment($assignment);

        // Delete associated file
        if ($assignment->file_path) {
            Storage::disk('public')->delete($assignment->file_path);
        }

        $assignment->delete();

        return redirect()->route('assignments.index')->with('success', 'Assignment deleted successfully.');
    }

    /**
     * Publish assignment
     */
    public function publish(Assignment $assignment)
    {
        $this->authorizeAssignment($assignment);
        
        $assignment->update(['status' => 'published']);
        
        return redirect()->back()->with('success', 'Assignment published successfully.');
    }

    /**
     * Close assignment
     */
    public function close(Assignment $assignment)
    {
        $this->authorizeAssignment($assignment);
        
        $assignment->update(['status' => 'closed']);
        
        return redirect()->back()->with('success', 'Assignment closed successfully.');
    }

    /**
     * Show submissions for an assignment
     */
    public function submissions(Assignment $assignment)
    {
        $this->authorizeAssignment($assignment);
        
        $submissions = $assignment->submissions()
            ->with('student')
            ->orderBy('submitted_at', 'desc')
            ->paginate(20);
        
        return view('assignments.submissions', compact('assignment', 'submissions'));
    }

    /**
     * Get submission details for viewing (AJAX)
     */
    public function viewSubmission(AssignmentSubmission $submission)
    {
        $this->authorizeAssignment($submission->assignment);
        
        $submission->load(['student', 'assignment']);
        
        $html = view('assignments.partials.submission-view', compact('submission'))->render();
        
        return response()->json(['html' => $html]);
    }

    /**
     * Get submission details for grading (AJAX)
     */
    public function getSubmissionDetails(AssignmentSubmission $submission)
    {
        $this->authorizeAssignment($submission->assignment);
        
        $submission->load(['student', 'assignment']);
        
        $html = view('assignments.partials.submission-details', compact('submission'))->render();
        
        return response()->json(['html' => $html]);
    }

    /**
     * Get existing grade data (AJAX)
     */
    public function getGradeData(AssignmentSubmission $submission)
    {
        $this->authorizeAssignment($submission->assignment);
        
        return response()->json([
            'score' => $submission->score,
            'feedback' => $submission->teacher_feedback
        ]);
    }

    /**
     * Grade a submission
     */
    public function gradeSubmission(Request $request, AssignmentSubmission $submission)
    {
        $this->authorizeAssignment($submission->assignment);

        try {
            $validator = Validator::make($request->all(), [
                'score' => 'required|numeric|min:0|max:' . $submission->assignment->max_score,
                'feedback' => 'nullable|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()->first()
                ], 422);
            }

            $submission->markAsGraded($request->score, $request->feedback);

            return response()->json([
                'success' => true,
                'message' => 'Submission graded successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error saving grade: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export assignments to PDF
     */
    public function exportPdf(Assignment $assignment)
    {
        $this->authorizeAssignment($assignment);
        
        $assignment->load(['teacher', 'subject', 'section', 'submissions.student']);
        
        $pdf = PDF::loadView('assignments.pdf', compact('assignment'));
        
        return $pdf->download("assignment_{$assignment->id}.pdf");
    }

    /**
     * Export assignments to Excel
     */
    public function exportExcel()
    {
        $user = Auth::user();
        $query = Assignment::with(['teacher', 'subject', 'section']);

        if ($user->role_name === 'Teacher') {
            $teacher = $user->teacher;
            if ($teacher) {
                $query->where('teacher_id', $teacher->id);
            }
        }

        $assignments = $query->get();

        // TODO: Create AssignmentsExport class
        return redirect()->back()->with('info', 'Excel export feature coming soon.');
    }

    /**
     * Authorize assignment access
     */
    private function authorizeAssignment(Assignment $assignment)
    {
        $user = Auth::user();
        
        if ($user->role_name === 'Admin') {
            return true;
        }
        
        if ($user->role_name === 'Teacher') {
            $teacher = $user->teacher;
            if ($teacher && $teacher->id === $assignment->teacher_id) {
                return true;
            }
        }
        
        abort(403, 'Unauthorized action.');
    }
}
