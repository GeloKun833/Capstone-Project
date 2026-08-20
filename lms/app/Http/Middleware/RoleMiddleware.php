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

        // Handle pipe-separated roles (e.g., 'Admin|Registrar')
        $allowedRoles = [];
        foreach ($roles as $role) {
            if (strpos($role, '|') !== false) {
                $explodedRoles = explode('|', $role);
                foreach ($explodedRoles as $explodedRole) {
                    $allowedRoles[] = $explodedRole;
                }
            } else {
                $allowedRoles[] = $role;
            }
        }

        if (!in_array($user->role_name, $allowedRoles)) {
            abort(403, 'Unauthorized action.');
        }
        return $next($request);
    }
} 