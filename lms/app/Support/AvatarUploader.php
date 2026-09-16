<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class AvatarUploader
{
    /**
     * Store avatar on the public disk (works on Render/Docker).
     * Returns relative path under storage/app/public (e.g. avatars/xxx.jpg).
     */
    public static function store(?UploadedFile $file, ?string $oldAvatar = null): ?string
    {
        if (! $file || ! $file->isValid()) {
            return null;
        }

        $name = time().'_'.uniqid().'.'.strtolower($file->getClientOriginalExtension());
        $path = $file->storeAs('avatars', $name, 'public');

        self::deleteOld($oldAvatar);

        return $path; // avatars/xxx.jpg
    }

    public static function deleteOld(?string $oldAvatar): void
    {
        if (! $oldAvatar || $oldAvatar === 'photo_defaults.jpg') {
            return;
        }

        if (str_starts_with($oldAvatar, 'avatars/') && Storage::disk('public')->exists($oldAvatar)) {
            Storage::disk('public')->delete($oldAvatar);
            return;
        }

        $legacy = public_path('images/'.$oldAvatar);
        if (is_file($legacy)) {
            @unlink($legacy);
        }
    }

    public static function url(?string $avatar): string
    {
        $default = asset('images/photo_defaults.jpg');
        $avatar = trim((string) $avatar);

        if ($avatar === '' || $avatar === 'photo_defaults.jpg') {
            return $default;
        }

        if (str_starts_with($avatar, 'http://') || str_starts_with($avatar, 'https://')) {
            return $avatar;
        }

        $avatar = ltrim(str_replace('\\', '/', $avatar), '/');
        if (str_starts_with($avatar, 'storage/')) {
            $avatar = substr($avatar, strlen('storage/'));
        }

        $candidates = [];
        if (str_contains($avatar, '/')) {
            $candidates[] = $avatar;
        } else {
            $candidates[] = 'avatars/'.$avatar;
            $candidates[] = 'student-photos/'.$avatar;
            $candidates[] = $avatar;
        }

        foreach ($candidates as $path) {
            if (Storage::disk('public')->exists($path)) {
                return asset('storage/'.$path);
            }
        }

        $legacy = public_path('images/'.$avatar);
        if (is_file($legacy)) {
            return asset('images/'.$avatar);
        }

        $assetsDefault = public_path('assets/img/profiles/avatar-01.jpg');
        if (is_file($assetsDefault) && ! is_file(public_path('images/photo_defaults.jpg'))) {
            return asset('assets/img/profiles/avatar-01.jpg');
        }

        return $default;
    }

    public static function urlForUser(?\App\Models\User $user): string
    {
        if (! $user) {
            return self::url(null);
        }

        if (! empty($user->avatar)) {
            return self::url($user->avatar);
        }

        if ($user->role_name === \App\Models\User::ROLE_STUDENT) {
            $student = $user->relationLoaded('student') ? $user->student : $user->student()->first();
            if ($student && ! empty($student->upload)) {
                return self::url($student->upload);
            }
        }

        if ($user->role_name === \App\Models\User::ROLE_TEACHER) {
            $teacher = $user->relationLoaded('teacher') ? $user->teacher : $user->teacher()->first();
            if ($teacher && ! empty($teacher->avatar)) {
                return self::url($teacher->avatar);
            }
        }

        return self::url(null);
    }

    public static function urlForStudent(?\App\Models\Student $student): string
    {
        if (! $student) {
            return self::url(null);
        }

        $user = $student->relationLoaded('user') ? $student->user : $student->user()->first();
        if ($user && ! empty($user->avatar)) {
            return self::url($user->avatar);
        }

        return self::url($student->upload);
    }
}
