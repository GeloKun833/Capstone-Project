<?php

namespace App\Services;

use App\Models\AcademicYear;
use App\Models\ClassSchedule;
use App\Models\EnrollmentApplication;
use App\Models\GradeAlert;
use App\Models\QuarterlyGrade;
use App\Models\Section;
use App\Models\Semester;
use App\Models\Student;
use App\Models\StudentGpa;
use App\Models\Subject;
use App\Models\SubjectComponent;
use App\Models\Teacher;
use App\Models\User;
use App\Support\AcademicThresholds;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use RuntimeException;

/**
 * Generates and removes the 400 dummy/test students used for defense and demos.
 *
 * Every generated student is identified by BOTH an admission ID starting with
 * DUMMY-STU- and an email ending in @dummy.ams.local. Only existing sections,
 * subjects, teachers, academic years and semesters are referenced; nothing
 * outside the generated students/users and their own academic rows is written.
 */
class DummyStudentService
{
    public const TOTAL_STUDENTS = 400;

    public const LOW_PERFORMER_COUNT = 10;

    public const ADMISSION_PREFIX = 'DUMMY-STU-';

    public const EMAIL_PREFIX = 'dummy.stu.';

    public const EMAIL_DOMAIN = 'dummy.ams.local';

    public const PASSWORD = 'Dummy@123';

    public const DEFAULT_ATTENDANCE_DAYS = 10;

    public const DEFAULT_SEED = 2026;

    public const DEFAULT_SECTION_CAPACITY = 25;

    public const HIGH_PERFORMER_MIN_AVERAGE = 80.0;

    public const MISSING_TEACHER_ERROR = 1001;

    public const APPLICATION_PREFIX = 'DUMMY-APP-';

    public const APPLICATION_EMAIL_PREFIX = 'dummy.app.';

    /** Extra dummy applicants (not students) per registrar status, on top of one approved application per student. */
    public const EXTRA_APPLICATION_STATUSES = [
        'pending' => 30,
        'under_review' => 25,
        'needs_documents' => 25,
        'rejected' => 20,
    ];

    /** @var callable|null */
    protected $logger;

    public function __construct(
        protected GradeSubjectCatalogService $catalog,
        protected StudentPerformanceService $performance
    ) {}

    public function setLogger(?callable $logger): self
    {
        $this->logger = $logger;

        return $this;
    }

    public static function admissionId(int $n): string
    {
        return sprintf('%s%04d', self::ADMISSION_PREFIX, $n);
    }

    public static function email(int $n): string
    {
        return sprintf('%s%04d@%s', self::EMAIL_PREFIX, $n, self::EMAIL_DOMAIN);
    }

    public static function applicationNumber(int $n): string
    {
        return sprintf('%s%04d', self::APPLICATION_PREFIX, $n);
    }

    public static function totalApplications(): int
    {
        return self::TOTAL_STUDENTS + array_sum(self::EXTRA_APPLICATION_STATUSES);
    }

    public function dummyApplicationsQuery()
    {
        return EnrollmentApplication::withTrashed()
            ->where('application_number', 'like', self::APPLICATION_PREFIX.'%')
            ->where('email', 'like', '%@'.self::EMAIL_DOMAIN);
    }

    public static function isDummyEmail(?string $email): bool
    {
        return is_string($email) && str_ends_with(strtolower($email), '@'.self::EMAIL_DOMAIN);
    }

    public function dummyUsersQuery()
    {
        return User::query()
            ->whereIn('email', array_map([self::class, 'email'], range(1, self::TOTAL_STUDENTS)))
            ->where('role_name', User::ROLE_STUDENT);
    }

    public function dummyStudentsQuery()
    {
        return Student::withTrashed()
            ->where('admission_id', 'like', self::ADMISSION_PREFIX.'%')
            ->where('email', 'like', '%@'.self::EMAIL_DOMAIN);
    }

    /**
     * Resolve the full generation plan without writing anything.
     *
     * @param  array{academic_year_id?:int|null, semester_id?:int|null, attendance_days?:int|null, seed?:int|null, skip_unassigned?:bool}  $options
     */
    public function plan(array $options = []): array
    {
        [$academicYear, $semester] = $this->resolveAcademicPeriod(
            $options['academic_year_id'] ?? null,
            $options['semester_id'] ?? null
        );

        $gradeGroups = $this->resolveGradeGroups();
        if ($gradeGroups->isEmpty()) {
            throw new RuntimeException('No existing section has matching subjects (sections.grade_level ↔ subjects.class). Nothing can be generated.');
        }

        $skippedWithoutSubjects = $this->skippedSections($gradeGroups);
        $teacherMap = $this->resolveTeacherMap($gradeGroups);
        $skippedWithoutTeacher = [];

        if (! empty($teacherMap['missing'])) {
            $lines = collect($teacherMap['missing'])->flatten()->all();
            if (empty($options['skip_unassigned'])) {
                throw new RuntimeException(
                    "No existing teacher could be resolved for these section/subject pairs (assign one in the admin panel first, or rerun with --skip-unassigned to leave these sections out):\n - ".implode("\n - ", $lines),
                    self::MISSING_TEACHER_ERROR
                );
            }

            $excluded = array_keys($teacherMap['missing']);
            $gradeGroups = $gradeGroups
                ->map(function ($group) use ($excluded, &$skippedWithoutTeacher) {
                    [$skip, $keep] = $group['sections']->partition(fn ($section) => in_array($section->id, $excluded, true));
                    foreach ($skip as $section) {
                        $skippedWithoutTeacher[] = $section;
                    }
                    $group['sections'] = $keep->values();

                    return $group;
                })
                ->filter(fn ($group) => $group['sections']->isNotEmpty());

            if ($gradeGroups->isEmpty()) {
                throw new RuntimeException('Every section is missing a teacher for at least one subject. Assign teachers first; nothing was written.', self::MISSING_TEACHER_ERROR);
            }
        }

        $sections = $gradeGroups->flatMap(fn ($group) => $group['sections']);
        $occupancy = $this->sectionOccupancy($sections->pluck('id')->all(), $academicYear->id, $semester->id);
        $allocation = $this->allocate($gradeGroups, $occupancy);
        $lowPerformers = $this->lowPerformerNumbers();

        $warnings = [];
        foreach ($skippedWithoutSubjects as $skipped) {
            $warnings[] = "Section #{$skipped->id} {$skipped->name} ({$skipped->grade_level}) skipped: no subjects with a matching class.";
        }
        foreach ($skippedWithoutTeacher as $skipped) {
            $warnings[] = "Section #{$skipped->id} {$skipped->name} ({$skipped->grade_level}) skipped: no teacher for ".implode(', ', array_map(
                fn ($line) => last(explode(' / ', $line)),
                $teacherMap['missing'][$skipped->id]
            )).'.';
        }
        foreach ($sections as $section) {
            $capacity = (int) ($section->capacity ?? self::DEFAULT_SECTION_CAPACITY);
            $total = ($occupancy[$section->id] ?? 0) + ($allocation['counts'][$section->id] ?? 0);
            if ($total > $capacity) {
                $warnings[] = "Section #{$section->id} {$section->name} will hold {$total} students (capacity {$capacity}). Capacity is not changed.";
            }
        }
        foreach ($teacherMap['fallbacks'] as $sectionId => $sectionFallbacks) {
            if (! isset($teacherMap['missing'][$sectionId])) {
                array_push($warnings, ...$sectionFallbacks);
            }
        }

        return [
            'academic_year' => $academicYear,
            'semester' => $semester,
            'grade_groups' => $gradeGroups,
            'teacher_map' => array_diff_key($teacherMap['map'], $teacherMap['missing']),
            'occupancy' => $occupancy,
            'students' => $allocation['students'],
            'section_counts' => $allocation['counts'],
            'low_performers' => $lowPerformers,
            'attendance_dates' => $this->attendanceDates(
                $academicYear,
                max(0, (int) ($options['attendance_days'] ?? self::DEFAULT_ATTENDANCE_DAYS))
            ),
            'seed' => (int) ($options['seed'] ?? self::DEFAULT_SEED),
            'warnings' => $warnings,
        ];
    }

