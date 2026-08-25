<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use App\Models\User;
use App\Observers\UserObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Paginator::useBootstrapFive();

        if (str_starts_with((string) config('app.url'), 'https://')) {
            URL::forceScheme('https');
        }

        // Register User Observer for automatic role-specific record creation
        User::observe(UserObserver::class);

        // Share header notifications once per request (avoids duplicate queries in layout)
        View::composer('layouts.master', function ($view) {
            $empty = [
                'headerUnreadCount' => 0,
                'headerNotifications' => collect(),
            ];

            try {
                if (!auth()->check()) {
                    $view->with($empty);
                    return;
                }

                if (!Schema::hasTable('notifications')) {
                    $view->with($empty);
                    return;
                }

                $user = auth()->user();
                $view->with([
                    'headerUnreadCount' => $user->unreadNotifications()->count(),
                    'headerNotifications' => $user->notifications()->latest()->limit(5)->get(),
                ]);
            } catch (\Throwable $e) {
                Log::warning('Header notifications skipped: '.$e->getMessage());
                $view->with($empty);
            }
        });
    }
}
