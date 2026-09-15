<?php

namespace App\Support;

use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class SidebarMenu
{
    /**
     * Sidebar data used on almost every authenticated page.
     * Cached so Teacher/Student/Parent layouts do not hit MySQL on every click.
     */
    public static function forUser(?User $user): array
    {
        $empty = [
            'sidebarParentUsers' => collect(),
            'sidebarEnrollments' => collect(),
            'sidebarChildren' => collect(),
        ];

        if (!$user) {
            return $empty;
        }

        $role = session('role_name') ?: $user->role_name;

        return [
            'sidebarParentUsers' => ($role === 'Teacher' && request()->routeIs('chat.*'))
                ? self::teacherParents($user)
                : collect(),
            'sidebarEnrollments' => $role === 'Student' ? self::studentEnrollments($user) : collect(),
            'sidebarChildren' => $role === 'Parent' ? self::parentChildren($user) : collect(),
        ];
    }

    public static function forgetForUser(User $user): void
    {
        Cache::forget('sidebar.teacher.parents.'.$user->id);
        Cache::forget('sidebar.student.classes.'.$user->id);
        Cache::forget('sidebar.parent.children.'.$user->id);
        Cache::forget('sidebar.parent.children.v2.'.$user->id);
    }

    private static function teacherParents(User $user): Collection
    {
        return Cache::remember('sidebar.teacher.parents.v2.'.$user->id, 180, function () use ($user) {
            try {
                $teacher = $user->teacher;
                if (!$teacher) {
                    return collect();
                }

                // Two indexed queries beat one nested correlated subquery on remote MySQL.
                $parentEmails = DB::table('class_schedules')
                    ->join('enrollments', 'enrollments.subject_id', '=', 'class_schedules.subject_id')
                    ->join('students', 'students.id', '=', 'enrollments.student_id')
                    ->where('class_schedules.teacher_id', $teacher->id)
                    ->whereNotNull('students.parent_email')
                    ->when(SafeSchema::columnExists('students', 'deleted_at'), fn ($q) => $q->whereNull('students.deleted_at'))
                    ->distinct()
                    ->limit(200)
                    ->pluck('students.parent_email')
                    ->filter()
                    ->values();

                if ($parentEmails->isEmpty()) {
                    return collect();
                }

                return User::query()
                    ->select('users.id', 'users.name')
                    ->where('users.role_name', 'Parent')
                    ->whereIn('users.email', $parentEmails->all())
                    ->orderBy('users.name')
                    ->get();
            } catch (\Throwable $e) {
                return collect();
            }
        });
    }

    private static function studentEnrollments(User $user): Collection
    {
        return Cache::remember('sidebar.student.classes.'.$user->id, 180, function () use ($user) {
            $student = $user->student;
            if (!$student) {
                return collect();
            }

            // Reuse dashboard payload when warm to avoid a duplicate enrollments round-trip.
            $dash = Cache::get('student.dashboard.v2.'.$student->id);
            if (is_array($dash) && isset($dash['enrollments'])) {
                return $dash['enrollments'];
            }

            return $student->enrollments()
                ->with('subject:id,subject_name,class')
                ->where('status', 'active')
                ->whereHas('subject')
                ->get();
        });
    }

    private static function parentChildren(User $user): Collection
    {
        return Cache::remember('sidebar.parent.children.v2.'.$user->id, 180, function () use ($user) {
            return Student::query()
                ->select('id', 'first_name', 'last_name')
                ->forParent($user)
                ->orderBy('first_name')
                ->get();
        });
    }
}