    /**
     * Create the 400 dummy students inside a single transaction.
     * Never deletes anything; refuses to run while dummy students already exist.
     */
    public function create(array $options = []): array
    {
        $this->assertNoExistingDummyData();
        $plan = $this->plan($options);
        $this->assertNoCollisions();

        $previousLog = config('activitylog.enabled');
        config(['activitylog.enabled' => false]);

        try {
            $result = DB::transaction(fn () => $this->persist($plan));
            $this->forgetAdminDashboardCache();

            return $result;
        } finally {
            config(['activitylog.enabled' => $previousLog]);
        }
    }

    public function summarizePlan(array $plan): array
    {
        return [
            'academic_year' => $plan['academic_year'],
            'semester' => $plan['semester'],
            'grade_distribution' => $this->gradeDistribution($plan['students']),
            'section_distribution' => $this->sectionDistributionRows($plan),
            'teacher_assignments' => $this->teacherAssignmentRows($plan),
            'low_performers' => collect($plan['students'])
                ->filter(fn ($s) => in_array($s['n'], $plan['low_performers'], true))
                ->map(fn ($s) => [
                    'admission_id' => self::admissionId($s['n']),
                    'name' => null,
                    'email' => self::email($s['n']),
                    'grade' => $s['grade'],
                    'section' => $s['section']->name,
                    'general_average' => null,
                ])->values()->all(),
            'attendance_days' => count($plan['attendance_dates']),
            'warnings' => $plan['warnings'],
        ];
    }

    /**
     * Counts of rows the delete command would remove.
     */
    public function deletionPreview(): array
    {
        [$studentIds, $userIds] = $this->dummyIds();

        $counts = ['users' => count($userIds), 'students' => count($studentIds)];
        foreach ($this->studentScopedTables() as $table) {
            $counts[$table] = empty($studentIds) || ! Schema::hasTable($table)
                ? 0
                : DB::table($table)->whereIn('student_id', $studentIds)->count();
        }
        $counts['enrollment_applications'] = count($this->dummyApplicationIds());

        return $counts;
    }

    /**
     * Delete ONLY the dummy students, their dummy user accounts and rows that
     * reference them. Real rows are never updated or deleted.
     */
    public function delete(): array
    {
        $previousLog = config('activitylog.enabled');
        config(['activitylog.enabled' => false]);

        try {
            $result = DB::transaction(function () {
                [$studentIds, $userIds] = $this->dummyIds();
                $applicationIds = $this->dummyApplicationIds();
                $counts = ['users' => count($userIds), 'students' => count($studentIds), 'enrollment_applications' => count($applicationIds)];

                if (empty($studentIds) && empty($userIds) && empty($applicationIds)) {
                    return $counts;
                }

                foreach (array_chunk($studentIds, 200) as $chunk) {
                    if (Schema::hasTable('activity_grades') && Schema::hasTable('activity_submissions')) {
                        $submissionIds = DB::table('activity_submissions')->whereIn('student_id', $chunk)->pluck('id');
                        if ($submissionIds->isNotEmpty()) {
                            DB::table('activity_grades')->whereIn('submission_id', $submissionIds)->delete();
                        }
                    }
                    foreach ($this->studentScopedTables() as $table) {
                        if (Schema::hasTable($table)) {
                            $counts[$table] = ($counts[$table] ?? 0)
                                + DB::table($table)->whereIn('student_id', $chunk)->delete();
                        }
                    }
                    if (Schema::hasTable('activity_log')) {
                        DB::table('activity_log')
                            ->where('subject_type', Student::class)
                            ->whereIn('subject_id', $chunk)
                            ->delete();
                    }
                }

                foreach (array_chunk($userIds, 200) as $chunk) {
                    $emails = User::whereIn('id', $chunk)->pluck('email')->all();
                    if (Schema::hasTable('messages')) {
                        DB::table('messages')
                            ->where(fn ($q) => $q->whereIn('sender_id', $chunk)->orWhereIn('recipient_id', $chunk))
                            ->delete();
                    }
                    $this->deleteMorph('notifications', 'notifiable', $chunk);
                    $this->deleteMorph('model_has_roles', 'model', $chunk);
                    $this->deleteMorph('model_has_permissions', 'model', $chunk);
                    $this->deleteMorph('personal_access_tokens', 'tokenable', $chunk);
                    if (Schema::hasTable('activity_log')) {
                        DB::table('activity_log')
                            ->where(fn ($q) => $q
                                ->where(fn ($qq) => $qq->where('causer_type', User::class)->whereIn('causer_id', $chunk))
                                ->orWhere(fn ($qq) => $qq->where('subject_type', User::class)->whereIn('subject_id', $chunk)))
                            ->delete();
                    }
                    if (Schema::hasTable('sessions')) {
                        DB::table('sessions')->whereIn('user_id', $chunk)->delete();
                    }
                    if (Schema::hasTable('password_reset_tokens') && ! empty($emails)) {
                        DB::table('password_reset_tokens')->whereIn('email', $emails)->delete();
                    }
                }

                foreach (array_chunk($studentIds, 200) as $chunk) {
                    DB::table('students')->whereIn('id', $chunk)->delete();
                }
                foreach (array_chunk($userIds, 200) as $chunk) {
                    DB::table('users')->whereIn('id', $chunk)->delete();
                }
                foreach (array_chunk($applicationIds, 200) as $chunk) {
                    if (Schema::hasTable('enrollment_documents')) {
                        DB::table('enrollment_documents')->whereIn('enrollment_application_id', $chunk)->delete();
                    }
                    DB::table('enrollment_applications')->whereIn('id', $chunk)->delete();
                }

                return $counts;
            });

            if ($result['students'] > 0 || $result['users'] > 0) {
                $this->forgetAdminDashboardCache();
            }

            return $result;
        } finally {
            config(['activitylog.enabled' => $previousLog]);
        }
    }

    protected function forgetAdminDashboardCache(): void
    {
        try {
            Cache::forget('admin.dashboard.data.v3');
        } catch (\Throwable $e) {
            report($e);
        }
    }

