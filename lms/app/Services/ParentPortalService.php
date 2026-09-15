<?php

namespace App\Services;

use App\Models\AcademicYear;
use App\Models\Activity;
use App\Models\ActivityGrade;
use App\Models\ActivitySubmission;
use App\Models\AssignmentSubmission;
use App\Models\Attendance;
use App\Models\Enrollment;
use App\Models\Grade;
use App\Models\QuarterlyGrade;
use App\Models\Student;
use App\Models\StudentGpa;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class ParentPortalService
{
    public function getChildrenForParent(User $parent): Collection
    {
        return Student::query()->forParent($parent)
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();
    }

    public function resolveChildForParent(User $parent, int $childId): Student
    {
        return Student::query()->forParent($parent)
            ->where('id', $childId)
            ->firstOrFail();
    }

    public function getChildOverview(Student $child): array
    {
        $enrollments = $child->enrollments()
            ->with(['subject', 'academicYear', 'semester'])
            ->where('status', 'active')
            ->get();

        $totalGrades = Grade::where('student_id', $child->id)->count();
        $averageGrade = Grade::where('student_id', $child->id)->avg('percentage') ?? 0;

        $totalAttendance = Attendance::where('student_id', $child->id)->count();
        $presentAttendance = Attendance::where('student_id', $child->id)
            ->whereIn('status', ['present', 'late'])
            ->count();
        $attendancePercentage = $totalAttendance > 0
            ? round(($presentAttendance / $totalAttendance) * 100, 2)
            : 0;

        $currentGpa = StudentGpa::where('student_id', $child->id)
            ->with(['academicYear', 'semester'])
            ->orderByDesc('created_at')
            ->first();

        $recentGrades = Grade::where('student_id', $child->id)
            ->with('subject')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        $recentAttendance = Attendance::where('student_id', $child->id)
            ->with('subject')
            ->orderByDesc('date')
            ->limit(8)
            ->get();

        return compact(
            'enrollments',
            'totalGrades',
            'averageGrade',
            'totalAttendance',
            'presentAttendance',
            'attendancePercentage',
            'currentGpa',
            'recentGrades',
            'recentAttendance'
        );
    }

    public function getChildGrades(Student $child, $academicYear, $semester): Collection
    {
        if (!$academicYear) {
            return collect();
        }

        $grades = Grade::with(['subject', 'component', 'teacher'])
            ->where('student_id', $child->id)
            ->where('academic_year_id', $academicYear->id);

        if ($semester) {
            $grades->where('semester_id', $semester->id);
        }

        $gradeRecords = $grades->orderByDesc('created_at')->get();

        if ($gradeRecords->isNotEmpty()) {
            return $gradeRecords;
        }

        return $this->getChildQuarterlyGrades($child, $academicYear->id)
            ->map(function ($qg) {
                return (object) [
                    'subject' => $qg->subject,
                    'component' => (object) ['name' => 'Final Grade (Quarterly)'],
                    'score' => $qg->final_grade,
                    'max_score' => 100,
                    'percentage' => $qg->final_grade,
                    'teacher' => $qg->teacher,
                    'created_at' => $qg->updated_at,
                    'remarks' => $qg->remarks,
                ];
            });
    }

    public function getChildQuarterlyGrades(Student $child, ?int $academicYearId = null): Collection
    {
        $query = QuarterlyGrade::with(['subject', 'teacher', 'academicYear'])
            ->where('student_id', $child->id);

        if ($academicYearId) {
            $query->where('academic_year_id', $academicYearId);
        }

        return $query->orderByDesc('updated_at')->get();
    }

    public function getChildAttendance(Student $child, Request $request): Collection
    {
        $query = Attendance::with(['subject'])
            ->where('student_id', $child->id);

        if ($request->filled('date')) {
            $query->whereDate('date', $request->input('date'));
        } elseif ($request->filled('month')) {
            $query->whereMonth('date', $request->input('month'));
            if ($request->filled('year')) {
                $query->whereYear('date', $request->input('year'));
            }
        } else {
            $query->whereMonth('date', now()->month)
                ->whereYear('date', now()->year);
        }

        return $query->orderByDesc('date')->get();
    }

    public function getChildActivities(Student $child, $academicYear, $semester): Collection
    {
        if (!$academicYear) {
            return collect();
        }

        $enrolledSubjectIds = $this->getEnrolledSubjectIds($child);

        return Activity::with(['lesson.subject'])
            ->whereHas('lesson', function ($query) use ($enrolledSubjectIds, $academicYear, $semester) {
                $query->whereIn('subject_id', $enrolledSubjectIds)
                    ->where('academic_year_id', $academicYear->id);
                if ($semester) {
                    $query->where('semester_id', $semester->id);
                }
            })
            ->orderBy('due_date', 'asc')
            ->get();
    }

    public function getChildSubmissions(Student $child, $academicYear, $semester): Collection
    {
        if (!$academicYear) {
            return collect();
        }

        $enrolledSubjectIds = $this->getEnrolledSubjectIds($child);

        return ActivitySubmission::with(['activity.lesson.subject', 'grades'])
            ->where('student_id', $child->id)
            ->whereHas('activity.lesson', function ($query) use ($enrolledSubjectIds, $academicYear, $semester) {
                $query->whereIn('subject_id', $enrolledSubjectIds)
                    ->where('academic_year_id', $academicYear->id);
                if ($semester) {
                    $query->where('semester_id', $semester->id);
                }
            })
            ->orderByDesc('submitted_at')
            ->get();
    }

    public function getChildAssignmentSubmissions(Student $child): Collection
    {
        return AssignmentSubmission::with(['assignment.subject', 'assignment.teacher'])
            ->where('student_id', $child->id)
            ->orderByDesc('submitted_at')
            ->get();
    }

    public function getChildFeedback(Student $child): Collection
    {
        $assignmentFeedback = AssignmentSubmission::with(['assignment.subject'])
            ->where('student_id', $child->id)
            ->where(function ($query) {
                $query->whereNotNull('teacher_feedback')
                    ->orWhereNotNull('score');
            })
            ->get()
            ->map(function ($submission) {
                return (object) [
                    'type' => 'Assignment',
                    'title' => $submission->assignment->title ?? 'Assignment',
                    'subject' => $submission->assignment->subject->subject_name ?? 'N/A',
                    'score' => $submission->score,
                    'max_score' => $submission->max_score,
                    'feedback' => $submission->teacher_feedback,
                    'status' => $submission->status,
                    'date' => $submission->graded_at ?? $submission->submitted_at,
                ];
            });

        $activityFeedback = ActivityGrade::with(['submission.activity.lesson.subject', 'rubric'])
            ->whereHas('submission', function ($query) use ($child) {
                $query->where('student_id', $child->id);
            })
            ->get()
            ->map(function ($grade) {
                $activity = $grade->submission->activity ?? null;

                return (object) [
                    'type' => 'Activity',
                    'title' => $activity->title ?? 'Activity',
                    'subject' => $activity?->lesson?->subject?->subject_name ?? 'N/A',
                    'score' => $grade->score,
                    'max_score' => $grade->rubric->max_score ?? null,
                    'feedback' => $grade->feedback,
                    'status' => $grade->submission->status ?? 'graded',
                    'date' => $grade->graded_at ?? $grade->updated_at,
                ];
            });

        return $assignmentFeedback->concat($activityFeedback)->sortByDesc('date')->values();
    }

    public function getAcademicYear(?int $academicYearId = null): ?AcademicYear
    {
        if ($academicYearId) {
            return AcademicYear::find($academicYearId);
        }

        return AcademicYear::orderByDesc('name')->first();
    }

    protected function getEnrolledSubjectIds(Student $child): Collection
    {
        $fromEnrollments = Enrollment::where('student_id', $child->id)
            ->where('status', 'active')
            ->pluck('subject_id');

        $fromSections = $child->sections()->pluck('sections.id');
        $fromSchedules = \App\Models\Subject::whereHas('classSchedules', function ($query) use ($fromSections) {
            $query->whereIn('section_id', $fromSections)->where('is_active', true);
        })->pluck('id');

        return $fromEnrollments->merge($fromSchedules)->unique()->filter();
    }
}
