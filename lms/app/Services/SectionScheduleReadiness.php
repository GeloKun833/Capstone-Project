<?php

namespace App\Services;

use App\Models\AcademicYear;
use App\Models\ClassSchedule;
use App\Models\Section;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class SectionScheduleReadiness
{
    public const NOT_PLOTTED = 'not_plotted';

    public const INCOMPLETE = 'incomplete';

    public const CONFLICT = 'conflict';

    public const NOT_FINALIZED = 'not_finalized';

    public const SUBJECTS_MISSING = 'subjects_missing';

    public const READY = 'ready';

    public function assess(int $sectionId, ?int $academicYearId = null): array
    {
        $year = $academicYearId
            ? AcademicYear::query()->find($academicYearId)
            : AcademicYear::active();
        $yearId = $year?->id;
        $yearName = $year?->displayName();

        $section = Section::query()->with('subjects')->find($sectionId);
        $requiredIds = $section
            ? $section->subjects->pluck('id')->map(fn ($id) => (int) $id)->unique()->values()
            : collect();
        if ($requiredIds->isEmpty() && $section?->grade_level) {
            $requiredIds = app(GradeSubjectCatalogService::class)
                ->subjectsForGrade($section->grade_level)
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->unique()
                ->values();
        }
        $sectionLabel = $this->sectionLabel($section);
        $base = [
            'section_label' => $sectionLabel,
            'required_subjects' => $requiredIds->count(),
            'scheduled_subjects' => 0,
        ];

        if ($requiredIds->isEmpty()) {
            return $this->payload(self::SUBJECTS_MISSING, $yearName, $base);
        }

        if (! $yearId) {
            return $this->payload(self::NOT_PLOTTED, $yearName, $base);
        }

        $schedules = ClassSchedule::query()
            ->where('section_id', $sectionId)
            ->where('academic_year_id', $yearId)
            ->get();

        $active = $schedules->where('is_active', true)->values();
        $coveredIds = $active->pluck('subject_id')->map(fn ($id) => (int) $id)->unique()->values();
        $base['scheduled_subjects'] = $requiredIds->intersect($coveredIds)->count();

        if ($schedules->isEmpty() || $active->isEmpty()) {
            return $this->payload(self::NOT_PLOTTED, $yearName, $base);
        }

        foreach ($active as $schedule) {
            if (! $this->rowIsComplete($schedule)) {
                return $this->payload(self::INCOMPLETE, $yearName, $base);
            }
        }

        if ($requiredIds->diff($coveredIds)->isNotEmpty()) {
            return $this->payload(self::INCOMPLETE, $yearName, $base);
        }

        if ($this->hasConflicts($active, $yearId)) {
            return $this->payload(self::CONFLICT, $yearName, $base);
        }

        return $this->payload(self::READY, $yearName, $base);
    }

    public function enrollmentAllowed(int $sectionId): bool
    {
        return $this->yearEnrollmentBlock() === null
            && (bool) $this->assess($sectionId)['enrollment_allowed'];
    }

    public function yearEnrollmentBlock(): ?string
    {
        return app(EnrollmentReadiness::class)->studentMessage();
    }

    public function gradeHasEnrollableSection(string $gradeLevel): bool
    {
        if ($this->yearEnrollmentBlock() !== null) {
            return false;
        }

        $sections = app(GradeSubjectCatalogService::class)->sectionsForGrade($gradeLevel);

        foreach ($sections as $section) {
            if ((bool) $this->assess((int) $section->id)['enrollment_allowed']) {
                return true;
            }
        }

        return false;
    }

    public function enrollmentBlockMessage(int $sectionId, ?string $childName = null): ?string
    {
        $yearBlock = $this->yearEnrollmentBlock();
        if ($yearBlock) {
            return $yearBlock;
        }

        $status = $this->assess($sectionId);
        if ($status['enrollment_allowed']) {
            return null;
        }

        $sentence = match ($status['status']) {
            self::INCOMPLETE => 'The class schedule for '.($status['section_label'] ?: 'this section').' is incomplete. '.(int) ($status['scheduled_subjects'] ?? 0).' of '.(int) ($status['required_subjects'] ?? 0).' required subjects have been scheduled.',
            self::NOT_FINALIZED => 'The schedule for this section has not been finalized.',
            self::CONFLICT => 'This section has unresolved schedule conflicts.',
            self::SUBJECTS_MISSING => 'This section does not have its subjects assigned yet.',
            default => 'Enrollment is not yet available because the class schedule has not been completed. Please complete and finalize the class schedule first.',
        };

        if ($childName) {
            return 'Enrollment unavailable for '.$childName.'. '.$sentence;
        }

        return 'Enrollment unavailable. '.$sentence;
    }

    public function gradeBlockMessage(string $gradeLevel, ?string $childName = null): string
    {
        $yearBlock = $this->yearEnrollmentBlock();
        if ($yearBlock) {
            return $yearBlock;
        }

        $sentence = 'The schedule for '.$gradeLevel.' has not been configured or finalized yet. Please wait until the class schedule has been finalized before proceeding with enrollment.';

        if ($childName) {
            return 'Enrollment unavailable for '.$childName.'. '.$sentence;
        }

        return 'Enrollment unavailable. '.$sentence;
    }

    private function rowIsComplete(ClassSchedule $schedule): bool
    {
        if (! $schedule->section_id || ! $schedule->subject_id || ! $schedule->teacher_id || ! $schedule->day_of_week) {
            return false;
        }

        if (! $schedule->start_time || ! $schedule->end_time) {
            return false;
        }

        $start = Carbon::parse($schedule->start_time)->format('H:i:s');
        $end = Carbon::parse($schedule->end_time)->format('H:i:s');

        return $start < $end;
    }

    private function hasConflicts(Collection $sectionSchedules, int $academicYearId): bool
    {
        $days = $sectionSchedules->pluck('day_of_week')->filter()->unique()->values();
        if ($days->isEmpty()) {
            return true;
        }

        $others = ClassSchedule::query()
            ->where('is_active', true)
            ->where('academic_year_id', $academicYearId)
            ->whereIn('day_of_week', $days->all())
            ->whereNotIn('id', $sectionSchedules->pluck('id')->filter()->all())
            ->get();

        $candidates = $sectionSchedules->concat($others);

        foreach ($sectionSchedules as $schedule) {
            foreach ($candidates as $existing) {
                if ((int) $existing->id === (int) $schedule->id) {
                    continue;
                }

                if (ClassSchedule::sharesOverlappingSlot(
                    $existing,
                    (string) $schedule->day_of_week,
                    (string) $schedule->start_time,
                    (string) $schedule->end_time,
                    (int) $schedule->teacher_id,
                    (int) $schedule->section_id,
                    $schedule->room_id
                )) {
                    return true;
                }
            }
        }

        return false;
    }

    private function sectionLabel(?Section $section): string
    {
        if (! $section) {
            return 'this section';
        }

        $name = trim((string) $section->name);
        $grade = trim((string) $section->grade_level);
        if ($name === '') {
            return $grade !== '' ? $grade : 'this section';
        }

        if ($grade !== '' && ! str_contains($name, $grade)) {
            return $grade.' '.$name;
        }

        return $name;
    }

    private function payload(string $status, ?string $academicYear, array $extra = []): array
    {
        $labels = [
            self::NOT_PLOTTED => 'Not Plotted',
            self::INCOMPLETE => 'Schedule Incomplete',
            self::CONFLICT => 'Schedule Conflict',
            self::NOT_FINALIZED => 'Not Finalized',
            self::SUBJECTS_MISSING => 'Subjects Not Assigned',
            self::READY => 'Ready for Enrollment',
        ];

        return array_merge([
            'section_label' => null,
            'required_subjects' => 0,
            'scheduled_subjects' => 0,
        ], $extra, [
            'status' => $status,
            'label' => $labels[$status],
            'enrollment_allowed' => $status === self::READY,
            'academic_year' => $academicYear,
            'message' => $labels[$status],
        ]);
    }
}
