<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class StudentAssignmentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            if (auth()->user()->role_name !== 'Student') {
                abort(403, 'Only students can access this section.');
            }
            return $next($request);
        });
    }

    /**
     * Display a listing of assignments for the student
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $student = $user->student;

        if (!$student) {
            return redirect()->back()->with('error', 'Student profile not found.');
        }

        // Get assignments for subjects the student is enrolled in
        $enrolledSubjectIds = $student->enrollments()->pluck('subject_id');
        
        // Get student's sections
        $studentSectionIds = $student->sections()->pluck('sections.id');
        
        $query = Assignment::with(['teacher', 'subject', 'section'])
            ->whereIn('subject_id', $enrolledSubjectIds)
            ->where('status', 'published')
            ->where('is_active', true);
            
        // If student has sections, also filter by section (OR show assignments with no specific section)
        if ($studentSectionIds->isNotEmpty()) {
            $query->where(function($q) use ($studentSectionIds) {
                $q->whereIn('section_id', $studentSectionIds)
                  ->orWhereNull('section_id'); // Include assignments not tied to specific sections
            });
        }

        // Apply filters
        if ($request->filled('subject_id')) {
            $query->where('subject_id', $request->subject_id);
        }

        if ($request->filled('status')) {
            switch ($request->status) {
                case 'due_soon':
                    $query->dueSoon();
                    break;
                case 'overdue':
                    $query->overdue();
                    break;
                case 'completed':
                    $query->whereHas('submissions', function($q) use ($student) {
                        $q->where('student_id', $student->id)
                          ->whereIn('status', ['submitted', 'graded']);
                    });
                    break;
                case 'pending':
                    $query->whereDoesntHave('submissions', function($q) use ($student) {
                        $q->where('student_id', $student->id);
                    });
                    break;
            }
        }

        $assignments = $query->orderBy('due_date', 'asc')->paginate(15);
        $subjects = $student->subjects;

        return view('student.assignments.index', compact('assignments', 'subjects'));
    }

    /**
     * Display the specified assignment
     */
    public function show(Assignment $assignment)
    {
        $user = Auth::user();
        $student = $user->student;

        // Check if student has access to this assignment
        if (!$this->canAccessAssignment($assignment, $student)) {
            abort(403, 'You do not have access to this assignment.');
        }

        $assignment->load(['teacher', 'subject', 'section', 'academicYear', 'semester']);
        
        // Get student's submission if exists
        $submission = $assignment->submissions()
            ->where('student_id', $student->id)
            ->first();

        return view('student.assignments.show', compact('assignment', 'submission'));
    }

    /**
     * Submit assignment
     */
    public function submit(Request $request, Assignment $assignment)
    {
        $user = Auth::user();
        $student = $user->student;

        // Check if student has access to this assignment
        if (!$this->canAccessAssignment($assignment, $student)) {
            abort(403, 'You do not have access to this assignment.');
        }

        // Check if assignment is still open for submission
        if (!$assignment->canSubmit()) {
            if ($assignment->status === 'closed') {
                return redirect()->back()->with('error', 'Ang assignment na ito ay sarado na at hindi na tumatanggap ng mga magpapasa.');
            } elseif ($assignment->is_overdue && !$assignment->allows_late_submission) {
                return redirect()->back()->with('error', 'Ang assignment na ito ay lumipas na sa due date at hindi na tumatanggap ng mga magpapasa.');
            }
            return redirect()->back()->with('error', 'Ang assignment na ito ay hindi na tumatanggap ng mga magpapasa.');
        }

        // Check if student already submitted
        $existingSubmission = $assignment->submissions()
            ->where('student_id', $student->id)
            ->first();

        if ($existingSubmission) {
            return redirect()->back()->with('error', 'You have already submitted this assignment.');
        }

        $validator = Validator::make($request->all(), [
            'comments' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $file = $request->file('submission_file');
        if (! $file || $file->getError() === UPLOAD_ERR_NO_FILE) {
            return redirect()->back()
                ->withErrors(['submission_file' => 'Please choose a file to upload.'])
                ->withInput();
        }

        if (! $file->isValid()) {
            $maxMb = $assignment->submissionMaxMb();

            return redirect()->back()
                ->withErrors([
                    'submission_file' => "The submission file failed to upload. Use a file under {$maxMb}MB and try again.",
                ])
                ->withInput();
        }

        $allowed = $assignment->submissionAllowedExtensions();
        $ext = strtolower($file->getClientOriginalExtension());
        if (! in_array($ext, $allowed, true)) {
            $labels = $assignment->submissionAllowedLabels();

            return redirect()->back()
                ->withErrors([
                    'submission_file' => $assignment->requires_file_upload
                        ? "This assignment only accepts: {$labels}. Your file (.{$ext}) is not allowed."
                        : "Invalid file type. Allowed: {$labels}.",
                ])
                ->withInput();
        }

        $maxMb = $assignment->submissionMaxMb();
        if ($file->getSize() > $maxMb * 1024 * 1024) {
            return redirect()->back()
                ->withErrors(['submission_file' => "The file size must not exceed {$maxMb}MB."])
                ->withInput();
        }

        try {
            $safeName = time() . '_' . preg_replace('/[^A-Za-z0-9._-]/', '_', $file->getClientOriginalName());
            $filePath = $file->storeAs('assignment-submissions', $safeName, 'public');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['submission_file' => 'Could not save the file. Please try again.'])
                ->withInput();
        }

        // Calculate if submission is late
        $isLate = now() > $assignment->dueDateTime;
        $lateMinutes = $isLate ? now()->diffInMinutes($assignment->dueDateTime) : 0;

        $submissionData = [
            'assignment_id' => $assignment->id,
            'student_id' => $student->id,
            'file_path' => $filePath,
            'file_name' => $file->getClientOriginalName(),
            'file_type' => $file->getClientOriginalExtension(),
            'comments' => $request->comments,
            'status' => $isLate ? 'late' : 'submitted',
            'submitted_at' => now(),
            'is_late' => $isLate,
            'late_minutes' => $lateMinutes,
            'max_score' => $assignment->max_score,
            'is_active' => true
        ];

        AssignmentSubmission::create($submissionData);

        return redirect()->route('student.assignments.show', $assignment)
            ->with('success', 'Assignment submitted successfully.');
    }

    /**
     * View student's submission
     */
    public function submission(Assignment $assignment)
    {
        $user = Auth::user();
        $student = $user->student;

        // Check if student has access to this assignment
        if (!$this->canAccessAssignment($assignment, $student)) {
            abort(403, 'You do not have access to this assignment.');
        }

        $submission = $assignment->submissions()
            ->where('student_id', $student->id)
            ->first();

        if (!$submission) {
            return redirect()->route('student.assignments.show', $assignment)
                ->with('error', 'No submission found for this assignment.');
        }

        return view('student.assignments.submission', compact('assignment', 'submission'));
    }

    /**
     * Check if student can access the assignment
     */
    private function canAccessAssignment(Assignment $assignment, $student)
    {
        // Check if student is enrolled in the subject
        $enrolledSubjectIds = $student->enrollments()->pluck('subject_id');
        
        return $enrolledSubjectIds->contains($assignment->subject_id);
    }
}
