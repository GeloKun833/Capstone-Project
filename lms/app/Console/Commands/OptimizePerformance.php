<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;

class OptimizePerformance extends Command
{
    protected $signature = 'app:optimize-performance {--clear : Clear caches instead of building them}';

    protected $description = 'Warm or clear LMS performance caches (views, bootstrap, dashboard)';

    public function handle(): int
    {
        if ($this->option('clear')) {
            Cache::forget('admin.dashboard.data');
            Artisan::call('optimize:clear');
            $this->info(Artisan::output());
            $this->info('Performance caches cleared.');
            return self::SUCCESS;
        }

        Cache::forget('admin.dashboard.data');
        Artisan::call('config:clear');
        Artisan::call('event:cache');

        try {
            Artisan::call('view:cache');
            $this->info('Views cached.');
        } catch (\Throwable $e) {
            $this->warn('View cache skipped: ' . $e->getMessage());
        }

        $this->info('Events cached. Admin dashboard cache will rebuild on next visit.');
        $this->comment('Tip: keep APP_DEBUG=false in production for best speed.');
        $this->comment('Route cache skipped (web.php uses closures).');

        return self::SUCCESS;
    }
}
