<?php

namespace App\Services;

use App\Models\Activity;
use App\Models\ActivitySubmission;
use App\Models\Attendance;
use App\Models\Enrollment;
use App\Models\Grade;
use App\Models\QuarterlyGrade;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class ParentPortalService
{
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

        $gradeRecords = $grades->orderBy('created_at', 'desc')->get();

        if ($gradeRecords->isNotEmpty()) {
            return $gradeRecords;
        }

        return QuarterlyGrade::with(['subject', 'teacher'])
            ->where('student_id', $child->id)
            ->where('academic_year_id', $academicYear->id)
            ->get()
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

        return $query->orderBy('date', 'desc')->get();
    }

    public function getChildActivities(Student $child, $academicYear, $semester): Collection
    {
        if (!$academicYear) {
            return collect();
        }

        $enrolledSubjectIds = Enrollment::where('student_id', $child->id)
            ->where('status', 'active')
            ->pluck('subject_id');

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

        $enrolledSubjectIds = Enrollment::where('student_id', $child->id)
            ->where('status', 'active')
            ->pluck('subject_id');

        return ActivitySubmission::with(['activity.lesson.subject'])
            ->where('student_id', $child->id)
            ->whereHas('activity.lesson', function ($query) use ($enrolledSubjectIds, $academicYear, $semester) {
                $query->whereIn('subject_id', $enrolledSubjectIds)
                    ->where('academic_year_id', $academicYear->id);
                if ($semester) {
                    $query->where('semester_id', $semester->id);
                }
            })
            ->orderBy('submitted_at', 'desc')
            ->get();
    }
}
