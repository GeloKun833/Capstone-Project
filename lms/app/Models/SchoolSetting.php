<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SchoolSetting extends Model
{
    use HasFactory;

    public const CACHE_KEY = 'school_settings.singleton';

    public const CACHE_TTL = 600;

    protected $fillable = [
        'website_name',
        'logo',
        'favicon',
        'rtl_enabled',
        'address_line_1',
        'address_line_2',
        'city',
        'state_province',
        'zip_postal_code',
        'country',
        'phone',
        'email',
        'description',
        'facebook_url',
        'twitter_url',
        'linkedin_url',
        'instagram_url',
        'access_limits_enabled',
        'max_teachers',
        'max_students_per_grade',
        'max_parents_per_grade',
        'access_allowed_grades',
        'access_limits_message',
    ];

    protected $casts = [
        'rtl_enabled' => 'boolean',
        'access_limits_enabled' => 'boolean',
        'access_allowed_grades' => 'array',
        'max_teachers' => 'integer',
        'max_students_per_grade' => 'integer',
        'max_parents_per_grade' => 'integer',
    ];

    /**
     * Get the settings (singleton — cached to avoid a DB hit on every request).
     */
    public static function getSettings()
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
            $settings = self::first();

            if (! $settings) {
                $settings = self::create([
                    'website_name' => 'Panorama Montessori School Inc Sta Rosa Campus',
                    'email' => 'panoramamontessorischool1985@gmail.com',
                    'phone' => '(049302) 9290',
                    'facebook_url' => 'https://www.facebook.com/pms.starosacampus',
                    'address_line_1' => 'Panorama Ville, Brgy. Dita',
                    'city' => 'City of Santa Rosa',
                    'state_province' => 'Laguna',
                    'country' => 'Philippines',
                ]);
            }

            return $settings;
        });
    }

    public static function clearSettingsCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    protected static function booted(): void
    {
        static::saved(fn () => static::clearSettingsCache());
        static::deleted(fn () => static::clearSettingsCache());
    }
}
