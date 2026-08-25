<?php

namespace App\Support;

use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

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
            'sidebarParentUsers' => $role === 'Teacher' ? self::teacherParents($user) : collect(),
            'sidebarEnrollments' => $role === 'Student' ? self::studentEnrollments($user) : collect(),
            'sidebarChildren' => $role === 'Parent' ? self::parentChildren($user) : collect(),
        ];
    }

    public static function forgetForUser(User $user): void
    {
        Cache::forget('sidebar.teacher.parents.'.$user->id);
        Cache::forget('sidebar.student.classes.'.$user->id);
        Cache::forget('sidebar.parent.children.'.$user->id);
    }

    private static function teacherParents(User $user): Collection
    {
        return Cache::remember('sidebar.teacher.parents.'.$user->id, 90, function () use ($user) {
            try {
                $teacher = $user->teacher;
                if (!$teacher) {
                    return collect();
                }

                return User::query()
                    ->select('users.id', 'users.name')
                    ->where('users.role_name', 'Parent')
                    ->whereIn('users.email', function ($query) use ($teacher) {
                        $query->select('students.parent_email')
                            ->from('students')
                            ->whereNotNull('students.parent_email')
                            ->whereIn('students.id', function ($sub) use ($teacher) {
                                $sub->select('enrollments.student_id')
                                    ->from('enrollments')
                                    ->whereIn('enrollments.subject_id', function ($inner) use ($teacher) {
                                        $inner->select('class_schedules.subject_id')
                                            ->from('class_schedules')
                                            ->where('class_schedules.teacher_id', $teacher->id);
                                    });
                            });
                        if (SafeSchema::columnExists('students', 'deleted_at')) {
                            $query->whereNull('students.deleted_at');
                        }
                    })
                    ->orderBy('users.name')
                    ->get();
            } catch (\Throwable $e) {
                return collect();
            }
        });
    }

    private static function studentEnrollments(User $user): Collection
    {
        return Cache::remember('sidebar.student.classes.'.$user->id, 90, function () use ($user) {
            $student = $user->student;
            if (!$student) {
                return collect();
            }

            $query = $student->enrollments()->with('subject');
            if (SafeSchema::columnExists('enrollments', 'status')) {
                $query->where('status', 'active');
            }

            return $query->get();
        });
    }

    private static function parentChildren(User $user): Collection
    {
        return Cache::remember('sidebar.parent.children.'.$user->id, 90, function () use ($user) {
            return Student::query()
                ->select('id', 'first_name', 'last_name')
                ->where('parent_email', $user->email)
                ->orderBy('first_name')
                ->get();
        });
    }
}
