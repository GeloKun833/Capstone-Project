<?php

namespace App\Services;

use App\Models\AcademicYear;
use App\Models\Section;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class EnrollmentReadiness
{
    public function isAvailableFor(AcademicYear $year): bool
    {
        $active = AcademicYear::active();
        if (! $active || (int) $active->id !== (int) $year->id || ! $year->isCurrent()) {
            return false;
        }

        return (bool) $this->checklist($year)['available'];
    }

    public function studentMessage(?AcademicYear $year = null): ?string
    {
        $report = $this->checklist($year);
        if ($report['available']) {
            return null;
        }

        return $report['student_message'];
    }

    public function openBlock(AcademicYear $year): ?string
    {
        if (! $year->isCurrent()) {
            return 'Open enrollment only after this academic year is the active year.';
        }

        $report = $this->checklist($year, true);
        $rows = collect($report['rows'])->keyBy('key');

        if (! ($rows['sections']['ready'] ?? false) || ! ($rows['subjects']['ready'] ?? false) || ! ($rows['grade_levels']['ready'] ?? false)) {
            return 'Enrollment cannot be opened yet. Please complete the academic setup first.';
        }

        if (! ($rows['teachers']['ready'] ?? false)) {
            return 'Enrollment cannot be opened yet. Please complete the required teacher assignments and class schedule first.';
        }

        if (! ($rows['class_schedule']['ready'] ?? false) || ! ($rows['schedule_validation']['ready'] ?? false) || ! ($rows['schedule_finalization']['ready'] ?? false)) {
            return 'Enrollment cannot be opened yet. Please complete and finalize the class schedule first.';
        }

        if (! ($rows['enrollment_start']['ready'] ?? false) || ! ($rows['enrollment_end']['ready'] ?? false)) {
            return 'Set the enrollment start and end dates for this academic year before opening enrollment.';
        }

        return null;
    }

    /**
     * @return array{available:bool,student_message:?string,year:?AcademicYear,rows:array<int,array<string,mixed>>}
     */
    public function checklist(?AcademicYear $year = null, bool $ignoreStatusAndDates = false): array
    {
        $year = $year ?: AcademicYear::active();
        if (! $year || ! $year->isCurrent()) {
            return $this->report(null, [[
                'key' => 'academic_year',
                'label' => 'Academic Year',
                'status' => 'Not Ready',
                'ready' => false,
                'detail' => 'Enrollment is unavailable because there is no active Academic Year.',
            ]], 'Enrollment is currently unavailable because the school is still preparing the academic setup.');
        }

        $yearId = (int) $year->id;
        $sections = Section::query()->with('subjects')->forAcademicYear($yearId)->orderBy('grade_level')->orderBy('name')->get();
        $schedule = app(SectionScheduleReadiness::class);
        $assessments = $sections->map(fn (Section $section) => $schedule->assess((int) $section->id, $yearId));

        $gradesReady = $sections->isNotEmpty() && $sections->every(function (Section $section) {
            $aliases = GradeSubjectCatalogService::gradeAliases($section->grade_level);

            return count(array_intersect($aliases, GradeSubjectCatalogService::gradeLevels())) > 0;
        });
        $catalogSubjects = DB::table('subjects')->whereIn('class', GradeSubjectCatalogService::gradeLevels())->count();
        $sectionGrades = $sections->pluck('grade_level')->filter()->unique();
        $subjectsReady = $catalogSubjects > 0 && ($sectionGrades->isEmpty() || $sectionGrades->every(function ($grade) {
            return app(GradeSubjectCatalogService::class)->subjectsForGrade($grade)->isNotEmpty();
        }));

        $teachersReady = $sections->isNotEmpty() && $this->teachersCoverSections($sections, $yearId);
        $scheduleStates = $assessments->pluck('status');
        $completeStates = [SectionScheduleReadiness::CONFLICT, SectionScheduleReadiness::NOT_FINALIZED, SectionScheduleReadiness::READY];
        $validatedStates = [SectionScheduleReadiness::NOT_FINALIZED, SectionScheduleReadiness::READY];
        $scheduleReady = $sections->isNotEmpty() && $scheduleStates->every(fn ($status) => in_array($status, $completeStates, true));
        $validationReady = $sections->isNotEmpty() && $scheduleStates->every(fn ($status) => in_array($status, $validatedStates, true));
        $finalReady = $sections->isNotEmpty() && $scheduleStates->every(fn ($status) => $status === SectionScheduleReadiness::READY);

        $incomplete = $assessments->first(fn ($row) => $row['status'] === SectionScheduleReadiness::INCOMPLETE);
        $scheduleDetail = 'Enrollment is not yet available because the class schedule has not been completed. Please complete and finalize the class schedule first.';
        if ($incomplete) {
            $scheduleDetail = 'The class schedule for '.($incomplete['section_label'] ?: 'this section').' is incomplete. '.(int) $incomplete['scheduled_subjects'].' of '.(int) $incomplete['required_subjects'].' required subjects have been scheduled.';
        } elseif ($scheduleReady) {
            $scheduleDetail = 'Every current section has a schedule for each required subject.';
        }

        $start = $year->enrollment_starts_at;
        $end = $year->enrollment_ends_at;
        $today = Carbon::today();
        $datesReady = $start && $end;
        $beforeStart = $datesReady && $today->lt($start->copy()->startOfDay());
        $afterEnd = $datesReady && $today->gt($end->copy()->endOfDay());
        $withinPeriod = $datesReady && ! $beforeStart && ! $afterEnd;

        $rows = [
            $this->row('academic_year', 'Academic Year', true, 'Ready', $year->displayName()),
            $this->row('grade_levels', 'Grade Levels', $gradesReady, $gradesReady ? 'Ready' : 'Not Ready', $gradesReady ? 'Grade levels are configured for this academic year.' : 'No grade levels have sections for Academic Year '.$year->displayName().'.'),
            $this->row('subjects', 'Subjects/Curriculum', $subjectsReady, $subjectsReady ? 'Ready' : 'Not Ready', $subjectsReady ? 'Required subjects are configured.' : 'Required subjects or curriculum are not configured for this academic year.'),
            $this->row('sections', 'Sections', $sections->isNotEmpty(), $sections->isNotEmpty() ? 'Ready' : 'Not Ready', $sections->isNotEmpty() ? $sections->count().' section(s) for this academic year.' : 'No sections have been created for Academic Year '.$year->displayName().'.'),
            $this->row('teachers', 'Teacher Assignments', $teachersReady, $teachersReady ? 'Ready' : 'Not Ready', $teachersReady ? 'Teachers are assigned for this academic year.' : 'Required teacher assignments are incomplete for Academic Year '.$year->displayName().'.'),
            $this->row('class_schedule', 'Class Schedule', $scheduleReady, $scheduleReady ? 'Ready' : ($incomplete ? 'Incomplete' : 'Not Ready'), $scheduleDetail),
            $this->row('schedule_validation', 'Schedule Validation', $validationReady, $validationReady ? 'Ready' : 'Not Ready', $validationReady ? 'Schedule conflicts are resolved.' : 'Schedule validation has not passed for Academic Year '.$year->displayName().'.'),
            $this->row('schedule_finalization', 'Schedule Finalization', $finalReady, $finalReady ? 'Ready' : 'Not Ready', $finalReady ? 'Class schedules are complete for this academic year.' : 'The class schedule is not complete yet.'),
            $this->row('enrollment_start', 'Enrollment Start Date', (bool) $start, $start ? 'Configured' : 'Not Ready', $start ? $start->format('F j, Y') : 'Enrollment start date is not set.'),
            $this->row('enrollment_end', 'Enrollment End Date', (bool) $end, $end ? 'Configured' : 'Not Ready', $end ? $end->format('F j, Y') : 'Enrollment end date is not set.'),
            $this->row('enrollment_period', 'Current Date within Enrollment Period', $withinPeriod, $this->periodStatus($beforeStart, $afterEnd, $datesReady), $this->periodDetail($year, $beforeStart, $afterEnd, $withinPeriod)),
            $this->row('enrollment_status', 'Enrollment Status', (bool) $year->enrollment_open, $year->enrollment_open ? 'OPEN' : 'CLOSED', $year->enrollment_open ? 'Enrollment status is open.' : 'Enrollment is currently closed for Academic Year '.$year->displayName().'.'),
        ];

        $setupReady = collect($rows)->whereNotIn('key', ['enrollment_period', 'enrollment_status'])->every(fn ($row) => $row['ready']);
        $available = $setupReady && $withinPeriod && (bool) $year->enrollment_open;
        if ($ignoreStatusAndDates) {
            $available = false;
        }

        return $this->report($year, $rows, $this->studentReason($year, $rows, $available, $beforeStart, $afterEnd), $available);
    }

    private function teachersCoverSections($sections, int $yearId): bool
    {
        $subjectIds = DB::table('subject_teacher')->where('academic_year_id', $yearId)->pluck('subject_id')->map(fn ($id) => (int) $id)->all();
        $sectionIds = DB::table('section_teacher')->where('academic_year_id', $yearId)->pluck('section_id')->map(fn ($id) => (int) $id)->all();
        $grades = DB::table('teacher_grade_level')->where('academic_year_id', $yearId)->pluck('grade_level')->filter()->all();

        foreach ($sections as $section) {
            $required = $section->subjects->pluck('id')->map(fn ($id) => (int) $id)->unique()->values();
            if ($required->isEmpty()) {
                $required = app(GradeSubjectCatalogService::class)
                    ->subjectsForGrade($section->grade_level)
                    ->pluck('id')
                    ->map(fn ($id) => (int) $id)
                    ->unique()
                    ->values();
            }
            if ($required->isEmpty() || $required->diff($subjectIds)->isNotEmpty()) {
                return false;
            }

            $gradeMatch = false;
            foreach ($grades as $grade) {
                $aliases = GradeSubjectCatalogService::gradeAliases($grade);
                if (in_array($section->grade_level, $aliases, true)) {
                    $gradeMatch = true;
                    break;
                }
            }

            if (! in_array((int) $section->id, $sectionIds, true) && ! $gradeMatch) {
                return false;
            }
        }

        return true;
    }

    private function periodStatus(bool $beforeStart, bool $afterEnd, bool $datesReady): string
    {
        if (! $datesReady) {
            return 'Not Ready';
        }
        if ($beforeStart) {
            return 'Not Yet';
        }
        if ($afterEnd) {
            return 'Ended';
        }

        return 'Ready';
    }

    private function periodDetail(AcademicYear $year, bool $beforeStart, bool $afterEnd, bool $withinPeriod): string
    {
        if ($beforeStart && $year->enrollment_starts_at) {
            return 'Enrollment is not yet open. Enrollment will begin on '.$year->enrollment_starts_at->format('F j, Y').'.';
        }
        if ($afterEnd) {
            return 'The enrollment period has ended. Please contact the school administration for assistance.';
        }
        if ($withinPeriod) {
            return 'The current date is inside the enrollment period.';
        }

        return 'The enrollment period is not configured for Academic Year '.$year->displayName().'.';
    }

    private function studentReason(AcademicYear $year, array $rows, bool $available, bool $beforeStart, bool $afterEnd): ?string
    {
        if ($available) {
            return null;
        }

        $byKey = collect($rows)->keyBy('key');
        $setupKeys = ['grade_levels', 'subjects', 'sections', 'teachers'];
        foreach ($setupKeys as $key) {
            if (! ($byKey[$key]['ready'] ?? false)) {
                return 'Enrollment is currently unavailable because the school is still preparing the academic setup.';
            }
        }

        foreach (['class_schedule', 'schedule_validation', 'schedule_finalization'] as $key) {
            if (! ($byKey[$key]['ready'] ?? false)) {
                return 'Enrollment is currently unavailable because the class schedule has not yet been finalized.';
            }
        }

        if ($beforeStart) {
            return 'Enrollment is not yet open. Please check the enrollment schedule.';
        }
        if ($afterEnd) {
            return 'The enrollment period has ended. Please contact the school administration for assistance.';
        }
        if (! ($byKey['enrollment_start']['ready'] ?? false) || ! ($byKey['enrollment_end']['ready'] ?? false)) {
            return 'Enrollment is currently unavailable because the school is still preparing the academic setup.';
        }
        if (! ($byKey['enrollment_status']['ready'] ?? false)) {
            return 'Enrollment is currently closed for Academic Year '.$year->displayName().'.';
        }

        return 'Enrollment is currently unavailable because the school is still preparing the academic setup.';
    }

    private function row(string $key, string $label, bool $ready, string $status, string $detail): array
    {
        return compact('key', 'label', 'ready', 'status', 'detail');
    }

    private function report(?AcademicYear $year, array $rows, ?string $studentMessage, bool $available = false): array
    {
        $rows[] = $this->row(
            'availability',
            'Enrollment Availability',
            $available,
            $available ? 'AVAILABLE' : 'UNAVAILABLE',
            $available ? 'Enrollment is available.' : ($studentMessage ?: 'Enrollment is not available.')
        );

        return [
            'available' => $available,
            'student_message' => $available ? null : $studentMessage,
            'year' => $year,
            'rows' => $rows,
        ];
    }
}
