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
        'is_active',
        'total_score',
        'max_possible_score',
        'percentage',
        'letter_grade',
        'feedback',
        'graded_by',
        'graded_at',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'graded_at' => 'datetime',
        'is_active' => 'boolean',
        'total_score' => 'float',
        'max_possible_score' => 'float',
        'percentage' => 'float',
    ];

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

    public function gradedBy()
    {
        return $this->belongsTo(User::class, 'graded_by');
    }

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

    public function getFileUrlAttribute()
    {
        return $this->file_path ? asset('storage/' . $this->file_path) : null;
    }

    public function getIsLateAttribute()
    {
        return $this->submitted_at && $this->activity && $this->submitted_at->gt($this->activity->due_date);
    }

    public function getLetterGradeColorAttribute()
    {
        return match ($this->letter_grade) {
            'A', 'B+' => 'success',
            'B', 'C+' => 'primary',
            'C', 'D+' => 'warning',
            'D', 'F' => 'danger',
            default => 'secondary',
        };
    }
}
