<?php

namespace App\Console\Commands;

use App\Services\DemoDataService;
use Illuminate\Console\Command;

class DemoDataCleanupCommand extends Command
{
    protected $signature = 'demo:cleanup';

    protected $description = 'Remove ONLY DEMO-2026 dummy data (students, teachers, parents, and related academic records).';

    public function handle(): int
    {
        $this->warn('This deletes only @'.DemoDataService::EMAIL_DOMAIN.' / DEMO-* records.');
        $this->warn('Administrators, existing users, subjects, and sections are kept.');

        $service = new DemoDataService(fn (string $message) => $this->line($message));
        $result = $service->cleanup();

        $this->info('Removed demo users: '.$result['users']);
        $this->info('Removed demo students: '.$result['students']);
        $this->info('Removed demo teachers: '.$result['teachers']);

        return self::SUCCESS;
    }
}
