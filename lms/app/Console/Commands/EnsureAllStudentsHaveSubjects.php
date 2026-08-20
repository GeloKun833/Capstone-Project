<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Student;
use App\Models\Enrollment;
use App\Models\Subject;
use App\Models\AcademicYear;
use App\Models\Semester;

class EnsureAllStudentsHaveSubjects extends Command
{
    protected $signature = 'ensure:students-have-subjects';
    protected $description = 'Ensure all students have their grade-level subjects enrolled';

    public function handle()
    {
        $this->info("=== Ensuring All Students Have Subjects ===");
        
        // Get all active students
        $students = Student::where('enrollment_status', 'active')->get();
        
        if ($students->count() === 0) {
            $this->error("No active students found!");
            return;
        }
        
        $this->info("Found {$students->count()} active students");
        
        // Get academic year and semester
        $academicYear = AcademicYear::latest()->first();
        $semester = Semester::latest()->first();
        
        if (!$academicYear || !$semester) {
            $this->error("No academic year or semester found!");
            return;
        }
        
        $this->info("Using Academic Year: {$academicYear->name}, Semester: {$semester->name}");
        
        $fixedCount = 0;
        
        foreach ($students as $student) {
            $this->info("\n--- Processing: {$student->first_name} {$student->last_name} ({$student->year_level}) ---");
            
            // Check current enrollments
            $currentEnrollments = Enrollment::where('student_id', $student->id)->count();
            $this->info("Current enrollments: {$currentEnrollments}");
            
            if ($currentEnrollments > 0) {
                $this->info("✅ Student already has enrollments");
                continue;
            }
            
            // Get subjects for grade level
            $gradeLevel = $student->year_level;
            $gradeSubjects = config("grade_subjects.{$gradeLevel}", []);
            
            if (empty($gradeSubjects)) {
                $this->warn("⚠️  No subjects configured for grade level: {$gradeLevel}");
                continue;
            }
            
            $this->info("📖 Found subjects for {$gradeLevel}: " . implode(', ', $gradeSubjects));
            
            $enrolledCount = 0;
            foreach ($gradeSubjects as $subjectName) {
                // Find or create subject
                $subject = Subject::where('subject_name', $subjectName)
                    ->where('class', $gradeLevel)
                    ->first();
                
                if (!$subject) {
                    $subject = Subject::create([
                        'subject_name' => $subjectName,
                        'class' => $gradeLevel,
                    ]);
                    $this->info("   Created subject: {$subjectName}");
                }
                
                // Create enrollment record
                Enrollment::create([
                    'student_id' => $student->id,
                    'subject_id' => $subject->id,
                    'academic_year_id' => $academicYear->id,
                    'semester_id' => $semester->id,
                    'enrollment_date' => now(),
                    'status' => 'active',
                ]);
                
                $enrolledCount++;
            }
            
            $this->info("✅ Created {$enrolledCount} enrollments for {$student->first_name}");
            $fixedCount++;
        }
        
        $this->info("\n=== Summary ===");
        $this->info("✅ Fixed enrollments for: {$fixedCount} students");
        $this->info("All students should now have their grade-level subjects!");
    }
}

