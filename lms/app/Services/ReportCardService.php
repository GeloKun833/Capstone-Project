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

        if (! $academicYear) {
            return compact(
                'student',
                'academicYear',
                'observedGrouped',
                'observedRatings',
                'learningRows',
                'quarterAverages',
                'generalAverages',
                'attendanceMonths',
                'attendanceRows'
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

        foreach (['q1' => 'quarter_1', 'q2' => 'quarter_2', 'q3' => 'quarter_3', 'q4' => 'quarter_4', 'final' => 'final_grade'] as $key => $field) {
            $values = $grades->pluck($field)->filter(fn ($v) => $v !== null && $v !== '')->map(fn ($v) => (float) $v);
            $generalAverages[$key] = $values->isNotEmpty() ? round($values->avg(), 2) : null;
            $quarterAverages[$key] = $generalAverages[$key];
        }

        $attendanceRows = $this->buildAttendanceRows($student, $academicYear);

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
        ];
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
