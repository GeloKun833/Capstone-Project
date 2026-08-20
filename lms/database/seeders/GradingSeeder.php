<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SubjectComponent;
use App\Models\WeightSetting;
use App\Models\Grade;
use App\Models\StudentGpa;
use App\Models\GradeAlert;
use App\Models\Subject;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\AcademicYear;
use App\Models\Semester;
use App\Models\Section;

class GradingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get or create academic year and semester
        $academicYear = AcademicYear::firstOrCreate([
            'name' => '2024-2025'
        ], [
            'start_date' => '2024-06-01',
            'end_date' => '2025-05-31'
        ]);

        $semester1 = Semester::firstOrCreate([
            'name' => 'First Semester'
        ], [
            'academic_year_id' => $academicYear->id
        ]);

        $semester2 = Semester::firstOrCreate([
            'name' => 'Second Semester'
        ], [
            'academic_year_id' => $academicYear->id
        ]);

        // Get existing subjects or create sample ones
        $subjects = Subject::take(5)->get();
        if ($subjects->isEmpty()) {
            // Create sample subjects manually
            $subjects = collect([
                Subject::create(['subject_name' => 'Mathematics', 'class' => 'Grade 10']),
                Subject::create(['subject_name' => 'English', 'class' => 'Grade 10']),
                Subject::create(['subject_name' => 'Science', 'class' => 'Grade 10']),
                Subject::create(['subject_name' => 'History', 'class' => 'Grade 10']),
                Subject::create(['subject_name' => 'Computer Science', 'class' => 'Grade 10']),
            ]);
        }

        // Create subject components for each subject
        foreach ($subjects as $subject) {
            // Create grading components
            $components = [
                ['name' => 'Quiz', 'weight' => 20, 'description' => 'Weekly quizzes'],
                ['name' => 'Assignment', 'weight' => 25, 'description' => 'Homework assignments'],
                ['name' => 'Midterm Exam', 'weight' => 25, 'description' => 'Midterm examination'],
                ['name' => 'Final Exam', 'weight' => 30, 'description' => 'Final examination'],
            ];

            foreach ($components as $componentData) {
                SubjectComponent::firstOrCreate([
                    'name' => $componentData['name'],
                    'subject_id' => $subject->id
                ], [
                    'description' => $componentData['description'],
                    'weight' => $componentData['weight'],
                    'is_active' => true
                ]);
            }
        }

        // Get existing students and teachers
        $students = Student::take(10)->get();
        $teachers = Teacher::take(3)->get();

        if ($students->isEmpty()) {
            // Create sample students manually
            $students = collect([
                Student::create(['name' => 'John Doe', 'email' => 'john@example.com', 'phone' => '1234567890']),
                Student::create(['name' => 'Jane Smith', 'email' => 'jane@example.com', 'phone' => '1234567891']),
                Student::create(['name' => 'Mike Johnson', 'email' => 'mike@example.com', 'phone' => '1234567892']),
                Student::create(['name' => 'Sarah Wilson', 'email' => 'sarah@example.com', 'phone' => '1234567893']),
                Student::create(['name' => 'David Brown', 'email' => 'david@example.com', 'phone' => '1234567894']),
            ]);
        }

        if ($teachers->isEmpty()) {
            // Create sample teachers manually
            $teachers = collect([
                Teacher::create(['name' => 'Prof. Smith', 'email' => 'smith@example.com', 'phone' => '0987654321']),
                Teacher::create(['name' => 'Prof. Johnson', 'email' => 'johnson@example.com', 'phone' => '0987654322']),
                Teacher::create(['name' => 'Prof. Davis', 'email' => 'davis@example.com', 'phone' => '0987654323']),
            ]);
        }

        // Get or create a section
        $section = Section::firstOrCreate([
            'name' => 'Section A'
        ], [
            'description' => 'First year section A',
            'capacity' => 30
        ]);

        // Create sample grades for students
        foreach ($students as $student) {
            foreach ($subjects as $subject) {
                $components = $subject->components;
                $teacher = $teachers->random();

                foreach ($components as $component) {
                    // Create grades for both semesters
                    foreach ([$semester1, $semester2] as $semester) {
                        $score = rand(60, 100); // Random score between 60-100
                        $maxScore = 100;

                        Grade::firstOrCreate([
                            'student_id' => $student->id,
                            'subject_id' => $subject->id,
                            'component_id' => $component->id,
                            'academic_year_id' => $academicYear->id,
                            'semester_id' => $semester->id,
                        ], [
                            'teacher_id' => $teacher->id,
                            'score' => $score,
                            'max_score' => $maxScore,
                            'percentage' => ($score / $maxScore) * 100,
                            'remarks' => $score >= 90 ? 'Excellent' : ($score >= 80 ? 'Good' : ($score >= 70 ? 'Satisfactory' : 'Needs Improvement')),
                            'grading_period' => $semester->name
                        ]);
                    }
                }
            }
        }

        // Create weight settings for subjects
        foreach ($subjects as $subject) {
            $components = $subject->components;
            foreach ($components as $component) {
                WeightSetting::firstOrCreate([
                    'subject_id' => $subject->id,
                    'component_id' => $component->id,
                    'academic_year_id' => $academicYear->id,
                    'semester_id' => $semester1->id,
                ], [
                    'weight' => $component->weight,
                    'is_active' => true
                ]);

                WeightSetting::firstOrCreate([
                    'subject_id' => $subject->id,
                    'component_id' => $component->id,
                    'academic_year_id' => $academicYear->id,
                    'semester_id' => $semester2->id,
                ], [
                    'weight' => $component->weight,
                    'is_active' => true
                ]);
            }
        }

        // Create sample GPA records
        foreach ($students as $student) {
            foreach ([$semester1, $semester2] as $semester) {
                $grades = $student->grades()
                    ->where('academic_year_id', $academicYear->id)
                    ->where('semester_id', $semester->id)
                    ->get();

                if ($grades->count() > 0) {
                    $totalGradePoints = 0;
                    $totalUnits = 0;

                    foreach ($grades as $grade) {
                        if ($grade->percentage !== null) {
                            $gradePoints = $this->percentageToGradePoints($grade->percentage);
                            $totalGradePoints += $gradePoints;
                            $totalUnits += 1;
                        }
                    }

                    $gpa = $totalUnits > 0 ? $totalGradePoints / $totalUnits : 0;

                    StudentGpa::firstOrCreate([
                        'student_id' => $student->id,
                        'academic_year_id' => $academicYear->id,
                        'semester_id' => $semester->id,
                    ], [
                        'gpa' => round($gpa, 2),
                        'total_units' => $totalUnits,
                        'total_grade_points' => $totalGradePoints,
                        'remarks' => $gpa >= 3.5 ? 'Dean\'s List' : ($gpa >= 3.0 ? 'Good Standing' : 'Academic Warning')
                    ]);
                }
            }
        }

        // Create sample grade alerts
        foreach ($students as $student) {
            $lowGrades = $student->grades()
                ->where('academic_year_id', $academicYear->id)
                ->where('percentage', '<', 75)
                ->get();

            foreach ($lowGrades as $grade) {
                GradeAlert::firstOrCreate([
                    'student_id' => $student->id,
                    'subject_id' => $grade->subject_id,
                    'alert_type' => 'low_grade',
                    'academic_year_id' => $academicYear->id,
                    'semester_id' => $grade->semester_id,
                ], [
                    'message' => "Low grade in {$grade->subject->subject_name}: {$grade->percentage}%",
                    'threshold_value' => 75,
                    'current_value' => $grade->percentage,
                    'is_resolved' => false
                ]);
            }

            // Create some performance drop alerts
            if (rand(1, 10) <= 3) { // 30% chance
                GradeAlert::firstOrCreate([
                    'student_id' => $student->id,
                    'alert_type' => 'performance_drop',
                    'academic_year_id' => $academicYear->id,
                    'semester_id' => $semester2->id,
                ], [
                    'message' => "Significant performance drop detected for {$student->first_name} {$student->last_name}",
                    'threshold_value' => 80,
                    'current_value' => rand(65, 75),
                    'is_resolved' => false
                ]);
            }
        }

        $this->command->info('Grading module sample data seeded successfully!');
    }

    private function percentageToGradePoints($percentage)
    {
        if ($percentage >= 90) return 4.0;
        if ($percentage >= 85) return 3.7;
        if ($percentage >= 80) return 3.3;
        if ($percentage >= 75) return 3.0;
        if ($percentage >= 70) return 2.7;
        if ($percentage >= 65) return 2.3;
        if ($percentage >= 60) return 2.0;
        if ($percentage >= 55) return 1.7;
        if ($percentage >= 50) return 1.3;
        if ($percentage >= 45) return 1.0;
        return 0.0;
    }
} 