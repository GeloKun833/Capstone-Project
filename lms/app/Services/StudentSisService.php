<?php

namespace App\Services;

use App\Models\EnrollmentDocument;
use App\Models\Grade;
use App\Models\QuarterlyGrade;
use App\Models\Student;
use App\Models\StudentGpa;
use App\Models\StudentPromotion;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class StudentSisService
{
    public function getUserAndStudent(string $userId): array
    {
        $user = User::where('user_id', $userId)->firstOrFail();
        $student = Student::where('user_id', $userId)->with([
            'enrollments.subject',
            'enrollments.academicYear',
            'enrollments.semester',
            'grades.subject',
            'attendances',
            'enrollmentApplication',
            'promotions.fromAcademicYear',
            'promotions.toAcademicYear',
            'promotions.promoter',
        ])->firstOrFail();

        return [$user, $student];
    }

    public function buildProfile(string $userId): array
    {
        [$user, $student] = $this->getUserAndStudent($userId);

        $currentEnrollments = $student->enrollments()
            ->with(['subject', 'academicYear', 'semester'])
            ->where('status', 'active')
            ->get();

        $grades = Grade::where('student_id', $student->id)
            ->with(['subject', 'academicYear', 'semester', 'component', 'teacher'])
            ->orderByDesc('created_at')
            ->get();

        $quarterlyGrades = QuarterlyGrade::where('student_id', $student->id)
            ->with(['subject', 'teacher', 'academicYear'])
            ->orderByDesc('updated_at')
            ->get();

        $gpaRecords = StudentGpa::where('student_id', $student->id)
            ->with(['academicYear', 'semester'])
            ->orderByDesc('created_at')
            ->get();

        $totalAttendance = $student->attendances()->count();
        $presentCount = $student->attendances()->where('status', 'present')->count();
        $absentCount = $student->attendances()->where('status', 'absent')->count();
        $attendancePercentage = $totalAttendance > 0
            ? round(($presentCount / $totalAttendance) * 100, 1)
            : 0;

        $sectionAssignment = DB::table('student_section_assignments')
            ->join('sections', 'student_section_assignments.section_id', '=', 'sections.id')
            ->join('academic_years', 'student_section_assignments.academic_year_id', '=', 'academic_years.id')
            ->join('semesters', 'student_section_assignments.semester_id', '=', 'semesters.id')
            ->where('student_section_assignments.student_id', $student->id)
            ->select(
                'sections.*',
                'academic_years.name as academic_year_name',
                'semesters.name as semester_name'
            )
            ->orderByDesc('student_section_assignments.created_at')
            ->first();

        $promotionHistory = StudentPromotion::where('student_id', $student->id)
            ->with(['fromAcademicYear', 'toAcademicYear', 'promoter'])
            ->orderByDesc('promotion_date')
            ->get();

        $enrollmentDocuments = collect();
        if ($student->enrollment_application_id) {
            $enrollmentDocuments = EnrollmentDocument::where(
                'enrollment_application_id',
                $student->enrollment_application_id
            )->get();
        }

        return compact(
            'user',
            'student',
            'currentEnrollments',
            'grades',
            'quarterlyGrades',
            'gpaRecords',
            'totalAttendance',
            'presentCount',
            'absentCount',
            'attendancePercentage',
            'sectionAssignment',
            'promotionHistory',
            'enrollmentDocuments'
        ) + ['currentGPA' => $gpaRecords->first()];
    }

    public function searchStudents(?string $query, int $limit = 20)
    {
        return Student::query()
            ->when($query, function ($builder) use ($query) {
                $builder->where(function ($q) use ($query) {
                    $q->where('first_name', 'like', "%{$query}%")
                        ->orWhere('last_name', 'like', "%{$query}%")
                        ->orWhere('email', 'like', "%{$query}%")
                        ->orWhere('user_id', 'like', "%{$query}%");
                });
            })
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->limit($limit)
            ->get();
    }

    public function searchTeachers(?string $query, int $limit = 20)
    {
        return User::query()
            ->where('role_name', 'Teacher')
            ->when($query, function ($builder) use ($query) {
                $builder->where(function ($q) use ($query) {
                    $q->where('name', 'like', "%{$query}%")
                        ->orWhere('email', 'like', "%{$query}%")
                        ->orWhere('user_id', 'like', "%{$query}%");
                });
            })
            ->orderBy('name')
            ->limit($limit)
            ->get();
    }
}
