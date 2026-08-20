<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CurriculumObjective;
use App\Models\Lesson;
use App\Models\Activity;
use App\Models\ActivityRubric;
use App\Models\Subject;
use App\Models\Section;
use App\Models\AcademicYear;
use App\Models\Semester;
use App\Models\Teacher;
use App\Models\User;
use Carbon\Carbon;

class LessonPlannerBasicSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create basic data if it doesn't exist
        $this->createBasicData();
        
        // Create Curriculum Objectives
        $this->createCurriculumObjectives();

        // Create Lessons
        $this->createLessons();

        // Create Activities
        $this->createActivities();

        // Create Rubrics
        $this->createRubrics();

        $this->command->info('Lesson Planner basic sample data seeded successfully!');
    }

    private function createBasicData()
    {
        // Create a teacher user if none exists
        if (Teacher::count() === 0) {
            $user = User::create([
                'name' => 'John Teacher',
                'email' => 'teacher@school.com',
                'password' => bcrypt('password'),
                'role_name' => 'Teacher'
            ]);

            Teacher::create([
                'user_id' => $user->id,
                'full_name' => 'John Teacher',
                'phone_number' => '1234567890',
                'address' => '123 Teacher St',
                'city' => 'Sample City',
                'state' => 'Sample State',
                'country' => 'Sample Country'
            ]);
        }

        // Create subjects if none exist
        if (Subject::count() === 0) {
            Subject::create([
                'subject_name' => 'Mathematics',
                'class' => 'Grade 10'
            ]);
            Subject::create([
                'subject_name' => 'English',
                'class' => 'Grade 10'
            ]);
            Subject::create([
                'subject_name' => 'Science',
                'class' => 'Grade 10'
            ]);
            Subject::create([
                'subject_name' => 'History',
                'class' => 'Grade 10'
            ]);
        }

        // Create sections if none exist
        if (Section::count() === 0) {
            Section::create([
                'name' => 'Section A',
                'grade_level' => '10',
                'capacity' => 30,
                'description' => 'Grade 10 Section A'
            ]);
            Section::create([
                'name' => 'Section B',
                'grade_level' => '10',
                'capacity' => 30,
                'description' => 'Grade 10 Section B'
            ]);
        }

        // Create academic year if none exists
        if (AcademicYear::count() === 0) {
            AcademicYear::create([
                'name' => '2024-2025',
                'start_date' => '2024-06-01',
                'end_date' => '2025-05-31',
                'is_active' => true
            ]);
        }

        // Create semesters if none exist
        if (Semester::count() === 0) {
            Semester::create([
                'name' => 'First Semester',
                'is_active' => true
            ]);
            Semester::create([
                'name' => 'Second Semester',
                'is_active' => true
            ]);
        }
    }

    private function createCurriculumObjectives()
    {
        $subjects = Subject::all();
        $sections = Section::all();

        if ($subjects->isEmpty() || $sections->isEmpty()) {
            $this->command->warn('No subjects or sections found. Creating basic data first.');
            return;
        }

        $objectives = [
            [
                'code' => 'MATH-001',
                'title' => 'Basic Arithmetic Operations',
                'description' => 'Students will be able to perform addition, subtraction, multiplication, and division with whole numbers.',
                'subject_name' => 'Mathematics'
            ],
            [
                'code' => 'MATH-002',
                'title' => 'Fractions and Decimals',
                'description' => 'Students will understand and perform operations with fractions and decimals.',
                'subject_name' => 'Mathematics'
            ],
            [
                'code' => 'ENG-001',
                'title' => 'Reading Comprehension',
                'description' => 'Students will develop reading comprehension skills and analyze various text types.',
                'subject_name' => 'English'
            ],
            [
                'code' => 'ENG-002',
                'title' => 'Writing Skills',
                'description' => 'Students will learn to write clear, coherent, and well-structured essays.',
                'subject_name' => 'English'
            ],
            [
                'code' => 'SCI-001',
                'title' => 'Scientific Method',
                'description' => 'Students will understand and apply the scientific method in experiments.',
                'subject_name' => 'Science'
            ],
            [
                'code' => 'HIST-001',
                'title' => 'Ancient Civilizations',
                'description' => 'Students will explore the history and contributions of ancient civilizations.',
                'subject_name' => 'History'
            ]
        ];

        foreach ($objectives as $objective) {
            $subject = $subjects->where('subject_name', $objective['subject_name'])->first();
            if ($subject) {
                $section = $sections->first();
                CurriculumObjective::create([
                    'code' => $objective['code'],
                    'title' => $objective['title'],
                    'description' => $objective['description'],
                    'subject_id' => $subject->id,
                    'grade_level' => $section->grade_level,
                    'is_active' => true
                ]);
            }
        }
    }

    private function createLessons()
    {
        $subjects = Subject::all();
        $sections = Section::all();
        $academicYears = AcademicYear::all();
        $semesters = Semester::all();
        $teachers = Teacher::all();
        $curriculumObjectives = CurriculumObjective::all();

        if ($subjects->isEmpty() || $sections->isEmpty() || $academicYears->isEmpty() || $semesters->isEmpty() || $teachers->isEmpty() || $curriculumObjectives->isEmpty()) {
            $this->command->warn('Required data not found for lessons.');
            return;
        }

        $lessons = [
            [
                'title' => 'Introduction to Fractions',
                'description' => 'This lesson introduces students to the concept of fractions, including proper fractions, improper fractions, and mixed numbers.',
                'subject_name' => 'Mathematics',
                'curriculum_code' => 'MATH-002',
                'status' => 'published'
            ],
            [
                'title' => 'Reading Short Stories',
                'description' => 'Students will read and analyze short stories to develop comprehension skills and identify literary elements.',
                'subject_name' => 'English',
                'curriculum_code' => 'ENG-001',
                'status' => 'published'
            ],
            [
                'title' => 'Scientific Method Lab',
                'description' => 'Hands-on laboratory session where students will design and conduct experiments using the scientific method.',
                'subject_name' => 'Science',
                'curriculum_code' => 'SCI-001',
                'status' => 'draft'
            ]
        ];

        foreach ($lessons as $lessonData) {
            $subject = $subjects->where('subject_name', $lessonData['subject_name'])->first();
            $curriculumObjective = $curriculumObjectives->where('code', $lessonData['curriculum_code'])->first();
            
            if ($subject && $curriculumObjective) {
                $section = $sections->first();
                $academicYear = $academicYears->first();
                $semester = $semesters->first();
                $teacher = $teachers->first();

                Lesson::create([
                    'title' => $lessonData['title'],
                    'description' => $lessonData['description'],
                    'teacher_id' => $teacher->id,
                    'subject_id' => $subject->id,
                    'section_id' => $section->id,
                    'curriculum_objective_id' => $curriculumObjective->id,
                    'academic_year_id' => $academicYear->id,
                    'semester_id' => $semester->id,
                    'lesson_date' => Carbon::now()->addDays(rand(1, 30)),
                    'status' => $lessonData['status'],
                    'is_active' => true
                ]);
            }
        }
    }

    private function createActivities()
    {
        $lessons = Lesson::all();

        if ($lessons->isEmpty()) {
            $this->command->warn('No lessons found for activities.');
            return;
        }

        foreach ($lessons as $lesson) {
            // Create 2 activities per lesson
            for ($i = 1; $i <= 2; $i++) {
                $allowsSubmission = rand(0, 1);
                
                Activity::create([
                    'title' => $this->getActivityTitle($lesson->title, $i),
                    'instructions' => $this->getActivityInstructions($lesson->title, $i),
                    'lesson_id' => $lesson->id,
                    'due_date' => $lesson->lesson_date->addDays(rand(1, 7)),
                    'allows_submission' => $allowsSubmission,
                    'is_active' => true
                ]);
            }
        }
    }

    private function createRubrics()
    {
        $activities = Activity::where('allows_submission', true)->get();

        if ($activities->isEmpty()) {
            $this->command->warn('No activities with submissions found for rubrics.');
            return;
        }

        foreach ($activities as $activity) {
            // Create 3 rubric categories per activity
            for ($i = 1; $i <= 3; $i++) {
                $rubricData = $this->getRubricData($activity->title, $i);
                
                ActivityRubric::create([
                    'activity_id' => $activity->id,
                    'category_name' => $rubricData['category'],
                    'description' => $rubricData['description'],
                    'max_score' => $rubricData['max_score'],
                    'weight' => $rubricData['weight'],
                    'is_active' => true
                ]);
            }
        }
    }

    private function getActivityTitle($lessonTitle, $index)
    {
        $titles = [
            'Introduction to Fractions' => [
                'Fraction Identification Practice',
                'Fraction Comparison Exercise'
            ],
            'Reading Short Stories' => [
                'Story Analysis Worksheet',
                'Character Development Essay'
            ],
            'Scientific Method Lab' => [
                'Experiment Design Project',
                'Lab Report Writing'
            ]
        ];

        $lessonTitles = $titles[$lessonTitle] ?? [
            'Activity ' . $index,
            'Assignment ' . $index
        ];

        return $lessonTitles[$index - 1] ?? 'Activity ' . $index;
    }

    private function getActivityInstructions($lessonTitle, $index)
    {
        $instructions = [
            'Introduction to Fractions' => [
                'Identify and label different types of fractions from the provided examples.',
                'Compare the given fractions using <, >, or = symbols.'
            ],
            'Reading Short Stories' => [
                'Read the assigned short story and complete the analysis worksheet.',
                'Write a 2-page essay analyzing the main character\'s development.'
            ],
            'Scientific Method Lab' => [
                'Design an experiment to test your hypothesis.',
                'Write a comprehensive lab report following the scientific method format.'
            ]
        ];

        $lessonInstructions = $instructions[$lessonTitle] ?? [
            'Complete the assigned activity following the lesson guidelines.',
            'Submit your work according to the specified requirements.'
        ];

        return $lessonInstructions[$index - 1] ?? 'Complete this activity following the lesson guidelines.';
    }

    private function getRubricData($activityTitle, $index)
    {
        $rubrics = [
            'Fraction Identification Practice' => [
                ['category' => 'Accuracy', 'description' => 'Correct identification of fraction types', 'max_score' => 25, 'weight' => 1],
                ['category' => 'Completeness', 'description' => 'All problems attempted and completed', 'max_score' => 25, 'weight' => 1],
                ['category' => 'Presentation', 'description' => 'Clear and organized work presentation', 'max_score' => 20, 'weight' => 1]
            ],
            'Story Analysis Worksheet' => [
                ['category' => 'Comprehension', 'description' => 'Accurate understanding of story elements', 'max_score' => 30, 'weight' => 2],
                ['category' => 'Analysis', 'description' => 'Deep analysis of literary elements', 'max_score' => 30, 'weight' => 2],
                ['category' => 'Evidence', 'description' => 'Use of textual evidence to support claims', 'max_score' => 25, 'weight' => 1]
            ]
        ];

        $activityRubrics = $rubrics[$activityTitle] ?? [
            ['category' => 'Content', 'description' => 'Quality and accuracy of content', 'max_score' => 30, 'weight' => 2],
            ['category' => 'Organization', 'description' => 'Logical structure and flow', 'max_score' => 25, 'weight' => 1],
            ['category' => 'Presentation', 'description' => 'Clear and professional presentation', 'max_score' => 25, 'weight' => 1]
        ];

        return $activityRubrics[$index - 1] ?? $activityRubrics[0];
    }
} 