<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Curriculum;
use App\Models\Subject;

class CurriculumSubjectSeeder extends Seeder
{
    public function run(): void
    {
        $curriculum = Curriculum::first();
        $subjects = Subject::take(3)->pluck('id');
        if ($curriculum && $subjects->count()) {
            foreach ($subjects as $subjectId) {
                DB::table('curriculum_subject')->insert([
                    'curriculum_id' => $curriculum->id,
                    'subject_id' => $subjectId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
