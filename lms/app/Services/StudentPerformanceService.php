<?php

namespace App\Services;

use App\Models\AcademicYear;
use App\Models\Grade;
use App\Models\GradeAlert;
use App\Models\QuarterlyGrade;
use App\Models\Semester;
use App\Models\Student;
use App\Models\StudentGpa;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use App\Notifications\LowGradeAlertNotification;
use App\Support\AcademicThresholds;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class StudentPerformanceService
{
    public const LOW_GRADE_THRESHOLD = AcademicThresholds::PASSING_PERCENTAGE;

    public const AT_RISK_THRESHOLD = 70.0;

    public const PERFORMANCE_DROP_THRESHOLD = 10.0;

    public const AT_RISK_SUBJECT_COUNT = 3;

    /**
     * Resolve which quarter fields belong to a semester within an academic year.
     * First semester of the year → Q1–Q2; second → Q3–Q4; only one semester → all quarters.
     *
     * @return list<string>
     */
    public function quarterFieldsForSemester(int $academicYearId, int $semesterId): array
    {
        $semesters = Semester::where('academic_year_id', $academicYearId)
            ->orderBy('id')
            ->pluck('id')
            ->values();

        if ($semesters->isEmpty() || $semesters->count() === 1) {
            return ['quarter_1', 'quarter_2', 'quarter_3', 'quarter_4'];
        }

        $index = $semesters->search($semesterId);
        if ($index === false) {
            return ['quarter_1', 'quarter_2', 'quarter_3', 'quarter_4'];
        }

        return $index === 0
            ? ['quarter_1', 'quarter_2']
            : ['quarter_3', 'quarter_4'];
    }

    /**
     * Subject percentage for a student in a period (from quarterly grades, fallback to Grade rows).
     */
    public function subjectPercentage(QuarterlyGrade $row, array $quarterFields): ?float
    {
        $values = [];
        foreach ($quarterFields as $field) {
            $v = $row->{$field};
            if ($v !== null && $v !== '') {
                $values[] = (float) $v;
            }
        }

        if ($values !== []) {
            return round(array_sum($values) / count($values), 2);
        }

        if ($row->final_grade !== null && $row->final_grade !== '') {
            return round((float) $row->final_grade, 2);
        }

        return null;
    }

    public function percentageToGradePoints(float $percentage): float
    {
        if ($percentage >= 90) {
            return 4.0;
        }
        if ($percentage >= 85) {
            return 3.7;
        }
        if ($percentage >= 80) {
            return 3.3;
        }
        if ($percentage >= 75) {
            return 3.0;
        }
        if ($percentage >= 70) {
            return 2.7;
        }
        if ($percentage >= 65) {
            return 2.3;
        }
        if ($percentage >= 60) {
            return 2.0;
        }
        if ($percentage >= 55) {
            return 1.7;
        }
        if ($percentage >= 50) {
            return 1.3;
        }
        if ($percentage >= 45) {
            return 1.0;
        }

        return 0.0;
    }

    public static function remarkForAverage(?float $average): string
    {
        if ($average === null) {
            return '—';
        }

        return $average >= self::LOW_GRADE_THRESHOLD ? 'Passed' : 'Failed';
    }

    /**
     * Recalculate and persist StudentGpa for one student from QuarterlyGrade (+ Grade fallback).
     */
    public function recalculateStudentGpa(int $studentId, int $academicYearId, int $semesterId): ?StudentGpa
    {
        $quarterFields = $this->quarterFieldsForSemester($academicYearId, $semesterId);
        $subjectScores = $this->collectSubjectScores($studentId, $academicYearId, $semesterId, $quarterFields);

        if ($subjectScores->isEmpty()) {
            StudentGpa::where([
                'student_id' => $studentId,
                'academic_year_id' => $academicYearId,
                'semester_id' => $semesterId,
            ])->delete();

            return null;
        }

        $totalPoints = 0.0;
        $units = 0;
        $pctSum = 0.0;

        foreach ($subjectScores as $pct) {
            $totalPoints += $this->percentageToGradePoints($pct);
            $pctSum += $pct;
            $units++;
        }

        $gpa = $units > 0 ? round($totalPoints / $units, 2) : 0.0;
        $generalAverage = $units > 0 ? round($pctSum / $units, 2) : null;

        return StudentGpa::updateOrCreate(
            [
                'student_id' => $studentId,
                'academic_year_id' => $academicYearId,
                'semester_id' => $semesterId,
            ],
            [
                'gpa' => $gpa,
                'total_units' => $units,
                'total_grade_points' => (int) round($totalPoints),
                'remarks' => $generalAverage !== null
                    ? 'GA: '.number_format($generalAverage, 2).' | '.self::remarkForAverage($generalAverage)
                    : null,
            ]
        );
    }

    public function recalculateForStudents(iterable $studentIds, int $academicYearId, int $semesterId): void
    {
        foreach (collect($studentIds)->unique()->filter() as $studentId) {
            $this->recalculateStudentGpa((int) $studentId, $academicYearId, $semesterId);
        }
        $this->updateGlobalRankings($academicYearId, $semesterId);
    }

    /**
     * Competition ranking written to student_gpa.rank for the full AY/semester cohort.
     */
    public function updateGlobalRankings(int $academicYearId, int $semesterId): void
    {
        $records = StudentGpa::where('academic_year_id', $academicYearId)
            ->where('semester_id', $semesterId)
            ->orderByDesc('gpa')
            ->orderBy('student_id')
            ->get();

        $rank = 0;
        $position = 0;
        $prevGpa = null;

        foreach ($records as $record) {
            $position++;
            $gpa = (float) $record->gpa;
            if ($prevGpa === null || abs($gpa - $prevGpa) > 0.0001) {
                $rank = $position;
                $prevGpa = $gpa;
            }
            if ((int) $record->rank !== $rank) {
                $record->update(['rank' => $rank]);
            }
        }
    }

    /**
     * Ranking rows for UI (optionally scoped to section / teacher). Display ranks are local when filtered.
     *
     * @return Collection<int, object>
     */
    public function rankingRows(
        int $academicYearId,
        int $semesterId,
        ?int $sectionId = null,
        ?array $allowedStudentIds = null
    ): Collection {
        $this->ensureGpasExistForScope($academicYearId, $semesterId, $sectionId, $allowedStudentIds);

        $query = StudentGpa::with(['student.sections', 'academicYear', 'semester'])
            ->where('academic_year_id', $academicYearId)
            ->where('semester_id', $semesterId);

        if ($sectionId) {
            $query->whereHas('student.sections', function ($q) use ($sectionId, $academicYearId) {
                $q->where('sections.id', $sectionId)
                    ->where(function ($qq) use ($academicYearId) {
                        $qq->where('student_section_assignments.academic_year_id', $academicYearId)
                            ->orWhereNull('student_section_assignments.academic_year_id');
                    });
            });
        }

        if ($allowedStudentIds !== null) {
            $query->whereIn('student_id', $allowedStudentIds);
        }

        $records = $query->orderByDesc('gpa')->orderBy('student_id')->get();
        $quarterFields = $this->quarterFieldsForSemester($academicYearId, $semesterId);

        $displayRank = 0;
        $position = 0;
        $prevGpa = null;

        return $records->map(function (StudentGpa $record) use ($academicYearId, $semesterId, $quarterFields, &$displayRank, &$position, &$prevGpa) {
            $position++;
            $gpa = (float) $record->gpa;
            if ($prevGpa === null || abs($gpa - $prevGpa) > 0.0001) {
                $displayRank = $position;
                $prevGpa = $gpa;
            }

            $scores = $this->collectSubjectScores(
                (int) $record->student_id,
                $academicYearId,
                $semesterId,
                $quarterFields
            );
            $generalAverage = $scores->isNotEmpty() ? round($scores->avg(), 2) : null;

            return (object) [
                'record' => $record,
                'student' => $record->student,
                'rank' => $displayRank,
                'global_rank' => $record->rank,
                'gpa' => $gpa,
                'general_average' => $generalAverage,
                'subjects_count' => $scores->count(),
                'letter_grade' => $record->letter_grade,
                'description' => $record->grade_description,
                'pass_fail' => self::remarkForAverage($generalAverage),
            ];
        });
    }

    /**
     * Analytics payload for one student.
     *
     * @return array{
     *   subjects: Collection,
     *   quarter_averages: array,
     *   general_average: ?float,
     *   gpa: ?StudentGpa,
     *   trend_labels: array,
     *   trend_values: array,
     *   alerts: Collection
     * }
     */
    public function studentAnalytics(Student $student, int $academicYearId, ?int $semesterId = null): array
    {
        $semesterId = $semesterId
            ?? Semester::where('academic_year_id', $academicYearId)->orderBy('id')->value('id');

        $rows = QuarterlyGrade::with('subject')
            ->where('student_id', $student->id)
            ->where('academic_year_id', $academicYearId)
            ->get()
            ->sortBy(fn ($r) => $r->subject->subject_name ?? '');

        $subjects = $rows->map(function (QuarterlyGrade $row) {
            $final = $row->final_grade !== null
                ? (float) $row->final_grade
                : $row->calculateFinalGrade();

            return (object) [
                'subject_id' => $row->subject_id,
                'subject_name' => $row->subject->subject_name ?? 'N/A',
                'q1' => $row->quarter_1 !== null ? (float) $row->quarter_1 : null,
                'q2' => $row->quarter_2 !== null ? (float) $row->quarter_2 : null,
                'q3' => $row->quarter_3 !== null ? (float) $row->quarter_3 : null,
                'q4' => $row->quarter_4 !== null ? (float) $row->quarter_4 : null,
                'final' => $final,
                'remarks' => $row->remarks ?: self::remarkForAverage($final),
            ];
        })->values();

        $quarterAverages = [];
        foreach ([1, 2, 3, 4] as $q) {
            $field = 'quarter_'.$q;
            $vals = $rows->pluck($field)->filter(fn ($v) => $v !== null && $v !== '')->map(fn ($v) => (float) $v);
            $quarterAverages['q'.$q] = $vals->isNotEmpty() ? round($vals->avg(), 2) : null;
        }

        $finals = $subjects->pluck('final')->filter(fn ($v) => $v !== null);
        $generalAverage = $finals->isNotEmpty() ? round($finals->avg(), 2) : null;

        $gpa = $semesterId
            ? StudentGpa::where('student_id', $student->id)
                ->where('academic_year_id', $academicYearId)
                ->where('semester_id', $semesterId)
                ->first()
            : null;

        $trendLabels = ['Q1', 'Q2', 'Q3', 'Q4'];
        $trendValues = [
            $quarterAverages['q1'],
            $quarterAverages['q2'],
            $quarterAverages['q3'],
            $quarterAverages['q4'],
        ];

        $alerts = GradeAlert::with('subject')
            ->where('student_id', $student->id)
            ->where('academic_year_id', $academicYearId)
            ->where('is_resolved', false)
            ->orderByDesc('created_at')
            ->get();

        return compact(
            'subjects',
            'quarterAverages',
            'generalAverage',
            'gpa',
            'trendLabels',
            'trendValues',
            'alerts'
        ) + [
            'quarter_averages' => $quarterAverages,
            'general_average' => $generalAverage,
            'trend_labels' => $trendLabels,
            'trend_values' => $trendValues,
        ];
    }

    /**
     * Section-level analytics summary cards.
     */
    public function sectionAnalyticsSummary(int $academicYearId, int $semesterId, ?int $sectionId, ?array $allowedStudentIds = null): array
    {
        $rows = $this->rankingRows($academicYearId, $semesterId, $sectionId, $allowedStudentIds);
        $averages = $rows->pluck('general_average')->filter(fn ($v) => $v !== null);
        $gpas = $rows->pluck('gpa')->filter(fn ($v) => $v !== null);
        $passed = $rows->filter(fn ($r) => ($r->general_average ?? 0) >= self::LOW_GRADE_THRESHOLD)->count();
        $failed = $rows->filter(fn ($r) => $r->general_average !== null && $r->general_average < self::LOW_GRADE_THRESHOLD)->count();

        $alertQuery = GradeAlert::where('is_resolved', false)
            ->where('academic_year_id', $academicYearId);

        if ($allowedStudentIds !== null) {
            $alertQuery->whereIn('student_id', $allowedStudentIds);
        } elseif ($sectionId) {
            $alertQuery->whereHas('student.sections', fn ($q) => $q->where('sections.id', $sectionId));
        }

        return [
            'students' => $rows->count(),
            'class_average' => $averages->isNotEmpty() ? round($averages->avg(), 2) : null,
            'class_gpa' => $gpas->isNotEmpty() ? round($gpas->avg(), 2) : null,
            'passed' => $passed,
            'failed' => $failed,
            'open_alerts' => (clone $alertQuery)->count(),
            'top_students' => $rows->take(5)->values(),
            'needs_attention' => $rows->filter(fn ($r) => $r->general_average !== null && $r->general_average < self::LOW_GRADE_THRESHOLD)
                ->sortBy('general_average')
                ->take(5)
                ->values(),
        ];
    }

    /**
     * Create/update alerts from quarterly grades for affected subjects & students.
     */
    public function checkAlertsForSubjects(iterable $subjectIds, int $academicYearId, int $semesterId, bool $notify = true): void
    {
        $quarterFields = $this->quarterFieldsForSemester($academicYearId, $semesterId);

        foreach (collect($subjectIds)->unique()->filter() as $subjectId) {
            $this->checkSubjectAlerts((int) $subjectId, $academicYearId, $semesterId, $quarterFields, $notify);
        }

        $this->checkAtRiskStudents($academicYearId, $semesterId, $quarterFields);
    }

    /**
     * Build missing Grade Alerts from current failing averages so the hub
     * matches Passed/Failed counts even when grades were imported or seeded.
     */
    public function syncAlertsForScope(int $academicYearId, int $semesterId, ?int $sectionId = null, ?array $allowedStudentIds = null): void
    {
        $studentQuery = Student::query();
        if ($allowedStudentIds !== null) {
            $studentQuery->whereIn('id', $allowedStudentIds);
        }
        if ($sectionId) {
            $studentQuery->whereHas('sections', fn ($q) => $q->where('sections.id', $sectionId));
        }

        $studentIds = $studentQuery->pluck('id');
        if ($studentIds->isEmpty()) {
            return;
        }

        $subjectIds = QuarterlyGrade::query()
            ->where('academic_year_id', $academicYearId)
            ->whereIn('student_id', $studentIds)
            ->distinct()
            ->pluck('subject_id');

        if ($subjectIds->isEmpty()) {
            $subjectIds = Grade::query()
                ->where('academic_year_id', $academicYearId)
                ->where('semester_id', $semesterId)
                ->whereIn('student_id', $studentIds)
                ->distinct()
                ->pluck('subject_id');
        }

        $this->checkAlertsForSubjects($subjectIds, $academicYearId, $semesterId, false);
        $this->syncOverallFailingAlerts($academicYearId, $semesterId, $sectionId, $allowedStudentIds);
    }

    /**
     * Ensure each student below the passing average has a visible open alert.
     */
    protected function syncOverallFailingAlerts(int $academicYearId, int $semesterId, ?int $sectionId, ?array $allowedStudentIds): void
    {
        $rows = $this->rankingRows($academicYearId, $semesterId, $sectionId, $allowedStudentIds);
        $failing = $rows->filter(fn ($r) => $r->general_average !== null && $r->general_average < self::LOW_GRADE_THRESHOLD);

        foreach ($failing as $row) {
            $studentId = (int) (optional($row->student)->id ?? optional($row->record)->student_id ?? 0);
            if ($studentId < 1) {
                continue;
            }

            $avg = round((float) $row->general_average, 2);
            $first = optional($row->student)->first_name;
            $last = optional($row->student)->last_name;
            $name = trim(($last ?? '').', '.($first ?? ''), ' ,');

            GradeAlert::firstOrCreate(
                [
                    'student_id' => $studentId,
                    'subject_id' => null,
                    'alert_type' => GradeAlert::TYPE_AT_RISK,
                    'academic_year_id' => $academicYearId,
                    'semester_id' => $semesterId,
                    'is_resolved' => false,
                ],
                [
                    'message' => ($name !== '' ? $name.' is' : 'Student is')
                        .' below passing average ('.number_format($avg, 2).'%). Needs attention.',
                    'threshold_value' => self::LOW_GRADE_THRESHOLD,
                    'current_value' => $avg,
                ]
            );
        }
    }

    public function resolveAlert(GradeAlert $alert, User $resolver): GradeAlert
    {
        $alert->update([
            'is_resolved' => true,
            'resolved_at' => now(),
            'resolved_by' => $resolver->id,
        ]);

        return $alert->fresh();
    }

    /**
     * Student IDs a teacher may see (assigned sections).
     *
     * @return list<int>|null null = no restriction (admin)
     */
    public function allowedStudentIdsForUser(?User $user): ?array
    {
        if (! $user || $user->role_name === 'Admin') {
            return null;
        }

        if ($user->role_name !== 'Teacher' || ! $user->teacher) {
            return [];
        }

        return $this->studentIdsForTeacher($user->teacher);
    }

    /**
     * @return list<int>
     */
    public function studentIdsForTeacher(Teacher $teacher): array
    {
        $options = app(TeacherClassAssignmentService::class)->optionsFor($teacher);
        $sectionIds = $options['sections']->pluck('id')->map(fn ($id) => (int) $id)->all();

        if ($sectionIds === []) {
            return [];
        }

        return Student::whereHas('sections', fn ($q) => $q->whereIn('sections.id', $sectionIds))
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();
    }

    public function sectionsForUser(?User $user): Collection
    {
        if (! $user) {
            return collect();
        }

        if ($user->role_name === 'Admin') {
            return \App\Models\Section::orderBy('grade_level')->orderBy('name')->get();
        }

        if ($user->role_name === 'Teacher' && $user->teacher) {
            return app(TeacherClassAssignmentService::class)->optionsFor($user->teacher)['sections'];
        }

        return collect();
    }

    /**
     * @return Collection<int, float> subject_id => percentage
     */
    protected function collectSubjectScores(
        int $studentId,
        int $academicYearId,
        int $semesterId,
        array $quarterFields
    ): Collection {
        $scores = collect();

        $quarterly = QuarterlyGrade::where('student_id', $studentId)
            ->where('academic_year_id', $academicYearId)
            ->get();

        foreach ($quarterly as $row) {
            $pct = $this->subjectPercentage($row, $quarterFields);
            if ($pct !== null) {
                $scores->put((int) $row->subject_id, $pct);
            }
        }

        if ($scores->isNotEmpty()) {
            return $scores;
        }

        // Fallback: component Grade rows → average per subject
        $grades = Grade::where('student_id', $studentId)
            ->where('academic_year_id', $academicYearId)
            ->where('semester_id', $semesterId)
            ->whereNotNull('percentage')
            ->get()
            ->groupBy('subject_id');

        foreach ($grades as $subjectId => $group) {
            $avg = round((float) $group->avg('percentage'), 2);
            $scores->put((int) $subjectId, $avg);
        }

        return $scores;
    }

    protected function ensureGpasExistForScope(
        int $academicYearId,
        int $semesterId,
        ?int $sectionId,
        ?array $allowedStudentIds
    ): void {
        $query = Student::query();

        if ($allowedStudentIds !== null) {
            $query->whereIn('id', $allowedStudentIds);
        }

        if ($sectionId) {
            $query->whereHas('sections', function ($q) use ($sectionId, $academicYearId) {
                $q->where('sections.id', $sectionId)
                    ->where(function ($qq) use ($academicYearId) {
                        $qq->where('student_section_assignments.academic_year_id', $academicYearId)
                            ->orWhereNull('student_section_assignments.academic_year_id');
                    });
            });
        }

        // Only recalculate students who have quarterly/component grades but maybe stale GPA
        $studentIds = $query->where(function ($q) use ($academicYearId, $semesterId) {
            $q->whereHas('quarterlyGrades', fn ($qq) => $qq->where('academic_year_id', $academicYearId))
                ->orWhereHas('grades', function ($qq) use ($academicYearId, $semesterId) {
                    $qq->where('academic_year_id', $academicYearId)->where('semester_id', $semesterId);
                });
        })->pluck('id');

        foreach ($studentIds as $studentId) {
            $this->recalculateStudentGpa((int) $studentId, $academicYearId, $semesterId);
        }

        $this->updateGlobalRankings($academicYearId, $semesterId);
    }

    protected function checkSubjectAlerts(
        int $subjectId,
        int $academicYearId,
        int $semesterId,
        array $quarterFields,
        bool $notify = true
    ): void {
        $subject = Subject::find($subjectId);
        if (! $subject) {
            return;
        }

        $rows = QuarterlyGrade::with('student.user')
            ->where('subject_id', $subjectId)
            ->where('academic_year_id', $academicYearId)
            ->get();

        foreach ($rows as $row) {
            $pct = $this->subjectPercentage($row, $quarterFields);
            if ($pct === null) {
                continue;
            }

            if ($pct < self::LOW_GRADE_THRESHOLD) {
                $alert = GradeAlert::firstOrCreate(
                    [
                        'student_id' => $row->student_id,
                        'subject_id' => $subjectId,
                        'alert_type' => GradeAlert::TYPE_LOW_GRADE,
                        'academic_year_id' => $academicYearId,
                        'semester_id' => $semesterId,
                        'is_resolved' => false,
                    ],
                    [
                        'message' => "Low grade in {$subject->subject_name}: ".number_format($pct, 2).'%',
                        'threshold_value' => self::LOW_GRADE_THRESHOLD,
                        'current_value' => $pct,
                    ]
                );

                if ($alert->wasRecentlyCreated && $notify) {
                    $this->notifyLowGrade($row, $subject, $pct);
                } else {
                    $alert->update([
                        'message' => "Low grade in {$subject->subject_name}: ".number_format($pct, 2).'%',
                        'current_value' => $pct,
                    ]);
                }
            } else {
                // Auto-resolve open low-grade alert if student recovered
                GradeAlert::where([
                    'student_id' => $row->student_id,
                    'subject_id' => $subjectId,
                    'alert_type' => GradeAlert::TYPE_LOW_GRADE,
                    'academic_year_id' => $academicYearId,
                    'semester_id' => $semesterId,
                    'is_resolved' => false,
                ])->update([
                    'is_resolved' => true,
                    'resolved_at' => now(),
                    'resolved_by' => Auth::id(),
                ]);
            }

            // Performance drop vs previous quarter in the same year
            $this->maybeCreatePerformanceDropAlert($row, $subject, $academicYearId, $semesterId, $quarterFields, $pct);
        }
    }

    protected function maybeCreatePerformanceDropAlert(
        QuarterlyGrade $row,
        Subject $subject,
        int $academicYearId,
        int $semesterId,
        array $quarterFields,
        float $currentPct
    ): void {
        $ordered = ['quarter_1', 'quarter_2', 'quarter_3', 'quarter_4'];
        $filled = [];
        foreach ($ordered as $field) {
            if ($row->{$field} !== null && $row->{$field} !== '') {
                $filled[] = (float) $row->{$field};
            }
        }

        if (count($filled) < 2) {
            return;
        }

        $prev = $filled[count($filled) - 2];
        $curr = $filled[count($filled) - 1];
        $drop = $prev - $curr;

        if ($drop >= self::PERFORMANCE_DROP_THRESHOLD && $curr < self::LOW_GRADE_THRESHOLD) {
            GradeAlert::firstOrCreate(
                [
                    'student_id' => $row->student_id,
                    'subject_id' => $subject->id,
                    'alert_type' => GradeAlert::TYPE_PERFORMANCE_DROP,
                    'academic_year_id' => $academicYearId,
                    'semester_id' => $semesterId,
                    'is_resolved' => false,
                ],
                [
                    'message' => "Performance drop in {$subject->subject_name}: "
                        .number_format($prev, 2).'% → '.number_format($curr, 2)
                        .'% (Drop: '.number_format($drop, 2).'%)',
                    'threshold_value' => self::PERFORMANCE_DROP_THRESHOLD,
                    'current_value' => round($drop, 2),
                ]
            );
        }
    }

    protected function checkAtRiskStudents(int $academicYearId, int $semesterId, array $quarterFields): void
    {
        $byStudent = QuarterlyGrade::with('subject')
            ->where('academic_year_id', $academicYearId)
            ->get()
            ->groupBy('student_id');

        foreach ($byStudent as $studentId => $rows) {
            $lowSubjects = 0;
            $lowScores = [];

            foreach ($rows as $row) {
                $pct = $this->subjectPercentage($row, $quarterFields);
                if ($pct !== null && $pct < self::AT_RISK_THRESHOLD) {
                    $lowSubjects++;
                    $lowScores[] = $pct;
                }
            }

            if ($lowSubjects >= self::AT_RISK_SUBJECT_COUNT) {
                $avg = round(array_sum($lowScores) / count($lowScores), 2);
                GradeAlert::updateOrCreate(
                    [
                        'student_id' => $studentId,
                        'subject_id' => null,
                        'alert_type' => GradeAlert::TYPE_AT_RISK,
                        'academic_year_id' => $academicYearId,
                        'semester_id' => $semesterId,
                        'is_resolved' => false,
                    ],
                    [
                        'message' => "At-risk: low grades in {$lowSubjects} subjects (avg of low scores: {$avg}%)",
                        'threshold_value' => self::AT_RISK_THRESHOLD,
                        'current_value' => $avg,
                    ]
                );
            } else {
                GradeAlert::where([
                    'student_id' => $studentId,
                    'alert_type' => GradeAlert::TYPE_AT_RISK,
                    'academic_year_id' => $academicYearId,
                    'semester_id' => $semesterId,
                    'is_resolved' => false,
                ])->whereNull('subject_id')->update([
                    'is_resolved' => true,
                    'resolved_at' => now(),
                    'resolved_by' => Auth::id(),
                ]);
            }
        }
    }

    protected function notifyLowGrade(QuarterlyGrade $row, Subject $subject, float $pct): void
    {
        try {
            $student = $row->student;
            if (! $student) {
                return;
            }

            $payloadGrade = Grade::where('student_id', $student->id)
                ->where('subject_id', $subject->id)
                ->where('academic_year_id', $row->academic_year_id)
                ->latest('id')
                ->first();

            if (! $payloadGrade) {
                // Notification expects a Grade model; skip mail if none synced yet
                return;
            }

            if ($student->user) {
                $student->user->notify(new LowGradeAlertNotification($payloadGrade, $student, $subject));
            }

            if ($student->parent_email) {
                $parent = User::where('email', $student->parent_email)
                    ->where('role_name', 'Parent')
                    ->first();
                if ($parent) {
                    $parent->notify(new LowGradeAlertNotification($payloadGrade, $student, $subject));
                }
            }
        } catch (\Throwable $e) {
            Log::warning('Low grade notification failed', ['error' => $e->getMessage()]);
        }
    }
}
