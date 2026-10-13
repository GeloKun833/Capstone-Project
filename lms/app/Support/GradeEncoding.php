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

    /**
     * Null means every grade level is open. An array is the admin's explicit list.
     *
     * @return array<int, string>|null
     */
    public static function openLevels(): ?array
    {
        $stored = \App\Models\SchoolSetting::getSettings()->encoding_open_grades;
        if (! is_array($stored)) {
            return null;
        }

        return array_values($stored);
    }

    public static function isLevelOpen(?string $gradeLevel): bool
    {
        $open = self::openLevels();
        if ($open === null) {
            return true;
        }

        $aliases = \App\Services\GradeSubjectCatalogService::gradeAliases($gradeLevel);
        if ($aliases === []) {
            return false;
        }

        foreach ($open as $level) {
            if (array_intersect($aliases, \App\Services\GradeSubjectCatalogService::gradeAliases($level))) {
                return true;
            }
        }

        return false;
    }

    public static function closedMessage(?string $gradeLevel): string
    {
        $label = trim((string) $gradeLevel) !== '' ? $gradeLevel : 'this grade level';

        return 'The administrator has closed grade encoding for '.$label.'.';
    }
}
