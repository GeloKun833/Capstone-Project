<?php

namespace Database\Seeders;

use App\Services\DemoDataService;
use Illuminate\Database\Seeder;

/**
 * Presentation dummy data only (DEMO-2026 / @demo.pms.local).
 *
 * php artisan db:seed --class=DemoDataSeeder
 * php artisan demo:seed
 * php artisan demo:cleanup
 */
class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $service = new DemoDataService(function (string $message) {
            $this->command?->info($message);
        });

        $summary = $service->seed();

        $this->command?->info('Demo data seeded: '.$summary['students'].' students, '.$summary['teachers'].' teachers, '.$summary['parents'].' parents.');
        $this->command?->info('Login password for all demo accounts: '.$summary['password']);
        $this->command?->info('Remove later with: php artisan demo:cleanup');
    }
}
