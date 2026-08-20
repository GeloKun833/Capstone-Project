<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class AssignmentSubmission extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'assignment_id',
        'student_id',
        'file_path',
        'file_name',
        'file_type',
        'comments',
        'teacher_feedback',
        'score',
        'max_score',
        'status',
        'submitted_at',
        'graded_at',
        'is_late',
        'late_minutes',
        'late_penalty',
        'is_active'
    ];

    protected $casts = [
        'score' => 'decimal:2',
        'max_score' => 'decimal:2',
        'is_late' => 'boolean',
        'late_penalty' => 'decimal:2',
        'is_active' => 'boolean',
        'submitted_at' => 'datetime',
        'graded_at' => 'datetime'
    ];

    // Relationships
    public function assignment()
    {
        return $this->belongsTo(Assignment::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
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

    public function scopeByAssignment($query, $assignmentId)
    {
        return $query->where('assignment_id', $assignmentId);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeSubmitted($query)
    {
        return $query->whereIn('status', ['submitted', 'late']);
    }

    public function scopeGraded($query)
    {
        return $query->where('status', 'graded');
    }

    public function scopeLate($query)
    {
        return $query->where('is_late', true);
    }

    public function scopePending($query)
    {
        return $query->whereIn('status', ['submitted', 'late']);
    }

    // Accessors
    public function getFileUrlAttribute()
    {
        return $this->file_path ? asset('storage/' . $this->file_path) : null;
    }

    public function getPercentageAttribute()
    {
        if ($this->score && $this->max_score && $this->max_score > 0) {
            return round(($this->score / $this->max_score) * 100, 2);
        }
        return 0;
    }

    public function getGradeAttribute()
    {
        $percentage = $this->percentage;
        
        if ($percentage >= 90) return 'A+';
        if ($percentage >= 85) return 'A';
        if ($percentage >= 80) return 'A-';
        if ($percentage >= 75) return 'B+';
        if ($percentage >= 70) return 'B';
        if ($percentage >= 65) return 'B-';
        if ($percentage >= 60) return 'C+';
        if ($percentage >= 55) return 'C';
        if ($percentage >= 50) return 'C-';
        if ($percentage >= 45) return 'D+';
        if ($percentage >= 40) return 'D';
        return 'F';
    }

    public function getStatusColorAttribute()
    {
        return match($this->status) {
            'submitted' => 'primary',
            'late' => 'warning',
            'graded' => 'success',
            'returned' => 'info',
            default => 'secondary'
        };
    }

    public function getIsOverdueAttribute()
    {
        return $this->assignment && $this->assignment->is_overdue;
    }

    // Methods
    public function markAsGraded($score, $feedback = null)
    {
        $this->update([
            'score' => $score,
            'teacher_feedback' => $feedback,
            'status' => 'graded',
            'graded_at' => now()
        ]);
    }

    public function markAsReturned($feedback = null)
    {
        $this->update([
            'status' => 'returned',
            'teacher_feedback' => $feedback
        ]);
    }

    public function calculateLatePenalty()
    {
        if (!$this->is_late || !$this->assignment) {
            return 0;
        }

        $lateMinutes = $this->late_minutes;
        $penaltyPercentage = $this->assignment->late_submission_penalty;
        
        if ($penaltyPercentage > 0) {
            $this->late_penalty = ($penaltyPercentage / 100) * $this->max_score;
            $this->save();
        }

        return $this->late_penalty;
    }

    public function getFinalScoreAttribute()
    {
        $finalScore = $this->score ?? 0;
        
        if ($this->late_penalty > 0) {
            $finalScore = max(0, $finalScore - $this->late_penalty);
        }
        
        return round($finalScore, 2);
    }

    /**
     * Check if the submission is late
     * 
     * @return bool
     */
    public function isLate()
    {
        return (bool) $this->is_late;
    }
}
