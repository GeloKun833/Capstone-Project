<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\Subject;
use App\Models\Section;
use App\Models\AcademicYear;
use App\Models\Semester;
use App\Models\Grade;
use App\Models\Attendance;
use App\Models\SubjectComponent;
use App\Models\StudentGpa;
use Carbon\Carbon;

class AnalyticsDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create academic year and semester if they don't exist
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

        // Create sections if they don't exist
        $sections = [];
        for ($i = 1; $i <= 3; $i++) {
            $sections[] = Section::firstOrCreate([
                'name' => "Section A-{$i}"
            ], [
                'grade_level' => 10,
                'capacity' => 30,
                'description' => "Section A-{$i} description"
            ]);
        }

        // Create subjects if they don't exist
        $subjects = [];
        $subjectNames = ['Mathematics', 'English', 'Science', 'History', 'Geography'];
        foreach ($subjectNames as $name) {
            $subjects[] = Subject::firstOrCreate([
                'subject_name' => $name
            ], [
                'class' => 'Grade 10'
            ]);
        }

        // Create subject components
        $components = [];
        $componentNames = ['Quiz', 'Exam', 'Assignment', 'Project'];
        foreach ($componentNames as $name) {
            $components[] = SubjectComponent::firstOrCreate([
                'name' => $name
            ], [
                'description' => "{$name} component description",
                'weight' => 25.00, // Equal weight for each component
                'is_active' => true
            ]);
        }

        // Create students if they don't exist
        $students = [];
        for ($i = 1; $i <= 20; $i++) {
            $students[] = Student::firstOrCreate([
                'email' => "student{$i}@example.com"
            ], [
                'first_name' => "Student",
                'last_name' => "{$i}",
                'gender' => $i % 2 == 0 ? 'Male' : 'Female',
                'date_of_birth' => Carbon::now()->subYears(15 + ($i % 5)),
                'phone_number' => "123456789{$i}",
                'class' => 'Grade 10',
                'year_level' => 10
            ]);
        }

        // Create teachers if they don't exist
        $teachers = [];
        for ($i = 1; $i <= 5; $i++) {
            $teachers[] = Teacher::firstOrCreate([
                'full_name' => "Teacher {$i}"
            ], [
                'phone_number' => "987654321{$i}",
                'address' => "Teacher Address {$i}",
                'city' => 'Sample City',
                'state' => 'Sample State',
                'country' => 'Sample Country',
                'gender' => $i % 2 == 0 ? 'Male' : 'Female',
                'qualification' => 'Bachelor\'s Degree',
                'experience' => rand(1, 10) . ' years'
            ]);
        }

        // Assign students to sections
        foreach ($students as $index => $student) {
            $section = $sections[$index % count($sections)];
            $student->sections()->syncWithoutDetaching([$section->id]);
        }

        // Assign teachers to subjects
        foreach ($teachers as $index => $teacher) {
            $subject = $subjects[$index % count($subjects)];
            $teacher->subjects()->syncWithoutDetaching([$subject->id]);
        }

        // Generate grades
        $this->generateGrades($students, $subjects, $components, $academicYear, $semester1);
        $this->generateGrades($students, $subjects, $components, $academicYear, $semester2);

        // Generate attendance
        $this->generateAttendance($students, $sections, $academicYear, $semester1);
        $this->generateAttendance($students, $sections, $academicYear, $semester2);

        // Generate GPA records
        $this->generateGpaRecords($students, $academicYear, $semester1);
        $this->generateGpaRecords($students, $academicYear, $semester2);

        $this->command->info('Analytics sample data seeded successfully!');
    }

    /**
     * Generate sample grades
     */
    private function generateGrades($students, $subjects, $components, $academicYear, $semester)
    {
        // Get a teacher for each subject
        $teachers = Teacher::all();
        if ($teachers->isEmpty()) {
            return; // No teachers available
        }

        foreach ($students as $student) {
            foreach ($subjects as $subject) {
                // Get a random teacher for this subject
                $teacher = $teachers->random();
                
                foreach ($components as $component) {
                    // Generate 2-4 grades per component per subject
                    $numGrades = rand(2, 4);
                    for ($i = 0; $i < $numGrades; $i++) {
                        $maxScore = 100;
                        $score = rand(60, 100); // Random score between 60-100
                        $percentage = ($score / $maxScore) * 100;
                        
                        Grade::create([
                            'student_id' => $student->id,
                            'subject_id' => $subject->id,
                            'teacher_id' => $teacher->id,
                            'component_id' => $component->id,
                            'score' => $score,
                            'max_score' => $maxScore,
                            'percentage' => $percentage,
                            'academic_year_id' => $academicYear->id,
                            'semester_id' => $semester->id,
                            'created_at' => Carbon::now()->subDays(rand(1, 90))
                        ]);
                    }
                }
            }
        }
    }

    /**
     * Generate sample attendance
     */
    private function generateAttendance($students, $sections, $academicYear, $semester)
    {
        // Use hardcoded date ranges since semester model doesn't have start/end dates
        if ($semester->name === 'First Semester') {
            $startDate = Carbon::parse('2024-06-01');
            $endDate = Carbon::parse('2024-10-31');
        } else {
            $startDate = Carbon::parse('2024-11-01');
            $endDate = Carbon::parse('2025-03-31');
        }
        
        // Get teachers and subjects for attendance
        $teachers = Teacher::all();
        $subjects = Subject::all();
        
        if ($teachers->isEmpty() || $subjects->isEmpty()) {
            return; // No teachers or subjects available
        }
        
        foreach ($students as $student) {
            $currentDate = $startDate->copy();
            while ($currentDate <= $endDate) {
                // Skip weekends
                if ($currentDate->isWeekend()) {
                    $currentDate->addDay();
                    continue;
                }

                // 90% attendance rate
                $status = rand(1, 100) <= 90 ? 'present' : 'absent';
                
                // Get random teacher and subject for this attendance record
                $teacher = $teachers->random();
                $subject = $subjects->random();
                
                Attendance::create([
                    'student_id' => $student->id,
                    'subject_id' => $subject->id,
                    'teacher_id' => $teacher->id,
                    'date' => $currentDate->format('Y-m-d'),
                    'status' => $status,
                    'created_at' => $currentDate
                ]);

                $currentDate->addDay();
            }
        }
    }

    /**
     * Generate sample GPA records
     */
    private function generateGpaRecords($students, $academicYear, $semester)
    {
        foreach ($students as $student) {
            // Calculate GPA based on grades
            $grades = Grade::where('student_id', $student->id)
                ->where('academic_year_id', $academicYear->id)
                ->where('semester_id', $semester->id)
                ->get();

            if ($grades->count() > 0) {
                $averageScore = $grades->avg('percentage');
                $gpa = $this->percentageToGpa($averageScore);
                $totalUnits = $grades->count();
                $totalGradePoints = $gpa * $totalUnits;

                StudentGpa::create([
                    'student_id' => $student->id,
                    'academic_year_id' => $academicYear->id,
                    'semester_id' => $semester->id,
                    'gpa' => $gpa,
                    'total_units' => $totalUnits,
                    'total_grade_points' => $totalGradePoints,
                    'rank' => rand(1, 20), // Random rank for demo
                    'remarks' => $this->getGpaRemarks($gpa),
                    'created_at' => Carbon::now()
                ]);
            }
        }
    }

    /**
     * Get GPA remarks
     */
    private function getGpaRemarks($gpa)
    {
        if ($gpa >= 3.5) return 'Excellent performance';
        if ($gpa >= 3.0) return 'Good performance';
        if ($gpa >= 2.5) return 'Satisfactory performance';
        if ($gpa >= 2.0) return 'Needs improvement';
        return 'Failing - requires immediate attention';
    }

    /**
     * Convert percentage to GPA
     */
    private function percentageToGpa($percentage)
    {
        if ($percentage >= 90) return 4.0;
        if ($percentage >= 85) return 3.7;
        if ($percentage >= 80) return 3.3;
        if ($percentage >= 75) return 3.0;
        if ($percentage >= 70) return 2.7;
        if ($percentage >= 65) return 2.3;
        if ($percentage >= 60) return 2.0;
        return 0.0;
    }

    /**
     * Convert percentage to letter grade
     */
    private function percentageToLetterGrade($percentage)
    {
        if ($percentage >= 90) return 'A';
        if ($percentage >= 85) return 'B+';
        if ($percentage >= 80) return 'B';
        if ($percentage >= 75) return 'C+';
        if ($percentage >= 70) return 'C';
        if ($percentage >= 65) return 'D+';
        if ($percentage >= 60) return 'D';
        return 'F';
    }
} 