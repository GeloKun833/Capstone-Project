<?php

namespace App\Services;

use App\Models\AcademicYear;
use App\Models\EnrollmentApplication;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class AccountYearAccess
{
    /**
     * Students and parents may sign in only while the academic year
     * their account was created for is the current year.
     * Returns a message when access is denied.
     */
    public function denies(?User $user): ?string
    {
        if (! $user || ! in_array($user->role_name, [User::ROLE_STUDENT, User::ROLE_PARENT], true)) {
            return null;
        }

        $yearId = $this->creationYearId($user);
        $active = AcademicYear::active();
        if ($active && $yearId && (int) $active->id === (int) $yearId) {
            return null;
        }

        $year = $yearId ? AcademicYear::query()->find($yearId) : null;
        if ($year) {
            return 'This account was created for '.$year->displayName().'. Students and parents can sign in only while that year is the current academic year.';
        }

        return 'Students and parents can sign in only during the academic year their account was created for.';
    }

    public function creationYearId(User $user): ?int
    {
        if ($user->account_academic_year_id) {
            return (int) $user->account_academic_year_id;
        }

        $yearId = $this->resolveYearId($user);
        if ($yearId) {
            $user->forceFill(['account_academic_year_id' => $yearId])->save();
        }

        return $yearId;
    }

    private function resolveYearId(User $user): ?int
    {
        if ($user->role_name === User::ROLE_PARENT) {
            $fromApplication = EnrollmentApplication::withTrashed()
                ->where('parent_user_id', $user->id)
                ->whereNotNull('academic_year_id')
                ->orderBy('id')
                ->value('academic_year_id');
            if ($fromApplication) {
                return (int) $fromApplication;
            }

            $fromSection = DB::table('students')
                ->join('student_section_assignments', 'student_section_assignments.student_id', '=', 'students.id')
                ->where('students.parent_user_id', $user->id)
                ->whereNotNull('student_section_assignments.academic_year_id')
                ->orderBy('student_section_assignments.id')
                ->value('student_section_assignments.academic_year_id');
            if ($fromSection) {
                return (int) $fromSection;
            }
        }

        if ($user->role_name === User::ROLE_STUDENT) {
            $student = Student::query()->where('user_id', $user->user_id)->first();
            if ($student?->enrollment_application_id) {
                $fromApplication = EnrollmentApplication::withTrashed()
                    ->whereKey($student->enrollment_application_id)
                    ->value('academic_year_id');
                if ($fromApplication) {
                    return (int) $fromApplication;
                }
            }
            if ($student) {
                $fromSection = DB::table('student_section_assignments')
                    ->where('student_id', $student->id)
                    ->whereNotNull('academic_year_id')
                    ->orderBy('id')
                    ->value('academic_year_id');
                if ($fromSection) {
                    return (int) $fromSection;
                }
            }
        }

        if (! $user->created_at) {
            return null;
        }

        $fromDate = AcademicYear::query()
            ->whereDate('start_date', '<=', $user->created_at)
            ->whereDate('end_date', '>=', $user->created_at)
            ->orderBy('start_date')
            ->value('id');

        return $fromDate ? (int) $fromDate : null;
    }
}
