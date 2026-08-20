<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SchoolSetting extends Model
{
    use HasFactory;

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
    ];

    protected $casts = [
        'rtl_enabled' => 'boolean',
    ];

    /**
     * Get the settings (singleton pattern - only one record should exist)
     */
    public static function getSettings()
    {
        $settings = self::first();
        
        if (!$settings) {
            $settings = self::create([
                'website_name' => 'Panorama Montessori School',
                'email' => 'info@panoramamontessori.edu',
                'phone' => '+1234567890',
            ]);
        }
        
        return $settings;
    }
}
