<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Student;
use App\Models\User;

class ListStudents extends Command
{
    protected $signature = 'list:students';
    protected $description = 'List all students';

    public function handle()
    {
        $this->info("=== All Students ===");
        
        $students = Student::all();
        
        if ($students->count() === 0) {
            $this->error("No students found!");
            return;
        }
        
        foreach ($students as $student) {
            $this->info("Student ID: {$student->id}");
            $this->info("  - Name: {$student->first_name} {$student->last_name}");
            $this->info("  - User ID: {$student->user_id}");
            $this->info("  - Email: {$student->email}");
            $this->info("  - Grade Level: {$student->year_level}");
            $this->info("  - Status: {$student->enrollment_status}");
            $this->info("  ---");
        }
        
        $this->info("\n=== All Users ===");
        
        $users = User::where('role_name', 'Student')->get();
        
        foreach ($users as $user) {
            $this->info("User ID: {$user->user_id}");
            $this->info("  - Name: {$user->name}");
            $this->info("  - Email: {$user->email}");
            $this->info("  - Role: {$user->role_name}");
            $this->info("  ---");
        }
    }
}
