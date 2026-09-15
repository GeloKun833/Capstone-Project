<?php

namespace App\Services;

use App\Models\Activity;
use App\Models\ActivitySubmission;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Attendance;
use App\Models\ClassPost;
use App\Models\ClassSchedule;
use App\Models\Enrollment;
use App\Models\Grade;
use App\Models\Lesson;
use App\Models\QuarterlyGrade;
use App\Models\Room;
use App\Models\Section;
use App\Models\Student;
use App\Models\StudentGpa;
use App\Models\Subject;
use App\Models\SubjectComponent;
use App\Models\Teacher;
use App\Models\TeacherGradeLevel;
use App\Models\User;
use App\Support\AcademicThresholds;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

/**
 * Presentation-only dummy data. Identified by @demo.pms.local and DEMO-* IDs.
 * Does not modify existing administrators or non-demo records.
 */
class DemoDataService
{
    public const MARKER = 'DEMO-2026';
    public const EMAIL_DOMAIN = 'demo.pms.local';
    public const PASSWORD = 'Demo@123';
    public const TRACKER_FILE = 'demo-data-ids.json';

    /** @var callable|null */
    protected $logger;

    public function __construct(?callable $logger = null)
    {
        $this->logger = $logger;
    }

    public function log(string $message): void
    {
        if ($this->logger) {
            ($this->logger)($message);
        }
    }

    public static function isDemoEmail(?string $email): bool
    {
        return is_string($email) && str_ends_with(strtolower($email), '@'.self::EMAIL_DOMAIN);
    }

    public function seed(): array
    {
        $previousLog = config('activitylog.enabled');
        config(['activitylog.enabled' => false]);

        try {
            $this->cleanup();

            return DB::transaction(function () {
                $context = $this->resolveContext();
                $teachers = $this->createTeachers($context);
                $this->assignTeachers($teachers, $context);
                $parents = $this->createParents($context);
                $students = $this->createStudents($context, $parents);
                $this->populateAcademics($students, $teachers, $context);
                $this->createTeacherContent($teachers, $students, $context);

                $summary = $this->summarize($students, $teachers, $parents, $context);
                $this->writeTracker($summary);

                return $summary;
            });
        } finally {
            config(['activitylog.enabled' => $previousLog]);
        }
    }

    public function cleanup(): array
    {
        $previousLog = config('activitylog.enabled');
        config(['activitylog.enabled' => false]);

        try {
            $tracker = $this->readTracker();

            $userIds = User::query()
                ->where('email', 'like', '%@'.self::EMAIL_DOMAIN)
                ->pluck('id')
                ->all();

            $userKeys = User::query()
                ->where('email', 'like', '%@'.self::EMAIL_DOMAIN)
                ->pluck('user_id')
                ->all();

            $studentIds = Student::withTrashed()
                ->where(function ($q) use ($userKeys) {
                    $q->where('admission_id', 'like', 'DEMO-STU-%')
                        ->orWhere('email', 'like', '%@'.self::EMAIL_DOMAIN)
                        ->orWhere('parent_email', 'like', '%@'.self::EMAIL_DOMAIN);
                    if (!empty($userKeys)) {
                        $q->orWhereIn('user_id', $userKeys);
                    }
                })
                ->pluck('id')
                ->all();

            $teacherIds = Teacher::query()
                ->when(!empty($userKeys), fn ($q) => $q->whereIn('user_id', $userKeys))
                ->when(empty($userKeys), fn ($q) => $q->where('experience', 'like', '%'.self::MARKER.'%'))
                ->pluck('id')
                ->all();

            if (empty($userIds) && empty($studentIds) && empty($teacherIds)) {
                $this->forgetTracker();
                $this->log('No demo records found.');
                return ['users' => 0, 'students' => 0, 'teachers' => 0, 'parents' => 0];
            }

            $this->log('Removing existing DEMO-2026 records...');

            if (!empty($teacherIds)) {
                Section::whereIn('adviser_id', $teacherIds)->update(['adviser_id' => null]);
            }

            $lessonIds = [];
            if (!empty($teacherIds)) {
                $lessonIds = Lesson::whereIn('teacher_id', $teacherIds)->pluck('id')->all();
            }

            $activityIds = [];
            if (!empty($lessonIds)) {
                $activityIds = Activity::whereIn('lesson_id', $lessonIds)->pluck('id')->all();
            }

            $assignmentIds = [];
            if (!empty($teacherIds)) {
                $assignmentIds = Assignment::withTrashed()->whereIn('teacher_id', $teacherIds)->pluck('id')->all();
            }

            if (!empty($activityIds)) {
                ActivitySubmission::whereIn('activity_id', $activityIds)->delete();
            }
            if (!empty($studentIds)) {
                ActivitySubmission::whereIn('student_id', $studentIds)->delete();
            }
            if (!empty($activityIds)) {
                Activity::whereIn('id', $activityIds)->delete();
            }
            if (!empty($lessonIds)) {
                Lesson::whereIn('id', $lessonIds)->delete();
            }

            if (!empty($assignmentIds)) {
                AssignmentSubmission::withTrashed()->whereIn('assignment_id', $assignmentIds)->forceDelete();
                Assignment::withTrashed()->whereIn('id', $assignmentIds)->forceDelete();
            }
            if (!empty($studentIds)) {
                AssignmentSubmission::withTrashed()->whereIn('student_id', $studentIds)->forceDelete();
            }

            if (!empty($teacherIds)) {
                ClassPost::withTrashed()->whereIn('teacher_id', $teacherIds)->forceDelete();
                ClassSchedule::whereIn('teacher_id', $teacherIds)
                    ->where('notes', 'like', '%'.self::MARKER.'%')
                    ->delete();
                DB::table('subject_teacher')->whereIn('teacher_id', $teacherIds)->delete();
                DB::table('section_teacher')->whereIn('teacher_id', $teacherIds)->delete();
                TeacherGradeLevel::whereIn('teacher_id', $teacherIds)->delete();
            }

            $createdSectionSubject = $tracker['section_subject_ids'] ?? [];
            if (!empty($createdSectionSubject)) {
                DB::table('section_subject')->whereIn('id', $createdSectionSubject)->delete();
            }

            if (!empty($studentIds)) {
                Attendance::whereIn('student_id', $studentIds)->delete();
                Grade::whereIn('student_id', $studentIds)->delete();
                QuarterlyGrade::whereIn('student_id', $studentIds)->delete();
                StudentGpa::whereIn('student_id', $studentIds)->delete();
                Enrollment::whereIn('student_id', $studentIds)->delete();
                DB::table('student_section_assignments')->whereIn('student_id', $studentIds)->delete();
                if (Schema::hasTable('section_student')) {
                    DB::table('section_student')->whereIn('student_id', $studentIds)->delete();
                }
                Student::withTrashed()->whereIn('id', $studentIds)->forceDelete();
            }

            if (!empty($teacherIds)) {
                Teacher::whereIn('id', $teacherIds)->delete();
            }

            if (!empty($userIds)) {
                if (Schema::hasTable('model_has_roles')) {
                    DB::table('model_has_roles')->whereIn('model_id', $userIds)->delete();
                }
                if (Schema::hasTable('activity_log')) {
                    DB::table('activity_log')->whereIn('causer_id', $userIds)->delete();
                }
                User::whereIn('id', $userIds)->delete();
            }

            $this->forgetTracker();

            return [
                'users' => count($userIds),
                'students' => count($studentIds),
                'teachers' => count($teacherIds),
                'parents' => 0,
            ];
        } finally {
            config(['activitylog.enabled' => $previousLog]);
        }
    }

