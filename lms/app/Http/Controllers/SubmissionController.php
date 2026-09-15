<?php

namespace App\Http\Controllers;

use App\Models\Lesson;
use App\Models\Activity;
use App\Models\ActivitySubmission;
use App\Models\ActivityGrade;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Support\SafeUpload;

class SubmissionController extends Controller
{
    public function index(Lesson $lesson, Activity $activity)
    {
        // Check if teacher owns this lesson
        if (Auth::user()->role_name === 'Teacher') {
            $teacher = Auth::user()->teacher;
            if ($teacher && $lesson->teacher_id !== $teacher->id) {
                abort(403, 'Unauthorized action.');
            }
        }

        $submissions = $activity->submissions()->with(['student'])->orderBy('created_at', 'desc')->get();

        // Keep status in sync if a score was saved but status stayed "submitted"
        foreach ($submissions as $submission) {
            if ($submission->total_score !== null && $submission->status !== 'graded') {
                $submission->status = 'graded';
                $submission->save();
            }
        }

        return view('activities.submissions', compact('lesson', 'activity', 'submissions'));
    }

    public function store(Request $request, Lesson $lesson, Activity $activity)
    {
        // Check if student is enrolled in this lesson
        $student = Auth::user()->student;
        if (!$student) {
            return redirect()->back()->with('error', 'Student profile not found.');
        }

        // Check if student is enrolled in the lesson's section
        $isEnrolled = $student->sections()->where('section_id', $lesson->section_id)->exists();
        if (!$isEnrolled) {
            return redirect()->back()->with('error', 'You are not enrolled in this lesson.');
        }

        // Check if submission already exists
        $existingSubmission = $activity->submissions()->where('student_id', $student->id)->first();
        if ($existingSubmission) {
            return redirect()->back()->with('error', 'You have already submitted this activity.');
        }

        $request->validate([
            'file' => 'required|file|max:10240|mimes:pdf,docx,ppt,pptx,jpg,jpeg,png',
            'comments' => 'nullable|string|max:1000',
        ], [
            'file.mimes' => 'Please upload a PDF, Word (DOCX), PPT, PPTX, or image file.',
        ]);

        try {
            DB::beginTransaction();

            $stored = SafeUpload::store($request->file('file'), 'submissions/'.$activity->id);
            $filePath = $stored['path'];
            $fileName = $stored['original_name'];

            $submission = ActivitySubmission::create([
                'activity_id' => $activity->id,
                'student_id' => $student->id,
                'file_path' => $filePath,
                'file_name' => $fileName,
                'comments' => $request->comments,
                'status' => 'submitted',
                'submitted_at' => now(),
            ]);

            DB::commit();

            return redirect()->back()->with('success', 'Submission uploaded successfully!');

        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Error uploading submission: ' . $e->getMessage());
        }
    }

    public function show(Lesson $lesson, Activity $activity, ActivitySubmission $submission)
    {
        // Check if teacher owns this lesson or student owns this submission
        if (Auth::user()->role_name === 'Teacher') {
            $teacher = Auth::user()->teacher;
            if ($teacher && $lesson->teacher_id !== $teacher->id) {
                abort(403, 'Unauthorized action.');
            }
        } elseif (Auth::user()->role_name === 'Student') {
            $student = Auth::user()->student;
            if ($student && $submission->student_id !== $student->id) {
                abort(403, 'Unauthorized action.');
            }
        }

        $submission->load(['student', 'grades.rubric']);

        return view('activities.view-submission', compact('lesson', 'activity', 'submission'));
    }

    public function gradeSubmission(Lesson $lesson, Activity $activity, ActivitySubmission $submission)
    {
        // Check if teacher owns this lesson
        if (Auth::user()->role_name === 'Teacher') {
            $teacher = Auth::user()->teacher;
            if ($teacher && $lesson->teacher_id !== $teacher->id) {
                abort(403, 'Unauthorized action.');
            }
        }

        $rubrics = $activity->rubrics()->where('is_active', true)->orderBy('weight', 'desc')->get();
        $isEditing = false;
        $existingScores = collect();

        return view('activities.grade-submission', compact('lesson', 'activity', 'submission', 'rubrics', 'isEditing', 'existingScores'));
    }

