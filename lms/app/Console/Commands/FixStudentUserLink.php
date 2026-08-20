<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Student;

class FixStudentUserLink extends Command
{
    protected $signature = 'fix:student-user-link {email}';
    protected $description = 'Fix the user_id link for a student';

    public function handle()
    {
        $email = $this->argument('email');
        
        $this->info("Fixing user_id link for: {$email}");
        
        // Find user
        $user = User::where('email', $email)->first();
        if (!$user) {
            $this->error("User not found with email: {$email}");
            return;
        }
        
        $this->info("Found user: {$user->name} (ID: {$user->user_id})");
        
        // Find the correct student record (the latest one)
        $student = Student::where('email', $email)
            ->where('enrollment_status', 'active')
            ->latest()
            ->first();
            
        if (!$student) {
            $this->error("No active student found with email: {$email}");
            return;
        }
        
        $this->info("Found student: {$student->first_name} {$student->last_name} (ID: {$student->id})");
        
        // Update the student record with the correct user_id
        $student->update(['user_id' => $user->user_id]);
        
        $this->info("✅ Updated student record with user_id: {$user->user_id}");
        
        // Verify the fix
        $updatedStudent = Student::find($student->id);
        $this->info("Verification - Student user_id: {$updatedStudent->user_id}");
        
        // Test the relationship
        $testUser = User::where('email', $email)->first();
        $testStudent = $testUser->student;
        
        if ($testStudent) {
            $this->info("✅ User->student relationship now works!");
            $this->info("   Student: {$testStudent->first_name} {$testStudent->last_name}");
        } else {
            $this->error("❌ User->student relationship still not working");
        }
        
        $this->info("Fix completed!");
    }
}
