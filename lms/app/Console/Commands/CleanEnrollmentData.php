<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\EnrollmentApplication;
use Illuminate\Support\Facades\DB;

class CleanEnrollmentData extends Command
{
    protected $signature = 'clean:enrollment-data';
    protected $description = 'Permanently delete all soft-deleted enrollment applications';

    public function handle()
    {
        $this->info("=== Cleaning Enrollment Data ===");
        
        // Get soft-deleted applications
        $deletedApps = EnrollmentApplication::onlyTrashed()->get();
        
        if ($deletedApps->count() === 0) {
            $this->warn("No soft-deleted applications found!");
            return;
        }
        
        $this->info("Found {$deletedApps->count()} soft-deleted application(s):");
        $this->newLine();
        
        foreach ($deletedApps as $app) {
            $this->info("ID: {$app->id} - {$app->full_name} ({$app->email}) - Status: {$app->status}");
        }
        
        $this->newLine();
        $this->warn("This will PERMANENTLY delete these applications and their documents.");
        $this->warn("This action CANNOT be undone!");
        $this->newLine();
        
        DB::beginTransaction();
        
        try {
            $count = 0;
            $docCount = 0;
            
            foreach ($deletedApps as $app) {
                // Force delete documents
                DB::table('enrollment_documents')
                    ->where('enrollment_application_id', $app->id)
                    ->delete();
                $docCount += DB::table('enrollment_documents')
                    ->where('enrollment_application_id', $app->id)
                    ->count();
                
                // Force delete application
                $app->forceDelete();
                $count++;
                
                $this->info("✅ Permanently deleted: {$app->full_name}");
            }
            
            DB::commit();
            
            $this->newLine();
            $this->info("=== Summary ===");
            $this->info("✅ Applications permanently deleted: {$count}");
            $this->info("✅ Documents permanently deleted: {$docCount}");
            $this->info("All soft-deleted enrollment data has been cleaned!");
            
        } catch (\Exception $e) {
            DB::rollback();
            $this->error("❌ Error: " . $e->getMessage());
        }
    }
}

