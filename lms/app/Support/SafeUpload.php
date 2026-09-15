<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class SafeUpload
{
    public static function store(UploadedFile $file, string $directory): array
    {
        $ext = strtolower($file->getClientOriginalExtension() ?: $file->extension() ?: 'bin');
        $storedName = time().'_'.Str::random(16).'.'.$ext;
        $path = $file->storeAs($directory, $storedName, 'public');

        return [
            'path' => $path,
            'stored_name' => $storedName,
            'original_name' => $file->getClientOriginalName(),
        ];
    }
}
