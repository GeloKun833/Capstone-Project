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
        if (! $avatar || $avatar === 'photo_defaults.jpg') {
            return asset('images/photo_defaults.jpg');
        }

        if (str_starts_with($avatar, 'avatars/')) {
            return asset('storage/'.$avatar);
        }

        if (str_starts_with($avatar, 'http')) {
            return $avatar;
        }

        return asset('images/'.$avatar);
    }
}
