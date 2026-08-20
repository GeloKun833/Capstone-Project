<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityGrade extends Model
{
    use HasFactory;

    protected $fillable = [
        'submission_id',
        'rubric_id',
        'score',
        'feedback',
        'graded_by',
        'graded_at',
        'is_active'
    ];

    protected $casts = [
        'score' => 'integer',
        'graded_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    // Relationships
    public function submission()
    {
        return $this->belongsTo(ActivitySubmission::class, 'submission_id');
    }

    public function rubric()
    {
        return $this->belongsTo(ActivityRubric::class, 'rubric_id');
    }

    public function gradedBy()
    {
        return $this->belongsTo(User::class, 'graded_by');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeBySubmission($query, $submissionId)
    {
        return $query->where('submission_id', $submissionId);
    }

    public function scopeByRubric($query, $rubricId)
    {
        return $query->where('rubric_id', $rubricId);
    }

    public function scopeByGrader($query, $graderId)
    {
        return $query->where('graded_by', $graderId);
    }

    // Accessors
    public function getWeightedScoreAttribute()
    {
        return $this->score * $this->rubric->weight;
    }

    public function getPercentageAttribute()
    {
        return $this->rubric->max_score > 0 ? round(($this->score / $this->rubric->max_score) * 100, 2) : 0;
    }
} 