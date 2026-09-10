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
    }
}
