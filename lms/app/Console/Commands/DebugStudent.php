<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Student;
use App\Models\Enrollment;

class DebugStudent extends Command
{
    protected $signature = 'debug:student {email}';
    protected $description = 'Debug student data for a given email';

    public function handle()
    {
        $email = $this->argument('email');
        
        $this->info("=== Debugging Student Data for: {$email} ===");
        
        // Check user
        $user = User::where('email', $email)->first();
        if (!$user) {
            $this->error("❌ User not found with email: {$email}");
            return;
        }
        
        $this->info("✅ User found:");
        $this->info("   - Name: {$user->name}");
        $this->info("   - User ID: {$user->user_id}");
        $this->info("   - Role: {$user->role_name}");
        $this->info("   - Status: {$user->status}");
        
        // Check student relationship
        $student = $user->student;
        if (!$student) {
            $this->warn("⚠️  User->student relationship returned null");
            
            // Try direct query
            $student = Student::where('user_id', $user->user_id)->first();
            if ($student) {
                $this->info("✅ Found student via direct query:");
                $this->info("   - Name: {$student->first_name} {$student->last_name}");
                $this->info("   - Student ID: {$student->id}");
                $this->info("   - Grade Level: {$student->year_level}");
                $this->info("   - Email: {$student->email}");
            } else {
                $this->error("❌ No student record found with user_id: {$user->user_id}");
                return;
            }
        } else {
            $this->info("✅ Student found via relationship:");
            $this->info("   - Name: {$student->first_name} {$student->last_name}");
            $this->info("   - Student ID: {$student->id}");
            $this->info("   - Grade Level: {$student->year_level}");
        }
        
        // Check enrollments
        $enrollments = Enrollment::where('student_id', $student->id)->get();
        $this->info("📚 Enrollments found: {$enrollments->count()}");
        
        if ($enrollments->count() > 0) {
            foreach ($enrollments as $enrollment) {
                $this->info("   - Subject ID: {$enrollment->subject_id}, Status: {$enrollment->status}");
            }
        }
        
        // Test HomeController logic
        $this->info("\n=== Testing HomeController Logic ===");
        
        // Simulate loadStudentData method
        $testStudent = $user->student;
        
        if (!$testStudent) {
            $testStudent = Student::where('user_id', $user->user_id)->first();
            
            if (!$testStudent) {
                $this->error("❌ HomeController would return: hasStudent = false");
                return;
            }
        }
        
        $testEnrollments = $testStudent->enrollments()
            ->with(['subject', 'academicYear', 'semester'])
            ->where('status', 'active')
            ->get();
            
        $this->info("✅ HomeController would return:");
        $this->info("   - hasStudent: true");
        $this->info("   - student: {$testStudent->first_name} {$testStudent->last_name}");
        $this->info("   - enrollments: {$testEnrollments->count()}");
        
        if ($testEnrollments->count() > 0) {
            $this->info("   - Active enrollments:");
            foreach ($testEnrollments as $enrollment) {
                $subjectName = $enrollment->subject ? $enrollment->subject->subject_name : 'Unknown Subject';
                $this->info("     * {$subjectName} (ID: {$enrollment->subject_id})");
            }
        }
        
        $this->info("\n=== Debug Complete ===");
    }
}
