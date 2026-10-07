<?php

namespace App\Services;

use App\Models\Curriculum;
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

        $yearId = \App\Models\AcademicYear::active()?->id;

        return \App\Models\Section::query()
            ->whereIn('grade_level', $aliases)
            ->when($yearId, fn ($query) => $query->forAcademicYear($yearId), fn ($query) => $query->whereRaw('1 = 0'))
            ->with('adviser')
            ->orderBy('name')
            ->get();
    }

    /**
     * All sections grouped by grade for admin catalog.
     */
    public function sectionsGroupedByGrade(?int $academicYearId = null)
    {
        $yearId = $academicYearId ?: \App\Models\AcademicYear::active()?->id;
        $grouped = \App\Models\Section::query()
            ->when($yearId, fn ($query) => $query->forAcademicYear($yearId), fn ($query) => $query->whereRaw('1 = 0'))
            ->with([
                'adviser',
                'subjects',
                'teachers' => function ($query) use ($yearId) {
                    if ($yearId) {
                        $query->where('section_teacher.academic_year_id', $yearId);
                    }
                },
            ])
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
                    $subject = Subject::create([
                        'subject_name' => $name,
                        'class' => $grade,
                    ]);
                    $this->syncNewSubject($subject);
                    $created++;
                }
            }
        });

        return $created;
    }

    /**
     * Put a newly added catalog subject on that grade's curriculum, classes, and student enrollments.
     */
    public function syncNewSubject(Subject $subject): int
    {
        $grade = trim((string) ($subject->class ?? ''));
        if ($grade === '' || ! $subject->id) {
            return 0;
        }

        $curriculum = Curriculum::firstOrCreate(
            ['grade_level' => $grade],
            ['description' => $grade.' curriculum linked to the subject catalog.']
        );
        $curriculum->subjects()->syncWithoutDetaching([$subject->id]);

        foreach ($this->sectionsForGrade($grade) as $section) {
            $section->subjects()->syncWithoutDetaching([$subject->id]);
        }

        return $this->enrollGradeStudentsInSubject($subject);
    }

    /**
     * When admin adds a subject, enroll existing students in that grade so it shows on My Classes.
     */
    public function enrollGradeStudentsInSubject(Subject $subject): int
    {
        $grade = trim((string) ($subject->class ?? ''));
        if ($grade === '' || !$subject->id) {
            return 0;
        }

        $aliases = self::gradeAliases($grade);

        $studentIds = collect();

        // Students already enrolled in other subjects of this grade
        $siblingSubjectIds = Subject::query()
            ->whereIn('class', $aliases)
            ->where('id', '!=', $subject->id)
            ->pluck('id');

        if ($siblingSubjectIds->isNotEmpty()) {
            $studentIds = $studentIds->merge(
                \App\Models\Enrollment::query()
                    ->whereIn('subject_id', $siblingSubjectIds)
                    ->where('status', 'active')
                    ->pluck('student_id')
            );
        }

        // Students whose profile / application grade matches
        $studentIds = $studentIds->merge(
            \App\Models\Student::query()
                ->where(function ($q) use ($aliases) {
                    $q->whereIn('year_level', $aliases)
                        ->orWhereIn('class', $aliases)
                        ->orWhereHas('enrollmentApplication', function ($app) use ($aliases) {
                            $app->whereIn('grade_level_applying_for', $aliases);
                        });
                })
                ->pluck('id')
        )->unique()->filter()->values();

        if ($studentIds->isEmpty()) {
            \Illuminate\Support\Facades\Cache::forget('catalog.subjects.'.md5($grade));
            return 0;
        }

        [$defaultYearId, $defaultSemesterId] = $this->resolveDefaultAcademicPeriod();

        $created = 0;
        $students = \App\Models\Student::with('user')->whereIn('id', $studentIds)->get();

        foreach ($students as $student) {
            $period = \App\Models\Enrollment::query()
                ->where('student_id', $student->id)
                ->where('status', 'active')
                ->when($siblingSubjectIds->isNotEmpty(), fn ($q) => $q->whereIn('subject_id', $siblingSubjectIds))
                ->latest('id')
                ->first(['academic_year_id', 'semester_id']);

            $yearId = $period->academic_year_id ?? $defaultYearId;
            $semesterId = $period->semester_id ?? $defaultSemesterId;

            if (!$yearId || !$semesterId) {
                continue;
            }

            $exists = \App\Models\Enrollment::query()
                ->where('student_id', $student->id)
                ->where('subject_id', $subject->id)
                ->where('academic_year_id', $yearId)
                ->where('semester_id', $semesterId)
                ->exists();

            if ($exists) {
                continue;
            }

            \App\Models\Enrollment::create([
                'student_id' => $student->id,
                'subject_id' => $subject->id,
                'academic_year_id' => $yearId,
                'semester_id' => $semesterId,
                'enrollment_date' => now(),
                'status' => 'active',
            ]);
            $created++;

            \Illuminate\Support\Facades\Cache::forget('student.dashboard.v2.'.$student->id);
            if ($student->user) {
                \App\Support\SidebarMenu::forgetForUser($student->user);
            }
        }

        \Illuminate\Support\Facades\Cache::forget('catalog.subjects.'.md5($grade));

        return $created;
    }

    /**
     * Ensure every student in a grade has enrollments for every catalog subject in that grade.
     */
    public function syncMissingEnrollmentsForGrade(string $grade): int
    {
        $total = 0;
        foreach ($this->subjectsForGrade($grade) as $subject) {
            $total += $this->enrollGradeStudentsInSubject($subject);
        }

        return $total;
    }

    /**
     * @return array{0: ?int, 1: ?int}
     */
    private function resolveDefaultAcademicPeriod(): array
    {
        $year = \App\Models\AcademicYear::current();

        if (!$year) {
            return [null, null];
        }

        $semester = \App\Models\Semester::query()
            ->where('academic_year_id', $year->id)
            ->orderBy('id')
            ->first();

        return [$year->id, $semester?->id];
    }
}
