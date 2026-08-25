<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'instructions',
        'lesson_id',
        'due_date',
        'allows_submission',
        'is_active'
    ];

    protected $casts = [
        'due_date' => 'date',
        'allows_submission' => 'boolean',
        'is_active' => 'boolean',
    ];

    // Relationships
    public function lesson()
    {
        return $this->belongsTo(Lesson::class);
    }

    public function submissions()
    {
        return $this->hasMany(ActivitySubmission::class);
    }

    public function rubrics()
    {
        return $this->hasMany(ActivityRubric::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByLesson($query, $lessonId)
    {
        return $query->where('lesson_id', $lessonId);
    }

    public function scopeAllowsSubmission($query)
    {
        return $query->where('allows_submission', true);
    }

    public function scopeOverdue($query)
    {
        return $query->where('due_date', '<', now());
    }

    // Accessors
    public function getIsOverdueAttribute()
    {
        return $this->due_date < now();
    }

    public function getSubmissionCountAttribute()
    {
        if (array_key_exists('submissions_count', $this->attributes)) {
            return (int) $this->attributes['submissions_count'];
        }
        if ($this->relationLoaded('submissions')) {
            return $this->submissions->count();
        }

        return $this->submissions()->count();
    }

    public function getGradedCountAttribute()
    {
        if (array_key_exists('graded_submissions_count', $this->attributes)) {
            return (int) $this->attributes['graded_submissions_count'];
        }
        if ($this->relationLoaded('submissions')) {
            return $this->submissions->where('status', 'graded')->count();
        }

        return $this->submissions()->where('status', 'graded')->count();
    }
} 