    public function storeGrade(Request $request, Lesson $lesson, Activity $activity, ActivitySubmission $submission)
    {
        // Check if teacher owns this lesson
        if (Auth::user()->role_name === 'Teacher') {
            $teacher = Auth::user()->teacher;
            if ($teacher && $lesson->teacher_id !== $teacher->id) {
                abort(403, 'Unauthorized action.');
            }
        }

        $hasRubrics = $activity->rubrics()->where('is_active', true)->exists();

        if ($hasRubrics) {
            $request->validate([
                'scores' => 'required|array',
                'scores.*' => 'required|numeric|min:0',
                'feedback' => 'nullable|string|max:2000',
                'total_score' => 'required|numeric|min:0',
                'max_possible_score' => 'required|numeric|min:1',
                'percentage' => 'required|numeric|min:0|max:100',
                'letter_grade' => 'required|string|max:5',
            ]);
        } else {
            $request->validate([
                'total_score' => 'required|numeric|min:0',
                'max_possible_score' => 'required|numeric|min:1',
                'feedback' => 'nullable|string|max:2000',
            ]);
        }

        try {
            DB::beginTransaction();

            if ($hasRubrics) {
                ActivityGrade::where('submission_id', $submission->id)->delete();

                foreach ($request->scores as $rubricId => $score) {
                    ActivityGrade::create([
                        'submission_id' => $submission->id,
                        'rubric_id' => $rubricId,
                        'score' => $score,
                        'graded_by' => Auth::id(),
                        'graded_at' => now(),
                    ]);
                }

                $totalScore = (float) $request->total_score;
                $maxPossible = (float) $request->max_possible_score;
                $percentage = (float) $request->percentage;
                $letterGrade = $request->letter_grade;
            } else {
                $totalScore = (float) $request->total_score;
                $maxPossible = (float) $request->max_possible_score;
                if ($totalScore > $maxPossible) {
                    DB::rollBack();
                    return redirect()->back()->withInput()->withErrors([
                        'total_score' => 'Score cannot be higher than the maximum score.',
                    ]);
                }
                $percentage = $maxPossible > 0 ? round(($totalScore / $maxPossible) * 100, 1) : 0;
                $letterGrade = $this->letterGradeFromPercentage($percentage);
            }

            $submission->update([
                'total_score' => $totalScore,
                'max_possible_score' => $maxPossible,
                'percentage' => $percentage,
                'letter_grade' => $letterGrade,
                'feedback' => $request->feedback,
                'status' => 'graded',
                'graded_at' => now(),
                'graded_by' => Auth::id(),
            ]);

            DB::commit();

            return redirect()->route('lessons.activities.submissions', [$lesson, $activity])
                ->with('success', 'Grade saved successfully!');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->withInput()->with('error', 'Error saving grade: ' . $e->getMessage());
        }
    }

    protected function letterGradeFromPercentage(float $percentage): string
    {
        if ($percentage >= 90) {
            return 'A';
        }
        if ($percentage >= 85) {
            return 'B+';
        }
        if ($percentage >= 80) {
            return 'B';
        }
        if ($percentage >= 75) {
            return 'C+';
        }
        if ($percentage >= 70) {
            return 'C';
        }
        if ($percentage >= 65) {
            return 'D+';
        }
        if ($percentage >= 60) {
            return 'D';
        }

        return 'F';
    }