    protected function resolveContext(): array
    {
        $academicYear = DB::table('academic_years')->orderByDesc('id')->first();
        if (!$academicYear) {
            throw new \RuntimeException('No academic year found. Create an academic year before seeding demo data.');
        }

        $semester = DB::table('semesters')
            ->where('academic_year_id', $academicYear->id)
            ->orderBy('id')
            ->first();

        if (!$semester) {
            $semester = DB::table('semesters')->orderByDesc('id')->first();
        }

        if (!$semester) {
            throw new \RuntimeException('No semester/quarter found. Create a semester before seeding demo data.');
        }

        $rooms = Room::query()->orderBy('id')->get();
        if ($rooms->isEmpty()) {
            throw new \RuntimeException('No rooms found. Create at least one room before seeding demo data.');
        }

        $sectionsByGrade = Section::query()
            ->orderBy('id')
            ->get()
            ->groupBy('grade_level')
            ->map(fn ($group) => $group->values());

        foreach (array_keys($this->gradeDistribution()) as $grade) {
            if ($sectionsByGrade->get($grade, collect())->isEmpty()) {
                throw new \RuntimeException("No section found for {$grade}. Use existing sections; none were created.");
            }
        }

        return [
            'academic_year' => $academicYear,
            'semester' => $semester,
            'rooms' => $rooms,
            'sections_by_grade' => $sectionsByGrade,
            'password' => Hash::make(self::PASSWORD),
            'now' => now(),
        ];
    }

    protected function gradeDistribution(): array
    {
        return [
            'Nursery' => 15,
            'Kindergarten' => 20,
            'Grade 1' => 10,
            'Grade 2' => 10,
            'Grade 3' => 10,
            'Grade 4' => 10,
            'Grade 5' => 10,
            'Grade 6' => 5,
            'Grade 7' => 5,
            'Grade 8' => 5,
        ];
    }

