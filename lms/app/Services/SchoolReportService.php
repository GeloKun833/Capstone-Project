<?php

namespace App\Services;

use App\Models\AcademicYear;
use App\Models\User;
use App\Support\AcademicThresholds;
use App\Support\DescriptiveScale;
use App\Support\SchoolReportCatalog;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SchoolReportService
{
    private ?AcademicYear $year = null;

    private int $yearId = 0;

    private int $semesterId = 0;

    private ?array $studentIds = null;

    private array $sectionIds = [];

    private ?User $user = null;

    private ?array $scoreCache = null;

    public function __construct(private StudentPerformanceService $performance)
    {
    }

    public function available(User $user): array
    {
        return array_values(array_filter(
            SchoolReportCatalog::all(),
            fn (array $report) => $this->allows($user, $report['roles'])
        ));
    }

    public function allows(User $user, array $roles): bool
    {
        return in_array($user->role_name, $roles, true);
    }

    public function make(User $user, string $key, int|array|null $sectionId = null): array
    {
        $report = SchoolReportCatalog::find($key);
        if (! $report || ! $this->allows($user, $report['roles']) || $report['href']) {
            abort(403);
        }

        $this->user = $user;
        $this->year = AcademicYear::active();
        $this->yearId = (int) ($this->year?->id ?? 0);
        $this->semesterId = $this->yearId
            ? (int) (DB::table('semesters')->where('academic_year_id', $this->yearId)->orderBy('id')->value('id') ?? 0)
            : 0;
        $this->sectionIds = array_values(array_unique(array_filter(array_map(
            'intval',
            is_array($sectionId) ? $sectionId : ($sectionId ? [$sectionId] : [])
        ))));
        $this->studentIds = null;
        $this->scoreCache = null;
        if ($user->role_name === 'Teacher') {
            $this->studentIds = $user->teacher
                ? $this->performance->studentIdsForTeacher($user->teacher, $this->yearId ?: null)
                : [];
        }

        $built = $this->build($key);

        return [
            'report' => $report,
            'year' => $this->year,
            'columns' => $built['columns'],
            'rows' => $built['rows'],
            'summary' => $built['summary'] ?? [],
            'note' => $built['note'] ?? $report['description'],
        ];
    }

    private function build(string $key): array
    {
        $yearFree = [
            'enrollment-by-year', 'enrollment-grade-year', 'attendance-by-year',
            'user-accounts', 'account-status', 'login-history', 'account-changes',
            'grade-audit', 'attendance-audit', 'student-audit', 'export-history', 'failed-operations',
            'announcements-history', 'notifications-sent', 'notification-delivery',
            'school-events', 'communication-history', 'teacher-master-list', 'curriculum-offering',
        ];
        if (! $this->yearId && ! in_array($key, $yearFree, true)) {
            return $this->blank('The admin has not set a current academic year, so this report has no rows.');
        }

        return match ($key) {
            'enrollment-by-year' => $this->enrollmentByYear(),
            'enrollment-by-grade', 'enrollment-statistics' => $this->enrollmentByGrade(),
            'enrolled-master-list' => $this->masterList(),
            'new-vs-returning' => $this->newVsReturning(),
            'pending-applications' => $this->applications("a.status in ('pending','under_review','needs_documents')"),
            'enrollment-status' => $this->enrollmentStatus(),
            'section-assignment' => $this->masterList(true),
            'student-profile' => $this->studentProfile(),
            'withdrawn-transferred' => $this->withdrawnTransferred(),
            'incomplete-requirements' => $this->incompleteRequirements(),
            'class-grade-sheet' => $this->classGradeSheet(),
            'subject-grade-summary' => $this->subjectSummary(),
            'grade-level-performance', 'grade-level-comparison' => $this->gradeLevelPerformance(),
            'quarterly-performance' => $this->quarterlyPerformance(),
            'academic-ranking' => $this->ranking(),
            'failing-grades', 'students-at-risk', 'academic-risk' => $this->failingGrades(),
            'missing-grades', 'incomplete-records' => $this->missingGrades(),
            'grade-change-history', 'grade-audit' => $this->audit('%Grade%', 'No grade changes are recorded.'),
            'descriptive-assessment' => $this->descriptiveAssessment(),
            'daily-attendance' => $this->dailyAttendance(),
            'monthly-attendance' => $this->monthlyAttendance(),
            'student-attendance', 'attendance-percentage' => $this->studentAttendance(),
            'class-attendance' => $this->classAttendance(),
            'absences-by-grade' => $this->absencesByGrade(),
            'excessive-absences' => $this->excessiveAbsences(),
            'unexcused-absences' => $this->unexcusedAbsences(),
            'attendance-changes', 'attendance-audit' => $this->audit('%Attendance%', 'No attendance changes are recorded.'),
            'attendance-by-year' => $this->attendanceByYear(),
            'teacher-master-list' => $this->teacherList(),
            'teaching-load', 'teacher-workload' => $this->teachingLoad(),
            'teaching-load-by-grade' => $this->teachingLoadByGrade(),
            'subject-assignment' => $this->subjectAssignment(),
            'class-section-list' => $this->sectionList(),
            'class-schedule' => $this->classSchedule(),
            'schedule-conflicts' => $this->scheduleConflicts(),
            'unassigned-subjects' => $this->unassigned(),
            'teacher-availability' => $this->teacherAvailability(),
            'curriculum-offering' => $this->curriculumOffering(),
            'course-outlines', 'curriculum-implementation' => $this->courseOutlines(),
            'lesson-inventory' => $this->lessonInventory(),
            'activities-list' => $this->activitiesList(),
            'submission-status' => $this->submissionStatus(),
            'missing-submissions' => $this->missingSubmissions(),
            'assignment-completion' => $this->assignmentCompletion(),
            'activity-performance' => $this->activityPerformance(),
            'upcoming-deadlines' => $this->upcomingDeadlines(),
            'lesson-posting' => $this->lessonPosting(),
            'material-uploads' => $this->materialUploads(),
            'announcements-history' => $this->announcements(),
            'notifications-sent' => $this->notifications(false),
            'notification-delivery' => $this->notifications(true),
            'consultation-requests' => $this->consultations(false),
            'consultation-schedule' => $this->consultations(true),
            'consultation-status' => $this->consultationStatus(),
            'school-events' => $this->events(),
            'communication-history' => $this->communicationHistory(),
            'document-checklist' => $this->documents(null),
            'missing-documents' => $this->documents('missing'),
            'submitted-documents' => $this->documents('submitted'),
            'documents-pending' => $this->documents('pending'),
            'documents-verified' => $this->documents('reviewed'),
            'record-completeness' => $this->recordCompleteness(),
            'document-history' => $this->documentHistory(),
            'transfer-documents' => $this->transferDocuments(),
            'enrollment-grade-year' => $this->enrollmentGradeYear(),
            'school-performance' => $this->schoolPerformance(),
            'absenteeism-trends' => $this->absenteeismTrends(),
            'operations-summary' => $this->operationsSummary(),
            'user-accounts' => $this->userAccounts(false),
            'account-status' => $this->userAccounts(true),
            'login-history' => $this->loginHistory(),
            'account-changes' => $this->accountChanges(),
            'student-audit' => $this->audit('%Student%', 'No student record changes are recorded.'),
            'export-history' => $this->exportHistory(),
            'failed-operations' => $this->failedOperations(),
            default => abort(404),
        };
    }

    private function enrollmentByYear(): array
    {
        $query = DB::table('student_section_assignments as a')
            ->join('academic_years as y', 'y.id', '=', 'a.academic_year_id')
            ->selectRaw('y.name, y.status, COUNT(DISTINCT a.student_id) as students')
            ->groupBy('y.id', 'y.name', 'y.status', 'y.start_date')
            ->orderBy('y.start_date');
        $this->limitSection($query, 'a.section_id');
        $rows = $query->get();

        return $this->pack(
            ['Academic year', 'Status', 'Students placed'],
            $rows->map(fn ($row) => [$row->name, ucfirst((string) $row->status), (string) $row->students])->all(),
            ['Academic years' => $rows->count()],
            'Each row is one academic year. Students are counted from section placement for that year only.'
        );
    }

    private function enrollmentByGrade(): array
    {
        $query = DB::table('student_section_assignments as a')
            ->join('sections as s', 's.id', '=', 'a.section_id')
            ->where('a.academic_year_id', $this->yearId)
            ->selectRaw('s.grade_level, COUNT(DISTINCT a.student_id) as students')
            ->groupBy('s.grade_level')
            ->orderBy('s.grade_level');
        $this->limitStudents($query, 'a.student_id');
        $this->limitSection($query, 's.id');
        $rows = $query->get();

        return $this->pack(
            ['Grade level', 'Students'],
            $rows->map(fn ($row) => [$row->grade_level ?: '—', (string) $row->students])->all(),
            ['Students' => (int) $rows->sum('students')]
        );
    }

    private function masterList(bool $placementOnly = false): array
    {
        $query = DB::table('student_section_assignments as a')
            ->join('students as st', 'st.id', '=', 'a.student_id')
            ->join('sections as s', 's.id', '=', 'a.section_id')
            ->where('a.academic_year_id', $this->yearId)
            ->whereNull('st.deleted_at')
            ->orderBy('s.grade_level')
            ->orderBy('s.name')
            ->orderBy('st.last_name')
            ->select('st.admission_id', 'st.roll', 'st.last_name', 'st.first_name', 's.grade_level', 's.name as section_name', 'st.enrollment_status');
        $this->limitStudents($query, 'st.id');
        $this->limitSection($query, 's.id');
        $rows = $query->get();

        return $this->pack(
            $placementOnly
                ? ['Student', 'Number', 'Grade', 'Section']
                : ['Student', 'Number', 'Grade', 'Section', 'Enrollment status'],
            $rows->map(function ($row) use ($placementOnly) {
                $line = [$this->person($row), $row->admission_id ?: ($row->roll ?: '—'), $row->grade_level ?: '—', $row->section_name ?: '—'];
                if (! $placementOnly) {
                    $line[] = $row->enrollment_status ?: '—';
                }

                return $line;
            })->all(),
            ['Students' => $rows->count()]
        );
    }

    private function newVsReturning(): array
    {
        $currentStart = $this->year?->start_date?->toDateString();
        $first = DB::table('student_section_assignments as a')
            ->join('academic_years as y', 'y.id', '=', 'a.academic_year_id')
            ->selectRaw('a.student_id, MIN(y.start_date) as first_start')
            ->groupBy('a.student_id');
        $query = DB::table('student_section_assignments as a')
            ->joinSub($first, 'first', 'first.student_id', '=', 'a.student_id')
            ->join('students as st', 'st.id', '=', 'a.student_id')
            ->join('sections as s', 's.id', '=', 'a.section_id')
            ->where('a.academic_year_id', $this->yearId)
            ->whereNull('st.deleted_at')
            ->orderBy('st.last_name')
            ->select('st.last_name', 'st.first_name', 'st.admission_id', 'st.roll', 's.grade_level', 's.name as section_name', 'first.first_start');
        $this->limitStudents($query, 'st.id');
        $this->limitSection($query, 's.id');
        $rows = $query->get();
        $new = 0;
        $lines = $rows->map(function ($row) use ($currentStart, &$new) {
            $isNew = $currentStart && $row->first_start && substr((string) $row->first_start, 0, 10) >= $currentStart;
            if ($isNew) {
                $new++;
            }

            return [$this->person($row), $row->admission_id ?: ($row->roll ?: '—'), $row->grade_level ?: '—', $row->section_name ?: '—', $isNew ? 'New' : 'Returning'];
        })->all();

        return $this->pack(
            ['Student', 'Number', 'Grade', 'Section', 'Type'],
            $lines,
            ['New' => $new, 'Returning' => $rows->count() - $new],
            'New means the student’s earliest section placement starts with the current academic year.'
        );
    }

    private function applications(string $statusSql): array
    {
        $query = DB::table('enrollment_applications as a')
            ->leftJoin('sections as s', 's.id', '=', 'a.preferred_section_id')
            ->where('a.academic_year_id', $this->yearId)
            ->whereNull('a.deleted_at')
            ->whereRaw($statusSql)
            ->orderBy('a.last_name')
            ->select('a.application_number', 'a.last_name', 'a.first_name', 'a.grade_level_applying_for', 's.name as section_name', 'a.student_category', 'a.status', 'a.created_at');
        $this->limitSection($query, 'a.preferred_section_id');
        $rows = $query->get();

        return $this->pack(
            ['Application', 'Applicant', 'Grade', 'Section', 'Category', 'Status', 'Filed'],
            $rows->map(fn ($row) => [
                $row->application_number ?: '—',
                $this->person($row),
                $row->grade_level_applying_for ?: '—',
                $row->section_name ?: '—',
                str_replace('_', ' ', (string) $row->student_category) ?: '—',
                str_replace('_', ' ', (string) $row->status),
                $this->when($row->created_at),
            ])->all(),
            ['Applications' => $rows->count()]
        );
    }

    private function enrollmentStatus(): array
    {
        $query = DB::table('enrollment_applications')
            ->where('academic_year_id', $this->yearId)
            ->whereNull('deleted_at')
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->orderBy('status');
        $this->limitSection($query, 'preferred_section_id');
        $rows = $query->get();

        return $this->pack(
            ['Status', 'Applications'],
            $rows->map(fn ($row) => [str_replace('_', ' ', (string) $row->status) ?: '—', (string) $row->total])->all(),
            ['Applications' => (int) $rows->sum('total')]
        );
    }

    private function studentProfile(): array
    {
        $scores = $this->scores();
        $query = DB::table('student_section_assignments as a')
            ->join('students as st', 'st.id', '=', 'a.student_id')
            ->join('sections as s', 's.id', '=', 'a.section_id')
            ->where('a.academic_year_id', $this->yearId)
            ->whereNull('st.deleted_at')
            ->orderBy('st.last_name')
            ->select('st.id', 'st.last_name', 'st.first_name', 'st.admission_id', 'st.roll', 's.grade_level', 's.name as section_name', 'st.enrollment_status');
        $this->limitStudents($query, 'st.id');
        $this->limitSection($query, 's.id');
        $rows = $query->get();

        return $this->pack(
            ['Student', 'Number', 'Grade', 'Section', 'Status', 'General average'],
            $rows->map(function ($row) use ($scores) {
                $average = $scores['students'][(int) $row->id]['average'] ?? null;

                return [
                    $this->person($row),
                    $row->admission_id ?: ($row->roll ?: '—'),
                    $row->grade_level ?: '—',
                    $row->section_name ?: '—',
                    $row->enrollment_status ?: '—',
                    $average !== null ? number_format($average, 2).'%' : 'No grades',
                ];
            })->all(),
            ['Students' => $rows->count()],
            'The general average uses the current semester’s subject scores. Passing is '.number_format(AcademicThresholds::PASSING_PERCENTAGE, 0).'%.'
        );
    }

    private function withdrawnTransferred(): array
    {
        $students = DB::table('students as st')
            ->whereNull('st.deleted_at')
            ->where(function ($query) {
                $query->where('st.enrollment_status', 'like', '%withdraw%')
                    ->orWhere('st.enrollment_status', 'like', '%transfer%')
                    ->orWhere('st.enrollment_status', 'like', '%dropped%');
            })
            ->orderBy('st.last_name')
            ->select('st.id', 'st.last_name', 'st.first_name', 'st.admission_id', 'st.roll', 'st.enrollment_status');
        $this->limitPlacedSection($students, 'st.id');
        $students = $students->get();
        $promotions = DB::table('student_promotions as p')
            ->join('students as st', 'st.id', '=', 'p.student_id')
            ->where(function ($query) {
                $query->where('p.promotion_status', 'like', '%transfer%')
                    ->orWhere('p.promotion_status', 'like', '%withdraw%');
            })
            ->select('st.id', 'st.last_name', 'st.first_name', 'st.admission_id', 'p.promotion_status', 'p.promotion_date');
        $this->limitPlacedSection($promotions, 'st.id');
        $promotions = $promotions->get();
        $names = $this->placementNames(
            $students->pluck('id')->merge($promotions->pluck('id'))->map(fn ($id) => (int) $id)->all()
        );
        $lines = $students->map(fn ($row) => [
            $this->person($row),
            $row->admission_id ?: ($row->roll ?: '—'),
            $names[(int) $row->id] ?? '—',
            $row->enrollment_status,
            '—',
        ])->all();
        foreach ($promotions as $row) {
            $lines[] = [
                $this->person($row),
                $row->admission_id ?: '—',
                $names[(int) $row->id] ?? '—',
                $row->promotion_status,
                $this->when($row->promotion_date),
            ];
        }

        return $this->pack(
            ['Student', 'Number', 'Section', 'Status', 'Date'],
            $lines,
            ['Records' => count($lines)],
            'Only students with a withdrawn, transferred, or dropped status are listed.'
        );
    }

    private function incompleteRequirements(): array
    {
        $query = DB::table('enrollment_applications as a')
            ->leftJoin('enrollment_documents as d', 'd.enrollment_application_id', '=', 'a.id')
            ->leftJoin('sections as s', 's.id', '=', 'a.preferred_section_id')
            ->where('a.academic_year_id', $this->yearId)
            ->whereNull('a.deleted_at')
            ->where(function ($query) {
                $query->where('a.status', 'needs_documents')
                    ->orWhereNull('d.id')
                    ->orWhereIn('d.status', ['pending', 'rejected']);
            })
            ->groupBy('a.id', 'a.application_number', 'a.last_name', 'a.first_name', 'a.grade_level_applying_for', 's.name', 'a.status')
            ->selectRaw("a.application_number, a.last_name, a.first_name, a.grade_level_applying_for, s.name as section_name, a.status, COUNT(d.id) as documents, SUM(d.status = 'verified') as verified")
            ->orderBy('a.last_name');
        $this->limitSection($query, 'a.preferred_section_id');
        $rows = $query->get();

        return $this->pack(
            ['Application', 'Applicant', 'Grade', 'Section', 'Application status', 'Documents', 'Verified'],
            $rows->map(fn ($row) => [
                $row->application_number ?: '—',
                $this->person($row),
                $row->grade_level_applying_for ?: '—',
                $row->section_name ?: '—',
                str_replace('_', ' ', (string) $row->status),
                (string) $row->documents,
                (string) ((int) $row->verified),
            ])->all(),
            ['Applications' => $rows->count()]
        );
    }

    private function classGradeSheet(): array
    {
        return $this->subjectScoreRows('Each row is one student and one subject. Passing is '.number_format(AcademicThresholds::PASSING_PERCENTAGE, 0).'%.');
    }

    private function subjectSummary(): array
    {
        return $this->subjectScoreRows('Each row names the student and the subject average for the current academic year.');
    }

    private function gradeLevelPerformance(): array
    {
        $bundle = $this->scores();
        $places = $this->placements();
        $names = $this->names(array_keys($bundle['students']));
        $lines = [];
        foreach ($bundle['students'] as $studentId => $score) {
            $student = $names[(int) $studentId] ?? null;
            $place = $places[(int) $studentId] ?? null;
            $lines[] = array_merge($this->nameCells($student), [
                $place->grade_level ?? '—',
                $place->section_name ?? '—',
                number_format($score['average'], 2).'%',
                $score['average'] < AcademicThresholds::PASSING_PERCENTAGE ? 'Below 75%' : 'Passed',
            ]);
        }
        $this->sortByStudent($lines);

        return $this->pack(
            ['Student Name', 'Student Number', 'Grade', 'Section', 'Average', 'Standing'],
            $lines,
            ['Students' => count($lines)]
        );
    }

    private function quarterlyPerformance(): array
    {
        $bundle = $this->scores();
        $places = $this->placements();
        $labels = [];
        foreach ($bundle['fields'] as $field) {
            $number = (int) str_replace('quarter_', '', (string) $field);
            $term = \App\Support\SchoolQuarter::find($this->year, $number);
            $labels[$field] = $term['label'] ?? ('Term '.$number);
        }
        $byStudent = [];
        foreach ($bundle['grades'] as $grade) {
            $studentId = (int) $grade->student_id;
            foreach ($bundle['fields'] as $field) {
                $value = $grade->{$field} ?? null;
                if ($value !== null && $value !== '') {
                    $byStudent[$studentId][$field][] = (float) $value;
                }
            }
        }
        $names = $this->names(array_keys($byStudent));
        $lines = [];
        foreach ($byStudent as $studentId => $fields) {
            $student = $names[$studentId] ?? null;
            $place = $places[$studentId] ?? null;
            $line = array_merge($this->nameCells($student), [
                $place->grade_level ?? '—',
                $place->section_name ?? '—',
            ]);
            foreach (array_keys($labels) as $field) {
                $values = $fields[$field] ?? [];
                $line[] = $values ? number_format(array_sum($values) / count($values), 2).'%' : '—';
            }
            $lines[] = $line;
        }
        $this->sortByStudent($lines);

        return $this->pack(
            array_merge(['Student Name', 'Student Number', 'Grade', 'Section'], array_values($labels)),
            $lines,
            ['Students' => count($lines)],
            'Each column is a term in the current academic year, and each row names the student.'
        );
    }

    private function subjectScoreRows(string $note): array
    {
        $bundle = $this->scores();
        $places = $this->placements();
        $ids = [];
        foreach ($bundle['grades'] as $grade) {
            $ids[] = (int) $grade->student_id;
        }
        $names = $this->names(array_values(array_unique($ids)));
        $lines = [];
        foreach ($bundle['grades'] as $grade) {
            $percentage = $this->performance->subjectPercentage($grade, $bundle['fields']);
            if ($percentage === null) {
                continue;
            }
            $studentId = (int) $grade->student_id;
            $student = $names[$studentId] ?? null;
            $place = $places[$studentId] ?? null;
            $lines[] = array_merge($this->nameCells($student), [
                $place->grade_level ?? '—',
                $place->section_name ?? '—',
                $grade->subject_name ?: 'Subject',
                number_format($percentage, 2).'%',
            ]);
        }
        $this->sortByStudent($lines);

        return $this->pack(
            ['Student Name', 'Student Number', 'Grade', 'Section', 'Subject', 'Average'],
            $lines,
            ['Rows' => count($lines)],
            $note
        );
    }

    private function ranking(): array
    {
        $bundle = $this->scores();
        $places = $this->placements();
        $names = $this->names(array_keys($bundle['students']));
        $lines = [];
        $ordered = collect($bundle['students'])->map(fn ($score, $id) => ['id' => (int) $id] + $score)->sortByDesc('average')->values();
        $position = 0;
        $previous = null;
        $display = 0;
        foreach ($ordered as $index => $score) {
            $position++;
            if ($previous === null || abs($score['average'] - $previous) > 0.001) {
                $display = $position;
                $previous = $score['average'];
            }
            $student = $names[$score['id']] ?? null;
            $place = $places[$score['id']] ?? null;
            $lines[] = [
                (string) $display,
                ...$this->nameCells($student),
                $place->grade_level ?? '—',
                $place->section_name ?? '—',
                number_format($score['average'], 2).'%',
            ];
            unset($index);
        }

        return $this->pack(
            ['Rank', 'Student Name', 'Student Number', 'Grade', 'Section', 'Average'],
            $lines,
            ['Ranked students' => count($lines)],
            'Students with the same average share a rank. Students without grades are not ranked.'
        );
    }

    private function failingGrades(): array
    {
        $bundle = $this->scores();
        $places = $this->placements();
        $names = $this->names(array_keys($bundle['students']));
        $alerts = DB::table('grade_alerts')
            ->where('academic_year_id', $this->yearId)
            ->where('is_resolved', false)
            ->where(function ($query) {
                $query->where('semester_id', $this->semesterId)->orWhereNull('semester_id');
            })
            ->pluck('student_id')
            ->map(fn ($id) => (int) $id)
            ->flip();
        $lines = [];
        foreach ($bundle['students'] as $studentId => $score) {
            if ($score['average'] >= AcademicThresholds::PASSING_PERCENTAGE) {
                continue;
            }
            $student = $names[(int) $studentId] ?? null;
            $place = $places[(int) $studentId] ?? null;
            $lines[] = array_merge($this->nameCells($student), [
                $place->grade_level ?? '—',
                $place->section_name ?? '—',
                number_format($score['average'], 2).'%',
                isset($alerts[(int) $studentId]) ? 'Open alert' : 'Below passing',
            ]);
        }
        $this->sortByStudent($lines);

        return $this->pack(
            ['Student Name', 'Student Number', 'Grade', 'Section', 'Average', 'Flag'],
            $lines,
            ['Students' => count($lines)],
            'Below passing means under '.number_format(AcademicThresholds::PASSING_PERCENTAGE, 0).'%.'
        );
    }

    private function missingGrades(): array
    {
        $bundle = $this->scores();
        $places = $this->placements();
        $missingIds = [];
        foreach ($places as $studentId => $place) {
            if (! isset($bundle['students'][(int) $studentId])) {
                $missingIds[] = (int) $studentId;
            }
            unset($place);
        }
        $names = $this->names($missingIds);
        $lines = [];
        foreach ($missingIds as $studentId) {
            $student = $names[$studentId] ?? null;
            $place = $places[$studentId] ?? null;
            $lines[] = array_merge($this->nameCells($student), [
                $place->grade_level ?? '—',
                $place->section_name ?? '—',
                'No subject average',
            ]);
        }
        $this->sortByStudent($lines);

        return $this->pack(['Student Name', 'Student Number', 'Grade', 'Section', 'Record'], $lines, ['Students' => count($lines)]);
    }

    private function descriptiveAssessment(): array
    {
        $query = DB::table('quarterly_grades as q')
            ->join('students as st', 'st.id', '=', 'q.student_id')
            ->join('subjects as sub', 'sub.id', '=', 'q.subject_id')
            ->join('student_section_assignments as a', function ($join) {
                $join->on('a.student_id', '=', 'q.student_id')->where('a.academic_year_id', $this->yearId);
            })
            ->join('sections as s', 's.id', '=', 'a.section_id')
            ->where('q.academic_year_id', $this->yearId)
            ->whereNull('st.deleted_at')
            ->select('st.last_name', 'st.first_name', 'st.admission_id', 'st.roll', 's.grade_level', 's.name as section_name', 'sub.subject_name', 'q.q1_level', 'q.q2_level', 'q.q3_level', 'q.q4_level');
        $this->limitStudents($query, 'st.id');
        $rows = $query->get()->filter(fn ($row) => DescriptiveScale::isDescriptiveGradeLevel($row->grade_level))->take(400);
        $termLabels = [];
        foreach ([1, 2, 3, 4] as $number) {
            $term = \App\Support\SchoolQuarter::find($this->year, $number);
            $termLabels[] = $term['label'] ?? ('Term '.$number);
        }

        return $this->pack(
            array_merge(['Student Name', 'Student Number', 'Grade', 'Section', 'Subject'], $termLabels),
            $rows->map(fn ($row) => array_merge($this->nameCells($row), [
                $row->grade_level, $row->section_name, $row->subject_name,
                $row->q1_level ?: '—', $row->q2_level ?: '—', $row->q3_level ?: '—', $row->q4_level ?: '—',
            ]))->all(),
            ['Rows' => $rows->count()],
            'Descriptive levels are shown for Nursery through Grade 3 only.'
        );
    }

    private function attendanceBase()
    {
        $query = DB::table('attendances')->whereIn('student_id', $this->placedStudentIds() ?: [0]);
        if ($this->year?->start_date && $this->year?->end_date) {
            $query->where('date', '>=', $this->year->start_date->toDateString())
                ->where('date', '<=', $this->year->end_date->toDateString());
        } else {
            $query->whereRaw('1 = 0');
        }

        return $query;
    }

    private function dailyAttendance(): array
    {
        $today = now('Asia/Manila')->toDateString();
        $rows = DB::table('attendances as att')
            ->join('students as st', 'st.id', '=', 'att.student_id')
            ->leftJoin('student_section_assignments as a', function ($join) {
                $join->on('a.student_id', '=', 'st.id')->where('a.academic_year_id', $this->yearId);
            })
            ->leftJoin('sections as s', 's.id', '=', 'a.section_id')
            ->whereIn('att.student_id', $this->placedStudentIds() ?: [0])
            ->whereDate('att.date', $today)
            ->whereNull('st.deleted_at')
            ->orderBy('st.last_name')
            ->orderBy('st.first_name')
            ->get(['st.last_name', 'st.first_name', 'st.admission_id', 'st.roll', 's.grade_level', 's.name as section_name', 'att.status']);

        return $this->pack(
            ['Student Name', 'Student Number', 'Grade', 'Section', 'Status'],
            $rows->map(fn ($row) => array_merge($this->nameCells($row), [
                $row->grade_level ?: '—',
                $row->section_name ?: '—',
                ucfirst((string) $row->status),
            ]))->all(),
            ['Date' => $today, 'Students' => $rows->count()],
            'Each row is one student marked today. Today follows the school clock, Asia/Manila.'
        );
    }

    private function monthlyAttendance(): array
    {
        $start = now('Asia/Manila')->startOfMonth()->toDateString();
        $end = now('Asia/Manila')->endOfMonth()->toDateString();

        return $this->attendanceStudentReport($start, $end, 'Each row names the student and that student’s marks for '.now('Asia/Manila')->format('F Y').'.');
    }

    private function attendanceStats(): array
    {
        if (! $this->year?->start_date || ! $this->year?->end_date) {
            return [];
        }
        $rows = $this->attendanceBase()
            ->selectRaw("student_id, SUM(status = 'present') as present, SUM(status = 'late') as late, SUM(status = 'absent') as absent, SUM(status = 'excused') as excused, COUNT(*) as total")
            ->groupBy('student_id')
            ->get();
        $stats = [];
        foreach ($rows as $row) {
            $base = max(0, (int) $row->total - (int) $row->excused);
            $attended = (int) $row->present + (int) $row->late;
            $stats[(int) $row->student_id] = [
                'present' => (int) $row->present,
                'late' => (int) $row->late,
                'absent' => (int) $row->absent,
                'excused' => (int) $row->excused,
                'total' => (int) $row->total,
                'percentage' => $base > 0 ? round(($attended / $base) * 100, 2) : null,
            ];
        }

        return $stats;
    }

    private function studentAttendance(): array
    {
        if (! $this->year?->start_date || ! $this->year?->end_date) {
            return $this->blank('This academic year has no start and end dates, so attendance from other years is not included.');
        }
        $stats = $this->attendanceStats();
        $places = $this->placements();
        $names = $this->names(array_keys($stats));
        $lines = [];
        foreach ($stats as $studentId => $stat) {
            $student = $names[$studentId] ?? null;
            $place = $places[$studentId] ?? null;
            $lines[] = array_merge($this->nameCells($student), [
                $place->grade_level ?? '—',
                $place->section_name ?? '—',
                (string) $stat['present'],
                (string) $stat['late'],
                (string) $stat['absent'],
                (string) $stat['excused'],
                $stat['percentage'] !== null ? number_format($stat['percentage'], 2).'%' : '—',
            ]);
        }
        $this->sortByStudent($lines);

        return $this->pack(
            ['Student Name', 'Student Number', 'Grade', 'Section', 'Present', 'Late', 'Absent', 'Excused', 'Rate'],
            $lines,
            ['Students' => count($lines)],
            'The rate counts present and late marks. Excused marks are left out of the rate.'
        );
    }

    private function classAttendance(): array
    {
        return $this->attendanceStudentReport(
            $this->year?->start_date?->toDateString(),
            $this->year?->end_date?->toDateString(),
            'Each row names the student and that student’s attendance inside the current academic year.'
        );
    }

    private function absencesByGrade(): array
    {
        $stats = $this->attendanceStats();
        $places = $this->placements();
        $ids = array_keys(array_filter($stats, fn ($stat) => $stat['absent'] > 0));
        $names = $this->names($ids);
        $lines = [];
        foreach ($ids as $studentId) {
            $student = $names[$studentId] ?? null;
            $place = $places[$studentId] ?? null;
            $lines[] = array_merge($this->nameCells($student), [
                $place->grade_level ?? '—',
                $place->section_name ?? '—',
                (string) $stats[$studentId]['absent'],
            ]);
        }
        $this->sortByStudent($lines);

        return $this->pack(
            ['Student Name', 'Student Number', 'Grade', 'Section', 'Absences'],
            $lines,
            ['Students' => count($lines)]
        );
    }

    private function excessiveAbsences(): array
    {
        $stats = $this->attendanceStats();
        $places = $this->placements();
        $ids = array_keys(array_filter($stats, fn ($stat) => $stat['absent'] >= AcademicThresholds::RECURRING_ABSENCE_COUNT));
        $names = $this->names($ids);
        $lines = [];
        foreach ($ids as $studentId) {
            $student = $names[$studentId] ?? null;
            $place = $places[$studentId] ?? null;
            $lines[] = array_merge($this->nameCells($student), [
                $place->grade_level ?? '—',
                $place->section_name ?? '—',
                (string) $stats[$studentId]['absent'],
                $stats[$studentId]['percentage'] !== null ? number_format($stats[$studentId]['percentage'], 2).'%' : '—',
            ]);
        }
        $this->sortByStudent($lines);

        return $this->pack(
            ['Student Name', 'Student Number', 'Grade', 'Section', 'Absences', 'Rate'],
            $lines,
            ['Students' => count($lines)],
            'Excessive means '.AcademicThresholds::RECURRING_ABSENCE_COUNT.' or more absences inside this academic year’s dates.'
        );
    }

    private function unexcusedAbsences(): array
    {
        $stats = $this->attendanceStats();
        $places = $this->placements();
        $ids = array_keys(array_filter($stats, fn ($stat) => $stat['absent'] > 0));
        $names = $this->names($ids);
        $lines = [];
        foreach ($ids as $studentId) {
            $student = $names[$studentId] ?? null;
            $place = $places[$studentId] ?? null;
            $lines[] = array_merge($this->nameCells($student), [
                $place->grade_level ?? '—',
                $place->section_name ?? '—',
                (string) $stats[$studentId]['absent'],
                (string) $stats[$studentId]['excused'],
            ]);
        }
        $this->sortByStudent($lines);

        return $this->pack(
            ['Student Name', 'Student Number', 'Grade', 'Section', 'Absent', 'Excused'],
            $lines,
            ['Students with absences' => count($lines)],
            'Absent is the unexcused mark. Excused is stored as its own status and is not counted as absent.'
        );
    }

    private function attendanceByYear(): array
    {
        $years = AcademicYear::query()->orderBy('start_date')->get();
        $lines = [];
        foreach ($years as $year) {
            if (! $year->start_date || ! $year->end_date) {
                $lines[] = [$year->name, 'No dates', '—'];
                continue;
            }
            $row = DB::table('attendances')
                ->where('date', '>=', $year->start_date->toDateString())
                ->where('date', '<=', $year->end_date->toDateString())
                ->selectRaw("SUM(status = 'present') as present, SUM(status = 'late') as late, SUM(status = 'excused') as excused, COUNT(*) as total")
                ->first();
            $base = max(0, (int) $row->total - (int) $row->excused);
            $rate = $base > 0 ? number_format(round((((int) $row->present + (int) $row->late) / $base) * 100, 2), 2).'%' : '—';
            $lines[] = [$year->name, (string) ((int) $row->total), $rate];
        }

        return $this->pack(
            ['Academic year', 'Marks', 'Rate'],
            $lines,
            [],
            'Each year uses its own start and end dates. Attendance has no separate year field.'
        );
    }

    private function teacherList(): array
    {
        $rows = DB::table('teachers')
            ->leftJoin('users', 'users.id', '=', 'teachers.user_id')
            ->orderBy('teachers.full_name')
            ->get(['teachers.full_name', 'users.email', 'users.status', 'teachers.qualification']);

        return $this->pack(
            ['Teacher', 'Email', 'Account status', 'Qualification'],
            $rows->map(fn ($row) => [$row->full_name ?: '—', $row->email ?: '—', $row->status ?: '—', $row->qualification ?: '—'])->all(),
            ['Teachers' => $rows->count()]
        );
    }

    private function teachingLoad(): array
    {
        $query = DB::table('class_schedules as c')
            ->join('teachers as t', 't.id', '=', 'c.teacher_id')
            ->where('c.academic_year_id', $this->yearId)
            ->where('c.is_active', true)
            ->selectRaw('t.full_name, COUNT(*) as meetings, COUNT(DISTINCT c.subject_id) as subjects, COUNT(DISTINCT c.section_id) as sections')
            ->groupBy('t.id', 't.full_name')
            ->orderBy('t.full_name');
        if ($this->user?->role_name === 'Teacher' && $this->user->teacher) {
            $query->where('c.teacher_id', $this->user->teacher->id);
        }
        $rows = $query->get();

        return $this->pack(
            ['Teacher', 'Meetings', 'Subjects', 'Sections'],
            $rows->map(fn ($row) => [$row->full_name, (string) $row->meetings, (string) $row->subjects, (string) $row->sections])->all(),
            ['Teachers' => $rows->count()]
        );
    }

    private function teachingLoadByGrade(): array
    {
        $rows = DB::table('class_schedules as c')
            ->join('sections as s', 's.id', '=', 'c.section_id')
            ->where('c.academic_year_id', $this->yearId)
            ->where('c.is_active', true)
            ->selectRaw('s.grade_level, COUNT(*) as meetings, COUNT(DISTINCT c.teacher_id) as teachers')
            ->groupBy('s.grade_level')
            ->orderBy('s.grade_level')
            ->get();

        return $this->pack(
            ['Grade level', 'Meetings', 'Teachers'],
            $rows->map(fn ($row) => [$row->grade_level ?: '—', (string) $row->meetings, (string) $row->teachers])->all()
        );
    }

    private function subjectAssignment(): array
    {
        $query = DB::table('subject_teacher as st')
            ->join('teachers as t', 't.id', '=', 'st.teacher_id')
            ->join('subjects as s', 's.id', '=', 'st.subject_id')
            ->where('st.academic_year_id', $this->yearId)
            ->orderBy('s.class')
            ->orderBy('s.subject_name')
            ->select('t.full_name', 's.class', 's.subject_name');
        if ($this->user?->role_name === 'Teacher' && $this->user->teacher) {
            $query->where('st.teacher_id', $this->user->teacher->id);
        }
        $rows = $query->get();

        return $this->pack(
            ['Teacher', 'Grade', 'Subject'],
            $rows->map(fn ($row) => [$row->full_name, $row->class ?: '—', $row->subject_name])->all(),
            ['Assignments' => $rows->count()]
        );
    }

    private function sectionList(): array
    {
        $query = DB::table('sections as s')
            ->leftJoin('student_section_assignments as a', function ($join) {
                $join->on('a.section_id', '=', 's.id')->where('a.academic_year_id', $this->yearId);
            })
            ->where(function ($inner) {
                $inner->where('s.academic_year_id', $this->yearId)->orWhereNotNull('a.student_id');
            })
            ->groupBy('s.id', 's.grade_level', 's.name')
            ->orderBy('s.grade_level')
            ->orderBy('s.name')
            ->selectRaw('s.grade_level, s.name, COUNT(DISTINCT a.student_id) as students');
        $this->limitSection($query, 's.id');
        $rows = $query->get();

        return $this->pack(
            ['Grade', 'Section', 'Students'],
            $rows->map(fn ($row) => [$row->grade_level ?: '—', $row->name, (string) $row->students])->all(),
            ['Sections' => $rows->count()]
        );
    }

    private function classSchedule(): array
    {
        $query = DB::table('class_schedules as c')
            ->join('sections as s', 's.id', '=', 'c.section_id')
            ->join('subjects as sub', 'sub.id', '=', 'c.subject_id')
            ->join('teachers as t', 't.id', '=', 'c.teacher_id')
            ->leftJoin('rooms as r', 'r.id', '=', 'c.room_id')
            ->where('c.academic_year_id', $this->yearId)
            ->where('c.is_active', true)
            ->orderBy('s.grade_level')
            ->orderBy('c.day_of_week')
            ->orderBy('c.start_time')
            ->select('s.grade_level', 's.name as section_name', 'sub.subject_name', 't.full_name', 'c.day_of_week', 'c.start_time', 'c.end_time', 'r.room_name');
        if ($this->user?->role_name === 'Teacher' && $this->user->teacher) {
            $query->where('c.teacher_id', $this->user->teacher->id);
        }
        $rows = $query->get();

        return $this->pack(
            ['Grade', 'Section', 'Subject', 'Teacher', 'Day', 'Time', 'Room'],
            $rows->map(fn ($row) => [
                $row->grade_level ?: '—',
                $row->section_name,
                $row->subject_name,
                $row->full_name,
                (string) $row->day_of_week,
                substr((string) $row->start_time, 0, 5).'–'.substr((string) $row->end_time, 0, 5),
                $row->room_name ?: '—',
            ])->all(),
            ['Meetings' => $rows->count()]
        );
    }

    private function scheduleConflicts(): array
    {
        $rows = DB::table('class_schedules as c')
            ->join('sections as s', 's.id', '=', 'c.section_id')
            ->join('teachers as t', 't.id', '=', 'c.teacher_id')
            ->leftJoin('rooms as r', 'r.id', '=', 'c.room_id')
            ->where('c.academic_year_id', $this->yearId)
            ->where('c.is_active', true)
            ->get(['c.id', 'c.teacher_id', 'c.room_id', 'c.day_of_week', 'c.start_time', 'c.end_time', 's.name as section_name', 't.full_name', 'r.room_name']);
        $lines = [];
        $list = $rows->values();
        foreach ($list as $index => $row) {
            foreach ($list as $otherIndex => $other) {
                if ($otherIndex <= $index || (string) $row->day_of_week !== (string) $other->day_of_week) {
                    continue;
                }
                $sameTeacher = (int) $row->teacher_id === (int) $other->teacher_id;
                $sameRoom = $row->room_id && (int) $row->room_id === (int) $other->room_id;
                if (! $sameTeacher && ! $sameRoom) {
                    continue;
                }
                if ($row->start_time < $other->end_time && $row->end_time > $other->start_time) {
                    $lines[] = [
                        $sameTeacher ? 'Teacher' : 'Room',
                        $sameTeacher ? $row->full_name : ($row->room_name ?: 'Room'),
                        (string) $row->day_of_week,
                        $row->section_name.' / '.$other->section_name,
                        substr((string) $row->start_time, 0, 5).'–'.substr((string) $row->end_time, 0, 5),
                    ];
                }
            }
        }

        return $this->pack(['Conflict', 'Who or where', 'Day', 'Sections', 'Time'], $lines, ['Conflicts' => count($lines)]);
    }

    private function unassigned(): array
    {
        $assignedSubjects = DB::table('subject_teacher')->where('academic_year_id', $this->yearId)->pluck('subject_id');
        $grades = DB::table('sections')->where('academic_year_id', $this->yearId)->pluck('grade_level')->filter()->unique();
        $subjects = DB::table('subjects')
            ->whereIn('class', $grades->all() ?: ['__none__'])
            ->whereNotIn('id', $assignedSubjects->all() ?: [0])
            ->orderBy('class')
            ->orderBy('subject_name')
            ->get(['class', 'subject_name']);
        $assignedSections = DB::table('section_teacher')->where('academic_year_id', $this->yearId)->pluck('section_id');
        $sections = DB::table('sections')
            ->where('academic_year_id', $this->yearId)
            ->whereNotIn('id', $assignedSections->all() ?: [0])
            ->orderBy('grade_level')
            ->get(['grade_level', 'name']);
        $lines = $subjects->map(fn ($row) => ['Subject', $row->class ?: '—', $row->subject_name])->all();
        foreach ($sections as $section) {
            $lines[] = ['Section', $section->grade_level ?: '—', $section->name];
        }

        return $this->pack(['Type', 'Grade', 'Name'], $lines, ['Unassigned' => count($lines)]);
    }

    private function teacherAvailability(): array
    {
        $assigned = DB::table('subject_teacher')->where('academic_year_id', $this->yearId)->pluck('teacher_id')
            ->merge(DB::table('section_teacher')->where('academic_year_id', $this->yearId)->pluck('teacher_id'))
            ->unique();
        $scheduled = DB::table('class_schedules')->where('academic_year_id', $this->yearId)->where('is_active', true)->pluck('teacher_id');
        $open = $assigned->diff($scheduled);
        $rows = DB::table('teachers')->whereIn('id', $open->all() ?: [0])->orderBy('full_name')->get(['full_name']);

        return $this->pack(
            ['Teacher', 'Schedule'],
            $rows->map(fn ($row) => [$row->full_name, 'No active schedule this year'])->all(),
            ['Teachers' => $rows->count()]
        );
    }

    private function curriculumOffering(): array
    {
        $rows = DB::table('curriculum_subject as cs')
            ->join('curricula as c', 'c.id', '=', 'cs.curriculum_id')
            ->join('subjects as s', 's.id', '=', 'cs.subject_id')
            ->orderBy('c.grade_level')
            ->orderBy('s.subject_name')
            ->get(['c.grade_level', 's.subject_name']);

        return $this->pack(
            ['Grade', 'Subject'],
            $rows->map(fn ($row) => [$row->grade_level ?: '—', $row->subject_name])->all(),
            ['Subjects' => $rows->count()],
            'This is the configured curriculum catalog, separate from one academic year’s class assignments.'
        );
    }

    private function courseOutlines(): array
    {
        if (! Schema::hasTable('curriculum_course_outlines')) {
            return $this->blank('Course outlines are not available.');
        }
        $rows = DB::table('curriculum_course_outlines')
            ->where('academic_year_id', $this->yearId)
            ->orderBy('grade_level')
            ->get(['grade_level', 'title', 'status', 'document_type']);

        return $this->pack(
            ['Grade', 'Title', 'Status', 'Type'],
            $rows->map(fn ($row) => [$row->grade_level ?: '—', $row->title, $row->status ?: '—', $row->document_type ?: '—'])->all(),
            ['Outlines' => $rows->count()],
            'Only outlines saved for the current academic year are shown.'
        );
    }

    private function lessonInventory(): array
    {
        $rows = $this->lessonQuery()
            ->join('subjects as s', 's.id', '=', 'lessons.subject_id')
            ->leftJoin('sections as sec', 'sec.id', '=', 'lessons.section_id')
            ->leftJoin('teachers as t', 't.id', '=', 'lessons.teacher_id')
            ->orderByDesc('lessons.lesson_date')
            ->limit(400)
            ->get(['lessons.title', 's.subject_name', 'sec.name as section_name', 't.full_name', 'lessons.status', 'lessons.lesson_date', 'lessons.file_name']);

        return $this->pack(
            ['Lesson', 'Subject', 'Section', 'Teacher', 'Status', 'Date', 'File'],
            $rows->map(fn ($row) => [$row->title, $row->subject_name, $row->section_name ?: '—', $row->full_name ?: '—', $row->status ?: '—', $this->when($row->lesson_date), $row->file_name ?: '—'])->all(),
            ['Lessons' => $rows->count()]
        );
    }

    private function activitiesList(): array
    {
        $activities = DB::table('activities as a')
            ->join('lessons as l', 'l.id', '=', 'a.lesson_id')
            ->join('subjects as s', 's.id', '=', 'l.subject_id')
            ->where('l.academic_year_id', $this->yearId)
            ->orderBy('a.due_date')
            ->limit(400);
        $this->limitTeacherLessons($activities, 'l.teacher_id');
        $activityRows = $activities->get(['a.title', 'a.status', 'a.due_date', 's.subject_name']);
        $assignments = DB::table('assignments')
            ->where('academic_year_id', $this->yearId)
            ->whereNull('deleted_at');
        $this->limitTeacherLessons($assignments, 'teacher_id');
        $assignmentRows = $assignments->limit(400)->get(['title', 'status', 'due_date']);
        $lines = $activityRows->map(fn ($row) => ['Activity', $row->title, $row->subject_name, $row->status ?: '—', $this->when($row->due_date)])->all();
        foreach ($assignmentRows as $row) {
            $lines[] = ['Assignment', $row->title, '—', $row->status ?: '—', $this->when($row->due_date)];
        }

        return $this->pack(['Kind', 'Title', 'Subject', 'Status', 'Due'], $lines, ['Items' => count($lines)]);
    }

    private function submissionStatus(): array
    {
        $query = DB::table('activities as a')
            ->join('lessons as l', 'l.id', '=', 'a.lesson_id')
            ->leftJoin('activity_submissions as sub', 'sub.activity_id', '=', 'a.id')
            ->where('l.academic_year_id', $this->yearId)
            ->groupBy('a.id', 'a.title', 'a.status')
            ->selectRaw('a.title, a.status, COUNT(sub.id) as submissions')
            ->orderBy('a.title');
        $this->limitTeacherLessons($query, 'l.teacher_id');
        $rows = $query->get();

        return $this->pack(
            ['Activity', 'Status', 'Submissions'],
            $rows->map(fn ($row) => [$row->title, $row->status ?: '—', (string) $row->submissions])->all(),
            ['Activities' => $rows->count()]
        );
    }

    private function missingSubmissions(): array
    {
        $activities = DB::table('activities as a')
            ->join('lessons as l', 'l.id', '=', 'a.lesson_id')
            ->where('l.academic_year_id', $this->yearId)
            ->where('a.status', 'published')
            ->select('a.id', 'a.title', 'l.section_id');
        $this->limitTeacherLessons($activities, 'l.teacher_id');
        $activities = $activities->get();
        $sectionCounts = DB::table('student_section_assignments')
            ->where('academic_year_id', $this->yearId)
            ->selectRaw('section_id, COUNT(DISTINCT student_id) as students')
            ->groupBy('section_id')
            ->pluck('students', 'section_id');
        $submitted = DB::table('activity_submissions')
            ->whereIn('activity_id', $activities->pluck('id')->all() ?: [0])
            ->selectRaw('activity_id, COUNT(*) as total')
            ->groupBy('activity_id')
            ->pluck('total', 'activity_id');
        $lines = [];
        foreach ($activities as $activity) {
            $expected = (int) ($sectionCounts[$activity->section_id] ?? 0);
            $got = (int) ($submitted[$activity->id] ?? 0);
            if ($expected > $got) {
                $lines[] = [$activity->title, (string) $expected, (string) $got, (string) ($expected - $got)];
            }
        }
        $late = DB::table('assignment_submissions as s')
            ->join('assignments as a', 'a.id', '=', 's.assignment_id')
            ->where('a.academic_year_id', $this->yearId)
            ->where('s.is_late', true);
        $this->limitTeacherLessons($late, 'a.teacher_id');
        $lateCount = (int) $late->count();

        return $this->pack(
            ['Activity', 'Students in section', 'Submitted', 'Missing'],
            $lines,
            ['Activities with missing work' => count($lines), 'Late assignment submissions' => $lateCount]
        );
    }

    private function assignmentCompletion(): array
    {
        $query = DB::table('assignments as a')
            ->leftJoin('assignment_submissions as s', 's.assignment_id', '=', 'a.id')
            ->where('a.academic_year_id', $this->yearId)
            ->whereNull('a.deleted_at')
            ->groupBy('a.id', 'a.title', 'a.status')
            ->selectRaw('a.title, a.status, COUNT(s.id) as submissions, SUM(s.is_late = 1) as late')
            ->orderBy('a.title');
        $this->limitTeacherLessons($query, 'a.teacher_id');
        $rows = $query->get();

        return $this->pack(
            ['Assignment', 'Status', 'Submissions', 'Late'],
            $rows->map(fn ($row) => [$row->title, $row->status ?: '—', (string) $row->submissions, (string) ((int) $row->late)])->all(),
            ['Assignments' => $rows->count()]
        );
    }

    private function activityPerformance(): array
    {
        $query = DB::table('activity_submissions as s')
            ->join('activities as a', 'a.id', '=', 's.activity_id')
            ->join('lessons as l', 'l.id', '=', 'a.lesson_id')
            ->join('subjects as sub', 'sub.id', '=', 'l.subject_id')
            ->where('l.academic_year_id', $this->yearId)
            ->whereNotNull('s.percentage')
            ->groupBy('sub.id', 'sub.subject_name')
            ->selectRaw('sub.subject_name, COUNT(s.id) as scores, ROUND(AVG(s.percentage), 2) as average');
        $this->limitTeacherLessons($query, 'l.teacher_id');
        $rows = $query->get();

        return $this->pack(
            ['Subject', 'Scored submissions', 'Average'],
            $rows->map(fn ($row) => [$row->subject_name, (string) $row->scores, number_format((float) $row->average, 2).'%'])->all()
        );
    }

    private function upcomingDeadlines(): array
    {
        $today = now('Asia/Manila')->toDateString();
        $activities = DB::table('activities as a')
            ->join('lessons as l', 'l.id', '=', 'a.lesson_id')
            ->where('l.academic_year_id', $this->yearId)
            ->where('a.status', 'published')
            ->whereDate('a.due_date', '>=', $today);
        $this->limitTeacherLessons($activities, 'l.teacher_id');
        $assignments = DB::table('assignments')
            ->where('academic_year_id', $this->yearId)
            ->whereNull('deleted_at')
            ->whereDate('due_date', '>=', $today);
        $this->limitTeacherLessons($assignments, 'teacher_id');
        $lines = $activities->orderBy('a.due_date')->limit(100)->get(['a.title', 'a.due_date'])
            ->map(fn ($row) => ['Activity', $row->title, $this->when($row->due_date)])->all();
        foreach ($assignments->orderBy('due_date')->limit(100)->get(['title', 'due_date']) as $row) {
            $lines[] = ['Assignment', $row->title, $this->when($row->due_date)];
        }

        return $this->pack(['Kind', 'Title', 'Due'], $lines, ['Due items' => count($lines)]);
    }

    private function lessonPosting(): array
    {
        $query = DB::table('lessons as l')
            ->join('teachers as t', 't.id', '=', 'l.teacher_id')
            ->where('l.academic_year_id', $this->yearId)
            ->groupBy('t.id', 't.full_name')
            ->selectRaw('t.full_name, COUNT(*) as lessons, SUM(l.file_path IS NOT NULL) as files')
            ->orderBy('t.full_name');
        $this->limitTeacherLessons($query, 'l.teacher_id');
        $rows = $query->get();

        return $this->pack(
            ['Teacher', 'Lessons', 'With a file'],
            $rows->map(fn ($row) => [$row->full_name, (string) $row->lessons, (string) ((int) $row->files)])->all()
        );
    }

    private function materialUploads(): array
    {
        $rows = $this->lessonQuery()
            ->whereNotNull('lessons.file_path')
            ->orderByDesc('lessons.updated_at')
            ->limit(200)
            ->get(['lessons.title', 'lessons.file_name', 'lessons.updated_at']);

        return $this->pack(
            ['Lesson', 'File', 'Updated'],
            $rows->map(fn ($row) => [$row->title, $row->file_name ?: '—', $this->when($row->updated_at)])->all(),
            ['Files' => $rows->count()]
        );
    }

    private function announcements(): array
    {
        $rows = DB::table('announcements')->orderByDesc('created_at')->limit(200)
            ->get(['title', 'type', 'priority', 'is_active', 'created_at']);

        return $this->pack(
            ['Title', 'Type', 'Priority', 'Active', 'Posted'],
            $rows->map(fn ($row) => [$row->title, $row->type ?: '—', $row->priority ?: '—', $row->is_active ? 'Yes' : 'No', $this->when($row->created_at)])->all(),
            ['Announcements' => $rows->count()]
        );
    }

    private function notifications(bool $delivery): array
    {
        $rows = DB::table('notifications')
            ->selectRaw('DATE(created_at) as day, COUNT(*) as sent, SUM(read_at IS NOT NULL) as opened')
            ->groupByRaw('DATE(created_at)')
            ->orderByDesc('day')
            ->limit(90)
            ->get();
        $note = $delivery
            ? 'Opened means a user opened the notification in the portal. Delivery to email or a device is not tracked.'
            : 'Counts come from notifications stored in the portal.';

        return $this->pack(
            ['Date', 'Sent', 'Opened'],
            $rows->map(fn ($row) => [$this->when($row->day), (string) $row->sent, (string) ((int) $row->opened)])->all(),
            ['Days' => $rows->count()],
            $note
        );
    }

    private function consultations(bool $scheduledOnly): array
    {
        $query = DB::table('consultation_requests as c')
            ->join('students as st', 'st.id', '=', 'c.student_id')
            ->leftJoin('teachers as t', 't.id', '=', 'c.teacher_id')
            ->orderByDesc('c.created_at');
        $this->limitStudents($query, 'c.student_id');
        if ($this->user?->role_name === 'Teacher' && $this->user->teacher) {
            $query->where('c.teacher_id', $this->user->teacher->id);
        }
        if ($scheduledOnly) {
            $query->whereNotNull('c.scheduled_start_at');
        }
        $rows = $query->limit(200)->get(['st.last_name', 'st.first_name', 't.full_name', 'c.status', 'c.requested_start_at', 'c.scheduled_start_at']);

        return $this->pack(
            ['Student', 'Teacher', 'Status', 'Requested', 'Scheduled'],
            $rows->map(fn ($row) => [$this->person($row), $row->full_name ?: '—', $row->status, $this->when($row->requested_start_at), $this->when($row->scheduled_start_at)])->all(),
            ['Requests' => $rows->count()]
        );
    }

    private function consultationStatus(): array
    {
        $query = DB::table('consultation_requests')->selectRaw('status, COUNT(*) as total')->groupBy('status');
        if ($this->user?->role_name === 'Teacher' && $this->user->teacher) {
            $query->where('teacher_id', $this->user->teacher->id);
        } elseif ($this->studentIds !== null) {
            $query->whereIn('student_id', $this->studentIds ?: [0]);
        }
        $rows = $query->get();

        return $this->pack(
            ['Status', 'Requests'],
            $rows->map(fn ($row) => [ucfirst((string) $row->status), (string) $row->total])->all(),
            ['Requests' => (int) $rows->sum('total')]
        );
    }

    private function events(): array
    {
        $query = DB::table('calendar_events')->orderByDesc('start_time')->limit(200);
        if ($this->user?->role_name === 'Teacher' && $this->user->teacher) {
            $query->where('teacher_id', $this->user->teacher->id);
        }
        $rows = $query->get(['title', 'event_type', 'start_time', 'end_time']);

        return $this->pack(
            ['Event', 'Type', 'Starts', 'Ends'],
            $rows->map(fn ($row) => [$row->title, $row->event_type ?: '—', $this->when($row->start_time), $this->when($row->end_time)])->all(),
            ['Events' => $rows->count()]
        );
    }

    private function communicationHistory(): array
    {
        $announcements = DB::table('announcements')->orderByDesc('created_at')->limit(50)->get(['title', 'created_at']);
        $messages = DB::table('messages')->orderByDesc('created_at')->limit(50)->get(['subject', 'type', 'is_read', 'created_at']);
        $lines = $announcements->map(fn ($row) => ['Announcement', $row->title, '—', $this->when($row->created_at)])->all();
        foreach ($messages as $row) {
            $lines[] = ['Message', $row->subject ?: '—', $row->is_read ? 'Read' : 'Unread', $this->when($row->created_at)];
        }

        return $this->pack(['Kind', 'Title', 'Status', 'Date'], $lines, ['Items' => count($lines)]);
    }

    private function documents(?string $mode): array
    {
        $query = DB::table('enrollment_documents as d')
            ->join('enrollment_applications as a', 'a.id', '=', 'd.enrollment_application_id')
            ->where('a.academic_year_id', $this->yearId)
            ->whereNull('a.deleted_at')
            ->orderByDesc('d.created_at')
            ->select('a.application_number', 'a.last_name', 'a.first_name', 'a.grade_level_applying_for', 'd.document_type', 'd.status', 'd.created_at');
        if ($mode === 'pending') {
            $query->where('d.status', 'pending');
        } elseif ($mode === 'reviewed') {
            $query->whereIn('d.status', ['verified', 'rejected']);
        } elseif ($mode === 'submitted') {
            $query->whereNotNull('d.file_path');
        } elseif ($mode === 'missing') {
            $query->where(function ($inner) {
                $inner->whereNull('d.file_path')->orWhere('d.status', 'rejected')->orWhere('a.status', 'needs_documents');
            });
        }
        $rows = $query->limit(400)->get();

        return $this->pack(
            ['Application', 'Applicant', 'Grade', 'Document', 'Status', 'Uploaded'],
            $rows->map(fn ($row) => [
                $row->application_number ?: '—',
                $this->person($row),
                $row->grade_level_applying_for ?: '—',
                $row->document_type ?: '—',
                $row->status ?: '—',
                $this->when($row->created_at),
            ])->all(),
            ['Documents' => $rows->count()]
        );
    }

    private function recordCompleteness(): array
    {
        $rows = DB::table('enrollment_applications as a')
            ->leftJoin('enrollment_documents as d', 'd.enrollment_application_id', '=', 'a.id')
            ->where('a.academic_year_id', $this->yearId)
            ->whereNull('a.deleted_at')
            ->groupBy('a.id', 'a.application_number', 'a.last_name', 'a.first_name')
            ->selectRaw("a.application_number, a.last_name, a.first_name, COUNT(d.id) as documents, SUM(d.status = 'verified') as verified")
            ->orderBy('a.last_name')
            ->get();

        return $this->pack(
            ['Application', 'Applicant', 'Documents', 'Verified', 'Complete'],
            $rows->map(function ($row) {
                $documents = (int) $row->documents;
                $verified = (int) $row->verified;
                $percent = $documents > 0 ? round(($verified / $documents) * 100).'%' : 'No documents';

                return [$row->application_number ?: '—', $this->person($row), (string) $documents, (string) $verified, $percent];
            })->all(),
            ['Applications' => $rows->count()]
        );
    }

    private function documentHistory(): array
    {
        $rows = DB::table('enrollment_documents as d')
            ->join('enrollment_applications as a', 'a.id', '=', 'd.enrollment_application_id')
            ->leftJoin('users as u', 'u.id', '=', 'd.verified_by')
            ->where('a.academic_year_id', $this->yearId)
            ->orderByDesc('d.updated_at')
            ->limit(200)
            ->get(['a.last_name', 'a.first_name', 'd.document_type', 'd.status', 'd.created_at', 'd.verified_at', 'u.name']);

        return $this->pack(
            ['Applicant', 'Document', 'Status', 'Uploaded', 'Reviewed', 'Reviewed by'],
            $rows->map(fn ($row) => [$this->person($row), $row->document_type ?: '—', $row->status ?: '—', $this->when($row->created_at), $this->when($row->verified_at), $row->name ?: '—'])->all()
        );
    }

    private function transferDocuments(): array
    {
        $rows = DB::table('enrollment_applications as a')
            ->leftJoin('enrollment_documents as d', 'd.enrollment_application_id', '=', 'a.id')
            ->where('a.academic_year_id', $this->yearId)
            ->whereNull('a.deleted_at')
            ->where(function ($query) {
                $query->where('a.enrollment_type', 'transferee')
                    ->orWhere('a.student_category', 'transferee')
                    ->orWhere('d.document_type', 'like', '%transfer%')
                    ->orWhere('d.document_type', 'like', '%clearance%');
            })
            ->orderBy('a.last_name')
            ->limit(300)
            ->get(['a.application_number', 'a.last_name', 'a.first_name', 'a.student_category', 'd.document_type', 'd.status']);

        return $this->pack(
            ['Application', 'Applicant', 'Category', 'Document', 'Status'],
            $rows->map(fn ($row) => [
                $row->application_number ?: '—',
                $this->person($row),
                str_replace('_', ' ', (string) $row->student_category) ?: '—',
                $row->document_type ?: 'No document row',
                $row->status ?: '—',
            ])->all(),
            ['Rows' => $rows->count()]
        );
    }

    private function enrollmentGradeYear(): array
    {
        $rows = DB::table('student_section_assignments as a')
            ->join('sections as s', 's.id', '=', 'a.section_id')
            ->join('academic_years as y', 'y.id', '=', 'a.academic_year_id')
            ->selectRaw('y.name as year_name, s.grade_level, COUNT(DISTINCT a.student_id) as students')
            ->groupBy('y.id', 'y.name', 'y.start_date', 's.grade_level')
            ->orderBy('y.start_date')
            ->orderBy('s.grade_level')
            ->get();

        return $this->pack(
            ['Academic year', 'Grade level', 'Students'],
            $rows->map(fn ($row) => [$row->year_name, $row->grade_level ?: '—', (string) $row->students])->all(),
            [],
            'Each row belongs to one academic year. Counts are not combined across years.'
        );
    }

    private function schoolPerformance(): array
    {
        $bundle = $this->scores();
        $placed = count($this->placements());
        $graded = count($bundle['students']);
        $below = collect($bundle['students'])->filter(fn ($score) => $score['average'] < AcademicThresholds::PASSING_PERCENTAGE)->count();
        $average = $graded ? round(collect($bundle['students'])->avg('average'), 2) : null;

        return $this->pack(
            ['Measure', 'Value'],
            [
                ['Students placed', (string) $placed],
                ['Students with a subject average', (string) $graded],
                ['Average', $average !== null ? number_format($average, 2).'%' : '—'],
                ['Below 75%', (string) $below],
                ['Incomplete', (string) max(0, $placed - $graded)],
            ],
            [],
            'Passing is '.number_format(AcademicThresholds::PASSING_PERCENTAGE, 0).'% for the current semester.'
        );
    }

    private function absenteeismTrends(): array
    {
        if (! $this->year?->start_date || ! $this->year?->end_date) {
            return $this->blank('This academic year has no start and end dates, so monthly attendance is not shown.');
        }
        $rows = $this->attendanceBase()
            ->selectRaw("DATE_FORMAT(date, '%Y-%m') as month, SUM(status = 'present') as present, SUM(status = 'late') as late, SUM(status = 'absent') as absent, SUM(status = 'excused') as excused, COUNT(*) as total")
            ->groupByRaw("DATE_FORMAT(date, '%Y-%m')")
            ->orderBy('month')
            ->get();
        $lines = $rows->map(function ($row) {
            $base = max(0, (int) $row->total - (int) $row->excused);
            $rate = $base > 0 ? number_format(round((((int) $row->present + (int) $row->late) / $base) * 100, 2), 2).'%' : '—';

            return [$row->month, (string) ((int) $row->absent), $rate];
        })->all();

        return $this->pack(['Month', 'Absences', 'Attendance rate'], $lines, ['Months' => count($lines)]);
    }

    private function operationsSummary(): array
    {
        $students = DB::table('student_section_assignments')->where('academic_year_id', $this->yearId)->distinct('student_id')->count('student_id');
        $sections = DB::table('sections')->where('academic_year_id', $this->yearId)->count();
        $applications = DB::table('enrollment_applications')->where('academic_year_id', $this->yearId)->whereNull('deleted_at')->whereIn('status', ['pending', 'under_review', 'needs_documents'])->count();
        $lessons = DB::table('lessons')->where('academic_year_id', $this->yearId)->count();
        $alerts = DB::table('grade_alerts')->where('academic_year_id', $this->yearId)->where('is_resolved', false)->count();

        return $this->pack(['Area', 'Count'], [
            ['Students placed this year', (string) $students],
            ['Sections', (string) $sections],
            ['Open enrollment applications', (string) $applications],
            ['Lessons', (string) $lessons],
            ['Open grade alerts', (string) $alerts],
        ]);
    }

    private function userAccounts(bool $summary): array
    {
        if ($summary) {
            $rows = DB::table('users')->selectRaw('role_name, status, COUNT(*) as total')->groupBy('role_name', 'status')->orderBy('role_name')->get();

            return $this->pack(
                ['Role', 'Status', 'Accounts'],
                $rows->map(fn ($row) => [$row->role_name ?: '—', $row->status ?: '—', (string) $row->total])->all(),
                ['Accounts' => (int) $rows->sum('total')]
            );
        }
        $rows = DB::table('users')->orderBy('role_name')->orderBy('name')->get(['name', 'email', 'role_name', 'status', 'created_at']);

        return $this->pack(
            ['Name', 'Email', 'Role', 'Status', 'Created'],
            $rows->map(fn ($row) => [$row->name, $row->email, $row->role_name ?: '—', $row->status ?: '—', $this->when($row->created_at)])->all(),
            ['Accounts' => $rows->count()],
            'Passwords are not included.'
        );
    }

    private function loginHistory(): array
    {
        return $this->audit('%login%', 'Sign-in events are not recorded, so this report has no rows.');
    }

    private function accountChanges(): array
    {
        $created = DB::table('users')->orderByDesc('created_at')->limit(100)->get(['name', 'role_name', 'status', 'created_at']);
        $lines = $created->map(fn ($row) => [$this->when($row->created_at), 'Created', $row->name, $row->role_name.' · '.($row->status ?: '—')])->all();
        $updates = $this->auditRows('%User%');
        foreach ($updates as $row) {
            $lines[] = [$this->when($row->created_at), $row->event ?: 'Updated', $row->name ?: '—', $row->description ?: 'Account update'];
        }

        return $this->pack(['When', 'Event', 'Account', 'Detail'], $lines, ['Rows' => count($lines)]);
    }

    private function exportHistory(): array
    {
        if (! Schema::hasTable('activity_log')) {
            return $this->blank('Export and document access events are not recorded.');
        }
        $rows = DB::table('activity_log')
            ->leftJoin('users', 'users.id', '=', 'activity_log.causer_id')
            ->where(function ($query) {
                $query->where('activity_log.description', 'like', '%export%')
                    ->orWhere('activity_log.description', 'like', '%download%')
                    ->orWhere('activity_log.log_name', 'like', '%export%');
            })
            ->orderByDesc('activity_log.created_at')
            ->limit(150)
            ->get(['activity_log.created_at', 'activity_log.description', 'users.name']);
        if ($rows->isEmpty()) {
            return $this->blank('Export and document access events are not recorded.');
        }

        return $this->pack(
            ['When', 'By', 'Detail'],
            $rows->map(fn ($row) => [$this->when($row->created_at), $row->name ?: '—', $row->description ?: '—'])->all()
        );
    }

    private function failedOperations(): array
    {
        if (! Schema::hasTable('failed_jobs')) {
            return $this->blank('Failed background jobs are not recorded.');
        }
        $rows = DB::table('failed_jobs')->orderByDesc('failed_at')->limit(50)->get(['failed_at', 'queue', 'exception']);
        if ($rows->isEmpty()) {
            return $this->blank('No failed background jobs are recorded.');
        }

        return $this->pack(
            ['When', 'Queue', 'Error'],
            $rows->map(fn ($row) => [$this->when($row->failed_at), $row->queue ?: '—', strtok((string) $row->exception, "\n") ?: '—'])->all()
        );
    }

    private function audit(string $like, string $emptyNote): array
    {
        $rows = $this->auditRows($like);
        if ($rows->isEmpty()) {
            return $this->blank($emptyNote);
        }

        return $this->pack(
            ['When', 'Event', 'By', 'Detail'],
            $rows->map(fn ($row) => [$this->when($row->created_at), $row->event ?: '—', $row->name ?: '—', $row->description ?: '—'])->all()
        );
    }

    private function auditRows(string $like)
    {
        if (! Schema::hasTable('activity_log')) {
            return collect();
        }

        return DB::table('activity_log')
            ->leftJoin('users', function ($join) {
                $join->on('users.id', '=', 'activity_log.causer_id')
                    ->where('activity_log.causer_type', 'like', '%User%');
            })
            ->where(function ($query) use ($like) {
                $query->where('activity_log.subject_type', 'like', $like)
                    ->orWhere('activity_log.description', 'like', $like);
            })
            ->orderByDesc('activity_log.created_at')
            ->limit(150)
            ->get(['activity_log.created_at', 'activity_log.event', 'activity_log.description', 'users.name']);
    }

    private function scores(): array
    {
        if ($this->scoreCache === null) {
            $this->scoreCache = $this->semesterId
                ? $this->performance->periodScores($this->yearId, $this->semesterId, $this->placedStudentIds())
                : ['students' => [], 'grades' => collect(), 'fields' => []];
        }

        return $this->scoreCache;
    }

    private function placedStudentIds(): array
    {
        $query = DB::table('student_section_assignments')->where('academic_year_id', $this->yearId);
        $this->limitStudents($query, 'student_id');
        $this->limitSection($query, 'section_id');

        return $query->pluck('student_id')->map(fn ($id) => (int) $id)->unique()->values()->all();
    }

    private function placements(): array
    {
        $query = DB::table('student_section_assignments as a')
            ->join('sections as s', 's.id', '=', 'a.section_id')
            ->where('a.academic_year_id', $this->yearId)
            ->select('a.student_id', 's.grade_level', 's.name as section_name', 's.id as section_id');
        $this->limitStudents($query, 'a.student_id');
        $this->limitSection($query, 's.id');

        return $query->get()->keyBy(fn ($row) => (int) $row->student_id)->all();
    }

    private function names(array $ids): array
    {
        if ($ids === []) {
            return [];
        }

        return DB::table('students')
            ->whereIn('id', $ids)
            ->get(['id', 'first_name', 'last_name', 'admission_id', 'roll'])
            ->keyBy(fn ($row) => (int) $row->id)
            ->all();
    }

    private function lessonQuery()
    {
        $query = DB::table('lessons')->where('lessons.academic_year_id', $this->yearId);
        $this->limitTeacherLessons($query, 'lessons.teacher_id');
        $this->limitSection($query, 'lessons.section_id');

        return $query;
    }

    private function limitStudents($query, string $column): void
    {
        if ($this->studentIds !== null) {
            $query->whereIn($column, $this->studentIds ?: [0]);
        }
    }

    private function limitSection($query, string $column): void
    {
        if ($this->sectionIds !== []) {
            $query->whereIn($column, $this->sectionIds);
        }
    }

    private function limitPlacedSection($query, string $studentColumn): void
    {
        if ($this->sectionIds === []) {
            return;
        }

        $query->whereExists(function ($sub) use ($studentColumn) {
            $sub->selectRaw('1')
                ->from('student_section_assignments as placed')
                ->whereColumn('placed.student_id', $studentColumn)
                ->whereIn('placed.section_id', $this->sectionIds);
            if ($this->yearId) {
                $sub->where('placed.academic_year_id', $this->yearId);
            }
        });
    }

    private function placementNames(array $ids): array
    {
        $ids = array_values(array_unique(array_filter($ids)));
        if ($ids === []) {
            return [];
        }

        $query = DB::table('student_section_assignments as a')
            ->join('sections as s', 's.id', '=', 'a.section_id')
            ->whereIn('a.student_id', $ids)
            ->orderByDesc('a.id')
            ->select('a.student_id', 's.name');
        if ($this->yearId) {
            $query->where('a.academic_year_id', $this->yearId);
        }
        $this->limitSection($query, 's.id');

        $names = [];
        foreach ($query->get() as $row) {
            $id = (int) $row->student_id;
            if (! array_key_exists($id, $names)) {
                $names[$id] = $row->name ?: '—';
            }
        }

        return $names;
    }

    private function limitTeacherLessons($query, string $column): void
    {
        if ($this->user?->role_name === 'Teacher' && $this->user->teacher) {
            $query->where($column, $this->user->teacher->id);
        }
    }

    private function attendanceStudentReport(?string $start, ?string $end, string $note): array
    {
        if (! $start || ! $end) {
            return $this->blank('This report has no date range, so student names are not included.');
        }

        $rows = DB::table('attendances')
            ->whereIn('student_id', $this->placedStudentIds() ?: [0])
            ->where('date', '>=', $start)
            ->where('date', '<=', $end)
            ->selectRaw("student_id, SUM(status = 'present') as present, SUM(status = 'late') as late, SUM(status = 'absent') as absent, SUM(status = 'excused') as excused, COUNT(*) as total")
            ->groupBy('student_id')
            ->get();
        $places = $this->placements();
        $names = $this->names($rows->pluck('student_id')->map(fn ($id) => (int) $id)->all());
        $lines = [];
        foreach ($rows as $row) {
            $studentId = (int) $row->student_id;
            $base = max(0, (int) $row->total - (int) $row->excused);
            $rate = $base > 0 ? round((((int) $row->present + (int) $row->late) / $base) * 100, 2) : null;
            $lines[] = array_merge($this->nameCells($names[$studentId] ?? null), [
                $places[$studentId]->grade_level ?? '—',
                $places[$studentId]->section_name ?? '—',
                (string) (int) $row->present,
                (string) (int) $row->late,
                (string) (int) $row->absent,
                (string) (int) $row->excused,
                $rate !== null ? number_format($rate, 2).'%' : '—',
            ]);
        }
        $this->sortByStudent($lines);

        return $this->pack(
            ['Student Name', 'Student Number', 'Grade', 'Section', 'Present', 'Late', 'Absent', 'Excused', 'Rate'],
            $lines,
            ['Students' => count($lines)],
            $note
        );
    }

    private function nameCells(?object $student): array
    {
        if (! $student) {
            return ['—', '—'];
        }

        $number = trim((string) ($student->admission_id ?? ''));
        if ($number === '') {
            $number = trim((string) ($student->roll ?? ''));
        }

        return [$this->person($student), $number !== '' ? $number : '—'];
    }

    private function sortByStudent(array &$lines): void
    {
        usort($lines, fn (array $left, array $right) => strcasecmp((string) ($left[0] ?? ''), (string) ($right[0] ?? '')));
    }

    private function person(object $row): string
    {
        $name = trim(($row->last_name ?? '').', '.($row->first_name ?? ''), ' ,');

        return $name !== '' ? $name : '—';
    }

    private function when($value): string
    {
        if (! $value) {
            return '—';
        }

        return Carbon::parse($value)->timezone('Asia/Manila')->format('M j, Y g:i A');
    }

    private function pack(array $columns, array $rows, array $summary = [], ?string $note = null): array
    {
        return compact('columns', 'rows', 'summary', 'note');
    }

    private function blank(string $note): array
    {
        return $this->pack(['Detail'], [], [], $note);
    }
}
