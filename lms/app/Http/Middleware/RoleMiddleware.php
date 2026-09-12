<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  ...$roles
     * @return mixed
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        $user = Auth::user();
        if (!$user) {
            abort(403, 'Unauthorized action.');
        }

        // Handle pipe/comma-separated roles (e.g. 'Admin|Registrar' or 'Admin,Teacher')
        $allowedRoles = [];
        foreach ($roles as $role) {
            foreach (preg_split('/[|,]/', $role) as $part) {
                $part = trim($part);
                if ($part !== '') {
                    $allowedRoles[] = $part;
                }
            }
        }

        if (!in_array($user->role_name, $allowedRoles, true)) {
            abort(403, 'Unauthorized action.');
        }
        return $next($request);
    }
} 