<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
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

    public function test_create_verify_and_delete_only_touch_dummy_records(): void
    {
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
}
