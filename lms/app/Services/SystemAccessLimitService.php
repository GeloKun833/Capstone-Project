<?php

namespace App\Services;

use App\Models\SchoolSetting;
use App\Models\Student;
use App\Models\User;
use App\Services\GradeSubjectCatalogService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class SystemAccessLimitService
{
    public const CACHE_KEY = 'system.access_limits.snapshot';

    public function settings(): SchoolSetting
    {
        return SchoolSetting::getSettings();
    }

    public function enabled(): bool
    {
        return (bool) $this->settings()->access_limits_enabled;
    }

    public function message(): string
    {
        $custom = trim((string) ($this->settings()->access_limits_message ?? ''));
        if ($custom !== '') {
            return $custom;
        }

        return 'The system is under limited access due to high load / maintenance. Please try again later or contact the school admin.';
    }

    public function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
        SchoolSetting::clearSettingsCache();
    }

    /**
     * Admin and Registrar always bypass limits.
     */
    public function isAllowed(?User $user): bool
    {
        if (!$user) {
            return true;
        }

        if (in_array($user->role_name, ['Admin', 'Registrar'], true)) {
            return true;
        }

        if (!$this->enabled()) {
            return true;
        }

        $allowedIds = $this->allowedUserIds();

        return in_array((int) $user->id, $allowedIds, true);
    }

    /**
     * Stable allow-list based on configured quotas.
     * Teachers: first N active teachers by user id.
     * Students: first N active students per grade by user id.
     * Parents: first N parent accounts per grade (linked via parent_email).
     *
     * @return int[]
     */
    public function allowedUserIds(): array
    {
        if (!$this->enabled()) {
            return [];
        }

        return Cache::remember(self::CACHE_KEY, 300, function () {
            $settings = $this->settings();
            $grades = $this->normalizedGrades($settings->access_allowed_grades);
            $ids = [];

            // null = unlimited for that role; 0 = none; N = first N (by user id)
            if ($settings->max_teachers === null) {
                $ids = array_merge($ids, $this->allowedTeacherIds(null));
            } elseif ((int) $settings->max_teachers > 0) {
                $ids = array_merge($ids, $this->allowedTeacherIds((int) $settings->max_teachers));
            }

            if ($settings->max_students_per_grade === null) {
                foreach ($grades as $grade) {
                    $ids = array_merge($ids, $this->allowedStudentIdsForGrade($grade, null));
                }
            } elseif ((int) $settings->max_students_per_grade > 0) {
                $maxStudents = (int) $settings->max_students_per_grade;
                foreach ($grades as $grade) {
                    $ids = array_merge($ids, $this->allowedStudentIdsForGrade($grade, $maxStudents));
                }
            }

            if ($settings->max_parents_per_grade === null) {
                foreach ($grades as $grade) {
                    $ids = array_merge($ids, $this->allowedParentIdsForGrade($grade, null));
                }
            } elseif ((int) $settings->max_parents_per_grade > 0) {
                $maxParents = (int) $settings->max_parents_per_grade;
                foreach ($grades as $grade) {
                    $ids = array_merge($ids, $this->allowedParentIdsForGrade($grade, $maxParents));
                }
            }

            return array_values(array_unique(array_map('intval', $ids)));
        });
    }

    /**
     * Preview counts for the Settings UI.
     */
    public function preview(): array
    {
        $settings = $this->settings();
        $grades = $this->normalizedGrades($settings->access_allowed_grades);
        $teacherTotal = User::query()
            ->where('role_name', 'Teacher')
            ->whereRaw('LOWER(status) = ?', ['active'])
            ->count();

        $teachersAllowed = $this->quotaAllowedCount($settings->max_teachers, $teacherTotal);

        $perGrade = [];
        foreach ($grades as $grade) {
            $studentTotal = $this->studentsInGradeQuery($grade)->count();
            $parentTotal = count($this->parentUserIdsForGrade($grade));
            $perGrade[] = [
                'grade' => $grade,
                'students_total' => $studentTotal,
                'students_allowed' => $this->quotaAllowedCount($settings->max_students_per_grade, $studentTotal),
                'parents_total' => $parentTotal,
                'parents_allowed' => $this->quotaAllowedCount($settings->max_parents_per_grade, $parentTotal),
            ];
        }

        return [
            'enabled' => $this->enabled(),
            'teachers_total' => $teacherTotal,
            'teachers_allowed' => $teachersAllowed,
            'grades' => $perGrade,
            'allowed_user_count' => count($this->allowedUserIds()) + $this->alwaysAllowedCount(),
        ];
    }

    protected function quotaAllowedCount($quota, int $total): int
    {
        if ($quota === null) {
            return $total;
        }
        $quota = (int) $quota;
        if ($quota <= 0) {
            return 0;
        }

        return min($total, $quota);
    }

    protected function alwaysAllowedCount(): int
    {
        return User::query()
            ->whereIn('role_name', ['Admin', 'Registrar'])
            ->whereRaw('LOWER(status) = ?', ['active'])
            ->count();
    }

    protected function normalizedGrades($stored): array
    {
        $all = GradeSubjectCatalogService::gradeLevels();
        if (is_string($stored)) {
            $stored = json_decode($stored, true);
        }
        if (!is_array($stored) || empty($stored)) {
            return $all;
        }

        $selected = array_values(array_intersect($all, $stored));
        return !empty($selected) ? $selected : $all;
    }

    protected function allowedTeacherIds(?int $limit): array
    {
        $query = User::query()
            ->where('role_name', 'Teacher')
            ->whereRaw('LOWER(status) = ?', ['active'])
            ->orderBy('id');

        if ($limit !== null) {
            $query->limit($limit);
        }

        return $query->pluck('id')->map(fn ($id) => (int) $id)->all();
    }

    protected function studentsInGradeQuery(string $grade)
    {
        $aliases = GradeSubjectCatalogService::gradeAliases($grade);
        if (empty($aliases)) {
            $aliases = [$grade];
        }

        return User::query()
            ->where('users.role_name', 'Student')
            ->whereRaw('LOWER(users.status) = ?', ['active'])
            ->whereHas('student', function ($q) use ($aliases) {
                $q->whereIn('year_level', $aliases)
                    ->orWhereIn('class', $aliases);
            });
    }

    protected function allowedStudentIdsForGrade(string $grade, ?int $limit): array
    {
        $query = $this->studentsInGradeQuery($grade)->orderBy('users.id');
        if ($limit !== null) {
            $query->limit($limit);
        }

        return $query->pluck('users.id')->map(fn ($id) => (int) $id)->all();
    }

    /**
     * Parent user ids linked to students in a grade (via parent_email).
     */
    protected function parentUserIdsForGrade(string $grade): array
    {
        $aliases = GradeSubjectCatalogService::gradeAliases($grade);
        if (empty($aliases)) {
            $aliases = [$grade];
        }

        $emails = Student::query()
            ->where(function ($q) use ($aliases) {
                $q->whereIn('year_level', $aliases)->orWhereIn('class', $aliases);
            })
            ->whereNotNull('parent_email')
            ->where('parent_email', '!=', '')
            ->pluck('parent_email')
            ->map(fn ($e) => strtolower(trim($e)))
            ->unique()
            ->values()
            ->all();

        if (empty($emails)) {
            return [];
        }

        return User::query()
            ->where('role_name', 'Parent')
            ->whereRaw('LOWER(status) = ?', ['active'])
            ->whereIn(DB::raw('LOWER(email)'), $emails)
            ->orderBy('id')
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    protected function allowedParentIdsForGrade(string $grade, ?int $limit): array
    {
        $ids = $this->parentUserIdsForGrade($grade);
        if ($limit === null) {
            return $ids;
        }

        return array_slice($ids, 0, $limit);
    }
}
