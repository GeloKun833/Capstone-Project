<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Student;
use App\Models\EnrollmentApplication;
use App\Models\Enrollment;
use App\Models\Subject;
use App\Models\AcademicYear;
use App\Models\Semester;

class FixStudentProfile extends Command
{
    protected $signature = 'fix:student-profile {email}';
    protected $description = 'Fix student profile for a given email';

    public function handle()
    {
        $email = $this->argument('email');
        
        $this->info("Checking student profile for: {$email}");
        
        // Find user
        $user = User::where('email', $email)->first();
        if (!$user) {
            $this->error("User not found with email: {$email}");
            return;
        }
        
        $this->info("Found user: {$user->name} (ID: {$user->user_id})");
        
        // Check if student exists
        $student = Student::where('user_id', $user->user_id)->first();
        if (!$student) {
            $this->info("Student profile not found. Checking for enrollment application...");
            
            // Find enrollment application
            $application = EnrollmentApplication::where('email', $email)->first();
            if (!$application) {
                $this->error("No enrollment application found for email: {$email}");
                return;
            }
            
            $this->info("Found enrollment application: {$application->application_number}");
            
            // Create student record
            $student = Student::create([
                'user_id' => $user->user_id,
                'first_name' => $application->first_name,
                'last_name' => $application->last_name,
                'middle_name' => $application->middle_name,
                'gender' => $application->gender,
                'date_of_birth' => $application->date_of_birth,
                'email' => $application->email,
                'phone_number' => $application->phone_number,
                'address' => $application->address,
                'parent_email' => $application->parent_email,
                'parent_name' => $application->parent_name,
                'parent_phone' => $application->parent_phone,
                'parent_relationship' => $application->parent_relationship,
                'emergency_contact_name' => $application->emergency_contact_name,
                'emergency_contact_phone' => $application->emergency_contact_phone,
                'previous_school' => $application->previous_school,
                'enrollment_application_id' => $application->id,
                'enrollment_status' => 'active',
                'year_level' => $application->grade_level_applying_for,
            ]);
            
            $this->info("Created student profile: {$student->first_name} {$student->last_name}");
        } else {
            $this->info("Student profile already exists: {$student->first_name} {$student->last_name}");
        }
        
        // Check enrollments
        $enrollments = Enrollment::where('student_id', $student->id)->count();
        $this->info("Current enrollments: {$enrollments}");
        
        if ($enrollments === 0) {
            $this->info("No enrollments found. Creating enrollments...");
            
            // Get academic year and semester
            $academicYear = AcademicYear::latest()->first();
            $semester = Semester::latest()->first();
            
            if (!$academicYear || !$semester) {
                $this->error("No academic year or semester found!");
                return;
            }
            
            $this->info("Using Academic Year: {$academicYear->name}, Semester: {$semester->name}");
            
            // Get subjects for grade level
            $gradeLevel = $student->year_level;
            $gradeSubjects = config("grade_subjects.{$gradeLevel}", []);
            
            if (empty($gradeSubjects)) {
                $this->error("No subjects configured for grade level: {$gradeLevel}");
                return;
            }
            
            $this->info("Found subjects for {$gradeLevel}: " . implode(', ', $gradeSubjects));
            
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
                    $this->info("Created subject: {$subjectName}");
                }
                
                // Check if already enrolled
                $existingEnrollment = Enrollment::where([
                    'student_id' => $student->id,
                    'subject_id' => $subject->id,
                    'academic_year_id' => $academicYear->id,
                    'semester_id' => $semester->id,
                ])->first();
                
                if (!$existingEnrollment) {
                    Enrollment::create([
                        'student_id' => $student->id,
                        'subject_id' => $subject->id,
                        'academic_year_id' => $academicYear->id,
                        'semester_id' => $semester->id,
                        'enrollment_date' => now(),
                        'status' => 'active',
                    ]);
                    $enrolledCount++;
                    $this->info("Enrolled in: {$subjectName}");
                }
            }
            
            $this->info("Created {$enrolledCount} new enrollments");
        }
        
        // Final check
        $finalEnrollments = Enrollment::where('student_id', $student->id)->count();
        $this->info("Total enrollments now: {$finalEnrollments}");
        
        $this->info("Student profile fix completed!");
    }
}