    /**
     * Post-generation validation. Every check is read-only.
     *
     * @return array{checks: list<array{check:string, passed:bool, detail:string}>, low_performers: array, section_distribution: array, academic_year: ?AcademicYear, semester: ?Semester}
     */
    public function verify(): array
    {
        $checks = [];
        $add = function (string $check, bool $passed, string $detail = '') use (&$checks) {
            $checks[] = ['check' => $check, 'passed' => $passed, 'detail' => $detail];
        };

        $students = $this->dummyStudentsQuery()->whereNull('deleted_at')->with('user')->orderBy('admission_id')->get();
        $studentIds = $students->pluck('id')->all();
        $add('Exactly '.self::TOTAL_STUDENTS.' dummy students', $students->count() === self::TOTAL_STUDENTS, $students->count().' found');

        if ($students->isEmpty()) {
            return ['checks' => $checks, 'low_performers' => [], 'section_distribution' => [], 'academic_year' => null, 'semester' => null];
        }

        $assignment = DB::table('student_section_assignments')->whereIn('student_id', $studentIds)->first();
        $academicYear = $assignment ? AcademicYear::find($assignment->academic_year_id) : null;
        $semester = $assignment ? Semester::find($assignment->semester_id) : null;
        $add('Academic year / semester exist', $academicYear !== null && $semester !== null,
            ($academicYear->name ?? '?').' / '.($semester->name ?? '?'));

        $add('Admission IDs and emails are unique',
            $students->pluck('admission_id')->unique()->count() === $students->count()
                && $students->pluck('email')->unique()->count() === $students->count()
                && User::whereIn('email', $students->pluck('email'))->count() === $students->count());

        $badProfiles = $students->filter(fn ($s) => collect(['first_name', 'last_name', 'gender', 'date_of_birth', 'address', 'phone_number', 'class', 'section', 'enrollment_status'])
            ->contains(fn ($field) => blank($s->{$field})))->count();
        $add('Student profiles have required fields', $badProfiles === 0, $badProfiles.' incomplete');

        $badUsers = $students->filter(fn ($s) => ! $s->user
            || $s->user->email !== $s->email
            || $s->user->role_name !== User::ROLE_STUDENT
            || ! $s->user->isActiveAccount()
            || ! $s->user->hasRole(User::ROLE_STUDENT))->count();
        $add('Each student has an active Student user account', $badUsers === 0, $badUsers.' invalid');

        $sample = $students->first()->user;
        $add('Generated password works (Hash::check)', $sample && Hash::check(self::PASSWORD, $sample->password));

        $assignments = DB::table('student_section_assignments as ssa')
            ->join('sections as s', 's.id', '=', 'ssa.section_id')
            ->whereIn('ssa.student_id', $studentIds)
            ->get(['ssa.student_id', 's.id as section_id', 's.name', 's.grade_level']);
        $byStudent = $assignments->groupBy('student_id');
        $badSections = $students->filter(function ($s) use ($byStudent) {
            $rows = $byStudent->get($s->id, collect());

            return $rows->count() !== 1
                || ! in_array($rows->first()->grade_level, GradeSubjectCatalogService::gradeAliases($s->class), true)
                || $rows->first()->name !== $s->section;
        })->count();
        $add('Each student belongs to one existing section of their grade', $badSections === 0, $badSections.' invalid');

        $enrollments = DB::table('enrollments')->whereIn('student_id', $studentIds)->get()->groupBy('student_id');
        $subjectsByGrade = [];
        $badEnrollments = $students->filter(function ($s) use ($enrollments, &$subjectsByGrade, $academicYear, $semester) {
            $expected = $subjectsByGrade[$s->class] ??= $this->catalog->subjectsForGrade($s->class)->pluck('id')->sort()->values()->all();
            $rows = $enrollments->get($s->id, collect());
            $actual = $rows->pluck('subject_id')->map(fn ($id) => (int) $id)->sort()->values()->all();

            return empty($expected)
                || $actual !== $expected
                || $rows->contains(fn ($r) => $r->status !== 'active'
                    || (int) $r->academic_year_id !== (int) optional($academicYear)->id
                    || (int) $r->semester_id !== (int) optional($semester)->id);
        })->count();
        $add('Each student is enrolled in exactly the subjects of their grade', $badEnrollments === 0, $badEnrollments.' mismatched');

        $enrollmentCount = DB::table('enrollments')->whereIn('student_id', $studentIds)->count();
        $quarterly = QuarterlyGrade::whereIn('student_id', $studentIds)->get();
        $missingGrades = DB::table('enrollments as e')
            ->leftJoin('quarterly_grades as q', function ($join) {
                $join->on('q.student_id', '=', 'e.student_id')
                    ->on('q.subject_id', '=', 'e.subject_id')
                    ->on('q.academic_year_id', '=', 'e.academic_year_id');
            })
            ->whereIn('e.student_id', $studentIds)
            ->whereNull('q.id')
            ->count();
        $add('Every enrolled subject has a quarterly grade', $missingGrades === 0 && $quarterly->count() === $enrollmentCount,
            $quarterly->count().' quarterly grades / '.$enrollmentCount.' enrollments');

        $invalidScores = $quarterly->filter(fn ($q) => collect(['quarter_1', 'quarter_2', 'quarter_3', 'quarter_4', 'final_grade'])
            ->contains(fn ($f) => $q->{$f} !== null && ((float) $q->{$f} < 60 || (float) $q->{$f} > 100)))->count();
        $add('All grade values are within 60–100', $invalidScores === 0, $invalidScores.' invalid');

        $orphans = $this->orphanCounts($studentIds);
        $add('No orphaned foreign keys (subjects, teachers, sections, periods)', array_sum($orphans) === 0,
            array_sum($orphans) === 0 ? '0 orphans' : json_encode(array_filter($orphans)));

        $teacherIds = $quarterly->pluck('teacher_id')->unique()->filter();
        $add('Grades reference existing teachers', Teacher::whereIn('id', $teacherIds)->count() === $teacherIds->count(),
            $teacherIds->count().' distinct teachers');

        $attendanceStudents = DB::table('attendances')->whereIn('student_id', $studentIds)->distinct()->count('student_id');
        $badStatus = DB::table('attendances')->whereIn('student_id', $studentIds)->whereNotIn('status', ['present', 'absent', 'late', 'excused'])->count();
        $add('Every student has valid attendance records', $attendanceStudents === $students->count() && $badStatus === 0,
            $attendanceStudents.' students with attendance');

        $gpaCount = $semester ? StudentGpa::whereIn('student_id', $studentIds)->where('semester_id', $semester->id)->count() : 0;
        $add('Every student has a GPA record', $gpaCount === $students->count(), $gpaCount.' GPA rows');

        $averages = $this->generalAverages($students, $quarterly, $academicYear, $semester);
        $low = $averages->filter(fn ($row) => $row['general_average'] < AcademicThresholds::PASSING_PERCENTAGE);
        $high = $averages->filter(fn ($row) => $row['general_average'] >= self::HIGH_PERFORMER_MIN_AVERAGE && $row['min_subject'] >= AcademicThresholds::PASSING_PERCENTAGE);
        $add('Exactly '.self::LOW_PERFORMER_COUNT.' low performers (general average < '.AcademicThresholds::PASSING_PERCENTAGE.')',
            $low->count() === self::LOW_PERFORMER_COUNT, $low->count().' found');
        $add((self::TOTAL_STUDENTS - self::LOW_PERFORMER_COUNT).' high performers (GA ≥ '.self::HIGH_PERFORMER_MIN_AVERAGE.', no failing subject)',
            $high->count() === self::TOTAL_STUDENTS - self::LOW_PERFORMER_COUNT, $high->count().' found');
        $distinctPatterns = $quarterly->groupBy('student_id')
            ->map(fn ($rows) => $rows->sortBy('subject_id')->pluck('final_grade')->implode(','))
            ->unique()->count();
        $add('Grade patterns vary between students', $distinctPatterns > $students->count() * 0.9, $distinctPatterns.' distinct patterns');

        $lowIds = $low->keys()->all();
        $alerted = empty($lowIds) ? 0 : GradeAlert::whereIn('student_id', $lowIds)->where('is_resolved', false)
            ->where('alert_type', GradeAlert::TYPE_AT_RISK)->distinct()->count('student_id');
        $add('Every low performer has an open at-risk alert', $alerted === count($lowIds), $alerted.' alerted');

        if (Schema::hasTable('enrollment_applications')) {
            $applications = $this->dummyApplicationsQuery()->whereNull('deleted_at')->get(['id', 'status']);
            $approvedIds = $applications->where('status', 'approved')->pluck('id')->all();
            $linked = empty($approvedIds) ? 0 : Student::whereIn('id', $studentIds)->whereIn('enrollment_application_id', $approvedIds)->count();
            $byStatus = $applications->countBy('status');
            $extrasOk = collect(self::EXTRA_APPLICATION_STATUSES)->every(fn ($count, $status) => ($byStatus[$status] ?? 0) === $count);
            $add('Dummy enrollment applications ('.self::TOTAL_STUDENTS.' approved + '.array_sum(self::EXTRA_APPLICATION_STATUSES).' other statuses)',
                count($approvedIds) === self::TOTAL_STUDENTS && $linked === self::TOTAL_STUDENTS && $extrasOk,
                $byStatus->map(fn ($c, $st) => "{$st}: {$c}")->implode(', ') ?: 'none (run php artisan dummy:students:applications)');
        }

        try {
            $lowStudent = $students->firstWhere('id', $lowIds[0] ?? $students->first()->id);
            $analytics = $this->performance->studentAnalytics($lowStudent, $academicYear->id, $semester->id);
            $report = app(ReportCardService::class)->forStudent($lowStudent, $academicYear);
            $add('Analytics and report card load for a dummy student',
                ! empty($analytics) && collect($report['learningRows'] ?? [])->isNotEmpty(),
                'report card subjects: '.collect($report['learningRows'] ?? [])->count());
        } catch (\Throwable $e) {
            $add('Analytics and report card load for a dummy student', false, $e->getMessage());
        }

        $sectionRows = $assignments->groupBy('section_id')->map(fn ($rows) => [
            'section_id' => $rows->first()->section_id,
            'section' => $rows->first()->name,
            'grade' => $rows->first()->grade_level,
            'dummy_students' => $rows->count(),
        ])->sortBy(fn ($r) => sprintf('%03d-%s', $this->gradeOrder($r['grade']), $r['section']))->values()->all();

        return [
            'checks' => $checks,
            'low_performers' => $low->values()->all(),
            'section_distribution' => $sectionRows,
            'academic_year' => $academicYear,
            'semester' => $semester,
        ];
    }

