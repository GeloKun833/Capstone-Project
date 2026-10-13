<?php

namespace App\Http\Middleware;

use App\Services\AccountYearAccess;
use Brian2694\Toastr\Facades\Toastr;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RestrictAccountToCreationYear
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();
        if (! $user) {
            return $next($request);
        }

        $message = app(AccountYearAccess::class)->denies($user);
        if ($message === null) {
            return $next($request);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        Toastr::error($message, 'Academic Year');

        return redirect()->route('login')->with('error', $message);
    }
}
