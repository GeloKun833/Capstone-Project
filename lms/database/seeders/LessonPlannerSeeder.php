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
use Carbon\Carbon;

class LessonPlannerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get existing data
        $subjects = Subject::all();
        $sections = Section::all();
        $academicYears = AcademicYear::all();
        $semesters = Semester::all();
        $teachers = Teacher::all();

        if ($subjects->isEmpty() || $sections->isEmpty() || $academicYears->isEmpty() || $semesters->isEmpty() || $teachers->isEmpty()) {
            $this->command->warn('Required data not found. Please run other seeders first.');
            return;
        }

        // Create Curriculum Objectives
        $this->createCurriculumObjectives($subjects, $sections);

        // Create Lessons
        $this->createLessons($subjects, $sections, $academicYears, $semesters, $teachers);

        // Create Activities
        $this->createActivities();

        // Create Rubrics
        $this->createRubrics();

        $this->command->info('Lesson Planner sample data seeded successfully!');
    }

    private function createCurriculumObjectives($subjects, $sections)
    {
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
                'code' => 'SCI-002',
                'title' => 'Ecosystems and Biodiversity',
                'description' => 'Students will learn about different ecosystems and the importance of biodiversity.',
                'subject_name' => 'Science'
            ],
            [
                'code' => 'HIST-001',
                'title' => 'Ancient Civilizations',
                'description' => 'Students will explore the history and contributions of ancient civilizations.',
                'subject_name' => 'History'
            ],
            [
                'code' => 'HIST-002',
                'title' => 'World Wars',
                'description' => 'Students will understand the causes and effects of World War I and II.',
                'subject_name' => 'History'
            ]
        ];

        foreach ($objectives as $objective) {
            $subject = $subjects->where('subject_name', $objective['subject_name'])->first();
            if ($subject) {
                $section = $sections->first(); // Use first section for simplicity
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

    private function createLessons($subjects, $sections, $academicYears, $semesters, $teachers)
    {
        $lessons = [
            [
                'title' => 'Introduction to Fractions',
                'description' => 'This lesson introduces students to the concept of fractions, including proper fractions, improper fractions, and mixed numbers. Students will learn to identify, compare, and perform basic operations with fractions.',
                'subject_name' => 'Mathematics',
                'curriculum_code' => 'MATH-002',
                'status' => 'published'
            ],
            [
                'title' => 'Reading Short Stories',
                'description' => 'Students will read and analyze short stories to develop comprehension skills, identify literary elements, and practice critical thinking through discussion and written responses.',
                'subject_name' => 'English',
                'curriculum_code' => 'ENG-001',
                'status' => 'published'
            ],
            [
                'title' => 'Scientific Method Lab',
                'description' => 'Hands-on laboratory session where students will design and conduct experiments using the scientific method, including hypothesis formation, data collection, and analysis.',
                'subject_name' => 'Science',
                'curriculum_code' => 'SCI-001',
                'status' => 'draft'
            ],
            [
                'title' => 'Ancient Egypt Civilization',
                'description' => 'Exploration of Ancient Egyptian civilization, including their government, religion, architecture, and daily life. Students will examine primary sources and artifacts.',
                'subject_name' => 'History',
                'curriculum_code' => 'HIST-001',
                'status' => 'published'
            ],
            [
                'title' => 'Essay Writing Workshop',
                'description' => 'Interactive workshop focusing on essay structure, thesis development, and persuasive writing techniques. Students will practice writing and peer review.',
                'subject_name' => 'English',
                'curriculum_code' => 'ENG-002',
                'status' => 'completed'
            ]
        ];

        foreach ($lessons as $lessonData) {
            $subject = $subjects->where('subject_name', $lessonData['subject_name'])->first();
            $curriculumObjective = CurriculumObjective::where('code', $lessonData['curriculum_code'])->first();
            
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

        foreach ($lessons as $lesson) {
            // Create 2-3 activities per lesson
            $activityCount = rand(2, 3);
            
            for ($i = 1; $i <= $activityCount; $i++) {
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

        foreach ($activities as $activity) {
            // Create 3-4 rubric categories per activity
            $rubricCount = rand(3, 4);
            
            for ($i = 1; $i <= $rubricCount; $i++) {
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
                'Fraction Comparison Exercise',
                'Fraction Operations Quiz'
            ],
            'Reading Short Stories' => [
                'Story Analysis Worksheet',
                'Character Development Essay',
                'Plot Summary Assignment'
            ],
            'Scientific Method Lab' => [
                'Experiment Design Project',
                'Data Collection Exercise',
                'Lab Report Writing'
            ],
            'Ancient Egypt Civilization' => [
                'Egyptian Artifact Research',
                'Pharaoh Timeline Project',
                'Pyramid Construction Model'
            ],
            'Essay Writing Workshop' => [
                'Thesis Statement Practice',
                'Essay Outline Creation',
                'Final Essay Submission'
            ]
        ];

        $lessonTitles = $titles[$lessonTitle] ?? [
            'Activity ' . $index,
            'Assignment ' . $index,
            'Exercise ' . $index
        ];

        return $lessonTitles[$index - 1] ?? 'Activity ' . $index;
    }

    private function getActivityInstructions($lessonTitle, $index)
    {
        $instructions = [
            'Introduction to Fractions' => [
                'Identify and label different types of fractions from the provided examples. Show your work clearly.',
                'Compare the given fractions using <, >, or = symbols. Explain your reasoning.',
                'Complete the fraction operations (addition, subtraction, multiplication, division) step by step.'
            ],
            'Reading Short Stories' => [
                'Read the assigned short story and complete the analysis worksheet with your observations.',
                'Write a 2-page essay analyzing the main character\'s development throughout the story.',
                'Create a detailed plot summary including the exposition, rising action, climax, falling action, and resolution.'
            ],
            'Scientific Method Lab' => [
                'Design an experiment to test your hypothesis. Include materials, procedure, and expected outcomes.',
                'Collect data during the experiment and record your observations in the provided data table.',
                'Write a comprehensive lab report following the scientific method format.'
            ],
            'Ancient Egypt Civilization' => [
                'Research and create a presentation about a specific Egyptian artifact. Include its historical significance.',
                'Create a timeline of major pharaohs and their contributions to Egyptian civilization.',
                'Build a model of an Egyptian pyramid using appropriate materials and include a written explanation.'
            ],
            'Essay Writing Workshop' => [
                'Practice writing clear and concise thesis statements for the given essay topics.',
                'Create detailed outlines for your essays including main points and supporting evidence.',
                'Write a complete essay following the structure and guidelines discussed in class.'
            ]
        ];

        $lessonInstructions = $instructions[$lessonTitle] ?? [
            'Complete the assigned activity following the lesson guidelines.',
            'Submit your work according to the specified requirements.',
            'Demonstrate your understanding of the lesson concepts.'
        ];

        return $lessonInstructions[$index - 1] ?? 'Complete this activity following the lesson guidelines.';
    }

    private function getRubricData($activityTitle, $index)
    {
        $rubrics = [
            'Fraction Identification Practice' => [
                ['category' => 'Accuracy', 'description' => 'Correct identification of fraction types', 'max_score' => 25, 'weight' => 1],
                ['category' => 'Completeness', 'description' => 'All problems attempted and completed', 'max_score' => 25, 'weight' => 1],
                ['category' => 'Presentation', 'description' => 'Clear and organized work presentation', 'max_score' => 20, 'weight' => 1],
                ['category' => 'Understanding', 'description' => 'Demonstrated understanding of concepts', 'max_score' => 30, 'weight' => 2]
            ],
            'Story Analysis Worksheet' => [
                ['category' => 'Comprehension', 'description' => 'Accurate understanding of story elements', 'max_score' => 30, 'weight' => 2],
                ['category' => 'Analysis', 'description' => 'Deep analysis of literary elements', 'max_score' => 30, 'weight' => 2],
                ['category' => 'Evidence', 'description' => 'Use of textual evidence to support claims', 'max_score' => 25, 'weight' => 1],
                ['category' => 'Clarity', 'description' => 'Clear and coherent written responses', 'max_score' => 15, 'weight' => 1]
            ],
            'Experiment Design Project' => [
                ['category' => 'Scientific Method', 'description' => 'Proper application of scientific method', 'max_score' => 30, 'weight' => 2],
                ['category' => 'Creativity', 'description' => 'Original and innovative experiment design', 'max_score' => 25, 'weight' => 1],
                ['category' => 'Feasibility', 'description' => 'Practical and achievable experiment', 'max_score' => 25, 'weight' => 1],
                ['category' => 'Documentation', 'description' => 'Clear documentation of procedures', 'max_score' => 20, 'weight' => 1]
            ]
        ];

        $activityRubrics = $rubrics[$activityTitle] ?? [
            ['category' => 'Content', 'description' => 'Quality and accuracy of content', 'max_score' => 30, 'weight' => 2],
            ['category' => 'Organization', 'description' => 'Logical structure and flow', 'max_score' => 25, 'weight' => 1],
            ['category' => 'Presentation', 'description' => 'Clear and professional presentation', 'max_score' => 25, 'weight' => 1],
            ['category' => 'Effort', 'description' => 'Demonstrated effort and engagement', 'max_score' => 20, 'weight' => 1]
        ];

        return $activityRubrics[$index - 1] ?? $activityRubrics[0];
    }
} 