    protected function persist(array $plan): array
    {
        mt_srand($plan['seed']);

        $academicYear = $plan['academic_year'];
        $semester = $plan['semester'];
        $now = now();
        $password = Hash::make(self::PASSWORD);
        $hasLegacyPivot = Schema::hasTable('section_student');

        $this->log('Creating '.count($plan['students']).' dummy student accounts and profiles...');
        $created = [];
        foreach ($plan['students'] as $item) {
            $created[] = $this->createStudent($item, $plan, $password, $now, $hasLegacyPivot);
        }

        $this->log('Creating enrollments, grades, attendance, GPA and alerts...');
        $finalComponents = SubjectComponent::query()
            ->where('name', 'Final Grade')
            ->where('is_active', true)
            ->get()
            ->keyBy('subject_id');

        $counts = ['enrollments' => 0, 'quarterly_grades' => 0, 'grades' => 0, 'attendances' => 0];
        foreach (array_chunk($created, 50) as $batch) {
            $rows = ['enrollments' => [], 'quarterly_grades' => [], 'grades' => [], 'attendances' => []];

            foreach ($batch as $row) {
                $isLow = in_array($row['n'], $plan['low_performers'], true);
                $subjects = $plan['grade_groups'][$row['grade']]['subjects'];
                $scores = $this->scoresFor($subjects->count(), $isLow);

                foreach ($subjects->values() as $index => $subject) {
                    $teacherId = $plan['teacher_map'][$row['section']->id][$subject->id]['teacher_id'];
                    [$q1, $q2, $q3, $q4] = $scores[$index];
                    $quarterly = new QuarterlyGrade(['quarter_1' => $q1, 'quarter_2' => $q2, 'quarter_3' => $q3, 'quarter_4' => $q4]);
                    $final = $quarterly->calculateFinalGrade();
                    $quarterly->final_grade = $final;
                    $remarks = $quarterly->getRemarks();

                    $rows['enrollments'][] = [
                        'student_id' => $row['student']->id,
                        'subject_id' => $subject->id,
                        'academic_year_id' => $academicYear->id,
                        'semester_id' => $semester->id,
                        'enrollment_date' => $now,
                        'status' => 'active',
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];

                    $rows['quarterly_grades'][] = [
                        'student_id' => $row['student']->id,
                        'subject_id' => $subject->id,
                        'teacher_id' => $teacherId,
                        'academic_year_id' => $academicYear->id,
                        'quarter_1' => $q1,
                        'quarter_2' => $q2,
                        'quarter_3' => $q3,
                        'quarter_4' => $q4,
                        'final_grade' => $final,
                        'remarks' => $remarks,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];

                    if ($component = $finalComponents->get($subject->id)) {
                        $rows['grades'][] = [
                            'student_id' => $row['student']->id,
                            'subject_id' => $subject->id,
                            'teacher_id' => $teacherId,
                            'component_id' => $component->id,
                            'score' => $final,
                            'max_score' => 100,
                            'percentage' => $final,
                            'remarks' => $remarks,
                            'grading_period' => 'quarterly',
                            'academic_year_id' => $academicYear->id,
                            'semester_id' => $semester->id,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ];
                    }

                    foreach ($plan['attendance_dates'] as $date) {
                        $rows['attendances'][] = [
                            'student_id' => $row['student']->id,
                            'subject_id' => $subject->id,
                            'teacher_id' => $teacherId,
                            'date' => $date,
                            'status' => $this->attendanceStatus($isLow),
                            'remarks' => null,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ];
                    }
                }
            }

            foreach ($rows as $table => $tableRows) {
                foreach (array_chunk($tableRows, 500) as $chunk) {
                    DB::table($table)->insert($chunk);
                }
                $counts[$table] += count($tableRows);
            }
        }

        foreach ($created as $row) {
            $this->performance->recalculateStudentGpa($row['student']->id, $academicYear->id, $semester->id);
        }

        $counts['grade_alerts'] = $this->createAlertsForLowPerformers($created, $plan);
        if (Schema::hasTable('enrollment_applications')) {
            $counts['enrollment_applications'] = array_sum($this->createApplications(collect($created)->pluck('student'), $plan['seed']));
        }

        $studentIds = collect($created)->pluck('student.id');
        $averages = $this->generalAverages(
            collect($created)->pluck('student'),
            QuarterlyGrade::whereIn('student_id', $studentIds)->get(),
            $academicYear,
            $semester
        );

        $summary = $this->summarizePlan($plan);
        $summary['counts'] = ['students' => count($created), 'users' => count($created)] + $counts
            + ['student_gpa' => StudentGpa::whereIn('student_id', $studentIds)->count()];
        $summary['low_performers'] = collect($created)
            ->filter(fn ($row) => in_array($row['n'], $plan['low_performers'], true))
            ->map(fn ($row) => [
                'admission_id' => $row['student']->admission_id,
                'name' => $row['student']->first_name.' '.$row['student']->last_name,
                'email' => $row['student']->email,
                'grade' => $row['grade'],
                'section' => $row['section']->name,
                'general_average' => $averages[$row['student']->id]['general_average'] ?? null,
            ])->values()->all();
        $lowAdmissionIds = array_column($summary['low_performers'], 'admission_id');
        $highAverages = $averages->reject(fn ($r) => in_array($r['admission_id'], $lowAdmissionIds, true));
        $summary['high_average_range'] = [
            'min' => $highAverages->min('general_average'),
            'max' => $highAverages->max('general_average'),
        ];

        return $summary;
    }