    protected function teacherBlueprints(): array
    {
        return [
            [
                'code' => '001',
                'first' => 'Michael',
                'middle' => 'James',
                'last' => 'Anderson',
                'gender' => 'Male',
                'dob' => '1988-03-14',
                'phone' => '09175551001',
                'position' => 'Subject Teacher',
                'department' => 'Mathematics',
                'qualification' => 'M.A. Mathematics',
                'specialization' => 'Mathematics',
                'assignments' => [
                    ['grade' => 'Grade 7', 'names' => ['Math', 'Mathematics']],
                    ['grade' => 'Grade 8', 'names' => ['Math', 'Mathematics']],
                ],
            ],
            [
                'code' => '002',
                'first' => 'Sarah',
                'middle' => 'Anne',
                'last' => 'Williams',
                'gender' => 'Female',
                'dob' => '1990-07-22',
                'phone' => '09175551002',
                'position' => 'Subject Teacher',
                'department' => 'English',
                'qualification' => 'B.A. English Language',
                'specialization' => 'English',
                'assignments' => [
                    ['grade' => 'Grade 5', 'names' => ['English', 'English 2']],
                    ['grade' => 'Grade 6', 'names' => ['English 2', 'English']],
                ],
            ],
            [
                'code' => '003',
                'first' => 'Daniel',
                'middle' => 'Luis',
                'last' => 'Carter',
                'gender' => 'Male',
                'dob' => '1986-11-05',
                'phone' => '09175551003',
                'position' => 'Subject Teacher',
                'department' => 'Science',
                'qualification' => 'B.S. Biology',
                'specialization' => 'Science',
                'assignments' => [
                    ['grade' => 'Grade 7', 'names' => ['Science']],
                    ['grade' => 'Grade 8', 'names' => ['Science']],
                ],
            ],
            [
                'code' => '004',
                'first' => 'Emily',
                'middle' => 'Rose',
                'last' => 'Johnson',
                'gender' => 'Female',
                'dob' => '1992-01-18',
                'phone' => '09175551004',
                'position' => 'Subject Teacher',
                'department' => 'Filipino',
                'qualification' => 'B.A. Filipino',
                'specialization' => 'Filipino',
                'assignments' => [
                    ['grade' => 'Grade 3', 'names' => ['Filipino 2', 'Filipino']],
                    ['grade' => 'Grade 4', 'names' => ['Filipino', 'Filipino 2']],
                ],
            ],
            [
                'code' => '005',
                'first' => 'James',
                'middle' => 'Robert',
                'last' => 'Miller',
                'gender' => 'Male',
                'dob' => '1984-09-09',
                'phone' => '09175551005',
                'position' => 'Subject Teacher',
                'department' => 'Araling Panlipunan',
                'qualification' => 'B.A. Social Studies',
                'specialization' => 'Araling Panlipunan',
                'assignments' => [
                    ['grade' => 'Grade 5', 'names' => ['Aralin Panlipunan', 'Araling Panlipunan', 'Makabansa']],
                    ['grade' => 'Grade 6', 'names' => ['Aralin Panlipunan', 'Araling Panlipunan', 'Makabansa']],
                ],
            ],
        ];
    }

    protected function createTeachers(array $context): array
    {
        $this->log('Creating 5 demo teachers...');
        $created = [];

        foreach ($this->teacherBlueprints() as $blueprint) {
            $email = 'demo.tch.'.$blueprint['code'].'@'.self::EMAIL_DOMAIN;
            $fullName = $blueprint['first'].' '.$blueprint['last'];

            $user = User::create([
                'name' => $fullName,
                'email' => $email,
                'password' => $context['password'],
                'role_name' => User::ROLE_TEACHER,
                'status' => 'Active',
                'phone_number' => $blueprint['phone'],
                'date_of_birth' => $blueprint['dob'],
                'join_date' => '2024-06-10',
                'position' => $blueprint['position'],
                'department' => $blueprint['department'],
                'email_verified_at' => $context['now'],
            ]);
            $user->assignRole(User::ROLE_TEACHER);

            $teacher = Teacher::create([
                'user_id' => $user->user_id,
                'full_name' => $fullName,
                'gender' => $blueprint['gender'],
                'date_of_birth' => $blueprint['dob'],
                'qualification' => $blueprint['qualification'].' | '.self::MARKER,
                'experience' => 'DEMO-TCH-'.$blueprint['code'].' | '.$blueprint['specialization'].' | 8 years | '.self::MARKER,
                'phone_number' => $blueprint['phone'],
                'address' => 'Unit '.$blueprint['code'].', DEMO-2026 Faculty Residences, Brgy. Dita',
                'city' => 'City of Santa Rosa',
                'state' => 'Laguna',
                'zip_code' => '4026',
                'country' => 'Philippines',
            ]);

            $created[] = [
                'blueprint' => $blueprint,
                'user' => $user,
                'teacher' => $teacher,
                'pairs' => [],
            ];
        }

        return $created;
    }

    protected function assignTeachers(array &$teachers, array $context): void
    {
        $this->log('Assigning teachers to subjects and sections...');
        $createdSectionSubjectIds = [];
        $roomIndex = 0;
        $days = ['monday', 'wednesday', 'friday', 'tuesday', 'thursday'];
        $colors = ['#3d5ee1', '#00cfe8', '#28c76f', '#ff9f43', '#ea5455'];

        foreach ($teachers as $index => &$row) {
            $teacher = $row['teacher'];
            $blueprint = $row['blueprint'];
            $pairs = [];
            $gradeLevels = [];

            foreach ($blueprint['assignments'] as $assignment) {
                $grade = $assignment['grade'];
                $subject = $this->findSubject($grade, $assignment['names']);
                $sections = $context['sections_by_grade']->get($grade, collect());

                if (!$subject || $sections->isEmpty()) {
                    continue;
                }

                $gradeLevels[$grade] = true;

                DB::table('subject_teacher')->insertOrIgnore([
                    'subject_id' => $subject->id,
                    'teacher_id' => $teacher->id,
                    'created_at' => $context['now'],
                    'updated_at' => $context['now'],
                ]);

                foreach ($sections as $section) {
                    DB::table('section_teacher')->insertOrIgnore([
                        'teacher_id' => $teacher->id,
                        'section_id' => $section->id,
                        'created_at' => $context['now'],
                        'updated_at' => $context['now'],
                    ]);

                    $linkId = $this->ensureSectionSubject($section->id, $subject->id, $context['now']);
                    if ($linkId) {
                        $createdSectionSubjectIds[] = $linkId;
                    }

                    if (!$section->adviser_id) {
                        $section->adviser_id = $teacher->id;
                        $section->save();
                    }

                    $room = $context['rooms'][$roomIndex % $context['rooms']->count()];
                    $roomIndex++;
                    $day = $days[$index % count($days)];

                    ClassSchedule::create([
                        'section_id' => $section->id,
                        'subject_id' => $subject->id,
                        'teacher_id' => $teacher->id,
                        'room_id' => $room->id,
                        'day_of_week' => $day,
                        'start_time' => '08:00:00',
                        'end_time' => '09:00:00',
                        'class_type' => 'lecture',
                        'color' => $colors[$index % count($colors)],
                        'is_active' => true,
                        'notes' => self::MARKER.' demo schedule',
                    ]);

                    $pairs[] = [
                        'grade' => $grade,
                        'subject' => $subject,
                        'section' => $section,
                    ];
                }
            }

            foreach (array_keys($gradeLevels) as $grade) {
                TeacherGradeLevel::firstOrCreate([
                    'teacher_id' => $teacher->id,
                    'grade_level' => $grade,
                ]);
            }

            $row['pairs'] = $pairs;
        }
        unset($row);

        $this->writeTracker(['section_subject_ids' => array_values(array_unique($createdSectionSubjectIds))]);
    }

