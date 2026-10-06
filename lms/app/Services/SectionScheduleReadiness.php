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

        if ($requiredIds->isEmpty()) {
            return $this->payload(self::SUBJECTS_MISSING, $yearName);
        }

        if (! $yearId) {
            return $this->payload(self::NOT_PLOTTED, $yearName);
        }

        $schedules = ClassSchedule::query()
            ->where('section_id', $sectionId)
            ->where('academic_year_id', $yearId)
            ->get();

        $active = $schedules->where('is_active', true)->values();
        if ($schedules->isEmpty() || $active->isEmpty()) {
            return $this->payload(self::NOT_PLOTTED, $yearName);
        }

        foreach ($active as $schedule) {
            if (! $this->rowIsComplete($schedule)) {
                return $this->payload(self::INCOMPLETE, $yearName);
            }
        }

        $coveredIds = $active->pluck('subject_id')->map(fn ($id) => (int) $id)->unique()->values();
        if ($requiredIds->diff($coveredIds)->isNotEmpty()) {
            return $this->payload(self::INCOMPLETE, $yearName);
        }

        if ($this->hasConflicts($active, $yearId)) {
            return $this->payload(self::CONFLICT, $yearName);
        }

        if ($active->contains(fn ($schedule) => ! $schedule->is_finalized)) {
            return $this->payload(self::NOT_FINALIZED, $yearName);
        }

        return $this->payload(self::READY, $yearName);
    }

    public function enrollmentAllowed(int $sectionId): bool
    {
        return $this->yearEnrollmentBlock() === null
            && (bool) $this->assess($sectionId)['enrollment_allowed'];
    }

    public function yearEnrollmentBlock(): ?string
    {
        $year = AcademicYear::active();
        if (! $year) {
            return 'Enrollment is unavailable because there is no active Academic Year.';
        }

        if (! $year->enrollment_open) {
            return 'Enrollment is currently closed for Academic Year '.$year->displayName().'.';
        }

        return null;
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
            self::INCOMPLETE => 'The schedule for this section is incomplete.',
            self::NOT_FINALIZED => 'The schedule for this section has not been finalized.',
            self::CONFLICT => 'This section has unresolved schedule conflicts.',
            self::SUBJECTS_MISSING => 'This section does not have its subjects assigned yet.',
            default => 'The schedule for this section has not been configured or finalized yet. Please wait until the class schedule has been finalized before proceeding with enrollment.',
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

    private function payload(string $status, ?string $academicYear): array
    {
        $labels = [
            self::NOT_PLOTTED => 'Not Plotted',
            self::INCOMPLETE => 'Schedule Incomplete',
            self::CONFLICT => 'Schedule Conflict',
            self::NOT_FINALIZED => 'Not Finalized',
            self::SUBJECTS_MISSING => 'Subjects Not Assigned',
            self::READY => 'Ready for Enrollment',
        ];

        return [
            'status' => $status,
            'label' => $labels[$status],
            'enrollment_allowed' => $status === self::READY,
            'academic_year' => $academicYear,
            'message' => $labels[$status],
        ];
    }
}