    public function viewGrade(Lesson $lesson, Activity $activity, ActivitySubmission $submission)
    {
        // Check if teacher owns this lesson or student owns this submission
        if (Auth::user()->role_name === 'Teacher') {
            $teacher = Auth::user()->teacher;
            if ($teacher && $lesson->teacher_id !== $teacher->id) {
                abort(403, 'Unauthorized action.');
            }
        } elseif (Auth::user()->role_name === 'Student') {
            $student = Auth::user()->student;
            if ($student && $submission->student_id !== $student->id) {
                abort(403, 'Unauthorized action.');
            }
        }

        $submission->load(['student', 'grades.rubric', 'gradedBy']);

        return view('activities.view-grade', compact('lesson', 'activity', 'submission'));
    }

    public function editGrade(Lesson $lesson, Activity $activity, ActivitySubmission $submission)
    {
        // Check if teacher owns this lesson
        if (Auth::user()->role_name === 'Teacher') {
            $teacher = Auth::user()->teacher;
            if ($teacher && $lesson->teacher_id !== $teacher->id) {
                abort(403, 'Unauthorized action.');
            }
        }

        $rubrics = $activity->rubrics()->where('is_active', true)->orderBy('weight', 'desc')->get();
        $submission->load(['student', 'grades.rubric']);
        $isEditing = true;
        $existingScores = $submission->grades->pluck('score', 'rubric_id');

        return view('activities.grade-submission', compact(
            'lesson',
            'activity',
            'submission',
            'rubrics',
            'isEditing',
            'existingScores'
        ));
    }

    public function updateGrade(Request $request, Lesson $lesson, Activity $activity, ActivitySubmission $submission)
    {
        // Same save logic as initial grading
        return $this->storeGrade($request, $lesson, $activity, $submission);
    }

    public function destroy(Lesson $lesson, Activity $activity, ActivitySubmission $submission)
    {
        // Check if teacher owns this lesson or student owns this submission
        if (Auth::user()->role_name === 'Teacher') {
            $teacher = Auth::user()->teacher;
            if ($teacher && $lesson->teacher_id !== $teacher->id) {
                abort(403, 'Unauthorized action.');
            }
        } elseif (Auth::user()->role_name === 'Student') {
            $student = Auth::user()->student;
            if ($student && $submission->student_id !== $student->id) {
                abort(403, 'Unauthorized action.');
            }
        }

        try {
            DB::beginTransaction();

            // Delete file
            if ($submission->file_path) {
                Storage::disk('public')->delete($submission->file_path);
            }

            // Delete grades
            ActivityGrade::where('submission_id', $submission->id)->delete();

            // Delete submission
            $submission->delete();

            DB::commit();

            return redirect()->back()->with('success', 'Submission deleted successfully!');

        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Error deleting submission: ' . $e->getMessage());
        }
    }

    public function exportSubmissions(Lesson $lesson, Activity $activity)
    {
        // Check if teacher owns this lesson
        if (Auth::user()->role_name === 'Teacher') {
            $teacher = Auth::user()->teacher;
            if ($teacher && $lesson->teacher_id !== $teacher->id) {
                abort(403, 'Unauthorized action.');
            }
        }

        $submissions = $activity->submissions()->with(['student', 'grades.rubric'])->get();

        // Generate CSV
        $filename = 'submissions_' . $activity->id . '_' . date('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($submissions, $activity) {
            $file = fopen('php://output', 'w');
            
            // Headers
            fputcsv($file, [
                'Student Name',
                'Student Email',
                'Submitted Date',
                'Status',
                'Total Score',
                'Max Possible Score',
                'Percentage',
                'Letter Grade',
                'Feedback'
            ]);

            // Data
            foreach ($submissions as $submission) {
                fputcsv($file, [
                    $submission->student->first_name . ' ' . $submission->student->last_name,
                    $submission->student->email,
                    $submission->created_at->format('Y-m-d H:i:s'),
                    $submission->status,
                    $submission->total_score ?? '-',
                    $submission->max_possible_score ?? '-',
                    $submission->percentage ? $submission->percentage . '%' : '-',
                    $submission->letter_grade ?? '-',
                    $submission->feedback ?? '-'
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
} 