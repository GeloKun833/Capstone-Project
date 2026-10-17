<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\GradeAlert;
use App\Models\Semester;
use App\Models\Student;
use App\Models\Subject;
use App\Services\StudentPerformanceService;
use App\Services\TeacherClassAssignmentService;
use App\Support\AcademicThresholds;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class PerformanceHubController extends Controller
{
    public function __construct(private StudentPerformanceService $performance)
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        if (! in_array($user->role_name, ['Teacher', 'Admin'], true)) {
            abort(403);
        }

        $tab = $request->get('tab', 'ranking');
        if (! in_array($tab, ['ranking', 'analytics', 'alerts'], true)) {
            $tab = 'ranking';
        }

        $selectedYear = AcademicYear::active();
        $selectedAcademicYearId = (int) ($selectedYear?->id ?? 0);

        $semesters = Semester::where('academic_year_id', $selectedAcademicYearId)
            ->orderBy('id')
            ->get();

        $selectedSemesterId = (int) ($request->get('semester_id') ?: 0);
        if (! $semesters->contains('id', $selectedSemesterId)) {
            $selectedSemesterId = (int) ($semesters->first()->id ?? 0);
        }

        if ($user->role_name === 'Teacher' && $user->teacher) {
            $assignmentOptions = app(TeacherClassAssignmentService::class)->optionsFor($user->teacher, $selectedAcademicYearId ?: null);
            $sections = $assignmentOptions['sections'];
            $subjects = $assignmentOptions['subjects'];
            $allowedStudentIds = $this->performance->studentIdsForTeacher($user->teacher, $selectedAcademicYearId ?: null, $sections);
        } else {
            $sections = $this->performance->sectionsForUser($user, $selectedAcademicYearId ?: null);
            $subjects = Subject::query()->orderBy('class')->orderBy('subject_name')->get(['id', 'subject_name', 'class']);
            $allowedStudentIds = $this->performance->allowedStudentIdsForUser($user, $selectedAcademicYearId ?: null);
        }
        $gradeLevels = $sections->pluck('grade_level')->filter()->unique()->sort()->values();
        $selectedGrade = trim((string) $request->get('grade_level', ''));
        if ($selectedGrade !== '' && ! $gradeLevels->contains($selectedGrade)) {
            $selectedGrade = '';
        }

        $selectedSectionId = $request->filled('section_id') ? (int) $request->get('section_id') : null;
        if ($selectedSectionId && ! $sections->contains('id', $selectedSectionId)) {
            $selectedSectionId = null;
        }
        if ($selectedSectionId && $selectedGrade !== '') {
            $section = $sections->firstWhere('id', $selectedSectionId);
            if (! $section || $section->grade_level !== $selectedGrade) {
                $selectedSectionId = null;
            }
        }

        $selectedSubjectId = $request->filled('subject_id') ? (int) $request->get('subject_id') : null;
        if ($selectedSubjectId && ! $subjects->contains('id', $selectedSubjectId)) {
            $selectedSubjectId = null;
        }

        $search = mb_substr(trim(strip_tags((string) $request->query('q', ''))), 0, 80);
        $performanceStatus = (string) $request->get('performance_status', '');
        if (! in_array($performanceStatus, ['', 'passed', 'below_passing', 'incomplete', 'attendance'], true)) {
            $performanceStatus = '';
        }

        $routePrefix = $user->role_name === 'Admin' ? 'admin' : 'teacher';

        $rankingRows = collect();
        $sectionSummary = null;
        $students = collect();
        $analytics = null;
        $selectedStudentId = $request->filled('student_id') ? (int) $request->get('student_id') : null;
        $alerts = null;
        $alertFilter = $request->get('alert_status', 'open');
        $alertType = $request->get('alert_type');
        $monitor = null;
        $kpis = null;
        $charts = null;
        $attendanceSummary = null;
        $lastUpdated = null;
        $matchCount = 0;
        $loadError = null;

        if ($selectedAcademicYearId && $selectedSemesterId) {
            try {
            $hub = $this->buildHub(
                $request,
                $selectedYear,
                $selectedAcademicYearId,
                $selectedSemesterId,
                $selectedSectionId,
                $selectedGrade,
                $selectedSubjectId,
                $search,
                $performanceStatus,
                $allowedStudentIds
            );
            $kpis = $hub['kpis'];
            $charts = $hub['charts'];
            $attendanceSummary = $hub['attendance'];
            $monitor = $hub['monitor'];
            $matchCount = $hub['matchCount'];
            $lastUpdated = $hub['lastUpdated'];
            $sectionSummary = [
                'students' => $kpis['students'],
                'class_average' => $kpis['average'],
                'class_gpa' => $kpis['gpa'],
                'passed' => $kpis['passed'],
                'failed' => $kpis['below_passing'],
                'open_alerts' => $kpis['open_alerts'],
            ];

            if ($selectedStudentId && ($allowedStudentIds === null || in_array($selectedStudentId, $allowedStudentIds, true))) {
                $student = Student::find($selectedStudentId);
                if ($student) {
                    $analytics = $this->performance->studentAnalytics(
                        $student,
                        $selectedAcademicYearId,
                        $selectedSemesterId
                    );
                    $analytics['student'] = $student;
                }
            }

            $alertsQuery = GradeAlert::with(['student', 'subject', 'resolvedBy'])
                ->where('academic_year_id', $selectedAcademicYearId)
                ->where(function ($query) use ($selectedSemesterId) {
                    $query->where('semester_id', $selectedSemesterId)->orWhereNull('semester_id');
                })
                ->orderByDesc('created_at');

            if ($allowedStudentIds !== null) {
                $alertsQuery->whereIn('student_id', $allowedStudentIds);
            }
            if ($hub['studentIds'] !== []) {
                $alertsQuery->whereIn('student_id', $hub['studentIds']);
            } elseif ($search !== '' || $performanceStatus !== '' || $selectedSubjectId || $selectedGrade !== '') {
                $alertsQuery->whereRaw('1 = 0');
            }
            if ($alertFilter === 'open') {
                $alertsQuery->where('is_resolved', false);
            } elseif ($alertFilter === 'resolved') {
                $alertsQuery->where('is_resolved', true);
            }
            if ($alertType) {
                $alertsQuery->where('alert_type', $alertType);
            }

            $alerts = $alertsQuery->paginate(15, ['*'], 'alerts_page')->withQueryString();
            } catch (\Throwable $exception) {
                report($exception);
                $loadError = 'Performance data could not be loaded. Refresh the page to try again.';
            }
        }

        return view('grading.performance-hub', compact(
            'tab',
            'semesters',
            'sections',
            'gradeLevels',
            'subjects',
            'selectedAcademicYearId',
            'selectedYear',
            'selectedSemesterId',
            'selectedSectionId',
            'selectedGrade',
            'selectedSubjectId',
            'selectedStudentId',
            'search',
            'performanceStatus',
            'rankingRows',
            'sectionSummary',
            'students',
            'analytics',
            'alerts',
            'alertFilter',
            'alertType',
            'routePrefix',
            'monitor',
            'kpis',
            'charts',
            'attendanceSummary',
            'lastUpdated',
            'matchCount',
            'loadError'
        ));
    }

    private function buildHub(
        Request $request,
        ?AcademicYear $year,
        int $academicYearId,
        int $semesterId,
        ?int $sectionId,
        string $gradeLevel,
        ?int $subjectId,
        string $search,
        string $performanceStatus,
        ?array $allowedStudentIds
    ): array {
        $yearStudentIds = collect()
            ->merge(DB::table('student_section_assignments')->where('academic_year_id', $academicYearId)->pluck('student_id'))
            ->merge(DB::table('enrollments')->where('academic_year_id', $academicYearId)->pluck('student_id'))
            ->merge(DB::table('quarterly_grades')->where('academic_year_id', $academicYearId)->pluck('student_id'))
            ->merge(DB::table('student_gpa')->where('academic_year_id', $academicYearId)->where('semester_id', $semesterId)->pluck('student_id'))
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();
        if ($allowedStudentIds !== null) {
            $allowed = array_flip($allowedStudentIds);
            $yearStudentIds = $yearStudentIds->filter(fn (int $id) => isset($allowed[$id]))->values();
        }

        $scope = Student::query()
            ->select(['id', 'first_name', 'last_name', 'middle_name', 'admission_id', 'roll'])
            ->with(['sections' => function ($query) use ($academicYearId) {
                $query->select('sections.id', 'sections.name', 'sections.grade_level')
                    ->where('student_section_assignments.academic_year_id', $academicYearId);
            }])
            ->whereIn('id', $yearStudentIds->all() ?: [0]);

        if ($sectionId) {
            $scope->whereHas('sections', function ($query) use ($sectionId, $academicYearId) {
                $query->where('sections.id', $sectionId)
                    ->where('student_section_assignments.academic_year_id', $academicYearId);
            });
        }
        if ($gradeLevel !== '') {
            $scope->whereHas('sections', function ($query) use ($gradeLevel, $academicYearId) {
                $query->where('sections.grade_level', $gradeLevel)
                    ->where('student_section_assignments.academic_year_id', $academicYearId);
            });
        }
        if ($subjectId) {
            $scope->whereHas('quarterlyGrades', function ($query) use ($subjectId, $academicYearId) {
                $query->where('academic_year_id', $academicYearId)->where('subject_id', $subjectId);
            });
        }
        if ($search !== '') {
            $like = '%'.$search.'%';
            $scope->where(function ($query) use ($like, $academicYearId) {
                $query->where('first_name', 'like', $like)
                    ->orWhere('last_name', 'like', $like)
                    ->orWhere('middle_name', 'like', $like)
                    ->orWhere('admission_id', 'like', $like)
                    ->orWhere('roll', 'like', $like)
                    ->orWhereRaw("CONCAT(first_name, ' ', last_name) like ?", [$like])
                    ->orWhereRaw("CONCAT(last_name, ' ', first_name) like ?", [$like])
                    ->orWhereHas('sections', function ($sections) use ($like, $academicYearId) {
                        $sections->where('student_section_assignments.academic_year_id', $academicYearId)
                            ->where(function ($match) use ($like) {
                                $match->where('sections.name', 'like', $like)
                                    ->orWhere('sections.grade_level', 'like', $like);
                            });
                    })
                    ->orWhereHas('quarterlyGrades', function ($grades) use ($like, $academicYearId) {
                        $grades->where('academic_year_id', $academicYearId)
                            ->whereHas('subject', fn ($subjects) => $subjects->where('subjects.subject_name', 'like', $like));
                    });
            });
        }

        $students = $scope->orderBy('last_name')->orderBy('first_name')->get();
        $studentIds = $students->pluck('id')->map(fn ($id) => (int) $id)->all();
        $scores = $this->performance->periodScores($academicYearId, $semesterId, $studentIds);
        $scoreByStudent = $scores['students'];

        $openAlerts = $studentIds === []
            ? collect()
            : GradeAlert::query()
                ->where('academic_year_id', $academicYearId)
                ->where(function ($query) use ($semesterId) {
                    $query->where('semester_id', $semesterId)->orWhereNull('semester_id');
                })
                ->where('is_resolved', false)
                ->whereIn('student_id', $studentIds)
                ->get(['id', 'student_id', 'message'])
                ->groupBy(fn ($alert) => (int) $alert->student_id);

        $attendanceByStudent = $this->attendanceByStudent($year, $studentIds);

        $rows = $students->map(function (Student $student) use ($scoreByStudent, $openAlerts, $attendanceByStudent) {
            $score = $scoreByStudent[(int) $student->id] ?? null;
            $section = $student->sections->first();
            $attendance = $attendanceByStudent[$student->id] ?? null;
            $average = $score['average'] ?? null;
            $reasons = [];

            if ($average === null) {
                $reasons[] = 'Incomplete grades for this semester';
            } elseif ($average < AcademicThresholds::PASSING_PERCENTAGE) {
                $reasons[] = 'Below the passing average of '.number_format(AcademicThresholds::PASSING_PERCENTAGE, 0).'%';
            }
            if ($attendance && $attendance['total'] > 0) {
                if ($attendance['percentage'] < AcademicThresholds::ATTENDANCE_CONCERN_PERCENTAGE) {
                    $reasons[] = 'Attendance is below '.number_format(AcademicThresholds::ATTENDANCE_CONCERN_PERCENTAGE, 0).'%';
                }
                if ($attendance['absent'] >= AcademicThresholds::RECURRING_ABSENCE_COUNT) {
                    $reasons[] = $attendance['absent'].' absences in this academic year';
                }
            }
            foreach ($openAlerts->get($student->id, collect()) as $alert) {
                if ($alert->message) {
                    $reasons[] = $alert->message;
                }
            }
            $reasons = array_values(array_unique($reasons));

            $indicator = 'On track';
            if ($average !== null && $average < AcademicThresholds::PASSING_PERCENTAGE) {
                $indicator = 'Below passing';
            } elseif ($attendance && ($attendance['percentage'] < AcademicThresholds::ATTENDANCE_CONCERN_PERCENTAGE || $attendance['absent'] >= AcademicThresholds::RECURRING_ABSENCE_COUNT)) {
                $indicator = 'Attendance concern';
            } elseif ($average === null) {
                $indicator = 'Incomplete';
            } elseif ($openAlerts->has($student->id)) {
                $indicator = 'Open alert';
            }

            return (object) [
                'student' => $student,
                'number' => $student->admission_id ?: ($student->roll ?: '—'),
                'grade' => $section->grade_level ?? '—',
                'section' => $section->name ?? '—',
                'average' => $average,
                'gpa' => $score['gpa'] ?? null,
                'attendance' => $attendance['percentage'] ?? null,
                'attendance_known' => (bool) $attendance,
                'indicator' => $indicator,
                'reasons' => $reasons,
                'updated_at' => null,
                'rank' => null,
            ];
        });

        if ($performanceStatus === 'passed') {
            $rows = $rows->filter(fn ($row) => $row->average !== null && $row->average >= AcademicThresholds::PASSING_PERCENTAGE);
        } elseif ($performanceStatus === 'below_passing') {
            $rows = $rows->filter(fn ($row) => $row->average !== null && $row->average < AcademicThresholds::PASSING_PERCENTAGE);
        } elseif ($performanceStatus === 'incomplete') {
            $rows = $rows->filter(fn ($row) => $row->average === null);
        } elseif ($performanceStatus === 'attendance') {
            $rows = $rows->filter(fn ($row) => $row->indicator === 'Attendance concern' || collect($row->reasons)->contains(fn ($reason) => str_contains($reason, 'absences') || str_contains($reason, 'Attendance')));
        }

        $rows = $rows->values();
        $withAverage = $rows->filter(fn ($row) => $row->average !== null);
        $below = $withAverage->filter(fn ($row) => $row->average < AcademicThresholds::PASSING_PERCENTAGE);
        $page = max(1, (int) $request->get('page', 1));
        $monitor = new LengthAwarePaginator(
            $rows->slice(($page - 1) * 15, 15)->values(),
            $rows->count(),
            15,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        $ids = array_flip($rows->map(fn ($row) => (int) $row->student->id)->all());
        $charts = $this->chartPayload($rows, $scores['fields'], $scores['grades']->filter(fn ($grade) => isset($ids[(int) $grade->student_id])));
        $attendance = $this->attendanceSummary($year, $attendanceByStudent, $rows);
        $lastUpdated = $scores['grades']
            ->filter(fn ($grade) => isset($ids[(int) $grade->student_id]))
            ->max('updated_at');

        return [
            'kpis' => [
                'students' => $rows->count(),
                'average' => $withAverage->isNotEmpty() ? round($withAverage->avg('average'), 2) : null,
                'graded' => $withAverage->count(),
                'gpa' => $withAverage->isNotEmpty() ? round($withAverage->avg('gpa'), 2) : null,
                'passed' => $withAverage->count() - $below->count(),
                'below_passing' => $below->count(),
                'incomplete' => $rows->filter(fn ($row) => $row->average === null)->count(),
                'open_alerts' => $rows->sum(fn ($row) => $openAlerts->get($row->student->id, collect())->count()),
                'attendance' => $attendance['percentage'],
                'attendance_ready' => $attendance['ready'],
            ],
            'charts' => $charts,
            'attendance' => $attendance,
            'monitor' => $monitor,
            'matchCount' => $rows->count(),
            'studentIds' => array_keys($ids),
            'lastUpdated' => $lastUpdated,
        ];
    }

    private function attendanceByStudent(?AcademicYear $year, array $studentIds): array
    {
        if (! $year?->start_date || ! $year?->end_date || $studentIds === []) {
            return [];
        }

        $rows = DB::table('attendances')
            ->whereIn('student_id', $studentIds)
            ->where('date', '>=', $year->start_date->toDateString())
            ->where('date', '<=', $year->end_date->toDateString())
            ->selectRaw("student_id,
                SUM(status = 'present') as present,
                SUM(status = 'late') as late,
                SUM(status = 'absent') as absent,
                SUM(status = 'excused') as excused,
                COUNT(*) as total")
            ->groupBy('student_id')
            ->get();

        $rates = [];
        foreach ($rows as $row) {
            $rates[(int) $row->student_id] = $this->attendanceRate($row);
        }

        return $rates;
    }

    private function attendanceRate(object $row): array
    {
        $total = (int) $row->total;
        $excused = (int) $row->excused;
        $attended = (int) $row->present + (int) $row->late;
        $rateBase = max(0, $total - $excused);

        return [
            'total' => $total,
            'present' => (int) $row->present,
            'late' => (int) $row->late,
            'excused' => $excused,
            'absent' => (int) $row->absent,
            'percentage' => $rateBase > 0 ? round(($attended / $rateBase) * 100, 2) : 0,
        ];
    }

    private function attendanceSummary(?AcademicYear $year, array $byStudent, $rows): array
    {
        $ready = (bool) ($year?->start_date && $year?->end_date);
        $empty = [
            'ready' => $ready,
            'percentage' => null,
            'months' => [],
            'month_rates' => [],
            'grades' => [],
            'grade_absences' => [],
            'recurring' => 0,
        ];
        if (! $ready || $rows->isEmpty()) {
            return $empty;
        }

        $present = $late = $absent = $excused = $total = 0;
        $absences = [];
        foreach ($rows as $row) {
            $stat = $byStudent[(int) $row->student->id] ?? null;
            if (! $stat || $stat['total'] < 1) {
                continue;
            }
            $present += $stat['present'];
            $late += $stat['late'];
            $absent += $stat['absent'];
            $excused += $stat['excused'];
            $total += $stat['total'];
            if ($stat['absent'] > 0) {
                $absences[$row->grade] = ($absences[$row->grade] ?? 0) + $stat['absent'];
            }
        }

        $months = DB::table('attendances')
            ->whereIn('student_id', $rows->map(fn ($row) => (int) $row->student->id)->all())
            ->where('date', '>=', $year->start_date->toDateString())
            ->where('date', '<=', $year->end_date->toDateString())
            ->selectRaw("DATE_FORMAT(date, '%Y-%m') as month,
                SUM(status = 'present') as present,
                SUM(status = 'late') as late,
                SUM(status = 'excused') as excused,
                COUNT(*) as total")
            ->groupByRaw("DATE_FORMAT(date, '%Y-%m')")
            ->orderBy('month')
            ->get();

        $rateBase = max(0, $total - $excused);

        return [
            'ready' => true,
            'percentage' => $total > 0 && $rateBase > 0 ? round((($present + $late) / $rateBase) * 100, 2) : null,
            'months' => $months->pluck('month')->all(),
            'month_rates' => $months->map(function ($month) {
                $attended = (int) $month->present + (int) $month->late;
                $base = max(0, (int) $month->total - (int) $month->excused);

                return $base > 0 ? round(($attended / $base) * 100, 2) : 0;
            })->all(),
            'grades' => array_keys($absences),
            'grade_absences' => array_values($absences),
            'recurring' => $rows->filter(fn ($row) => $row->indicator === 'Attendance concern')->count(),
        ];
    }

    private function chartPayload($rows, array $fields, $grades): array
    {
        $bands = ['90–100' => 0, '85–89' => 0, '80–84' => 0, '75–79' => 0, 'Below 75' => 0, 'Incomplete' => 0];
        foreach ($rows as $row) {
            if ($row->average === null) {
                $bands['Incomplete']++;
            } elseif ($row->average >= 90) {
                $bands['90–100']++;
            } elseif ($row->average >= 85) {
                $bands['85–89']++;
            } elseif ($row->average >= 80) {
                $bands['80–84']++;
            } elseif ($row->average >= 75) {
                $bands['75–79']++;
            } else {
                $bands['Below 75']++;
            }
        }

        $byGrade = $rows->filter(fn ($row) => $row->average !== null)->groupBy('grade')->map(fn ($group) => round($group->avg('average'), 2));
        $quarters = [];
        $subjectAverages = [];

        foreach ($fields as $field) {
            $label = strtoupper(str_replace('quarter_', 'Q', $field));
            $values = $grades->pluck($field)->filter(fn ($value) => $value !== null && $value !== '')->map(fn ($value) => (float) $value);
            $quarters[$label] = $values->isNotEmpty() ? round($values->avg(), 2) : null;
        }
        if ($grades->isNotEmpty()) {
            $subjectAverages = $grades->groupBy('subject_id')->map(function ($group) use ($fields) {
                $scores = $group->map(fn ($row) => $this->performance->subjectPercentage($row, $fields))->filter(fn ($value) => $value !== null);

                return [
                    'name' => $group->first()->subject_name ?? ($group->first()->subject->subject_name ?? 'Subject'),
                    'average' => $scores->isNotEmpty() ? round($scores->avg(), 2) : null,
                ];
            })->filter(fn ($item) => $item['average'] !== null)->sortByDesc('average')->take(8)->values();
        }

        return [
            'distribution_labels' => array_keys($bands),
            'distribution_values' => array_values($bands),
            'grade_labels' => $byGrade->keys()->values()->all(),
            'grade_values' => $byGrade->values()->all(),
            'quarter_labels' => array_keys($quarters),
            'quarter_values' => array_values($quarters),
            'subject_labels' => collect($subjectAverages)->pluck('name')->all(),
            'subject_values' => collect($subjectAverages)->pluck('average')->all(),
        ];
    }

    public function resolveAlert(Request $request, GradeAlert $alert)
    {
        $user = Auth::user();
        if (! in_array($user->role_name, ['Teacher', 'Admin'], true)) {
            abort(403);
        }

        $allowed = $this->performance->allowedStudentIdsForUser($user, $alert->academic_year_id ? (int) $alert->academic_year_id : null);
        if ($allowed !== null && ! in_array((int) $alert->student_id, $allowed, true)) {
            abort(403, 'You cannot resolve alerts for this student.');
        }

        $this->performance->resolveAlert($alert, $user);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Alert resolved.']);
        }

        return redirect()->back()->with('success', 'Alert resolved successfully.');
    }

    public function exportGpa(Request $request)
    {
        $user = Auth::user();
        if (! in_array($user->role_name, ['Teacher', 'Admin'], true)) {
            abort(403);
        }

        $academicYearId = (int) (AcademicYear::active()?->id ?? 0);
        $semesterId = (int) $request->get('semester_id');
        $sectionId = $request->filled('section_id') ? (int) $request->get('section_id') : null;
        $semesterBelongsToYear = $academicYearId && Semester::query()
            ->where('id', $semesterId)
            ->where('academic_year_id', $academicYearId)
            ->exists();
        if (! $semesterBelongsToYear) {
            return back()->with('error', 'The GPA export uses the current academic year.');
        }
        $format = $request->get('format', 'pdf');
        if ($format !== 'pdf') {
            $format = 'pdf';
        }

        $allowed = $this->performance->allowedStudentIdsForUser($user, $academicYearId ?: null);

        $fileName = 'gpa_report_'.date('Y-m-d_H-i-s');

        $query = \App\Models\StudentGpa::with(['student', 'academicYear', 'semester'])
            ->where('academic_year_id', $academicYearId)
            ->where('semester_id', $semesterId)
            ->orderByDesc('gpa');

        if ($sectionId) {
            $query->whereHas('student.sections', fn ($q) => $q->where('sections.id', $sectionId));
        }
        if ($allowed !== null) {
            $query->whereIn('student_id', $allowed);
        }

        $gpaRecords = $query->get();

        if ($format === 'pdf') {
            $pdf = Pdf::loadView('exports.gpa-pdf', compact('gpaRecords'));

            return $pdf->download($fileName.'.pdf');
        }

        return back()->with('error', 'Export format not supported.');
    }

    /** Legacy redirects keep old bookmarks working. */
    public function redirectRanking(Request $request)
    {
        return redirect()->route(
            Auth::user()->role_name === 'Admin' ? 'admin.grading.performance-hub' : 'teacher.grading.performance-hub',
            array_merge($request->query(), ['tab' => 'ranking'])
        );
    }

    public function redirectAnalytics(Request $request)
    {
        return redirect()->route(
            Auth::user()->role_name === 'Admin' ? 'admin.grading.performance-hub' : 'teacher.grading.performance-hub',
            array_merge($request->query(), ['tab' => 'analytics'])
        );
    }

    public function redirectAlerts(Request $request)
    {
        return redirect()->route(
            Auth::user()->role_name === 'Admin' ? 'admin.grading.performance-hub' : 'teacher.grading.performance-hub',
            array_merge($request->query(), ['tab' => 'alerts'])
        );
    }
}