    protected function createStudent(array $item, array $plan, string $password, Carbon $now, bool $hasLegacyPivot): array
    {
        $n = $item['n'];
        $grade = $item['grade'];
        $section = $item['section'];
        $identity = $this->identityFor($n, $grade);

        $user = new User([
            'name' => $identity['first'].' '.$identity['last'],
            'email' => self::email($n),
            'password' => $password,
            'role_name' => User::ROLE_STUDENT,
            'status' => 'active',
            'join_date' => $now->format('Y-m-d'),
            'date_of_birth' => $identity['dob'],
            'phone_number' => $identity['phone'],
            'position' => 'Student',
            'department' => 'Student Affairs',
            'avatar' => 'default-avatar.png',
        ]);
        $user->email_verified_at = $now;
        $user->save();
        $user->assignRole(User::ROLE_STUDENT);

        $student = Student::create([
            'user_id' => $user->user_id,
            'first_name' => $identity['first'],
            'middle_name' => $identity['middle'],
            'last_name' => $identity['last'],
            'gender' => $identity['gender'],
            'date_of_birth' => $identity['dob'],
            'roll' => self::admissionId($n),
            'blood_group' => $identity['blood_group'],
            'religion' => $identity['religion'],
            'email' => self::email($n),
            'parent_name' => $identity['parent_name'],
            'parent_phone' => $identity['parent_phone'],
            'parent_relationship' => $identity['parent_relationship'],
            'emergency_contact_name' => $identity['parent_name'],
            'emergency_contact_phone' => $identity['parent_phone'],
            'address' => $identity['address'],
            'previous_school' => $identity['previous_school'],
            'enrollment_status' => 'active',
            'class' => $grade,
            'year_level' => $grade,
            'section' => $section->name,
            'admission_id' => self::admissionId($n),
            'phone_number' => $identity['phone'],
        ]);

        DB::table('student_section_assignments')->insert([
            'student_id' => $student->id,
            'section_id' => $section->id,
            'academic_year_id' => $plan['academic_year']->id,
            'semester_id' => $plan['semester']->id,
            'assigned_date' => $now,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        if ($hasLegacyPivot) {
            DB::table('section_student')->insert([
                'section_id' => $section->id,
                'student_id' => $student->id,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        return ['n' => $n, 'grade' => $grade, 'section' => $section, 'student' => $student, 'user' => $user];
    }

    /**
     * Quarterly scores per subject (integers, DepEd-style).
     * Low performers: every subject below 75 and at least three below 70 so the
     * existing low-grade and at-risk rules in StudentPerformanceService apply.
     * High performers: varied 84–98 around a personal base.
     *
     * @return list<array{0:int,1:int,2:int,3:int}>
     */
    protected function scoresFor(int $subjectCount, bool $isLow): array
    {
        $scores = [];

        if ($isLow) {
            $atRiskSubjects = [];
            $indexes = range(0, $subjectCount - 1);
            shuffle($indexes);
            foreach (array_slice($indexes, 0, min(StudentPerformanceService::AT_RISK_SUBJECT_COUNT, $subjectCount)) as $i) {
                $atRiskSubjects[$i] = true;
            }

            for ($i = 0; $i < $subjectCount; $i++) {
                [$base, $min, $max] = isset($atRiskSubjects[$i]) ? [mt_rand(68, 69), 67, 69] : [mt_rand(71, 74), 70, 74];
                $scores[] = array_map(fn () => max($min, min($max, $base + mt_rand(-1, 1))), range(1, 4));
            }

            return $scores;
        }

        $personalBase = mt_rand(86, 95);
        for ($i = 0; $i < $subjectCount; $i++) {
            $subjectBase = $personalBase + mt_rand(-4, 4);
            $scores[] = array_map(fn () => max(84, min(98, $subjectBase + mt_rand(-2, 2))), range(1, 4));
        }

        return $scores;
    }

    protected function attendanceStatus(bool $isLow): string
    {
        $roll = mt_rand(1, 1000);
        [$present, $late, $excused] = $isLow ? [780, 860, 890] : [930, 965, 985];

        return match (true) {
            $roll <= $present => 'present',
            $roll <= $late => 'late',
            $roll <= $excused => 'excused',
            default => 'absent',
        };
    }

    protected function createAlertsForLowPerformers(array $created, array $plan): int
    {
        $academicYearId = $plan['academic_year']->id;
        $semesterId = $plan['semester']->id;
        $quarterFields = $this->performance->quarterFieldsForSemester($academicYearId, $semesterId);
        $count = 0;

        foreach ($created as $row) {
            if (! in_array($row['n'], $plan['low_performers'], true)) {
                continue;
            }

            $grades = QuarterlyGrade::with('subject')
                ->where('student_id', $row['student']->id)
                ->where('academic_year_id', $academicYearId)
                ->get();

            $atRiskScores = [];
            $allScores = [];
            foreach ($grades as $grade) {
                $pct = $this->performance->subjectPercentage($grade, $quarterFields);
                if ($pct === null) {
                    continue;
                }
                $allScores[] = $pct;

                if ($pct < StudentPerformanceService::LOW_GRADE_THRESHOLD) {
                    GradeAlert::create([
                        'student_id' => $row['student']->id,
                        'subject_id' => $grade->subject_id,
                        'alert_type' => GradeAlert::TYPE_LOW_GRADE,
                        'message' => 'Low grade in '.optional($grade->subject)->subject_name.': '.number_format($pct, 2).'%',
                        'threshold_value' => StudentPerformanceService::LOW_GRADE_THRESHOLD,
                        'current_value' => $pct,
                        'is_resolved' => false,
                        'academic_year_id' => $academicYearId,
                        'semester_id' => $semesterId,
                    ]);
                    $count++;
                }
                if ($pct < StudentPerformanceService::AT_RISK_THRESHOLD) {
                    $atRiskScores[] = $pct;
                }
            }

            if (count($atRiskScores) >= StudentPerformanceService::AT_RISK_SUBJECT_COUNT) {
                $avg = round(array_sum($atRiskScores) / count($atRiskScores), 2);
                $message = 'At-risk: low grades in '.count($atRiskScores).' subjects (avg of low scores: '.$avg.'%)';
                $threshold = StudentPerformanceService::AT_RISK_THRESHOLD;
            } elseif (! empty($allScores)) {
                $avg = round(array_sum($allScores) / count($allScores), 2);
                $message = $row['student']->last_name.', '.$row['student']->first_name.' is below passing average ('.number_format($avg, 2).'%). Needs attention.';
                $threshold = StudentPerformanceService::LOW_GRADE_THRESHOLD;
            } else {
                continue;
            }

            GradeAlert::create([
                'student_id' => $row['student']->id,
                'subject_id' => null,
                'alert_type' => GradeAlert::TYPE_AT_RISK,
                'message' => $message,
                'threshold_value' => $threshold,
                'current_value' => $avg,
                'is_resolved' => false,
                'academic_year_id' => $academicYearId,
                'semester_id' => $semesterId,
            ]);
            $count++;
        }

        return $count;
    }

    /**
     * @return Collection<int, array{admission_id:string, name:string, email:string, grade:string, section:string, general_average:float, min_subject:float}>
     */
    protected function generalAverages(Collection $students, Collection $quarterly, ?AcademicYear $academicYear, ?Semester $semester): Collection
    {
        $quarterFields = $academicYear && $semester
            ? $this->performance->quarterFieldsForSemester($academicYear->id, $semester->id)
            : ['quarter_1', 'quarter_2', 'quarter_3', 'quarter_4'];
        $byStudent = $quarterly->groupBy('student_id');

        return $students->mapWithKeys(function (Student $student) use ($byStudent, $quarterFields) {
            $scores = $byStudent->get($student->id, collect())
                ->map(fn ($row) => $this->performance->subjectPercentage($row, $quarterFields))
                ->filter(fn ($pct) => $pct !== null);

            return [$student->id => [
                'admission_id' => $student->admission_id,
                'name' => trim($student->first_name.' '.$student->last_name),
                'email' => $student->email,
                'grade' => $student->class,
                'section' => $student->section,
                'general_average' => $scores->isEmpty() ? 0.0 : round($scores->avg(), 2),
                'min_subject' => $scores->isEmpty() ? 0.0 : (float) $scores->min(),
            ]];
        });
    }

    /**
     * Same period resolution the app uses by default: the academic year covering
     * today (else the newest), and that year's first semester (else the newest).
     *
     * @return array{0:AcademicYear, 1:Semester}
     */
    protected function resolveAcademicPeriod(?int $academicYearId, ?int $semesterId): array
    {
        if ($academicYearId) {
            $academicYear = AcademicYear::find($academicYearId);
            if (! $academicYear) {
                throw new RuntimeException("Academic year #{$academicYearId} does not exist.");
            }
        } else {
            $academicYear = AcademicYear::current();
        }

        if (! $academicYear) {
            throw new RuntimeException('No academic year exists. Create one in the admin panel first; none will be created.');
        }

        if ($semesterId) {
            $semester = Semester::where('academic_year_id', $academicYear->id)->find($semesterId);
            if (! $semester) {
                throw new RuntimeException("Semester #{$semesterId} does not belong to academic year {$academicYear->name}.");
            }
        } else {
            $semester = Semester::where('academic_year_id', $academicYear->id)->orderBy('id')->first()
                ?? Semester::query()->latest('id')->first();
        }

        if (! $semester) {
            throw new RuntimeException('No semester exists. Create one in the admin panel first; none will be created.');
        }

        return [$academicYear, $semester];
    }

    /**
     * Existing sections grouped by the subject grade label (subjects.class) they map to.
     *
     * @return Collection<string, array{grade:string, sections:Collection, subjects:Collection}>
     */
    protected function resolveGradeGroups(): Collection
    {
        $groups = [];
        $subjectsByLabel = [];

        foreach (Section::query()->orderBy('name')->orderBy('id')->get() as $section) {
            foreach (GradeSubjectCatalogService::gradeAliases($section->grade_level) as $alias) {
                $subjects = $subjectsByLabel[$alias] ??= $this->catalog->subjectsForGrade($alias);
                if ($subjects->isNotEmpty()) {
                    $groups[$alias] ??= ['grade' => $alias, 'sections' => collect(), 'subjects' => $subjects];
                    $groups[$alias]['sections']->push($section);
                    break;
                }
            }
        }

        return collect($groups)->sortBy(fn ($group) => sprintf('%03d-%s', $this->gradeOrder($group['grade']), $group['grade']));
    }

    protected function skippedSections(Collection $gradeGroups): Collection
    {
        $used = $gradeGroups->flatMap(fn ($g) => $g['sections']->pluck('id'))->all();

        return Section::query()->whereNotIn('id', $used)->orderBy('id')->get();
    }

    /**
     * Teacher for every (section, subject) pair, preferring the most specific
     * existing assignment: active class schedule → subject teacher also assigned
     * to the section → subject teacher handling the grade level → any subject
     * teacher → section adviser → any section teacher.
     */
    protected function resolveTeacherMap(Collection $gradeGroups): array
    {
        $sectionIds = $gradeGroups->flatMap(fn ($g) => $g['sections']->pluck('id'))->all();
        $existingTeachers = Teacher::query()->pluck('full_name', 'id');

        $schedules = ClassSchedule::query()
            ->where('is_active', true)
            ->whereIn('section_id', $sectionIds)
            ->orderByDesc('id')
            ->get(['section_id', 'subject_id', 'teacher_id'])
            ->groupBy(fn ($row) => $row->section_id.':'.$row->subject_id);
        $subjectTeachers = DB::table('subject_teacher')->orderBy('teacher_id')->get()
            ->groupBy('subject_id')->map(fn ($rows) => $rows->pluck('teacher_id')->map(fn ($id) => (int) $id)->unique()->values()->all());
        $sectionTeachers = DB::table('section_teacher')->whereIn('section_id', $sectionIds)->orderBy('teacher_id')->get()
            ->groupBy('section_id')->map(fn ($rows) => $rows->pluck('teacher_id')->map(fn ($id) => (int) $id)->unique()->values()->all());
        $gradeTeachers = Schema::hasTable('teacher_grade_level')
            ? DB::table('teacher_grade_level')->get()->groupBy('teacher_id')->map(fn ($rows) => $rows->pluck('grade_level')->all())
            : collect();

        $map = [];
        $missing = [];
        $fallbacks = [];

        foreach ($gradeGroups as $group) {
            $aliases = GradeSubjectCatalogService::gradeAliases($group['grade']);
            foreach ($group['sections'] as $section) {
                foreach ($group['subjects'] as $subject) {
                    $valid = fn ($id) => $id && $existingTeachers->has((int) $id);
                    $bySubject = array_values(array_filter($subjectTeachers->get($subject->id, []), $valid));
                    $bySection = array_values(array_filter($sectionTeachers->get($section->id, []), $valid));
                    $candidates = [];

                    $scheduled = $schedules->get($section->id.':'.$subject->id, collect())->pluck('teacher_id')->first(fn ($id) => $valid($id));
                    if ($scheduled) {
                        $candidates = [(int) $scheduled, 'class schedule'];
                    } elseif ($both = array_values(array_intersect($bySubject, $bySection))) {
                        $candidates = [$both[$section->id % count($both)], 'subject + section teacher'];
                    } elseif ($byGrade = array_values(array_filter($bySubject, fn ($id) => count(array_intersect($aliases, $gradeTeachers->get($id, []))) > 0))) {
                        $candidates = [$byGrade[$section->id % count($byGrade)], 'subject teacher for grade'];
                    } elseif ($bySubject) {
                        $candidates = [$bySubject[$section->id % count($bySubject)], 'subject teacher'];
                    } elseif ($valid($section->adviser_id)) {
                        $candidates = [(int) $section->adviser_id, 'section adviser'];
                    } elseif ($bySection) {
                        $candidates = [$bySection[0], 'section teacher'];
                    }

                    if (empty($candidates)) {
                        $missing[$section->id][] = "{$group['grade']} / {$section->name} / {$subject->subject_name}";

                        continue;
                    }

                    [$teacherId, $source] = $candidates;
                    $map[$section->id][$subject->id] = [
                        'teacher_id' => $teacherId,
                        'teacher_name' => $existingTeachers->get($teacherId),
                        'source' => $source,
                    ];

                    if (in_array($source, ['section adviser', 'section teacher'], true)) {
                        $fallbacks[$section->id][] = "{$subject->subject_name} ({$group['grade']}, {$section->name}) has no subject teacher; using {$source} {$existingTeachers->get($teacherId)}.";
                    }
                }
            }
        }

        return ['map' => $map, 'fallbacks' => $fallbacks, 'missing' => $missing];
    }

    /**
     * @return array<int,int> section_id => students already assigned in the period
     */
    protected function sectionOccupancy(array $sectionIds, int $academicYearId, int $semesterId): array
    {
        return DB::table('student_section_assignments')
            ->whereIn('section_id', $sectionIds)
            ->where('academic_year_id', $academicYearId)
            ->where('semester_id', $semesterId)
            ->selectRaw('section_id, COUNT(DISTINCT student_id) as total')
            ->groupBy('section_id')
            ->pluck('total', 'section_id')
            ->map(fn ($v) => (int) $v)
            ->all();
    }

    /**
     * Spread students so every section ends up with a similar fill ratio of its
     * capacity (existing students included), then number them grade by grade.
     */
    protected function allocate(Collection $gradeGroups, array $occupancy): array
    {
        $sections = [];
        foreach ($gradeGroups as $group) {
            foreach ($group['sections'] as $section) {
                $sections[] = ['grade' => $group['grade'], 'section' => $section];
            }
        }

        $counts = array_fill_keys(array_map(fn ($s) => $s['section']->id, $sections), 0);
        for ($i = 0; $i < self::TOTAL_STUDENTS; $i++) {
            $best = null;
            $bestScore = null;
            foreach ($sections as $entry) {
                $id = $entry['section']->id;
                $capacity = max(1, (int) ($entry['section']->capacity ?? self::DEFAULT_SECTION_CAPACITY));
                $score = [(($occupancy[$id] ?? 0) + $counts[$id] + 1) / $capacity, $counts[$id], $id];
                if ($bestScore === null || $score < $bestScore) {
                    $best = $id;
                    $bestScore = $score;
                }
            }
            $counts[$best]++;
        }

        $students = [];
        $n = 1;
        foreach ($sections as $entry) {
            for ($i = 0; $i < $counts[$entry['section']->id]; $i++) {
                $students[] = ['n' => $n++, 'grade' => $entry['grade'], 'section' => $entry['section']];
            }
        }

        return ['students' => $students, 'counts' => array_filter($counts)];
    }

    /**
     * Ten evenly spaced student numbers so low performers span several grades.
     *
     * @return list<int>
     */
    protected function lowPerformerNumbers(): array
    {
        $step = self::TOTAL_STUDENTS / self::LOW_PERFORMER_COUNT;

        return array_map(
            fn ($k) => (int) floor(($k + 0.5) * $step) + 1,
            range(0, self::LOW_PERFORMER_COUNT - 1)
        );
    }

    /**
     * Most recent school days (Mon–Fri) inside the academic year, up to today.
     *
     * @return list<string>
     */
    protected function attendanceDates(AcademicYear $academicYear, int $days): array
    {
        if ($days === 0) {
            return [];
        }

        $start = Carbon::parse($academicYear->start_date)->startOfDay();
        $end = Carbon::parse($academicYear->end_date)->startOfDay();
        $cursor = now()->startOfDay()->min($end);
        $dates = [];

        while ($cursor->gte($start) && count($dates) < $days) {
            if ($cursor->isWeekday()) {
                $dates[] = $cursor->toDateString();
            }
            $cursor = $cursor->copy()->subDay();
        }

        $cursor = $start->copy();
        while (count($dates) < $days && $cursor->lte($end)) {
            if ($cursor->isWeekday() && ! in_array($cursor->toDateString(), $dates, true)) {
                $dates[] = $cursor->toDateString();
            }
            $cursor->addDay();
        }

        sort($dates);

        return $dates;
    }

    protected function identityFor(int $n, string $grade): array
    {
        $male = ['Adrian', 'Benjamin', 'Carlo', 'Daniel', 'Elijah', 'Francis', 'Gabriel', 'Harold', 'Ivan', 'Joshua', 'Kenneth', 'Lorenzo', 'Miguel', 'Nathan', 'Oliver', 'Paulo', 'Rafael', 'Samuel', 'Tristan', 'Vincent', 'Xavier', 'Zachary', 'Aaron', 'Bryan', 'Christian'];
        $female = ['Andrea', 'Bea', 'Clarisse', 'Danica', 'Ella', 'Faith', 'Gwen', 'Hazel', 'Irish', 'Jasmine', 'Kyla', 'Leah', 'Mika', 'Nicole', 'Pia', 'Queenie', 'Rhea', 'Samantha', 'Trisha', 'Vanessa', 'Ysabel', 'Zia', 'Alyssa', 'Bianca', 'Carmela'];
        $last = ['Abad', 'Bautista', 'Castro', 'Dela Cruz', 'Estrada', 'Francisco', 'Gomez', 'Hernandez', 'Ilagan', 'Jimenez', 'Lacson', 'Manalo', 'Navarro', 'Ocampo', 'Pascual', 'Quiambao', 'Rivera', 'Salazar', 'Tolentino', 'Umali', 'Valdez', 'Yap', 'Zamora', 'Aguilar', 'Buenaventura', 'Cabrera', 'Domingo', 'Enriquez', 'Fajardo', 'Galang', 'Lim', 'Mercado', 'Panganiban', 'Roxas', 'Soriano', 'Tan', 'Velasco'];
        $middle = ['Alonzo', 'Bernal', 'Cortez', 'Diaz', 'Esguerra', 'Flores', 'Guzman', 'Lopez', 'Medina', 'Perez', 'Reyes', 'Santos', 'Torres', 'Villanueva'];
        $parentFirst = ['Roberto', 'Maricel', 'Eduardo', 'Josephine', 'Ramon', 'Lorna', 'Arnel', 'Cristina', 'Dennis', 'Rowena', 'Ferdinand', 'Marites'];
        $barangays = ['Brgy. San Isidro', 'Brgy. Malinis', 'Brgy. Santo Niño', 'Brgy. Poblacion', 'Brgy. Bagong Silang', 'Brgy. Maligaya'];
        $religions = ['Roman Catholic', 'Roman Catholic', 'Christian', 'Iglesia ni Cristo', 'Born Again Christian', 'Seventh-day Adventist'];
        $bloodGroups = ['O+', 'A+', 'B+', 'AB+', 'O-', 'A-'];

        $isFemale = mt_rand(0, 1) === 1;
        $first = $isFemale ? $female[mt_rand(0, count($female) - 1)] : $male[mt_rand(0, count($male) - 1)];
        $lastName = $last[mt_rand(0, count($last) - 1)];
        $parentIndex = mt_rand(0, count($parentFirst) - 1);
        $gradeIndex = array_search($grade, GradeSubjectCatalogService::gradeLevels(), true);
        $birthYear = (int) now()->format('Y') - 4 - ($gradeIndex === false ? 6 : $gradeIndex) - ((int) now()->format('m') < 6 ? 1 : 0);

        return [
            'first' => $first,
            'middle' => $middle[mt_rand(0, count($middle) - 1)],
            'last' => $lastName,
            'gender' => $isFemale ? 'Female' : 'Male',
            'dob' => Carbon::create($birthYear, mt_rand(1, 12), mt_rand(1, 28))->toDateString(),
            'phone' => sprintf('0999%07d', $n),
            'blood_group' => $bloodGroups[mt_rand(0, count($bloodGroups) - 1)],
            'religion' => $religions[mt_rand(0, count($religions) - 1)],
            'parent_name' => $parentFirst[$parentIndex].' '.$lastName,
            'parent_phone' => sprintf('0998%07d', $n),
            'parent_relationship' => $parentIndex % 2 === 0 ? 'Father' : 'Mother',
            'address' => sprintf('Blk %d Lot %d, Dummy Homes Subdivision, %s, City of Santa Rosa, Laguna 4026', mt_rand(1, 30), mt_rand(1, 40), $barangays[mt_rand(0, count($barangays) - 1)]),
            'previous_school' => $gradeIndex !== false && $gradeIndex > 1 ? 'Dummy Elementary Learning Center' : 'N/A',
        ];
    }

    protected function gradeDistribution(array $students): array
    {
        $distribution = [];
        foreach ($students as $student) {
            $distribution[$student['grade']] = ($distribution[$student['grade']] ?? 0) + 1;
        }

        return $distribution;
    }

    protected function sectionDistributionRows(array $plan): array
    {
        $rows = [];
        foreach ($plan['grade_groups'] as $group) {
            foreach ($group['sections'] as $section) {
                $dummy = $plan['section_counts'][$section->id] ?? 0;
                if ($dummy === 0) {
                    continue;
                }
                $existing = $plan['occupancy'][$section->id] ?? 0;
                $rows[] = [
                    'section_id' => $section->id,
                    'section' => $section->name,
                    'grade' => $group['grade'],
                    'capacity' => $section->capacity ?? self::DEFAULT_SECTION_CAPACITY,
                    'existing_students' => $existing,
                    'dummy_students' => $dummy,
                    'total' => $existing + $dummy,
                ];
            }
        }

        return $rows;
    }

    protected function teacherAssignmentRows(array $plan): array
    {
        $rows = [];
        foreach ($plan['grade_groups'] as $group) {
            foreach ($group['subjects'] as $subject) {
                $teachers = [];
                foreach ($group['sections'] as $section) {
                    if (empty($plan['section_counts'][$section->id])) {
                        continue;
                    }
                    $entry = $plan['teacher_map'][$section->id][$subject->id];
                    $teachers[] = $section->name.': '.$entry['teacher_name'].' (#'.$entry['teacher_id'].', '.$entry['source'].')';
                }
                $rows[] = [
                    'grade' => $group['grade'],
                    'subject' => $subject->subject_name.' (#'.$subject->id.')',
                    'teachers' => implode('; ', $teachers),
                ];
            }
        }

        return $rows;
    }

    protected function orphanCounts(array $studentIds): array
    {
        $count = fn (string $table, string $column, string $parent) => DB::table($table)
            ->leftJoin($parent, "{$parent}.id", '=', "{$table}.{$column}")
            ->whereIn("{$table}.student_id", $studentIds)
            ->whereNotNull("{$table}.{$column}")
            ->whereNull("{$parent}.id")
            ->count();

        return [
            'enrollments.subject' => $count('enrollments', 'subject_id', 'subjects'),
            'enrollments.academic_year' => $count('enrollments', 'academic_year_id', 'academic_years'),
            'enrollments.semester' => $count('enrollments', 'semester_id', 'semesters'),
            'quarterly_grades.subject' => $count('quarterly_grades', 'subject_id', 'subjects'),
            'quarterly_grades.teacher' => $count('quarterly_grades', 'teacher_id', 'teachers'),
            'grades.teacher' => $count('grades', 'teacher_id', 'teachers'),
            'grades.component' => $count('grades', 'component_id', 'subject_components'),
            'attendances.subject' => $count('attendances', 'subject_id', 'subjects'),
            'attendances.teacher' => $count('attendances', 'teacher_id', 'teachers'),
            'student_section_assignments.section' => $count('student_section_assignments', 'section_id', 'sections'),
            'section_student.section' => Schema::hasTable('section_student') ? $count('section_student', 'section_id', 'sections') : 0,
        ];
    }

    public function assertNoExistingDummyData(): void
    {
        $students = $this->dummyStudentsQuery()->count();
        $users = $this->dummyUsersQuery()->count();
        $applications = count($this->dummyApplicationIds());

        if ($students > 0 || $users > 0 || $applications > 0) {
            throw new RuntimeException("Dummy data already exists ({$students} students, {$users} users, {$applications} applications). Nothing was changed. Run `php artisan dummy:students:delete` first if you want to regenerate.");
        }
    }

    protected function assertNoCollisions(): void
    {
        $admissionIds = array_map([self::class, 'admissionId'], range(1, self::TOTAL_STUDENTS));
        $emails = array_map([self::class, 'email'], range(1, self::TOTAL_STUDENTS));

        $studentClash = Student::withTrashed()
            ->where(fn ($q) => $q->whereIn('admission_id', $admissionIds)->orWhereIn('email', $emails)->orWhereIn('roll', $admissionIds))
            ->count();
        $userClash = User::whereIn('email', $emails)->count();
        $applicationClash = $this->applicationCollisions();

        if ($studentClash > 0 || $userClash > 0 || $applicationClash > 0) {
            throw new RuntimeException("Planned dummy IDs/emails collide with {$studentClash} existing student(s), {$userClash} user(s) and {$applicationClash} enrollment application(s). Nothing was changed.");
        }
    }

    protected function applicationCollisions(): int
    {
        if (! Schema::hasTable('enrollment_applications')) {
            return 0;
        }

        return EnrollmentApplication::withTrashed()
            ->whereIn('application_number', array_map([self::class, 'applicationNumber'], range(1, self::totalApplications())))
            ->count();
    }

    /**
     * @return list<int>
     */
    protected function dummyApplicationIds(): array
    {
        if (! Schema::hasTable('enrollment_applications')) {
            return [];
        }

        return $this->dummyApplicationsQuery()->pluck('id')->map(fn ($id) => (int) $id)->all();
    }

    /**
     * Add the dummy enrollment applications for an already generated set of
     * dummy students (sets created before applications were part of the generator).
     */
    public function addApplications(int $seed = self::DEFAULT_SEED): array
    {
        if (! Schema::hasTable('enrollment_applications')) {
            throw new RuntimeException('The enrollment_applications table does not exist. Nothing was changed.');
        }
        if (($existing = count($this->dummyApplicationIds())) > 0) {
            throw new RuntimeException("{$existing} dummy enrollment applications already exist. Nothing was changed.");
        }
        $students = $this->dummyStudentsQuery()->whereNull('deleted_at')->orderBy('admission_id')->get();
        if ($students->count() !== self::TOTAL_STUDENTS) {
            throw new RuntimeException('Expected '.self::TOTAL_STUDENTS." dummy students, found {$students->count()}. Run php artisan dummy:students:create first. Nothing was changed.");
        }
        if (($clash = $this->applicationCollisions()) > 0) {
            throw new RuntimeException("Planned dummy application numbers collide with {$clash} existing application(s). Nothing was changed.");
        }

        $previousLog = config('activitylog.enabled');
        config(['activitylog.enabled' => false]);

        try {
            return DB::transaction(fn () => $this->createApplications($students, $seed));
        } finally {
            config(['activitylog.enabled' => $previousLog]);
        }
    }

    /**
     * One approved application per dummy student (linked through
     * students.enrollment_application_id, like the registrar approval flow),
     * plus extra dummy applicants in the other registrar statuses.
     *
     * @return array<string,int> status => rows created
     */
    protected function createApplications(Collection $students, int $seed): array
    {
        mt_srand($seed + 1);
        $now = now();
        $columns = array_flip(Schema::getColumnListing('enrollment_applications'));
        $sectionIds = DB::table('student_section_assignments')
            ->whereIn('student_id', $students->pluck('id'))
            ->orderByDesc('id')
            ->get(['student_id', 'section_id'])
            ->unique('student_id')
            ->pluck('section_id', 'student_id');

        $rows = [];
        foreach ($students as $student) {
            $n = (int) substr($student->admission_id, strlen(self::ADMISSION_PREFIX));
            $rows[] = $this->applicationRow($n, 'approved', [
                'first' => $student->first_name,
                'middle' => $student->middle_name,
                'last' => $student->last_name,
                'gender' => $student->gender,
                'dob' => Carbon::parse($student->date_of_birth)->toDateString(),
                'phone' => $student->phone_number,
                'address' => $student->address,
                'parent_name' => $student->parent_name,
                'parent_phone' => $student->parent_phone,
                'parent_relationship' => $student->parent_relationship,
                'religion' => $student->religion,
                'previous_school' => $student->previous_school,
            ], $student->email, $student->class, $sectionIds[$student->id] ?? null, $now);
        }

        $grades = $students->pluck('class')->unique()->values()->all();
        $n = self::TOTAL_STUDENTS;
        foreach (self::EXTRA_APPLICATION_STATUSES as $status => $count) {
            for ($i = 0; $i < $count; $i++) {
                $n++;
                $grade = $grades[$n % count($grades)];
                $email = sprintf('%s%04d@%s', self::APPLICATION_EMAIL_PREFIX, $n, self::EMAIL_DOMAIN);
                $rows[] = $this->applicationRow($n, $status, $this->identityFor($n, $grade), $email, $grade, null, $now);
            }
        }

        $rows = array_map(fn ($row) => array_intersect_key($row, $columns), $rows);
        foreach (array_chunk($rows, 200) as $chunk) {
            DB::table('enrollment_applications')->insert($chunk);
        }

        if (Schema::hasColumn('students', 'enrollment_application_id')) {
            $applicationIds = $this->dummyApplicationsQuery()->where('status', 'approved')->pluck('id', 'email');
            foreach ($students as $student) {
                if ($id = $applicationIds[$student->email] ?? null) {
                    DB::table('students')->where('id', $student->id)->update(['enrollment_application_id' => $id]);
                }
            }
        }

        return collect($rows)->countBy('status')->all();
    }

    protected function applicationRow(int $n, string $status, array $identity, string $email, string $grade, ?int $sectionId, Carbon $now): array
    {
        $submitted = $now->copy()->subDays(mt_rand(7, 75))->setTime(mt_rand(8, 16), mt_rand(0, 59));
        $reviewed = $status === 'pending' ? null : $submitted->copy()->addDays(mt_rand(1, 5));
        $missingDocs = $status === 'needs_documents';
        [$parentFirst, $parentLast] = array_pad(explode(' ', (string) $identity['parent_name'], 2), 2, $identity['last']);
        $isFather = $identity['parent_relationship'] === 'Father';

        return [
            'application_number' => self::applicationNumber($n),
            'enrollment_type' => 'parent',
            'student_category' => 'new_student',
            'first_name' => $identity['first'],
            'middle_name' => $identity['middle'],
            'last_name' => $identity['last'],
            'date_of_birth' => $identity['dob'],
            'age_years' => Carbon::parse($identity['dob'])->age,
            'gender' => $identity['gender'],
            'email' => $email,
            'phone_number' => $identity['phone'],
            'address' => $identity['address'],
            'religion' => $identity['religion'],
            'citizenship' => 'Filipino',
            'previous_school' => $identity['previous_school'],
            'parent_name' => $identity['parent_name'],
            'parent_phone' => $identity['parent_phone'],
            'parent_email' => sprintf('dummy.parent.%04d@%s', $n, self::EMAIL_DOMAIN),
            'parent_relationship' => $identity['parent_relationship'],
            'father_first_name' => $isFather ? $parentFirst : null,
            'father_last_name' => $isFather ? $parentLast : null,
            'mother_first_name' => $isFather ? null : $parentFirst,
            'mother_last_name' => $isFather ? null : $parentLast,
            'emergency_contact_name' => $identity['parent_name'],
            'emergency_contact_phone' => $identity['parent_phone'],
            'grade_level_applying_for' => $grade,
            'preferred_section_id' => $sectionId,
            'doc_submitted_form138' => ! $missingDocs,
            'doc_submitted_psa_birth' => ! $missingDocs || $n % 2 === 0,
            'doc_submitted_pic_1x1' => true,
            'doc_submitted_pic_2x2' => true,
            'status' => $status,
            'notes' => match ($status) {
                'needs_documents' => 'DUMMY-2026 test data. Missing Form 138'.($n % 2 === 0 ? '.' : ' and PSA birth certificate.'),
                default => 'DUMMY-2026 test data.',
            },
            'rejection_reason' => $status === 'rejected' ? 'Requirements not completed within the enrollment period (dummy data).' : null,
            'reviewed_at' => $reviewed,
            'date_enrolled' => $status === 'approved' ? $reviewed->toDateString() : null,
            'created_at' => $submitted,
            'updated_at' => $reviewed ?? $submitted,
        ];
    }

    /**
     * @return array{0: list<int>, 1: list<int>}
     */
    protected function dummyIds(): array
    {
        $users = $this->dummyUsersQuery()->get(['id', 'user_id']);
        $userKeys = $users->pluck('user_id')->filter()->all();

        $studentIds = Student::withTrashed()
            ->where('admission_id', 'like', self::ADMISSION_PREFIX.'%')
            ->where(fn ($q) => $q->where('email', 'like', '%@'.self::EMAIL_DOMAIN)
                ->when(! empty($userKeys), fn ($qq) => $qq->orWhereIn('user_id', $userKeys)))
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        return [$studentIds, $users->pluck('id')->map(fn ($id) => (int) $id)->all()];
    }

    /**
     * @return list<string>
     */
    protected function studentScopedTables(): array
    {
        return [
            'grade_alerts',
            'attendances',
            'grades',
            'quarterly_grades',
            'student_gpa',
            'enrollments',
            'student_section_assignments',
            'section_student',
            'activity_submissions',
            'assignment_submissions',
            'student_observed_values',
            'student_promotions',
            'messages',
        ];
    }

    protected function deleteMorph(string $table, string $morph, array $userIds): void
    {
        if (! Schema::hasTable($table)) {
            return;
        }

        DB::table($table)
            ->where("{$morph}_type", User::class)
            ->whereIn("{$morph}_id", $userIds)
            ->delete();
    }

    protected function gradeOrder(string $grade): int
    {
        $index = array_search($grade, GradeSubjectCatalogService::gradeLevels(), true);
        if ($index === false && in_array($grade, ['Kinder'], true)) {
            $index = 1;
        }

        return $index === false ? 99 : $index;
    }

    protected function log(string $message): void
    {
        if ($this->logger) {
            ($this->logger)($message);
        }
    }
}
