<?php

namespace Tests\Unit;

use App\Models\Curriculum;
use App\Models\Subject;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CurriculumUnitTest extends TestCase
{
    use RefreshDatabase;

    public function test_curriculum_has_many_subjects()
    {
        $curriculum = Curriculum::factory()->create();
        $subject = Subject::factory()->create();
        $curriculum->subjects()->attach($subject->id);
        $this->assertTrue($curriculum->subjects->contains($subject));
    }

    public function test_fillable_fields()
    {
        $data = ['grade_level' => 'Grade 5', 'description' => 'Test'];
        $curriculum = Curriculum::create($data);
        $this->assertEquals('Grade 5', $curriculum->grade_level);
        $this->assertEquals('Test', $curriculum->description);
    }

    public function test_deleting_curriculum_removes_subject_links()
    {
        $curriculum = Curriculum::factory()->create();
        $subject = Subject::factory()->create();
        $curriculum->subjects()->attach($subject->id);
        $curriculum->delete();
        $this->assertDatabaseMissing('curriculum_subject', [
            'curriculum_id' => $curriculum->id,
            'subject_id' => $subject->id,
        ]);
    }
}
