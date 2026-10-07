<?php

namespace App\Services;

use App\Models\AcademicYear;
use App\Models\ClassSchedule;
use App\Models\Section;
use App\Models\Teacher;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class TeacherDeactivationService
{
    public function normalizeDisabledTeachers(): int
    {
        return User::query()
            ->where('role_name', User::ROLE_TEACHER)
            ->whereRaw("LOWER(TRIM(status)) IN ('disable', 'disabled')")
            ->update(['status' => 'Inactive']);
    }

    public function inactiveTeachersMessage(array $teacherIds): ?string
    {
        if ($teacherIds === []) {
            return null;
        }

        $blocked = Teacher::with('user')
            ->whereIn('id', $teacherIds)
            ->get()
            ->contains(fn (Teacher $teacher) => ! $teacher->user || ! $teacher->user->isActiveAccount());

        return $blocked ? 'Inactive teachers cannot receive new assignments.' : null;
    }

    /**
     * Current-year assignments that must be transferred before deactivation.
     *
     * @return array<int, array<string, mixed>>
     */
    public function assignmentRows(Teacher $teacher, int $yearId): array
    {
        $schedules = ClassSchedule::query()
            ->with(['section:id,name,grade_level,academic_year_id', 'subject:id,subject_name'])
            ->where('teacher_id', $teacher->id)
            ->where('academic_year_id', $yearId)
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get();

        $scheduledSubjectIds = $schedules->pluck('subject_id')->filter()->map(fn ($id) => (int) $id)->all();
        $scheduledSectionIds = $schedules->pluck('section_id')->filter()->map(fn ($id) => (int) $id)->all();

        $rows = [];
        foreach ($schedules as $schedule) {
            $rows[] = [
                'key' => 'schedule:'.$schedule->id,
                'type' => 'schedule',
                'id' => (int) $schedule->id,
                'section' => $schedule->section->name ?? '—',
                'grade' => $schedule->section->grade_level ?? '—',
                'subject' => $schedule->subject->subject_name ?? '—',
                'schedule' => $this->scheduleLabel($schedule),
                'schedule_id' => (int) $schedule->id,
                'subject_id' => (int) $schedule->subject_id,
                'section_id' => (int) $schedule->section_id,
                'checks_conflict' => (bool) $schedule->is_active,
            ];
        }

        $subjects = DB::table('subject_teacher')
            ->join('subjects', 'subjects.id', '=', 'subject_teacher.subject_id')
            ->where('subject_teacher.teacher_id', $teacher->id)
            ->where('subject_teacher.academic_year_id', $yearId)
            ->whereNotIn('subject_teacher.subject_id', $scheduledSubjectIds ?: [0])
            ->orderBy('subjects.subject_name')
            ->get(['subjects.id', 'subjects.subject_name', 'subjects.class']);

        foreach ($subjects as $subject) {
            $rows[] = [
                'key' => 'subject:'.$subject->id,
                'type' => 'subject',
                'id' => (int) $subject->id,
                'section' => '—',
                'grade' => $subject->class ?: '—',
                'subject' => $subject->subject_name,
                'schedule' => 'No schedule',
                'subject_id' => (int) $subject->id,
                'section_id' => null,
                'checks_conflict' => false,
            ];
        }

        $sections = DB::table('section_teacher')
            ->join('sections', 'sections.id', '=', 'section_teacher.section_id')
            ->where('section_teacher.teacher_id', $teacher->id)
            ->where('section_teacher.academic_year_id', $yearId)
            ->whereNotIn('section_teacher.section_id', $scheduledSectionIds ?: [0])
            ->orderBy('sections.grade_level')
            ->orderBy('sections.name')
            ->get(['sections.id', 'sections.name', 'sections.grade_level']);

        foreach ($sections as $section) {
            $rows[] = [
                'key' => 'section:'.$section->id,
                'type' => 'section',
                'id' => (int) $section->id,
                'section' => $section->name,
                'grade' => $section->grade_level ?: '—',
                'subject' => 'Section assignment',
                'schedule' => 'No schedule',
                'subject_id' => null,
                'section_id' => (int) $section->id,
                'checks_conflict' => false,
            ];
        }

        $grades = DB::table('teacher_grade_level')
            ->where('teacher_id', $teacher->id)
            ->where('academic_year_id', $yearId)
            ->orderBy('grade_level')
            ->pluck('grade_level');

        foreach ($grades as $grade) {
            $rows[] = [
                'key' => 'grade:'.$grade,
                'type' => 'grade',
                'id' => $grade,
                'section' => '—',
                'grade' => $grade,
                'subject' => 'Grade-level assignment',
                'schedule' => '—',
                'subject_id' => null,
                'section_id' => null,
                'checks_conflict' => false,
            ];
        }

        $adviserSections = Section::query()
            ->where('academic_year_id', $yearId)
            ->where('adviser_id', $teacher->id)
            ->orderBy('grade_level')
            ->orderBy('name')
            ->get(['id', 'name', 'grade_level']);

        foreach ($adviserSections as $section) {
            $rows[] = [
                'key' => 'adviser:'.$section->id,
                'type' => 'adviser',
                'id' => (int) $section->id,
                'section' => $section->name,
                'grade' => $section->grade_level ?: '—',
                'subject' => 'Section adviser',
                'schedule' => '—',
                'subject_id' => null,
                'section_id' => (int) $section->id,
                'checks_conflict' => false,
            ];
        }

        return $rows;
    }

    /**
     * @return \Illuminate\Support\Collection<int, Teacher>
     */
    public function activeReplacementTeachers(int $excludeTeacherId)
    {
        return Teacher::query()
            ->with('user:id,user_id,name,status,role_name')
            ->where('id', '!=', $excludeTeacherId)
            ->whereHas('user', function ($query) {
                $query->where('role_name', User::ROLE_TEACHER)
                    ->whereRaw('LOWER(TRIM(status)) = ?', ['active']);
            })
            ->orderBy('full_name')
            ->get();
    }

    /**
     * @param  array<string, mixed>  $row
     * @return array<int, array{id:int,name:string,eligible:bool,reason:?string}>
     */
    public function replacementChoices(array $row, $teachers, int $yearId): array
    {
        $choices = [];
        foreach ($teachers as $teacher) {
            $reason = null;
            if (! empty($row['checks_conflict']) && ! empty($row['schedule_id'])) {
                $schedule = ClassSchedule::find($row['schedule_id']);
                if ($schedule && $this->teacherConflictsWithSchedule($schedule, (int) $teacher->id, $yearId, [])) {
                    $reason = $teacher->full_name.' cannot receive this assignment because of a schedule conflict.';
                }
            }
            $choices[] = [
                'id' => (int) $teacher->id,
                'name' => $teacher->full_name ?: ($teacher->user->name ?? 'Teacher'),
                'eligible' => $reason === null,
                'reason' => $reason,
            ];
        }

        return $choices;
    }

    public function rowHasEligibleTeacher(array $choices): bool
    {
        foreach ($choices as $choice) {
            if ($choice['eligible']) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     * @param  array<string, mixed>  $replacements
     * @return array{ok:bool,errors:array<string,string>,message:?string}
     */
    public function validateTransfer(Teacher $teacher, int $yearId, array $rows, array $replacements): array
    {
        $errors = [];
        $activeTeachers = $this->activeReplacementTeachers($teacher->id);
        $activeIds = $activeTeachers->pluck('id')->map(fn ($id) => (int) $id)->all();

        if ($rows === []) {
            return ['ok' => true, 'errors' => [], 'message' => null];
        }

        foreach ($rows as $row) {
            $choices = $this->replacementChoices($row, $activeTeachers, $yearId);
            if (! $this->rowHasEligibleTeacher($choices)) {
                return [
                    'ok' => false,
                    'errors' => [$row['key'] => 'Teacher cannot be deactivated. There is no available teacher who can take over the current assignment. Please assign or activate another eligible teacher first.'],
                    'message' => 'Teacher cannot be deactivated. There is no available teacher who can take over the current assignment. Please assign or activate another eligible teacher first.',
                ];
            }
        }

        $missing = false;
        foreach ($rows as $row) {
            $selected = (int) ($replacements[$row['key']] ?? 0);
            if ($selected <= 0) {
                $missing = true;
                $errors[$row['key']] = 'All active assignments must be transferred before this teacher can be deactivated.';
                continue;
            }
            if (! in_array($selected, $activeIds, true) || $selected === (int) $teacher->id) {
                $errors[$row['key']] = 'Select an active teacher other than the teacher being deactivated.';
            }
        }

        if ($missing) {
            return [
                'ok' => false,
                'errors' => $errors,
                'message' => 'All active assignments must be transferred before this teacher can be deactivated.',
            ];
        }

        $proposed = [];
        foreach ($rows as $row) {
            if (($row['type'] ?? '') === 'schedule') {
                $proposed[(int) $row['schedule_id']] = (int) $replacements[$row['key']];
            }
        }

        foreach ($rows as $row) {
            if (isset($errors[$row['key']]) || empty($row['checks_conflict'])) {
                continue;
            }
            $schedule = ClassSchedule::find($row['schedule_id']);
            $selected = (int) $replacements[$row['key']];
            if ($schedule && $this->teacherConflictsWithSchedule($schedule, $selected, $yearId, $proposed)) {
                $name = Teacher::find($selected)?->full_name ?: 'The selected teacher';
                $errors[$row['key']] = $name.' cannot receive this assignment because of a schedule conflict.';
            }
        }

        if ($errors !== []) {
            return [
                'ok' => false,
                'errors' => $errors,
                'message' => collect($errors)->first(),
            ];
        }

        return ['ok' => true, 'errors' => [], 'message' => null];
    }

    /**
     * @param  array<string, mixed>  $replacements
     */
    public function transferAndDeactivate(User $user, array $replacements): void
    {
        $teacher = $user->teacher;
        $year = AcademicYear::active();
        if (! $teacher || ! $year) {
            throw new RuntimeException('Set an academic year as Current before deactivating a teacher.');
        }

        DB::transaction(function () use ($user, $teacher, $year, $replacements) {
            ClassSchedule::query()
                ->where('teacher_id', $teacher->id)
                ->where('academic_year_id', $year->id)
                ->lockForUpdate()
                ->get();

            $rows = $this->assignmentRows($teacher, $year->id);
            $check = $this->validateTransfer($teacher, $year->id, $rows, $replacements);
            if (! $check['ok']) {
                throw new RuntimeException($check['message'] ?: 'Teacher cannot be deactivated while active assignments remain. Transfer all assignments first.');
            }

            foreach ($rows as $row) {
                $replacementId = (int) $replacements[$row['key']];
                if ($row['type'] === 'schedule') {
                    ClassSchedule::query()
                        ->where('id', $row['schedule_id'])
                        ->where('teacher_id', $teacher->id)
                        ->where('academic_year_id', $year->id)
                        ->update(['teacher_id' => $replacementId]);
                    if (! empty($row['subject_id'])) {
                        $this->ensureSubjectLink($replacementId, (int) $row['subject_id'], $year->id);
                    }
                    if (! empty($row['section_id'])) {
                        $this->ensureSectionLink($replacementId, (int) $row['section_id'], $year->id);
                    }
                } elseif ($row['type'] === 'subject') {
                    $this->ensureSubjectLink($replacementId, (int) $row['subject_id'], $year->id);
                } elseif ($row['type'] === 'section') {
                    $this->ensureSectionLink($replacementId, (int) $row['section_id'], $year->id);
                } elseif ($row['type'] === 'grade') {
                    $this->ensureGradeLink($replacementId, (string) $row['id'], $year->id);
                } elseif ($row['type'] === 'adviser') {
                    Section::query()
                        ->where('id', $row['section_id'])
                        ->where('academic_year_id', $year->id)
                        ->where('adviser_id', $teacher->id)
                        ->update(['adviser_id' => $replacementId]);
                }
            }

            DB::table('subject_teacher')
                ->where('teacher_id', $teacher->id)
                ->where('academic_year_id', $year->id)
                ->delete();
            DB::table('section_teacher')
                ->where('teacher_id', $teacher->id)
                ->where('academic_year_id', $year->id)
                ->delete();
            DB::table('teacher_grade_level')
                ->where('teacher_id', $teacher->id)
                ->where('academic_year_id', $year->id)
                ->delete();

            if ($this->assignmentRows($teacher->fresh(), $year->id) !== []) {
                throw new RuntimeException('Teacher cannot be deactivated while active assignments remain. Transfer all assignments first.');
            }

            $user->update(['status' => 'Inactive']);
        });
    }

    /**
     * @param  array<int, int>  $proposedScheduleTeachers
     */
    public function teacherConflictsWithSchedule(ClassSchedule $schedule, int $replacementTeacherId, int $yearId, array $proposedScheduleTeachers): bool
    {
        if (! $schedule->is_active) {
            return false;
        }

        $others = ClassSchedule::query()
            ->where('academic_year_id', $yearId)
            ->where('is_active', true)
            ->where('id', '!=', $schedule->id)
            ->get(['id', 'teacher_id', 'day_of_week', 'start_time', 'end_time']);

        foreach ($others as $existing) {
            $effectiveTeacher = $proposedScheduleTeachers[$existing->id] ?? (int) $existing->teacher_id;
            if ((int) $effectiveTeacher !== $replacementTeacherId) {
                continue;
            }
            if ($this->sameTeacherOverlap($schedule, $existing)) {
                return true;
            }
        }

        return false;
    }

    private function sameTeacherOverlap(ClassSchedule $candidate, ClassSchedule $existing): bool
    {
        if ($existing->day_of_week !== null && $existing->day_of_week !== $candidate->day_of_week) {
            return false;
        }

        $existingStart = Carbon::parse($existing->start_time)->format('H:i:s');
        $existingEnd = Carbon::parse($existing->end_time)->format('H:i:s');
        $start = Carbon::parse($candidate->start_time)->format('H:i:s');
        $end = Carbon::parse($candidate->end_time)->format('H:i:s');

        return $existingStart < $end && $existingEnd > $start;
    }

    private function scheduleLabel(ClassSchedule $schedule): string
    {
        $day = $schedule->day_of_week ? ucfirst(substr((string) $schedule->day_of_week, 0, 3)) : 'Day';
        $start = Carbon::parse($schedule->start_time)->format('g:i A');
        $end = Carbon::parse($schedule->end_time)->format('g:i A');

        return $day.' '.$start.'–'.$end;
    }

    private function ensureSubjectLink(int $teacherId, int $subjectId, int $yearId): void
    {
        $exists = DB::table('subject_teacher')->where([
            'teacher_id' => $teacherId,
            'subject_id' => $subjectId,
            'academic_year_id' => $yearId,
        ])->exists();
        if ($exists) {
            return;
        }

        DB::table('subject_teacher')->insert([
            'teacher_id' => $teacherId,
            'subject_id' => $subjectId,
            'academic_year_id' => $yearId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function ensureSectionLink(int $teacherId, int $sectionId, int $yearId): void
    {
        $exists = DB::table('section_teacher')->where([
            'teacher_id' => $teacherId,
            'section_id' => $sectionId,
            'academic_year_id' => $yearId,
        ])->exists();
        if ($exists) {
            return;
        }

        DB::table('section_teacher')->insert([
            'teacher_id' => $teacherId,
            'section_id' => $sectionId,
            'academic_year_id' => $yearId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function ensureGradeLink(int $teacherId, string $gradeLevel, int $yearId): void
    {
        $exists = DB::table('teacher_grade_level')->where([
            'teacher_id' => $teacherId,
            'grade_level' => $gradeLevel,
            'academic_year_id' => $yearId,
        ])->exists();
        if ($exists) {
            return;
        }

        DB::table('teacher_grade_level')->insert([
            'teacher_id' => $teacherId,
            'grade_level' => $gradeLevel,
            'academic_year_id' => $yearId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
