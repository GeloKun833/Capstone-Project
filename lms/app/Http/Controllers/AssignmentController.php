<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Section;
use App\Models\AcademicYear;
use App\Models\Semester;
use App\Services\TeacherClassAssignmentService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Barryvdh\DomPDF\Facade\Pdf;

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

        $subjects = collect();
        $sections = collect();

        if ($user->role_name === 'Teacher') {
            $teacher = $user->teacher;
            if ($teacher) {
                $query->where('teacher_id', $teacher->id);
                $options = app(TeacherClassAssignmentService::class)->optionsFor($teacher);
                $assignedSubjectIds = array_map('intval', array_keys($options['assignmentMap']));
                $assignedSectionIds = collect($options['subjectsBySection'])->keys()->map(fn ($id) => (int) $id)->all();
                $subjects = Subject::query()
                    ->whereIn('id', $assignedSubjectIds)
                    ->orderBy('subject_name')
                    ->orderBy('class')
                    ->get();
                $sections = Section::query()
                    ->whereIn('id', $assignedSectionIds)
                    ->orderBy('name')
                    ->get();
            } else {
                $query->whereRaw('1 = 0');
            }
        } else {
            $subjects = Cache::remember('lookup.subjects.all', 300, fn () => Subject::query()->orderBy('subject_name')->get());
            $sections = Cache::remember('lookup.sections.all', 300, fn () => Section::query()->orderBy('name')->get());
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

        $viewingYearId = app(\App\Services\AcademicYearContext::class)->viewing()?->id;
        if ($request->filled('academic_year_id')) {
            $query->where('academic_year_id', $request->academic_year_id);
        } elseif ($viewingYearId) {
            $query->where('academic_year_id', $viewingYearId);
        }

        $stats = [
            'total' => (clone $query)->count(),
            'published' => (clone $query)->where('status', 'published')->count(),
            'draft' => (clone $query)->where('status', 'draft')->count(),
            'due_soon' => (clone $query)->where('status', 'published')->dueSoon(7)->count(),
        ];

        $assignments = $query->orderBy('created_at', 'desc')->paginate(15);

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
            'submission_instructions',
            'allowed_file_types',
            'max_file_size',
        ]);
        $data['teacher_id'] = $teacher->id;
        $data['allows_late_submission'] = false;
        $data['late_submission_penalty'] = 0;
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

        $isTeacherEdit = Auth::user()->role_name === 'Teacher';
        $subjectsBySection = [];
        if ($isTeacherEdit) {
            $options = app(TeacherClassAssignmentService::class)->optionsFor(Auth::user()->teacher);
            $subjects = $options['subjects'];
            $sections = $options['sections'];
            $subjectsBySection = $options['subjectsBySection'];

            $currentSubject = Subject::find($assignment->subject_id);
            $currentSection = Section::find($assignment->section_id);
            if ($currentSubject && ! $subjects->contains(fn ($subject) => (int) $subject->id === (int) $currentSubject->id)) {
                $subjects->push($currentSubject);
            }
            if ($currentSection && ! $sections->contains(fn ($section) => (int) $section->id === (int) $currentSection->id)) {
                $sections->push($currentSection);
            }
            if ($currentSubject && $currentSection) {
                $subjectsBySection[$currentSection->id] ??= [];
                if (! in_array((int) $currentSubject->id, $subjectsBySection[$currentSection->id], true)) {
                    $subjectsBySection[$currentSection->id][] = (int) $currentSubject->id;
                }
            }
        } else {
            $subjects = Subject::query()->orderBy('subject_name')->get();
            $sections = Section::query()->orderBy('name')->get();
        }

        $academicYears = AcademicYear::all();
        $semesters = Semester::all();

        return view('assignments.edit', compact('assignment', 'subjects', 'sections', 'academicYears', 'semesters', 'subjectsBySection', 'isTeacherEdit'));
    }

    /**
     * Update the specified assignment
     */
    public function update(Request $request, Assignment $assignment)
    {
        $this->authorizeAssignment($assignment);

        $isTeacher = Auth::user()->role_name === 'Teacher';
        $teacherOptions = $isTeacher
            ? app(TeacherClassAssignmentService::class)->optionsFor(Auth::user()->teacher)
            : null;
        if ($isTeacher) {
            $teacherOptions['subjects'] = $teacherOptions['subjects']
                ->pluck('id')
                ->push((int) $assignment->subject_id)
                ->unique()
                ->values();
            $teacherOptions['sections'] = $teacherOptions['sections']
                ->pluck('id')
                ->push((int) $assignment->section_id)
                ->unique()
                ->values();
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'subject_id' => $isTeacher
                ? ['required', Rule::in($teacherOptions['subjects']->pluck('id')->all())]
                : 'required|exists:subjects,id',
            'section_id' => $isTeacher
                ? ['required', Rule::in($teacherOptions['sections']->pluck('id')->all())]
                : 'required|exists:sections,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'semester_id' => 'required|exists:semesters,id',
            'due_date' => 'required|date',
            'due_time' => ['nullable', 'regex:/^\d{2}:\d{2}(:\d{2})?$/'],
            'max_score' => 'required|numeric|min:0|max:1000',
            'submission_instructions' => 'nullable|string',
            'allowed_file_types' => 'nullable|array',
            'max_file_size' => 'nullable|numeric|min:1|max:50',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        if ($isTeacher) {
            $selectedSubjectId = (int) $request->subject_id;
            $selectedSectionId = (int) $request->section_id;
            $isCurrentPair = $selectedSubjectId === (int) $assignment->subject_id
                && $selectedSectionId === (int) $assignment->section_id;
            $allowedSubjectIds = array_map('intval', $teacherOptions['subjectsBySection'][$selectedSectionId] ?? []);

            if (! $isCurrentPair && ! in_array($selectedSubjectId, $allowedSubjectIds, true)) {
                return redirect()->back()->withInput()->withErrors([
                    'subject_id' => 'Select a subject assigned to you for the selected section.',
                ]);
            }
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
            'submission_instructions',
            'allowed_file_types',
            'max_file_size',
        ]);
        $data['allows_late_submission'] = false;
        $data['late_submission_penalty'] = 0;
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
     * Reopen a closed or overdue assignment with a new deadline.
     */
    public function reopen(Request $request, Assignment $assignment)
    {
        $this->authorizeAssignment($assignment);

        if ($assignment->status !== 'closed' && ! ($assignment->status === 'published' && $assignment->is_overdue)) {
            return redirect()->back()->with('error', 'Only closed or overdue assignments can be reopened.');
        }

        if (! $this->missingStudentsQuery($assignment)->exists()) {
            return redirect()->back()->with('info', 'All eligible students have already submitted this assignment.');
        }

        $validator = Validator::make($request->all(), [
            'due_date' => 'required|date',
            'due_time' => ['nullable', 'regex:/^\d{2}:\d{2}(:\d{2})?$/'],
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $data = $validator->validated();
        $timezone = config('app.school_timezone', 'Asia/Manila');
        $dueTime = $data['due_time'] ?: '23:59:59';
        if (strlen($dueTime) === 5) {
            $dueTime .= ':00';
        }

        $newDeadline = Carbon::createFromFormat('!Y-m-d H:i:s', $data['due_date'].' '.$dueTime, $timezone);
        if (! $newDeadline->gt(Carbon::now($timezone))) {
            return redirect()->back()->withErrors([
                'due_date' => 'The new deadline must be in the future.',
            ])->withInput();
        }

        $assignment->update([
            'due_date' => $data['due_date'],
            'due_time' => $data['due_time'] ?: null,
            'status' => 'published',
            'is_active' => true,
        ]);

        return redirect()->route('assignments.submissions', $assignment)
            ->with('success', 'Assignment reopened. Students who have not submitted can submit until the new deadline.');
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

        $missingStudents = $this->missingStudentsQuery($assignment)
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get(['students.id', 'students.first_name', 'students.last_name', 'students.email', 'students.admission_id']);
        $reopenDefault = now(config('app.school_timezone', 'Asia/Manila'))->addHour();

        return view('assignments.submissions', compact('assignment', 'submissions', 'missingStudents', 'reopenDefault'));
    }

    private function missingStudentsQuery(Assignment $assignment)
    {
        $query = Student::query()
            ->whereHas('enrollments', fn ($enrollments) => $enrollments->where('subject_id', $assignment->subject_id))
            ->whereNotIn('students.id', AssignmentSubmission::query()
                ->select('student_id')
                ->where('assignment_id', $assignment->id));

        if ($assignment->section_id) {
            $query->where(function ($students) use ($assignment) {
                $students->whereDoesntHave('sections')
                    ->orWhereHas('sections', fn ($sections) => $sections->where('sections.id', $assignment->section_id));
            });
        }

        return $query;
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
