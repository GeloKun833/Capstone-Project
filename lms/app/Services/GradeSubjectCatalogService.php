<?php

namespace App\Services;

use App\Models\Subject;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class GradeSubjectCatalogService
{
    /**
     * Canonical grade labels used across admin + enrollment portal.
     */
    public static function gradeLevels(): array
    {
        return [
            'Nursery',
            'Kindergarten',
            'Grade 1',
            'Grade 2',
            'Grade 3',
            'Grade 4',
            'Grade 5',
            'Grade 6',
            'Grade 7',
            'Grade 8',
            'Grade 9',
            'Grade 10',
        ];
    }

    /**
     * Accept common aliases so admin/enrollment grade labels still match.
     */
    public static function gradeAliases(?string $gradeLevel): array
    {
        $gradeLevel = trim((string) $gradeLevel);
        if ($gradeLevel === '') {
            return [];
        }

        $aliases = [
            'Kindergarten' => ['Kindergarten', 'Kinder'],
            'Kinder' => ['Kindergarten', 'Kinder'],
        ];

        return $aliases[$gradeLevel] ?? [$gradeLevel];
    }

    /**
     * Sections for a grade (same table used by enrollment Block Section).
     */
    public function sectionsForGrade(?string $gradeLevel)
    {
        $aliases = self::gradeAliases($gradeLevel);
        if (empty($aliases)) {
            return collect();
        }

        return \App\Models\Section::query()
            ->whereIn('grade_level', $aliases)
            ->with('adviser')
            ->orderBy('name')
            ->get();
    }

    /**
     * All sections grouped by grade for admin catalog.
     */
    public function sectionsGroupedByGrade()
    {
        $grouped = \App\Models\Section::query()
            ->with('adviser')
            ->orderBy('grade_level')
            ->orderBy('name')
            ->get()
            ->groupBy(function ($section) {
                $grade = trim((string) $section->grade_level);
                if (in_array($grade, ['Kinder', 'Kindergarten'], true)) {
                    return 'Kindergarten';
                }
                return $grade ?: 'Unassigned';
            });

        $ordered = collect();
        foreach (self::gradeLevels() as $grade) {
            if ($grouped->has($grade)) {
                $ordered->put($grade, $grouped->get($grade));
            }
        }
        foreach ($grouped as $grade => $sections) {
            if (!$ordered->has($grade)) {
                $ordered->put($grade, $sections);
            }
        }

        return $ordered;
    }

    /**
     * Normalize legacy grade labels on sections (e.g. Kinder → Kindergarten).
     */
    public function normalizeSectionGradeLabels(): int
    {
        $updated = 0;
        $map = [
            'Kinder' => 'Kindergarten',
            'kinder' => 'Kindergarten',
            'KINDER' => 'Kindergarten',
        ];

        foreach ($map as $from => $to) {
            $updated += \App\Models\Section::where('grade_level', $from)->update(['grade_level' => $to]);
        }

        return $updated;
    }

    /**
     * Subjects for a grade from the admin-managed subjects table.
     * `subjects.class` is the grade label (e.g. "Grade 1").
     */
    public function subjectsForGrade(?string $gradeLevel): Collection
    {
        $gradeLevel = trim((string) $gradeLevel);
        if ($gradeLevel === '') {
            return collect();
        }

        return Subject::query()
            ->where('class', $gradeLevel)
            ->orderBy('subject_name')
            ->get();
    }

    /**
     * Subject names only (for simple previews / legacy callers).
     */
    public function subjectNamesForGrade(?string $gradeLevel): array
    {
        return $this->subjectsForGrade($gradeLevel)
            ->pluck('subject_name')
            ->values()
            ->all();
    }

    /**
     * All subjects grouped by grade/class for admin catalog view.
     */
    public function subjectsGroupedByGrade(): Collection
    {
        $grouped = Subject::query()
            ->orderBy('class')
            ->orderBy('subject_name')
            ->get()
            ->groupBy(fn (Subject $s) => $s->class ?: 'Unassigned');

        // Prefer canonical grade order, then any extras
        $ordered = collect();
        foreach (self::gradeLevels() as $grade) {
            if ($grouped->has($grade)) {
                $ordered->put($grade, $grouped->get($grade));
            }
        }
        foreach ($grouped as $grade => $subjects) {
            if (!$ordered->has($grade)) {
                $ordered->put($grade, $subjects);
            }
        }

        return $ordered;
    }

    /**
     * Seed subjects from config/grade_subjects.php when a grade has none yet.
     * Does not overwrite admin-created subjects.
     */
    public function importMissingFromConfig(?string $gradeLevel = null): int
    {
        $config = config('grade_subjects', []);
        $created = 0;

        $grades = $gradeLevel
            ? [$gradeLevel => $config[$gradeLevel] ?? []]
            : $config;

        DB::transaction(function () use ($grades, &$created) {
            foreach ($grades as $grade => $names) {
                if (!is_array($names)) {
                    continue;
                }
                foreach ($names as $name) {
                    $name = trim((string) $name);
                    if ($name === '') {
                        continue;
                    }
                    $exists = Subject::where('class', $grade)
                        ->where('subject_name', $name)
                        ->exists();
                    if ($exists) {
                        continue;
                    }
                    Subject::create([
                        'subject_name' => $name,
                        'class' => $grade,
                    ]);
                    $created++;
                }
            }
        });

        return $created;
    }
}
