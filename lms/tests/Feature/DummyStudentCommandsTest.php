<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\EnrollmentApplication;
use App\Models\GradeAlert;
use App\Models\Section;
use App\Models\Semester;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use App\Services\DummyStudentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DummyStudentCommandsTest extends TestCase
{
    use RefreshDatabase;

    protected Student $realStudent;

    protected User $realUser;

    protected function setUp(): void
    {
        parent::setUp();

        Role::findOrCreate(User::ROLE_STUDENT, 'web');

        $year = AcademicYear::create(['name' => '2026-2027', 'start_date' => now()->subMonths(2)->toDateString(), 'end_date' => now()->addMonths(8)->toDateString()]);
        $semester = Semester::create(['name' => 'First Semester', 'academic_year_id' => $year->id]);

        foreach (['Grade 1', 'Grade 2'] as $g => $grade) {
            $teacher = Teacher::create(['full_name' => "Real Teacher {$g}"]);
            foreach (['Math', 'English', 'Science', 'Filipino'] as $name) {
                $subject = Subject::create(['subject_id' => "{$grade}-{$name}", 'subject_name' => $name, 'class' => $grade]);
                DB::table('subject_teacher')->insert(['subject_id' => $subject->id, 'teacher_id' => $teacher->id]);
            }
            foreach (['Rosal', 'Sampaguita'] as $name) {
                Section::create(['name' => "{$grade} - {$name}", 'grade_level' => $grade, 'capacity' => 150, 'adviser_id' => $teacher->id]);
            }
        }

        $this->realUser = User::create([
            'name' => 'Real Student', 'email' => 'real.student@school.test', 'password' => Hash::make('secret123'),
            'role_name' => User::ROLE_STUDENT, 'status' => 'active',
        ]);
        $this->realStudent = Student::create([
            'user_id' => $this->realUser->user_id, 'first_name' => 'Real', 'last_name' => 'Student', 'gender' => 'Female',
            'date_of_birth' => '2018-01-01', 'email' => 'real.student@school.test', 'class' => 'Grade 1', 'section' => 'Grade 1 - Rosal',
            'admission_id' => 'STU-0001', 'enrollment_status' => 'active',
        ]);
        DB::table('student_section_assignments')->insert([
            'student_id' => $this->realStudent->id, 'section_id' => Section::first()->id,
            'academic_year_id' => $year->id, 'semester_id' => $semester->id, 'assigned_date' => now(),
        ]);
    }

    protected function createRealApplication(): EnrollmentApplication
    {
        return EnrollmentApplication::create([
            'application_number' => 'APP-2026-0001', 'first_name' => 'Real', 'last_name' => 'Applicant', 'date_of_birth' => '2019-02-02',
            'gender' => 'Male', 'email' => 'real.applicant@school.test', 'phone_number' => '09170000000', 'address' => 'Real Street',
            'parent_name' => 'Real Parent', 'parent_phone' => '09170000001', 'parent_email' => 'real.parent@school.test',
            'parent_relationship' => 'Father', 'emergency_contact_name' => 'Real Parent', 'emergency_contact_phone' => '09170000001',
            'grade_level_applying_for' => 'Grade 1', 'status' => 'pending',
        ]);
    }

    public function test_create_verify_and_delete_only_touch_dummy_records(): void
    {
        $realApplication = $this->createRealApplication();
        $realApplicationRow = (array) DB::table('enrollment_applications')->find($realApplication->id);

        $realStudentRow = (array) DB::table('students')->find($this->realStudent->id);
        $realUserRow = (array) DB::table('users')->find($this->realUser->id);

        $this->artisan('dummy:students:create', ['--force' => true, '--attendance-days' => 2])->assertSuccessful();

        $dummies = Student::where('admission_id', 'like', DummyStudentService::ADMISSION_PREFIX.'%')->get();
        $this->assertCount(DummyStudentService::TOTAL_STUDENTS, $dummies);
        $this->assertSame(DummyStudentService::TOTAL_STUDENTS, User::where('email', 'like', '%@'.DummyStudentService::EMAIL_DOMAIN)->count());
        $this->assertSame(1, Teacher::where('full_name', 'Real Teacher 0')->count());
        $this->assertSame(2, Teacher::count());
        $this->assertSame(4, Section::count());
        $this->assertSame(
            DummyStudentService::LOW_PERFORMER_COUNT,
            GradeAlert::where('alert_type', GradeAlert::TYPE_AT_RISK)->whereIn('student_id', $dummies->pluck('id'))->distinct()->count('student_id')
        );

        $applications = EnrollmentApplication::where('application_number', 'like', DummyStudentService::APPLICATION_PREFIX.'%');
        $this->assertSame(DummyStudentService::totalApplications(), (clone $applications)->count());
        $this->assertSame(DummyStudentService::TOTAL_STUDENTS, (clone $applications)->where('status', 'approved')->count());
        $this->assertSame(DummyStudentService::TOTAL_STUDENTS, $dummies->fresh()->whereNotNull('enrollment_application_id')->count());
        foreach (DummyStudentService::EXTRA_APPLICATION_STATUSES as $status => $count) {
            $this->assertSame($count, (clone $applications)->where('status', $status)->count());
        }

        $this->artisan('dummy:students:verify')->assertSuccessful();
        $this->artisan('dummy:students:create', ['--force' => true])->assertFailed();
        $this->assertSame(DummyStudentService::TOTAL_STUDENTS, Student::where('admission_id', 'like', DummyStudentService::ADMISSION_PREFIX.'%')->count());

        $this->artisan('dummy:students:delete', ['--force' => true])->assertSuccessful();

        $this->assertSame(0, Student::withTrashed()->where('admission_id', 'like', DummyStudentService::ADMISSION_PREFIX.'%')->count());
        $this->assertSame(0, User::where('email', 'like', '%@'.DummyStudentService::EMAIL_DOMAIN)->count());
        $this->assertSame(0, DB::table('enrollments')->count());
        $this->assertSame(0, DB::table('quarterly_grades')->count());
        $this->assertSame(0, DB::table('attendances')->count());
        $this->assertSame(1, DB::table('student_section_assignments')->count());
        $this->assertSame($realStudentRow, (array) DB::table('students')->find($this->realStudent->id));
        $this->assertSame($realUserRow, (array) DB::table('users')->find($this->realUser->id));
        $this->assertSame(8, Subject::count());
        $this->assertSame(1, EnrollmentApplication::count());
        $this->assertSame($realApplicationRow, (array) DB::table('enrollment_applications')->find($realApplication->id));
    }

    public function test_applications_command_adds_applications_to_existing_dummy_students(): void
    {
        $this->artisan('dummy:students:create', ['--force' => true, '--attendance-days' => 1])->assertSuccessful();
        DB::table('students')->where('admission_id', 'like', DummyStudentService::ADMISSION_PREFIX.'%')->update(['enrollment_application_id' => null]);
        DB::table('enrollment_applications')->delete();
        $this->createRealApplication();

        $this->artisan('dummy:students:applications')
            ->expectsConfirmation('Create these '.DummyStudentService::totalApplications().' dummy enrollment applications?', 'no')
            ->assertFailed();
        $this->assertSame(1, EnrollmentApplication::count());

        $this->artisan('dummy:students:applications', ['--force' => true])->assertSuccessful();
        $this->assertSame(DummyStudentService::totalApplications() + 1, EnrollmentApplication::count());
        $this->artisan('dummy:students:verify')->assertSuccessful();
        $this->artisan('dummy:students:applications', ['--force' => true])->assertFailed();

        $this->artisan('dummy:students:delete', ['--force' => true])->assertSuccessful();
        $this->assertSame(1, EnrollmentApplication::count());
        $this->assertSame('APP-2026-0001', EnrollmentApplication::first()->application_number);
    }

    public function test_delete_without_force_or_confirmation_deletes_nothing(): void
    {
        $this->artisan('dummy:students:create', ['--force' => true, '--attendance-days' => 0])->assertSuccessful();

        $this->artisan('dummy:students:delete')
            ->expectsConfirmation('Permanently delete these dummy records?', 'no')
            ->assertFailed();
        $this->artisan('dummy:students:delete', ['--dry-run' => true])->assertSuccessful();

        $this->assertSame(DummyStudentService::TOTAL_STUDENTS, Student::where('admission_id', 'like', DummyStudentService::ADMISSION_PREFIX.'%')->count());
    }

    public function test_create_fails_without_writing_when_a_subject_has_no_teacher(): void
    {
        Subject::create(['subject_id' => 'Grade 1-Orphan', 'subject_name' => 'Orphan', 'class' => 'Grade 1']);
        Section::query()->update(['adviser_id' => null]);

        $this->artisan('dummy:students:create', ['--force' => true])->assertFailed();

        $this->assertSame(1, Student::count());
        $this->assertSame(1, User::count());
    }

    public function test_skip_unassigned_leaves_out_sections_without_teachers(): void
    {
        Subject::create(['subject_id' => 'Grade 3-Math', 'subject_name' => 'Math', 'class' => 'Grade 3']);
        $orphan = Section::create(['name' => 'Grade 3 - Orphan', 'grade_level' => 'Grade 3', 'capacity' => 150]);

        $this->artisan('dummy:students:create', ['--force' => true])->assertFailed();
        $this->assertSame(1, Student::count());

        $this->artisan('dummy:students:create', ['--attendance-days' => 1])
            ->expectsConfirmation('Skip the sections listed above and put all '.DummyStudentService::TOTAL_STUDENTS.' dummy students in the other sections?', 'yes')
            ->expectsConfirmation('Create '.DummyStudentService::TOTAL_STUDENTS.' dummy students with this plan?', 'yes')
            ->assertSuccessful();

        $this->assertSame(DummyStudentService::TOTAL_STUDENTS, Student::where('admission_id', 'like', DummyStudentService::ADMISSION_PREFIX.'%')->count());
        $this->assertSame(0, DB::table('student_section_assignments')->where('section_id', $orphan->id)->count());
        $this->artisan('dummy:students:verify')->assertSuccessful();

        $this->artisan('dummy:students:delete', ['--force' => true])->assertSuccessful();
        $this->artisan('dummy:students:create', ['--force' => true, '--skip-unassigned' => true, '--attendance-days' => 0])->assertSuccessful();
        $this->assertSame(0, DB::table('student_section_assignments')->where('section_id', $orphan->id)->count());
    }
}
