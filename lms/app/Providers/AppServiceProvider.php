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
use Illuminate\Support\Facades\Validator;
use App\Support\NoEmoji;
use App\Support\PageAssets;
use App\Support\SafeSchema;
use App\Support\SidebarMenu;

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

        Validator::extend('no_emoji', function ($attribute, $value, $parameters, $validator) {
            if ($value === null || $value === '') {
                return true;
            }
            if (! is_string($value)) {
                return true;
            }

            return ! NoEmoji::contains($value);
        }, 'Emojis are not allowed in this field.');

        if (str_starts_with((string) config('app.url'), 'https://')) {
            URL::forceScheme('https');
        }

        // Register User Observer for automatic role-specific record creation
        User::observe(UserObserver::class);

        View::composer('sidebar.sidebar', function ($view) {
            try {
                $view->with(SidebarMenu::forUser(auth()->user()));
            } catch (\Throwable $e) {
                Log::warning('Sidebar menu skipped: '.$e->getMessage());
                $view->with([
                    'sidebarParentUsers' => collect(),
                    'sidebarEnrollments' => collect(),
                    'sidebarChildren' => collect(),
                ]);
            }
        });

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

                $user = auth()->user();
                if (! $user instanceof User) {
                    $view->with($empty);
                    return;
                }
                $cacheKey = 'header.notifs.'.$user->id;
                $cached = Cache::get($cacheKey);
                if (is_array($cached)) {
                    $view->with($cached);
                    return;
                }

                $header = Cache::remember($cacheKey, 90, function () use ($user) {
                    try {
                        return [
                            'headerUnreadCount' => $user->unreadNotifications()->count(),
                            'headerNotifications' => $user->notifications()->latest()->limit(5)->get(),
                        ];
                    } catch (\Throwable $e) {
                        return [
                            'headerUnreadCount' => 0,
                            'headerNotifications' => collect(),
                        ];
                    }
                });
                $view->with($header);
            } catch (\Throwable $e) {
                Log::warning('Header notifications skipped: '.$e->getMessage());
                $view->with($empty);
            }
        });
    }
}
