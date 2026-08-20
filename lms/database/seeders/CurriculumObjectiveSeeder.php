<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\CurriculumObjective;
use App\Models\Subject;

class CurriculumObjectiveSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $subjects = Subject::all();
        
        if ($subjects->isEmpty()) {
            $this->command->warn('No subjects found. Please run subject seeder first.');
            return;
        }

        $objectives = [
            // Mathematics
            [
                'code' => 'MATH-001',
                'title' => 'Basic Arithmetic Operations',
                'description' => 'Students will be able to perform addition, subtraction, multiplication, and division with whole numbers.',
                'subject_name' => 'Mathematics'
            ],
            [
                'code' => 'MATH-002',
                'title' => 'Fractions and Decimals',
                'description' => 'Students will understand and work with fractions and decimals.',
                'subject_name' => 'Mathematics'
            ],
            [
                'code' => 'MATH-003',
                'title' => 'Algebraic Expressions',
                'description' => 'Students will learn to work with variables and algebraic expressions.',
                'subject_name' => 'Mathematics'
            ],
            
            // English
            [
                'code' => 'ENG-001',
                'title' => 'Reading Comprehension',
                'description' => 'Students will develop reading comprehension skills and analyze texts.',
                'subject_name' => 'English'
            ],
            [
                'code' => 'ENG-002',
                'title' => 'Writing Skills',
                'description' => 'Students will develop writing skills including grammar and composition.',
                'subject_name' => 'English'
            ],
            [
                'code' => 'ENG-003',
                'title' => 'Literature Analysis',
                'description' => 'Students will analyze literary works and identify themes and elements.',
                'subject_name' => 'English'
            ],
            
            // Science
            [
                'code' => 'SCI-001',
                'title' => 'Scientific Method',
                'description' => 'Students will understand and apply the scientific method in experiments.',
                'subject_name' => 'Science'
            ],
            [
                'code' => 'SCI-002',
                'title' => 'Life Sciences',
                'description' => 'Students will learn about living organisms and biological processes.',
                'subject_name' => 'Science'
            ],
            [
                'code' => 'SCI-003',
                'title' => 'Physical Sciences',
                'description' => 'Students will explore physics and chemistry concepts.',
                'subject_name' => 'Science'
            ],
            
            // History
            [
                'code' => 'HIST-001',
                'title' => 'Ancient Civilizations',
                'description' => 'Students will explore the history and contributions of ancient civilizations.',
                'subject_name' => 'History'
            ],
            [
                'code' => 'HIST-002',
                'title' => 'World History',
                'description' => 'Students will learn about major events and developments in world history.',
                'subject_name' => 'History'
            ],
            [
                'code' => 'HIST-003',
                'title' => 'Modern History',
                'description' => 'Students will study recent historical events and their impact.',
                'subject_name' => 'History'
            ]
        ];

        foreach ($objectives as $objective) {
            $subject = $subjects->where('subject_name', $objective['subject_name'])->first();
            if ($subject) {
                CurriculumObjective::create([
                    'code' => $objective['code'],
                    'title' => $objective['title'],
                    'description' => $objective['description'],
                    'subject_id' => $subject->id,
                    'grade_level' => 10, // Default grade level
                    'is_active' => true
                ]);
            }
        }

        $this->command->info('Curriculum objectives seeded successfully!');
    }
}
