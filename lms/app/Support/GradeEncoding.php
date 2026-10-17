<?php

namespace App\Support;

use App\Models\SchoolSetting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Exceptions\PermissionDoesNotExist;

class GradeEncoding
{
    public const PERMISSION = 'encode grades';

    public static function allows(?User $user): bool
    {
        if (! $user || $user->role_name !== User::ROLE_TEACHER) {
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

    public static function startsOn(): ?string
    {
        return self::settings()->encoding_starts_on?->toDateString();
    }

    public static function dueOn(): ?string
    {
        return self::settings()->encoding_due_on?->toDateString();
    }

    public static function hasWindow(): bool
    {
        return self::startsOn() !== null && self::dueOn() !== null;
    }

    public static function isWindowOpen(): bool
    {
        if (! self::hasWindow()) {
            return false;
        }

        $today = now('Asia/Manila')->toDateString();

        return $today >= self::startsOn() && $today <= self::dueOn();
    }

    public static function windowNote(): string
    {
        if (! self::hasWindow()) {
            return 'The administrator has not opened grade entry yet.';
        }

        $opens = Carbon::parse(self::startsOn())->format('M j, Y');
        $due = Carbon::parse(self::dueOn())->format('M j, Y');
        $today = now('Asia/Manila')->toDateString();

        if ($today < self::startsOn()) {
            return 'Grade entry opens on '.$opens.' and is due '.$due.'.';
        }

        if ($today > self::dueOn()) {
            return 'The due date for grade entry has passed. It was due '.$due.'.';
        }

        return 'Grade entry is open from '.$opens.' through '.$due.'.';
    }

    /**
     * Null means every grade level is open. An array is the admin's explicit list.
     *
     * @return array<int, string>|null
     */
    public static function openLevels(): ?array
    {
        $stored = self::settings()->encoding_open_grades;
        if (! is_array($stored)) {
            return null;
        }

        return array_values($stored);
    }

    public static function isLevelOpen(?string $gradeLevel): bool
    {
        if (! self::isWindowOpen()) {
            return false;
        }

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
        if (! self::isWindowOpen()) {
            return self::windowNote();
        }

        $label = trim((string) $gradeLevel) !== '' ? $gradeLevel : 'this grade level';

        return 'The administrator has closed grade encoding for '.$label.'.';
    }

    private static function settings(): SchoolSetting
    {
        return SchoolSetting::getSettings();
    }
}
