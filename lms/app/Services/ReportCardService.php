<?php

namespace App\Services;

use App\Models\AcademicYear;
use App\Models\Attendance;
use App\Models\ObservedValueIndicator;
use App\Models\QuarterlyGrade;
use App\Models\Student;
use App\Models\StudentObservedValue;
use App\Support\AcademicThresholds;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ReportCardService
{
    /** School-year month order for Form 138 attendance (Jun–Mar). */
    public const ATTENDANCE_MONTHS = [
        6 => 'JUN',
        7 => 'JUL',
        8 => 'AUG',
        9 => 'SEP',
        10 => 'OCT',
        11 => 'NOV',
        12 => 'DEC',
        1 => 'JAN',
        2 => 'FEB',
        3 => 'MAR',
    ];

    /**
     * Build report-card data for one student (observed values + learning progress + attendance).
     *
     * @return array{
     *   student: Student,
     *   academicYear: ?AcademicYear,
     *   observedGrouped: Collection,
     *   observedRatings: Collection,
     *   learningRows: Collection,
     *   quarterAverages: array,
     *   generalAverages: array,
     *   attendanceMonths: array,
     *   attendanceRows: array,
     *   focusQuarter: ?int
     * }
     */
    public function forStudent(Student $student, ?AcademicYear $academicYear, ?int $focusQuarter = null): array
    {
        $observedGrouped = ObservedValueIndicator::active()->orderBy('sort_order')->get()->groupBy('core_value');
        $observedRatings = collect();
        $learningRows = collect();
        $quarterAverages = ['q1' => null, 'q2' => null, 'q3' => null, 'q4' => null, 'final' => null];
        $generalAverages = $quarterAverages;
        $attendanceMonths = self::ATTENDANCE_MONTHS;
        $attendanceRows = $this->emptyAttendanceRows();

        $student->loadMissing('sections');
        $reportSection = $academicYear
            ? $student->sections->first(fn ($section) => (int) $section->pivot->academic_year_id === (int) $academicYear->id)
            : null;
        $reportSection = $reportSection ?? $student->sections->first();
        $gradeLevel = $reportSection?->grade_level;
        $descriptive = \App\Support\DescriptiveScale::isNurseryOrKindergarten($gradeLevel);

        if (! $academicYear) {
            $reportStatus = 'Not started';

            return compact(
                'student',
                'academicYear',
                'observedGrouped',
                'observedRatings',
                'learningRows',
                'quarterAverages',
                'generalAverages',
                'attendanceMonths',
                'attendanceRows',
                'reportSection',
                'gradeLevel',
                'descriptive',
                'reportStatus'
            ) + ['focusQuarter' => $focusQuarter];
        }

        $observedRatings = StudentObservedValue::where('student_id', $student->id)
            ->where('academic_year_id', $academicYear->id)
            ->get()
            ->keyBy('indicator_id');

        $grades = QuarterlyGrade::where('student_id', $student->id)
            ->where('academic_year_id', $academicYear->id)
            ->with('subject')
            ->get()
            ->sortBy(fn ($g) => $g->subject->subject_name ?? '');

        $learningRows = $this->orderLearningRows($grades->values());

        foreach ([1 => 'q1', 2 => 'q2', 3 => 'q3', 4 => 'q4'] as $quarter => $key) {
            $values = $grades->filter(function ($grade) use ($quarter) {
                return ! \App\Support\DescriptiveScale::get($grade->{'q'.$quarter.'_level'})
                    && $grade->{'quarter_'.$quarter} !== null
                    && $grade->{'quarter_'.$quarter} !== '';
            })->map(fn ($grade) => (float) $grade->{'quarter_'.$quarter});
            $generalAverages[$key] = $values->isNotEmpty() ? round($values->avg(), 2) : null;
            $quarterAverages[$key] = $generalAverages[$key];
        }
        $finals = $grades->filter(fn ($grade) => ! $grade->usesDescriptive() && $grade->final_grade !== null && $grade->final_grade !== '')
            ->map(fn ($grade) => (float) $grade->final_grade);
        $generalAverages['final'] = $finals->isNotEmpty() ? round($finals->avg(), 2) : null;
        $quarterAverages['final'] = $generalAverages['final'];

        $attendanceRows = $this->buildAttendanceRows($student, $academicYear);
        $reportStatus = $this->statusForStudent(
            $grades,
            $reportSection?->id,
            (int) $academicYear->id,
            $focusQuarter,
            \App\Support\DescriptiveScale::isDescriptiveGradeLevel($gradeLevel)
        );

        return [
            'student' => $student,
            'academicYear' => $academicYear,
            'observedGrouped' => $observedGrouped,
            'observedRatings' => $observedRatings,
            'learningRows' => $learningRows,
            'quarterAverages' => $quarterAverages,
            'generalAverages' => $generalAverages,
            'attendanceMonths' => $attendanceMonths,
            'attendanceRows' => $attendanceRows,
            'focusQuarter' => $focusQuarter,
            'reportSection' => $reportSection,
            'gradeLevel' => $gradeLevel,
            'descriptive' => $descriptive,
            'reportStatus' => $reportStatus,
        ];
    }

    public static function reportStatus(int $filled, int $expected): string
    {
        if ($expected < 1 || $filled < 1) {
            return 'Not started';
        }

        return $filled >= $expected ? 'Complete' : 'Incomplete';
    }

    public static function remarkForScore(?float $score): string
    {
        if ($score === null) {
            return '—';
        }

        return $score >= AcademicThresholds::PASSING_PERCENTAGE ? 'Passed' : 'Failed';
    }

    public static function descriptorForScore(?float $score): string
    {
        if ($score === null) {
            return '—';
        }
        if ($score >= 90) {
            return 'Outstanding';
        }
        if ($score >= 85) {
            return 'Very Satisfactory';
        }
        if ($score >= 80) {
            return 'Satisfactory';
        }
        if ($score >= AcademicThresholds::PASSING_PERCENTAGE) {
            return 'Fairly Satisfactory';
        }

        return 'Did Not Meet Expectations';
    }

    private function statusForStudent(Collection $grades, ?int $sectionId, int $yearId, ?int $focusQuarter, bool $descriptive): string
    {
        $expectedIds = $this->expectedSubjectIds($sectionId, $yearId);
        if ($expectedIds->isEmpty()) {
            $expectedIds = $grades->pluck('subject_id')->map(fn ($id) => (int) $id)->unique()->values();
        }

        $quarters = $focusQuarter ? [$focusQuarter] : [1, 2, 3, 4];
        $filled = 0;
        foreach ($expectedIds as $subjectId) {
            $grade = $grades->first(fn ($row) => (int) $row->subject_id === (int) $subjectId);
            foreach ($quarters as $quarter) {
                if ($this->quarterFilled($grade, $quarter, $descriptive)) {
                    $filled++;
                }
            }
        }

        return self::reportStatus($filled, $expectedIds->count() * count($quarters));
    }

    private function expectedSubjectIds(?int $sectionId, int $yearId): Collection
    {
        if (! $sectionId) {
            return collect();
        }

        $fromSchedule = DB::table('class_schedules')
            ->where('academic_year_id', $yearId)
            ->where('section_id', $sectionId)
            ->where('is_active', true)
            ->whereNotNull('subject_id')
            ->pluck('subject_id');
        $fromPivot = Schema::hasTable('section_subject')
            ? DB::table('section_subject')->where('section_id', $sectionId)->pluck('subject_id')
            : collect();

        return $fromSchedule->merge($fromPivot)->map(fn ($id) => (int) $id)->unique()->values();
    }

    private function quarterFilled($grade, int $quarter, bool $descriptive): bool
    {
        if (! $grade) {
            return false;
        }

        if ($descriptive) {
            return \App\Support\DescriptiveScale::get($grade->{'q'.$quarter.'_level'}) !== null;
        }

        $value = $grade->{'quarter_'.$quarter};

        return $value !== null && $value !== '';
    }

    /**
     * Put MAPEH parent first, then Music/Arts/P.E./Health indented when present.
     */
    protected function orderLearningRows(Collection $rows): Collection
    {
        $mapehChildren = ['music', 'arts', 'p.e.', 'pe', 'physical education', 'health'];
        $parent = null;
        $children = collect();
        $others = collect();

        foreach ($rows as $row) {
            $name = strtolower(trim($row->subject->subject_name ?? ''));
            if ($name === 'mapeh') {
                $parent = $row;
                continue;
            }
            $isChild = false;
            foreach ($mapehChildren as $needle) {
                if ($name === $needle || str_contains($name, $needle)) {
                    $children->push($row);
                    $isChild = true;
                    break;
                }
            }
            if (! $isChild) {
                $others->push($row);
            }
        }

        $ordered = $others;
        if ($parent) {
            $ordered->push($parent);
        }
        foreach ($children as $child) {
            $ordered->push($child);
        }

        return $ordered->values();
    }

    public static function isMapehChild(?string $subjectName): bool
    {
        $name = strtolower(trim((string) $subjectName));
        foreach (['music', 'arts', 'p.e.', 'pe', 'physical education', 'health'] as $needle) {
            if ($name === $needle || str_contains($name, $needle)) {
                return $name !== 'mapeh';
            }
        }

        return false;
    }

    protected function emptyAttendanceRows(): array
    {
        $zeros = [];
        foreach (array_keys(self::ATTENDANCE_MONTHS) as $m) {
            $zeros[$m] = 0;
        }
        $zeros['total'] = 0;

        return [
            'school_days' => $zeros,
            'present' => $zeros,
            'late' => $zeros,
            'excused' => $zeros,
            'absent' => $zeros,
        ];
    }

    /**
     * Monthly attendance for Jun–Mar within the academic year (distinct school days).
     */
    protected function buildAttendanceRows(Student $student, AcademicYear $academicYear): array
    {
        $rows = $this->emptyAttendanceRows();

        $query = Attendance::where('student_id', $student->id);
        if ($academicYear->start_date && $academicYear->end_date) {
            $query->whereBetween('date', [
                $academicYear->start_date->toDateString(),
                $academicYear->end_date->toDateString(),
            ]);
        }

        // One status per calendar day: present > late > excused > absent.
        $rank = ['present' => 4, 'late' => 3, 'excused' => 2, 'absent' => 1];
        $byDate = [];
        foreach ($query->get(['date', 'status']) as $record) {
            $day = Carbon::parse($record->date)->toDateString();
            $status = strtolower((string) $record->status);
            if (! isset($byDate[$day]) || ($rank[$status] ?? 0) > ($rank[$byDate[$day]] ?? 0)) {
                $byDate[$day] = $status;
            }
        }

        foreach ($byDate as $day => $status) {
            $month = (int) Carbon::parse($day)->format('n');
            if (! array_key_exists($month, self::ATTENDANCE_MONTHS)) {
                continue;
            }
            $rows['school_days'][$month]++;
            $rows['school_days']['total']++;
            $bucket = in_array($status, ['present', 'late', 'excused', 'absent'], true) ? $status : 'absent';
            $rows[$bucket][$month]++;
            $rows[$bucket]['total']++;
        }

        return $rows;
    }
}
