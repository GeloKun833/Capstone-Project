<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use App\Models\User;
use App\Observers\UserObserver;
use App\Support\PageAssets;
use App\Support\SafeSchema;

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

            $view->with(PageAssets::flags());

            try {
                if (!auth()->check()) {
                    $view->with($empty);
                    return;
                }

                if (!SafeSchema::tableExists('notifications')) {
                    $view->with($empty);
                    return;
                }

                $user = auth()->user();
                $header = Cache::remember('header.notifs.'.$user->id, 20, function () use ($user) {
                    return [
                        'headerUnreadCount' => $user->unreadNotifications()->count(),
                        'headerNotifications' => $user->notifications()->latest()->limit(5)->get(),
                    ];
                });
                $view->with($header);
            } catch (\Throwable $e) {
                Log::warning('Header notifications skipped: '.$e->getMessage());
                $view->with($empty);
            }
        });
    }
}
