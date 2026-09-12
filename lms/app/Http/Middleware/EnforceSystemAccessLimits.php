<?php

namespace App\Http\Middleware;

use App\Services\SystemAccessLimitService;
use Brian2694\Toastr\Facades\Toastr;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnforceSystemAccessLimits
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();
        if (! $user) {
            return $next($request);
        }

        // Admins/registrars always bypass — no settings DB lookup.
        if (in_array($user->role_name, ['Admin', 'Registrar'], true)) {
            return $next($request);
        }

        $service = app(SystemAccessLimitService::class);
        if ($service->isAllowed($user)) {
            return $next($request);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        Toastr::error($service->message(), 'Limited Access');

        return redirect()->route('login')->with('error', $service->message());
    }
}
