<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\EnrollmentApplication;
use App\Models\Student;
use App\Models\User;
use App\Models\Enrollment;
use Illuminate\Support\Facades\DB;

class DeleteEnrollmentApplications extends Command
{
    protected $signature = 'delete:enrollment-applications';
    protected $description = 'Delete all enrollment applications and related data';

    public function handle()
    {
        $this->info("=== Deleting Enrollment Applications ===");
        
        // Get all applications
        $applications = EnrollmentApplication::all();
        
        if ($applications->count() === 0) {
            $this->error("No enrollment applications found!");
            return;
        }
        
        $this->info("Found {$applications->count()} enrollment application(s):");
        $this->newLine();
        
        foreach ($applications as $app) {
            $this->info("ID: {$app->id} - {$app->full_name} ({$app->email}) - Status: {$app->status}");
        }
        
        $this->newLine();
        
        if (!$this->confirm('Do you want to delete ALL enrollment applications and related student accounts?')) {
            $this->info('Operation cancelled.');
            return;
        }
        
        DB::beginTransaction();
        
        try {
            $deletedApps = 0;
            $deletedStudents = 0;
            $deletedUsers = 0;
            $deletedEnrollments = 0;
            
            foreach ($applications as $app) {
                $this->info("Processing: {$app->full_name}...");
                
                // Find and delete related student
                $student = Student::where('email', $app->email)->first();
                if ($student) {
                    // Delete enrollments
                    $enrollmentCount = Enrollment::where('student_id', $student->id)->count();
                    Enrollment::where('student_id', $student->id)->delete();
                    $deletedEnrollments += $enrollmentCount;
                    
                    // Delete student
                    $student->forceDelete();
                    $deletedStudents++;
                    $this->info("  ✅ Deleted student profile");
                }
                
                // Find and delete related user
                $user = User::where('email', $app->email)->first();
                if ($user) {
                    $user->delete();
                    $deletedUsers++;
                    $this->info("  ✅ Deleted user account");
                }
                
                // Delete documents
                $documentCount = $app->documents()->count();
                $app->documents()->delete();
                
                // Delete application
                $app->forceDelete();
                $deletedApps++;
                $this->info("  ✅ Deleted application and {$documentCount} document(s)");
            }
            
            DB::commit();
            
            $this->newLine();
            $this->info("=== Deletion Summary ===");
            $this->info("✅ Applications deleted: {$deletedApps}");
            $this->info("✅ Students deleted: {$deletedStudents}");
            $this->info("✅ Users deleted: {$deletedUsers}");
            $this->info("✅ Enrollments deleted: {$deletedEnrollments}");
            $this->info("All enrollment data has been successfully deleted!");
            
        } catch (\Exception $e) {
            DB::rollback();
            $this->error("❌ Error: " . $e->getMessage());
        }
    }
}

