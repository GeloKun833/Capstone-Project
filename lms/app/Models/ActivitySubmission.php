<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivitySubmission extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'activity_id',
        'file_path',
        'file_name',
        'comments',
        'status',
        'submitted_at',
        'is_active'
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    // Relationships
    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function activity()
    {
        return $this->belongsTo(Activity::class);
    }

    public function grades()
    {
        return $this->hasMany(ActivityGrade::class, 'submission_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByStudent($query, $studentId)
    {
        return $query->where('student_id', $studentId);
    }

    public function scopeByActivity($query, $activityId)
    {
        return $query->where('activity_id', $activityId);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeSubmitted($query)
    {
        return $query->where('status', 'submitted');
    }

    public function scopeGraded($query)
    {
        return $query->where('status', 'graded');
    }

    public function scopeLate($query)
    {
        return $query->where('status', 'late');
    }

    // Accessors
    public function getFileUrlAttribute()
    {
        return $this->file_path ? asset('storage/' . $this->file_path) : null;
    }

    public function getTotalScoreAttribute()
    {
        return $this->grades()->sum('score');
    }

    public function getMaxScoreAttribute()
    {
        return $this->activity->rubrics()->sum('max_score');
    }

    public function getPercentageAttribute()
    {
        $maxScore = $this->max_score;
        return $maxScore > 0 ? round(($this->total_score / $maxScore) * 100, 2) : 0;
    }

    public function getIsLateAttribute()
    {
        return $this->submitted_at && $this->submitted_at->gt($this->activity->due_date);
    }
} 