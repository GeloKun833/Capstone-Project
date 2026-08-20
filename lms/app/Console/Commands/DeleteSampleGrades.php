<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Grade;
use App\Models\StudentGpa;
use App\Models\GradeAlert;
use App\Models\SubjectComponent;
use App\Models\WeightSetting;
use App\Models\Attendance;
use Illuminate\Support\Facades\DB;

class DeleteSampleGrades extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'grades:delete-sample {--confirm : Skip confirmation prompt}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete all sample grades and related data from the database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if (!$this->option('confirm')) {
            if (!$this->confirm('This will delete ALL grades, GPA records, grade alerts, and related data. Are you sure?')) {
                $this->info('Operation cancelled.');
                return 0;
            }
        }

        $this->info('Starting to delete sample grades and related data...');

        try {
            DB::beginTransaction();

            // Disable foreign key checks temporarily
            DB::statement('SET FOREIGN_KEY_CHECKS = 0');

            // Delete all grades first (they reference other tables)
            $gradeCount = Grade::count();
            Grade::truncate();
            $this->info("Deleted {$gradeCount} grades");

            // Delete all GPA records
            $gpaCount = StudentGpa::count();
            StudentGpa::truncate();
            $this->info("Deleted {$gpaCount} GPA records");

            // Delete all grade alerts
            $alertCount = GradeAlert::count();
            GradeAlert::truncate();
            $this->info("Deleted {$alertCount} grade alerts");

            // Delete all weight settings
            $weightCount = WeightSetting::count();
            WeightSetting::truncate();
            $this->info("Deleted {$weightCount} weight settings");

            // Delete all attendance records
            $attendanceCount = Attendance::count();
            Attendance::truncate();
            $this->info("Deleted {$attendanceCount} attendance records");

            // Delete all subject components (they are referenced by grades)
            $componentCount = SubjectComponent::count();
            SubjectComponent::truncate();
            $this->info("Deleted {$componentCount} subject components");

            // Re-enable foreign key checks
            DB::statement('SET FOREIGN_KEY_CHECKS = 1');

            DB::commit();

            $this->info('Successfully deleted all sample grades and related data!');
            $this->info('The database is now clean of sample data.');

        } catch (\Exception $e) {
            DB::rollback();
            $this->error('Error deleting sample data: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }
} 