    protected function ensureSectionSubject(int $sectionId, int $subjectId, $now): ?int
    {
        $existing = DB::table('section_subject')
            ->where('section_id', $sectionId)
            ->where('subject_id', $subjectId)
            ->first();

        if ($existing) {
            return null;
        }

        return (int) DB::table('section_subject')->insertGetId([
            'section_id' => $sectionId,
            'subject_id' => $subjectId,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    protected function findSubject(string $grade, array $names): ?Subject
    {
        $subjects = Subject::where('class', $grade)->get();
        if ($subjects->isEmpty()) {
            return null;
        }

        foreach ($names as $name) {
            $exact = $subjects->first(fn ($s) => strcasecmp((string) $s->subject_name, $name) === 0);
            if ($exact) {
                return $exact;
            }
        }

        foreach ($names as $name) {
            $partial = $subjects->first(fn ($s) => stripos((string) $s->subject_name, $name) !== false);
            if ($partial) {
                return $partial;
            }
        }

        return $subjects->first();
    }

    protected function parentMap(): array
    {
        $map = [];
        for ($i = 1; $i <= 100; $i++) {
            $map[$i] = $i;
        }

        $pairs = [
            [1, 16], [2, 36], [3, 46], [4, 56], [5, 66],
            [6, 76], [7, 86], [8, 91], [9, 96], [10, 21],
        ];

        foreach ($pairs as [$a, $b]) {
            $map[$b] = $map[$a];
        }

        return $map;
    }

    protected function createParents(array $context): array
    {
        $this->log('Creating demo parent accounts...');
        $parentOf = $this->parentMap();
        $uniqueKeys = array_values(array_unique($parentOf));
        $parents = [];
        $now = $context['now'];
        $password = $context['password'];

        $guardianFirst = ['Maria', 'Jose', 'Ana', 'Roberto', 'Elena', 'Antonio', 'Carmen', 'Luis', 'Teresa', 'Miguel'];
        $guardianLast = ['Reyes', 'Santos', 'Cruz', 'Garcia', 'Ramos', 'Mendoza', 'Torres', 'Flores', 'Castillo', 'Navarro'];

        foreach ($uniqueKeys as $key) {
            $email = sprintf('demo.parent.%03d@%s', $key, self::EMAIL_DOMAIN);
            $first = $guardianFirst[($key - 1) % count($guardianFirst)];
            $last = $guardianLast[($key - 1) % count($guardianLast)];
            $name = $first.' '.$last;
            $phone = sprintf('09175552%03d', $key);
            $relationship = $key % 3 === 0 ? 'Father' : 'Mother';

            $user = User::create([
                'name' => $name,
                'email' => $email,
                'password' => $password,
                'role_name' => User::ROLE_PARENT,
                'status' => 'Active',
                'phone_number' => $phone,
                'date_of_birth' => Carbon::create(1980, (($key % 12) + 1), (($key % 27) + 1))->toDateString(),
                'join_date' => '2026-06-01',
                'email_verified_at' => $now,
            ]);
            $user->assignRole(User::ROLE_PARENT);

            $parents[$key] = [
                'user' => $user,
                'email' => $email,
                'name' => $name,
                'phone' => $phone,
                'relationship' => $relationship,
            ];
        }

        return $parents;
    }

    protected function createStudents(array $context, array $parents): array
    {
        $this->log('Creating 100 demo students...');
        $parentOf = $this->parentMap();
        $firstNames = [
            'Adrian', 'Amelia', 'Benjamin', 'Bianca', 'Carlo', 'Camille', 'Diego', 'Daphne',
            'Ethan', 'Elena', 'Felix', 'Fiona', 'Gabriel', 'Grace', 'Hugo', 'Hannah',
            'Ian', 'Isabel', 'Julian', 'Julia', 'Kyle', 'Kate', 'Liam', 'Luna',
            'Marco', 'Mira', 'Noah', 'Nina', 'Oscar', 'Olivia', 'Paolo', 'Patricia',
            'Rafael', 'Rosa', 'Sebastian', 'Sofia', 'Theo', 'Teresa', 'Victor', 'Valeria',
        ];
        $lastNames = [
            'Reyes', 'Santos', 'Cruz', 'Garcia', 'Ramos', 'Mendoza', 'Torres', 'Flores',
            'Castillo', 'Navarro', 'Villanueva', 'Aquino', 'Bautista', 'Fernandez', 'Gonzales',
            'Herrera', 'Ignacio', 'Javier', 'Lopez', 'Morales',
        ];
        $middleNames = ['Andres', 'Belle', 'Cesar', 'Dawn', 'Eliseo', 'Faith', 'Gino', 'Hope'];
        $blood = ['A+', 'B+', 'O+', 'AB+', 'A-', 'O-'];
        $religion = ['Catholic', 'Christian', 'Iglesia ni Cristo', 'Catholic', 'Born Again'];

        $plan = [];
        $n = 1;
        foreach ($this->gradeDistribution() as $grade => $count) {
            $sections = $context['sections_by_grade']->get($grade);
            for ($i = 0; $i < $count; $i++, $n++) {
                $plan[] = [
                    'n' => $n,
                    'grade' => $grade,
                    'section' => $sections[$i % $sections->count()],
                ];
            }
        }

        $created = [];
        foreach ($plan as $item) {
            $n = $item['n'];
            $grade = $item['grade'];
            $section = $item['section'];
            $admission = sprintf('DEMO-STU-%04d', $n);
            $email = sprintf('demo.stu.%04d@%s', $n, self::EMAIL_DOMAIN);
            $first = $firstNames[($n - 1) % count($firstNames)];
            $last = $lastNames[($n - 1) % count($lastNames)];
            $middle = $middleNames[($n - 1) % count($middleNames)];
            $gender = $n % 2 === 0 ? 'Female' : 'Male';
            $dob = $this->birthDateForGrade($grade, $n);
            $parent = $parents[$parentOf[$n]];
            $phone = sprintf('09175553%03d', $n);

            $user = User::create([
                'name' => $first.' '.$last,
                'email' => $email,
                'password' => $context['password'],
                'role_name' => User::ROLE_STUDENT,
                'status' => 'Active',
                'phone_number' => $phone,
                'date_of_birth' => $dob,
                'join_date' => '2026-06-15',
                'email_verified_at' => $context['now'],
            ]);
            $user->assignRole(User::ROLE_STUDENT);

            $student = Student::create([
                'user_id' => $user->user_id,
                'first_name' => $first,
                'middle_name' => $middle,
                'last_name' => $last,
                'gender' => $gender,
                'date_of_birth' => $dob,
                'roll' => $admission,
                'blood_group' => $blood[($n - 1) % count($blood)],
                'religion' => $religion[($n - 1) % count($religion)],
                'email' => $email,
                'parent_email' => $parent['email'],
                'parent_user_id' => $parent['user']->id,
                'parent_name' => $parent['name'],
                'parent_phone' => $parent['phone'],
                'parent_relationship' => $parent['relationship'],
                'emergency_contact_name' => $parent['name'],
                'emergency_contact_phone' => $parent['phone'],
                'address' => sprintf(
                    'Blk %d Lot %d, DEMO-2026 Residences, Brgy. Dita, City of Santa Rosa, Laguna 4026',
                    (($n - 1) % 20) + 1,
                    (($n - 1) % 15) + 1
                ),
                'previous_school' => $n % 5 === 0 ? 'Demo Learning Center' : null,
                'enrollment_status' => 'active',
                'class' => $grade,
                'year_level' => $grade,
                'section' => $section->name,
                'admission_id' => $admission,
                'phone_number' => $phone,
            ]);

            DB::table('student_section_assignments')->insert([
                'student_id' => $student->id,
                'section_id' => $section->id,
                'academic_year_id' => $context['academic_year']->id,
                'semester_id' => $context['semester']->id,
                'assigned_date' => '2026-06-15 08:00:00',
                'created_at' => $context['now'],
                'updated_at' => $context['now'],
            ]);

            if (Schema::hasTable('section_student')) {
                DB::table('section_student')->insertOrIgnore([
                    'section_id' => $section->id,
                    'student_id' => $student->id,
                    'created_at' => $context['now'],
                    'updated_at' => $context['now'],
                ]);
            }

            $created[] = [
                'n' => $n,
                'student' => $student,
                'user' => $user,
                'grade' => $grade,
                'section' => $section,
                'parent_key' => $parentOf[$n],
            ];
        }

        return $created;
    }

    protected function birthDateForGrade(string $grade, int $n): string
    {
        $years = [
            'Nursery' => 2022,
            'Kindergarten' => 2021,
            'Grade 1' => 2020,
            'Grade 2' => 2019,
            'Grade 3' => 2018,
            'Grade 4' => 2017,
            'Grade 5' => 2016,
            'Grade 6' => 2015,
            'Grade 7' => 2014,
            'Grade 8' => 2013,
        ];
        $year = $years[$grade] ?? 2018;
        $month = (($n - 1) % 12) + 1;
        $day = (($n - 1) % 27) + 1;

        return Carbon::create($year, $month, $day)->toDateString();
    }

    protected function populateAcademics(array $students, array $teachers, array $context): void
    {
        $this->log('Creating enrollments, grades, GPA, and attendance...');

        $subjectsByGrade = Subject::query()
            ->orderBy('id')
            ->get()
            ->groupBy('class');

        $teacherBySubjectId = [];
        foreach ($teachers as $row) {
            foreach ($row['pairs'] as $pair) {
                $teacherBySubjectId[$pair['subject']->id] = $row['teacher']->id;
            }
        }

        $fallbackTeacherId = $teachers[0]['teacher']->id ?? null;
        $enrollmentRows = [];
        $quarterRows = [];
        $gpaRows = [];
        $gradeRows = [];
        $attendanceRows = [];
        $dates = $this->schoolDates(18);
        $now = $context['now'];

        $componentsBySubject = SubjectComponent::query()
            ->when(Schema::hasColumn('subject_components', 'is_active'), fn ($q) => $q->where('is_active', true))
            ->get()
            ->groupBy('subject_id');

        foreach ($students as $row) {
            $student = $row['student'];
            $n = $row['n'];
            $grade = $row['grade'];
            $subjects = $subjectsByGrade->get($grade, collect());

            if ($subjects->isEmpty()) {
                continue;
            }

            $performance = $this->performanceProfile($n);
            $subjectAverages = [];

            foreach ($subjects as $offset => $subject) {
                $teacherId = $teacherBySubjectId[$subject->id] ?? $fallbackTeacherId;
                $enrollmentRows[] = [
                    'student_id' => $student->id,
                    'subject_id' => $subject->id,
                    'academic_year_id' => $context['academic_year']->id,
                    'semester_id' => $context['semester']->id,
                    'enrollment_date' => '2026-06-16 08:00:00',
                    'status' => 'active',
                    'created_at' => $now,
                    'updated_at' => $now,
                ];

                $q1 = $this->score($performance['base'], $offset, 0);
                $q2 = $this->score($performance['base'], $offset, 1);
                $q3 = $this->score($performance['base'], $offset, 2);
                $q4 = $this->score($performance['base'], $offset, 3);
                $final = round(($q1 + $q2 + $q3 + $q4) / 4, 2);
                $subjectAverages[] = $final;

                $quarterRows[] = [
                    'student_id' => $student->id,
                    'subject_id' => $subject->id,
                    'teacher_id' => $teacherId,
                    'academic_year_id' => $context['academic_year']->id,
                    'quarter_1' => $q1,
                    'quarter_2' => $q2,
                    'quarter_3' => $q3,
                    'quarter_4' => $q4,
                    'final_grade' => $final,
                    'remarks' => $final >= AcademicThresholds::PASSING_PERCENTAGE ? 'Passed' : 'Failed',
                    'created_at' => $now,
                    'updated_at' => $now,
                ];

                $components = $componentsBySubject->get($subject->id, collect())->take(2);
                foreach ($components as $component) {
                    $pct = $this->score($performance['base'], $offset + (int) $component->id, 0);
                    $gradeRows[] = [
                        'student_id' => $student->id,
                        'subject_id' => $subject->id,
                        'teacher_id' => $teacherId,
                        'component_id' => $component->id,
                        'score' => $pct,
                        'max_score' => 100,
                        'percentage' => $pct,
                        'remarks' => self::MARKER,
                        'grading_period' => 'Q1',
                        'academic_year_id' => $context['academic_year']->id,
                        'semester_id' => $context['semester']->id,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }

                foreach ($dates as $dIndex => $date) {
                    $statusInfo = $this->attendanceStatus($n, $dIndex, $performance['attendance']);
                    $attendanceRows[] = [
                        'student_id' => $student->id,
                        'subject_id' => $subject->id,
                        'date' => $date,
                        'status' => $statusInfo['status'],
                        'teacher_id' => $teacherId,
                        'remarks' => $statusInfo['remarks'],
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
            }

            if (!empty($subjectAverages)) {
                $avg = array_sum($subjectAverages) / count($subjectAverages);
                $gpa = round(max(1.00, min(4.00, ($avg / 100) * 4)), 2);
                $gpaRows[] = [
                    'student_id' => $student->id,
                    'academic_year_id' => $context['academic_year']->id,
                    'semester_id' => $context['semester']->id,
                    'gpa' => $gpa,
                    'total_units' => count($subjectAverages),
                    'total_grade_points' => (int) round($gpa * count($subjectAverages) * 100),
                    'rank' => null,
                    'remarks' => $avg >= 90 ? 'With Honors | '.self::MARKER : self::MARKER,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        $this->insertChunks('enrollments', $enrollmentRows);
        $this->insertChunks('quarterly_grades', $quarterRows);
        $this->insertChunks('student_gpa', $gpaRows);
        $this->insertChunks('grades', $gradeRows);
        $this->insertChunks('attendances', $attendanceRows);
    }

    protected function createTeacherContent(array $teachers, array $students, array $context): void
    {
        $this->log('Creating lessons, activities, assignments, and class posts...');
        $studentsBySection = collect($students)->groupBy(fn ($row) => $row['section']->id);
        $now = $context['now'];

        foreach ($teachers as $row) {
            $teacher = $row['teacher'];
            $user = $row['user'];
            $blueprint = $row['blueprint'];

            foreach (array_slice($row['pairs'], 0, 2) as $pairIndex => $pair) {
                $subject = $pair['subject'];
                $section = $pair['section'];

                $lesson = Lesson::create([
                    'title' => self::MARKER.' '.$blueprint['specialization'].' Lesson '.($pairIndex + 1).' — '.$section->name,
                    'description' => 'Dummy lesson for the DEMO-2026 presentation. Topic overview, guided practice, and short reflection for '.$subject->subject_name.'.',
                    'teacher_id' => $teacher->id,
                    'subject_id' => $subject->id,
                    'section_id' => $section->id,
                    'academic_year_id' => $context['academic_year']->id,
                    'semester_id' => $context['semester']->id,
                    'lesson_date' => Carbon::parse('2026-08-18')->addDays($pairIndex * 7)->toDateString(),
                    'status' => 'published',
                    'is_active' => true,
                ]);

                $activity = Activity::create([
                    'title' => self::MARKER.' Practice Activity '.($pairIndex + 1),
                    'instructions' => 'Complete the dummy worksheet and upload your answers. This activity is for presentation only.',
                    'lesson_id' => $lesson->id,
                    'due_date' => Carbon::parse('2026-09-05')->addDays($pairIndex)->toDateString(),
                    'allows_submission' => true,
                    'is_active' => true,
                ]);

                $sectionStudents = $studentsBySection->get($section->id, collect());
                foreach ($sectionStudents->take(8) as $sIndex => $studentRow) {
                    $pct = $this->score($this->performanceProfile($studentRow['n'])['base'], $sIndex, 0);
                    ActivitySubmission::create([
                        'student_id' => $studentRow['student']->id,
                        'activity_id' => $activity->id,
                        'comments' => 'Submitted dummy activity ('.self::MARKER.').',
                        'status' => 'graded',
                        'submitted_at' => Carbon::parse('2026-09-01')->addHours($sIndex),
                        'is_active' => true,
                        'total_score' => $pct,
                        'max_possible_score' => 100,
                        'percentage' => $pct,
                        'letter_grade' => $pct >= 90 ? 'A' : ($pct >= 85 ? 'B' : 'C'),
                        'feedback' => 'Good work. Keep practicing. ('.self::MARKER.')',
                        'graded_by' => $user->id,
                        'graded_at' => Carbon::parse('2026-09-03'),
                    ]);
                }

                $assignment = Assignment::create([
                    'title' => self::MARKER.' '.$subject->subject_name.' Assignment '.($pairIndex + 1),
                    'description' => 'Dummy assignment for '.$section->name.'. Answer the review questions and submit a DOCX or PDF file.',
                    'teacher_id' => $teacher->id,
                    'subject_id' => $subject->id,
                    'section_id' => $section->id,
                    'academic_year_id' => $context['academic_year']->id,
                    'semester_id' => $context['semester']->id,
                    'due_date' => Carbon::parse('2026-09-12')->addDays($pairIndex)->toDateString(),
                    'due_time' => '16:00:00',
                    'max_score' => 100,
                    'status' => 'published',
                    'allows_late_submission' => true,
                    'late_submission_penalty' => 5,
                    'requires_file_upload' => true,
                    'submission_instructions' => 'Upload a PDF or DOCX file. DEMO-2026 dummy assignment.',
                    'allowed_file_types' => ['pdf', 'docx'],
                    'max_file_size' => 5120,
                    'is_active' => true,
                ]);

                foreach ($sectionStudents->take(6) as $sIndex => $studentRow) {
                    $pct = $this->score($this->performanceProfile($studentRow['n'])['base'], $sIndex + 2, 1);
                    AssignmentSubmission::create([
                        'assignment_id' => $assignment->id,
                        'student_id' => $studentRow['student']->id,
                        'comments' => 'Dummy student submission ('.self::MARKER.').',
                        'teacher_feedback' => 'Checked. ('.self::MARKER.')',
                        'score' => $pct,
                        'max_score' => 100,
                        'status' => 'graded',
                        'submitted_at' => Carbon::parse('2026-09-08')->addHours($sIndex),
                        'graded_at' => Carbon::parse('2026-09-10'),
                        'is_late' => false,
                        'is_active' => true,
                    ]);
                }

                ClassPost::create([
                    'title' => self::MARKER.' Welcome to '.$subject->subject_name,
                    'content' => 'This is a dummy class post for the DEMO-2026 presentation. Please review the lesson materials and complete the assigned activity.',
                    'teacher_id' => $teacher->id,
                    'subject_id' => $subject->id,
                    'section_id' => $section->id,
                    'academic_year_id' => $context['academic_year']->id,
                    'semester_id' => $context['semester']->id,
                    'type' => 'announcement',
                    'priority' => 'normal',
                    'is_pinned' => $pairIndex === 0,
                    'allows_comments' => true,
                    'requires_confirmation' => false,
                    'is_active' => true,
                    'published_at' => $now,
                ]);
            }
        }
    }

    protected function performanceProfile(int $n): array
    {
        if ($n % 20 === 0) {
            return ['base' => 68, 'attendance' => 'low'];
        }
        if ($n % 7 === 0) {
            return ['base' => 78, 'attendance' => 'mid'];
        }
        if ($n % 5 === 0) {
            return ['base' => 94, 'attendance' => 'high'];
        }

        return ['base' => 86 + ($n % 8), 'attendance' => 'high'];
    }

    protected function score(int $base, int $offset, int $quarter): int
    {
        $value = $base + (($offset + $quarter) % 7) - 2;
        $options = [85, 87, 89, 90, 92, 94, 96];
        if ($value >= 84) {
            $value = $options[($base + $offset + $quarter) % count($options)];
        }

        return max(65, min(98, $value));
    }

    protected function attendanceStatus(int $n, int $dayIndex, string $band): array
    {
        $roll = ($n * 3 + $dayIndex * 7) % 100;

        if ($band === 'low') {
            if ($roll < 28) {
                return ['status' => 'absent', 'remarks' => 'Absent | '.self::MARKER];
            }
            if ($roll < 38) {
                return ['status' => 'late', 'remarks' => 'Late | '.self::MARKER];
            }
            if ($roll < 48) {
                return ['status' => 'excused', 'remarks' => 'Excused | '.self::MARKER];
            }
            return ['status' => 'present', 'remarks' => self::MARKER];
        }

        if ($band === 'mid') {
            if ($roll < 12) {
                return ['status' => 'absent', 'remarks' => 'Absent | '.self::MARKER];
            }
            if ($roll < 20) {
                return ['status' => 'late', 'remarks' => 'Late | '.self::MARKER];
            }
            if ($roll < 26) {
                return ['status' => 'excused', 'remarks' => 'Excused | '.self::MARKER];
            }
            return ['status' => 'present', 'remarks' => self::MARKER];
        }

        if ($roll < 3) {
            return ['status' => 'absent', 'remarks' => 'Absent | '.self::MARKER];
        }
        if ($roll < 8) {
            return ['status' => 'late', 'remarks' => 'Late | '.self::MARKER];
        }
        if ($roll < 11) {
            return ['status' => 'excused', 'remarks' => 'Excused | '.self::MARKER];
        }

        return ['status' => 'present', 'remarks' => self::MARKER];
    }

    protected function schoolDates(int $count): array
    {
        $dates = [];
        $cursor = Carbon::parse('2026-08-03');
        while (count($dates) < $count) {
            if ($cursor->isWeekday()) {
                $dates[] = $cursor->toDateString();
            }
            $cursor->addDay();
        }

        return $dates;
    }

    protected function insertChunks(string $table, array $rows, int $size = 250): void
    {
        if (empty($rows)) {
            return;
        }

        foreach (array_chunk($rows, $size) as $chunk) {
            DB::table($table)->insert($chunk);
        }
    }

    protected function summarize(array $students, array $teachers, array $parents, array $context): array
    {
        $gradeDist = [];
        $sectionDist = [];
        foreach ($students as $row) {
            $gradeDist[$row['grade']] = ($gradeDist[$row['grade']] ?? 0) + 1;
            $label = $row['grade'].' — '.$row['section']->name;
            $sectionDist[$label] = ($sectionDist[$label] ?? 0) + 1;
        }

        $subjects = [];
        foreach ($teachers as $row) {
            foreach ($row['pairs'] as $pair) {
                $subjects[] = $pair['subject']->subject_name.' ('.$pair['grade'].' / '.$pair['section']->name.')';
            }
        }

        $tracker = $this->readTracker();

        return [
            'students' => count($students),
            'teachers' => count($teachers),
            'parents' => count($parents),
            'grade_distribution' => $gradeDist,
            'section_distribution' => $sectionDist,
            'subjects_assigned' => $subjects,
            'academic_year' => $context['academic_year']->name ?? $context['academic_year']->id,
            'semester' => $context['semester']->name ?? $context['semester']->id,
            'password' => self::PASSWORD,
            'marker' => self::MARKER,
            'section_subject_ids' => $tracker['section_subject_ids'] ?? [],
            'credentials' => [
                'teachers' => collect($teachers)->map(fn ($row) => [
                    'id' => 'DEMO-TCH-'.$row['blueprint']['code'],
                    'name' => $row['teacher']->full_name,
                    'email' => $row['user']->email,
                    'password' => self::PASSWORD,
                    'department' => $row['blueprint']['department'],
                ])->all(),
                'sample_students' => [
                    [
                        'admission_id' => 'DEMO-STU-0001',
                        'email' => 'demo.stu.0001@'.self::EMAIL_DOMAIN,
                        'password' => self::PASSWORD,
                    ],
                    [
                        'admission_id' => 'DEMO-STU-0100',
                        'email' => 'demo.stu.0100@'.self::EMAIL_DOMAIN,
                        'password' => self::PASSWORD,
                    ],
                ],
                'sample_parent' => [
                    'email' => 'demo.parent.001@'.self::EMAIL_DOMAIN,
                    'password' => self::PASSWORD,
                    'note' => 'Parent of DEMO-STU-0001 and DEMO-STU-0016 (siblings).',
                ],
            ],
        ];
    }

    protected function writeTracker(array $data): void
    {
        $existing = $this->readTracker();
        Storage::disk('local')->put(self::TRACKER_FILE, json_encode(array_merge($existing, $data), JSON_PRETTY_PRINT));
    }

    protected function readTracker(): array
    {
        if (!Storage::disk('local')->exists(self::TRACKER_FILE)) {
            return [];
        }

        $decoded = json_decode(Storage::disk('local')->get(self::TRACKER_FILE), true);

        return is_array($decoded) ? $decoded : [];
    }

    protected function forgetTracker(): void
    {
        if (Storage::disk('local')->exists(self::TRACKER_FILE)) {
            Storage::disk('local')->delete(self::TRACKER_FILE);
        }
    }
}
