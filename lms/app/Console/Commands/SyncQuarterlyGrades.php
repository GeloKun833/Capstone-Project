<?php

namespace App\Console\Commands;

use App\Models\QuarterlyGrade;
use App\Models\Semester;
use App\Services\QuarterlyGradeSyncService;
use Illuminate\Console\Command;

class SyncQuarterlyGrades extends Command
{
    protected $signature = 'grades:sync-quarterly {--academic-year= : Academic year ID}';
    protected $description = 'Sync all quarterly grades to the Grade/GPA system';

    public function handle(QuarterlyGradeSyncService $syncService): int
    {
        $query = QuarterlyGrade::whereNotNull('final_grade');
        if ($this->option('academic-year')) {
            $query->where('academic_year_id', $this->option('academic-year'));
        }

        $grades = $query->get();
        $count = 0;

        foreach ($grades as $qg) {
            $semesterId = Semester::where('academic_year_id', $qg->academic_year_id)->orderBy('id')->value('id');
            if ($syncService->sync($qg, $semesterId)) {
                $count++;
            }
        }

        $this->info("Synced {$count} quarterly grade record(s).");
        return self::SUCCESS;
    }
}
