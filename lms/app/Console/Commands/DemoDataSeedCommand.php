<?php

namespace App\Console\Commands;

use App\Services\DemoDataService;
use Illuminate\Console\Command;

class DemoDataSeedCommand extends Command
{
    protected $signature = 'demo:seed';

    protected $description = 'Create DEMO-2026 presentation dummy students, teachers, parents, and academic records (idempotent).';

    public function handle(): int
    {
        $this->info('Seeding DEMO-2026 presentation data...');
        $this->warn('Existing DEMO-2026 records will be replaced. Other data is not modified.');

        $service = new DemoDataService(fn (string $message) => $this->line($message));
        $summary = $service->seed();

        $this->newLine();
        $this->info('Done.');
        $this->table(
            ['Item', 'Count'],
            [
                ['Students', $summary['students']],
                ['Teachers', $summary['teachers']],
                ['Parents', $summary['parents']],
                ['Academic year', $summary['academic_year']],
                ['Semester', $summary['semester']],
            ]
        );

        $this->info('Password for all demo accounts: '.$summary['password']);
        $this->info('Cleanup: php artisan demo:cleanup');

        return self::SUCCESS;
    }
}
