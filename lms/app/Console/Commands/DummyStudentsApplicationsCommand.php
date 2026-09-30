<?php

namespace App\Console\Commands;

use App\Services\DummyStudentService;
use Illuminate\Console\Command;

class DummyStudentsApplicationsCommand extends Command
{
    protected $signature = 'dummy:students:applications
        {--force : Skip the confirmation prompt}
        {--seed='.DummyStudentService::DEFAULT_SEED.' : Random seed so the generated data is reproducible}';

    protected $description = 'Add dummy enrollment applications (one approved per existing dummy student plus extra pending/under review/needs documents/rejected applicants). Never deletes data.';

    public function handle(DummyStudentService $service): int
    {
        $extras = collect(DummyStudentService::EXTRA_APPLICATION_STATUSES)->map(fn ($c, $s) => "{$c} {$s}")->implode(', ');
        $this->line('Will add '.DummyStudentService::TOTAL_STUDENTS.' approved applications (one per dummy student) and '.$extras.' dummy applicants ('.DummyStudentService::APPLICATION_PREFIX.'*).');

        if (! $this->option('force') && ! $this->confirm('Create these '.DummyStudentService::totalApplications().' dummy enrollment applications?')) {
            $this->warn('Cancelled. Nothing was written.');

            return self::FAILURE;
        }

        try {
            $counts = $service->addApplications((int) $this->option('seed'));
        } catch (\Throwable $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        $this->table(['Status', 'Applications'], collect($counts)->map(fn ($v, $k) => [$k, $v])->values()->all());
        $this->info('Remove together with the dummy students: php artisan dummy:students:delete');

        return self::SUCCESS;
    }
}
