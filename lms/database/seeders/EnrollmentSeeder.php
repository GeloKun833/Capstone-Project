<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Student;
use App\Models\Subject;
use App\Models\AcademicYear;
use App\Models\Semester;
use App\Models\Enrollment;

class EnrollmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $students = Student::all();
        $subjects = Subject::all();
        $academicYears = AcademicYear::all();
        $semesters = Semester::all();

        // Create sample enrollments
        foreach ($students as $student) {
            // Enroll each student in 4-6 random subjects
            $randomSubjects = $subjects->random(rand(4, 6));
            
            foreach ($randomSubjects as $subject) {
                Enrollment::create([
                    'student_id' => $student->id,
                    'subject_id' => $subject->id,
                    'academic_year_id' => $academicYears->random()->id,
                    'semester_id' => $semesters->random()->id,
                    'enrollment_date' => now(),
                    'status' => 'active',
                ]);
            }
        }

        $this->command->info('Sample enrollments created successfully!');
    }
} 