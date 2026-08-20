<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Curriculum;
use App\Models\Subject;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CurriculumFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();
        // Create an admin user
        $this->admin = User::factory()->create(['role_name' => 'Admin']);
    }

    public function test_guest_cannot_access_curriculum_routes()
    {
        $response = $this->get('/curriculum');
        $response->assertRedirect('/login');
    }

    public function test_admin_can_view_curriculum_index()
    {
        $response = $this->actingAs($this->admin)->get('/curriculum');
        $response->assertStatus(200);
    }

    public function test_admin_can_create_curriculum()
    {
        $response = $this->actingAs($this->admin)->post('/curriculum', [
            'grade_level' => 'Grade 1',
            'description' => 'Test curriculum',
        ]);
        $response->assertRedirect('/curriculum');
        $this->assertDatabaseHas('curricula', ['grade_level' => 'Grade 1']);
    }

    public function test_admin_can_update_curriculum()
    {
        $curriculum = Curriculum::factory()->create();
        $response = $this->actingAs($this->admin)->put('/curriculum/' . $curriculum->id, [
            'grade_level' => 'Grade 2',
            'description' => 'Updated',
        ]);
        $response->assertRedirect('/curriculum');
        $this->assertDatabaseHas('curricula', ['grade_level' => 'Grade 2']);
    }

    public function test_admin_can_delete_curriculum()
    {
        $curriculum = Curriculum::factory()->create();
        $response = $this->actingAs($this->admin)->delete('/curriculum/' . $curriculum->id);
        $response->assertRedirect('/curriculum');
        $this->assertDatabaseMissing('curricula', ['id' => $curriculum->id]);
    }

    public function test_admin_can_assign_subjects_to_curriculum()
    {
        $curriculum = Curriculum::factory()->create();
        $subjects = Subject::factory()->count(2)->create();
        $response = $this->actingAs($this->admin)->post('/curriculum/' . $curriculum->id . '/assign-subjects', [
            'subject_ids' => $subjects->pluck('id')->toArray(),
        ]);
        $response->assertRedirect('/curriculum/' . $curriculum->id);
        $this->assertEquals(2, $curriculum->subjects()->count());
    }

    public function test_subjects_assigned_are_displayed()
    {
        $curriculum = Curriculum::factory()->create();
        $subject = Subject::factory()->create();
        $curriculum->subjects()->attach($subject->id);
        $response = $this->actingAs($this->admin)->get('/curriculum/' . $curriculum->id);
        $response->assertSee($subject->subject_name);
    }
}
