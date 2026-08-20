<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityRubric extends Model
{
    use HasFactory;

    protected $fillable = [
        'activity_id',
        'category_name',
        'description',
        'max_score',
        'weight',
        'is_active'
    ];

    protected $casts = [
        'max_score' => 'integer',
        'weight' => 'integer',
        'is_active' => 'boolean',
    ];

    // Relationships
    public function activity()
    {
        return $this->belongsTo(Activity::class);
    }

    public function grades()
    {
        return $this->hasMany(ActivityGrade::class, 'rubric_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByActivity($query, $activityId)
    {
        return $query->where('activity_id', $activityId);
    }

    // Accessors
    public function getWeightedMaxScoreAttribute()
    {
        return $this->max_score * $this->weight;
    }
} 