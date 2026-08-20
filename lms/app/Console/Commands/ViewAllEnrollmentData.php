<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\EnrollmentApplication;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ViewAllEnrollmentData extends Command
{
    protected $signature = 'view:enrollment-data';
    protected $description = 'View all enrollment-related data including soft-deleted records';

    public function handle()
    {
        $this->info("=== Enrollment Applications (Active) ===");
        $apps = EnrollmentApplication::all();
        if ($apps->count() > 0) {
            foreach ($apps as $app) {
                $this->info("ID: {$app->id} - {$app->full_name} ({$app->email}) - Status: {$app->status}");
            }
        } else {
            $this->warn("No active applications found.");
        }
        
        $this->newLine();
        $this->info("=== Enrollment Applications (Soft Deleted) ===");
        $deletedApps = EnrollmentApplication::onlyTrashed()->get();
        if ($deletedApps->count() > 0) {
            foreach ($deletedApps as $app) {
                $this->info("ID: {$app->id} - {$app->full_name} ({$app->email}) - Status: {$app->status} - Deleted: {$app->deleted_at}");
            }
        } else {
            $this->warn("No soft-deleted applications found.");
        }
        
        $this->newLine();
        $this->info("=== Students from Portal ===");
        $students = Student::whereNotNull('enrollment_application_id')->get();
        if ($students->count() > 0) {
            foreach ($students as $student) {
                $this->info("ID: {$student->id} - {$student->first_name} {$student->last_name} ({$student->email}) - Grade: {$student->year_level}");
            }
        } else {
            $this->warn("No portal students found.");
        }
        
        $this->newLine();
        $this->info("=== Users (Student Role) ===");
        $users = User::where('role_name', 'Student')->get();
        if ($users->count() > 0) {
            foreach ($users as $user) {
                $this->info("ID: {$user->user_id} - {$user->name} ({$user->email}) - Status: {$user->status}");
            }
        } else {
            $this->warn("No student users found.");
        }
    }
}

