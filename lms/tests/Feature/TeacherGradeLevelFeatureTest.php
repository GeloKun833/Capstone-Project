<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Teacher;
use App\Models\TeacherGradeLevel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeacherGradeLevelFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role_name' => 'Admin']);
    }

    public function test_admin_can_view_assign_grade_levels_form()
    {
        $teacher = Teacher::factory()->create();
        $response = $this->actingAs($this->admin)->get('/teacher/' . $teacher->id . '/assign-grade-levels');
        $response->assertStatus(200);
        $response->assertSee('Assign Grade Levels');
    }

    public function test_admin_can_assign_grade_levels_to_teacher()
    {
        $teacher = Teacher::factory()->create();
        $gradeLevels = ['Grade 1', 'Grade 2'];
        $response = $this->actingAs($this->admin)->post('/teacher/' . $teacher->id . '/assign-grade-levels', [
            'grade_levels' => $gradeLevels,
        ]);
        $response->assertRedirect('/teacher/list/page');
        $this->assertDatabaseHas('teacher_grade_level', [
            'teacher_id' => $teacher->id,
            'grade_level' => 'Grade 1',
        ]);
        $this->assertDatabaseHas('teacher_grade_level', [
            'teacher_id' => $teacher->id,
            'grade_level' => 'Grade 2',
        ]);
    }

    public function test_assigned_grade_levels_are_displayed()
    {
        $teacher = Teacher::factory()->create();
        $teacher->gradeLevels()->create(['grade_level' => 'Grade 3']);
        $response = $this->actingAs($this->admin)->get('/teacher/' . $teacher->id . '/assign-grade-levels');
        $response->assertSee('Grade 3');
    }

    public function test_non_admin_cannot_access_grade_level_assignment()
    {
        $user = User::factory()->create(['role_name' => 'Teacher']);
        $teacher = Teacher::factory()->create();
        $response = $this->actingAs($user)->get('/teacher/' . $teacher->id . '/assign-grade-levels');
        $response->assertStatus(403);
    }
}
