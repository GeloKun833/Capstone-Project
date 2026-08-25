<?php

namespace App\Support;

class PageAssets
{
    /**
     * Decide which heavy plugins the current page actually needs.
     * Dashboard stays light; list/form pages still get DataTables/Select2.
     */
    public static function flags(): array
    {
        $isDashboard = request()->routeIs('home', 'dashboard');
        $role = auth()->user()->role_name ?? session('role_name');

        return [
            'loadCharts' => request()->routeIs('analytics.*')
                || ($isDashboard && $role === 'Admin'),
            'loadCalendar' => ($isDashboard && $role === 'Teacher') || request()->routeIs('*calendar*'),
            'loadCircleProgress' => $isDashboard && $role === 'Teacher',
            'loadFormPlugins' => ! $isDashboard,
        ];
    }
}
