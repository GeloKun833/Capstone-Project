<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Exceptions\PermissionDoesNotExist;

class GradeEncoding
{
    public const PERMISSION = 'encode grades';

    public static function allows(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        try {
            return $user->hasDirectPermission(self::PERMISSION);
        } catch (PermissionDoesNotExist) {
            return false;
        }
    }

    public static function authorize(Request $request): void
    {
        $user = $request->user() ?: Auth::user();
        if (self::allows($user)) {
            return;
        }

        $message = 'You are not allowed to encode grades. Ask an administrator to grant grade-encoding access.';
        if ($request->expectsJson() || $request->ajax()) {
            abort(response()->json([
                'success' => false,
                'message' => $message,
            ], 403));
        }

        abort(403, $message);
    }
}
