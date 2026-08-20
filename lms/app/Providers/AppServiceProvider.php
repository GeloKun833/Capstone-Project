<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
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
        // Register User Observer for automatic role-specific record creation
        User::observe(UserObserver::class);

        // Share header notifications once per request (avoids duplicate queries in layout)
        View::composer('layouts.master', function ($view) {
            if (!auth()->check()) {
                $view->with([
                    'headerUnreadCount' => 0,
                    'headerNotifications' => collect(),
                ]);
                return;
            }

            $user = auth()->user();
            $view->with([
                'headerUnreadCount' => $user->unreadNotifications()->count(),
                'headerNotifications' => $user->notifications()->latest()->limit(5)->get(),
            ]);
        });
    }
}
