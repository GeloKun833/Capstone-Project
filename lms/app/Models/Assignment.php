<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Assignment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'description',
        'file_path',
        'file_name',
        'file_type',
        'teacher_id',
        'subject_id',
        'section_id',
        'academic_year_id',
        'semester_id',
        'due_date',
        'due_time',
        'max_score',
        'status',
        'allows_late_submission',
        'late_submission_penalty',
        'requires_file_upload',
        'submission_instructions',
        'allowed_file_types',
        'max_file_size',
        'is_active'
    ];

    protected $casts = [
        'due_date' => 'date',
        'due_time' => 'datetime',
        'max_score' => 'decimal:2',
        'allows_late_submission' => 'boolean',
        'requires_file_upload' => 'boolean',
        'allowed_file_types' => 'array',
        'is_active' => 'boolean',
        'late_submission_penalty' => 'decimal:2'
    ];

    // Relationships
    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function section()
    {
        return $this->belongsTo(Section::class);
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function semester()
    {
        return $this->belongsTo(Semester::class);
    }

    public function submissions()
    {
        return $this->hasMany(AssignmentSubmission::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByTeacher($query, $teacherId)
    {
        return $query->where('teacher_id', $teacherId);
    }

    public function scopeBySubject($query, $subjectId)
    {
        return $query->where('subject_id', $subjectId);
    }

    public function scopeBySection($query, $sectionId)
    {
        return $query->where('section_id', $sectionId);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeClosed($query)
    {
        return $query->where('status', 'closed');
    }

    public function scopeDueSoon($query, $days = 7)
    {
        return $query->where('due_date', '<=', now()->addDays($days))
                    ->where('due_date', '>=', now());
    }

    public function scopeOverdue($query)
    {
        return $query->where('due_date', '<', now());
    }

    // Accessors
    public function getFileUrlAttribute()
    {
        return $this->file_path ? asset('storage/' . $this->file_path) : null;
    }

    public function getDueDateTimeAttribute()
    {
        if ($this->due_time) {
            return Carbon::parse($this->due_date)->setTimeFrom($this->due_time);
        }
        return Carbon::parse($this->due_date)->endOfDay();
    }

    public function getIsOverdueAttribute()
    {
        return $this->dueDateTime < now();
    }

    public function getIsDueSoonAttribute()
    {
        return $this->dueDateTime->diffInDays(now()) <= 3 && !$this->is_overdue;
    }

    public function getSubmissionCountAttribute()
    {
        return $this->submissions()->count();
    }

    public function getGradedCountAttribute()
    {
        return $this->submissions()->where('status', 'graded')->count();
    }

    public function getPendingCountAttribute()
    {
        return $this->submissions()->whereIn('status', ['submitted', 'late'])->count();
    }

    public function getAverageScoreAttribute()
    {
        $gradedSubmissions = $this->submissions()->where('status', 'graded')->whereNotNull('score');
        if ($gradedSubmissions->count() > 0) {
            return $gradedSubmissions->avg('score');
        }
        return 0;
    }

    // Methods
    public function canSubmit()
    {
        // Check if assignment is closed
        if ($this->status === 'closed') {
            return false;
        }

        // Check if assignment is published
        if ($this->status !== 'published') {
            return false;
        }

        // Check if assignment is overdue and late submission is not allowed
        if ($this->is_overdue && !$this->allows_late_submission) {
            return false;
        }

        return true;
    }

    public function getLatePenalty($submissionTime)
    {
        if (!$this->allows_late_submission || $submissionTime <= $this->dueDateTime) {
            return 0;
        }

        $lateMinutes = $submissionTime->diffInMinutes($this->dueDateTime);
        $this->late_minutes = $lateMinutes;
        
        if ($this->late_submission_penalty > 0) {
            return ($this->late_submission_penalty / 100) * $this->max_score;
        }

        return 0;
    }

    public function getStatusColorAttribute()
    {
        return match($this->status) {
            'draft' => 'secondary',
            'published' => 'success',
            'closed' => 'danger',
            default => 'secondary'
        };
    }

    public function getPriorityColorAttribute()
    {
        return match($this->priority ?? 'normal') {
            'low' => 'info',
            'normal' => 'primary',
            'high' => 'warning',
            'urgent' => 'danger',
            default => 'primary'
        };
    }
}
