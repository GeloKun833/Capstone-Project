<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ObservedValueIndicator extends Model
{
    use HasFactory;

    protected $fillable = [
        'core_value',
        'statement',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function ratings()
    {
        return $this->hasMany(StudentObservedValue::class, 'indicator_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }
}
