<?php

namespace App\Support;

class NoEmoji
{
    /**
     * Detect emoji / pictographic characters (including flags & ZWJ sequences).
     */
    public static function contains(?string $value): bool
    {
        if ($value === null || $value === '') {
            return false;
        }

        return (bool) preg_match(
            '/(?:\p{Extended_Pictographic}|\x{FE0F}|\x{200D}|[\x{1F1E6}-\x{1F1FF}])/u',
            $value
        );
    }

    /**
     * Recursively find field paths that contain emoji.
     *
     * @param  array<string, mixed>  $input
     * @return array<int, string>
     */
    public static function findFields(array $input, string $prefix = ''): array
    {
        $bad = [];

        foreach ($input as $key => $value) {
            $path = $prefix === '' ? (string) $key : $prefix . '.' . $key;

            if (is_array($value)) {
                $bad = array_merge($bad, self::findFields($value, $path));
                continue;
            }

            if (is_string($value) && self::contains($value)) {
                $bad[] = $path;
            }
        }

        return $bad;
    }

    public static function strip(string $value): string
    {
        return preg_replace(
            '/(?:\p{Extended_Pictographic}|\x{FE0F}|\x{200D}|[\x{1F1E6}-\x{1F1FF}])/u',
            '',
            $value
        ) ?? $value;
    }
}
