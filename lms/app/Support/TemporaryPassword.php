<?php

namespace App\Support;

use Illuminate\Support\Str;

class TemporaryPassword
{
    public static function make(): string
    {
        return Str::password(12, true, true, false);
    }
}
