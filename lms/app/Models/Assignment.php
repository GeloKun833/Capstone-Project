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
        $now = Carbon::now(config('app.school_timezone', 'Asia/Manila'));

        return $query->whereDate('due_date', '>=', $now->toDateString())
            ->whereDate('due_date', '<=', $now->copy()->addDays($days)->toDateString())
            ->where(function ($query) use ($now) {
                $query->whereDate('due_date', '>', $now->toDateString())
                    ->orWhere(function ($today) use ($now) {
                        $today->whereDate('due_date', $now->toDateString())
                            ->where(function ($time) use ($now) {
                                $time->whereNull('due_time')
                                    ->orWhere('due_time', '>=', $now->format('H:i:s'));
                            });
                    });
            });
    }

    public function scopeOverdue($query)
    {
        $now = Carbon::now(config('app.school_timezone', 'Asia/Manila'));

        return $query->where(function ($query) use ($now) {
            $query->whereDate('due_date', '<', $now->toDateString())
                ->orWhere(function ($today) use ($now) {
                    $today->whereDate('due_date', $now->toDateString())
                        ->whereNotNull('due_time')
                        ->where('due_time', '<', $now->format('H:i:s'));
                });
        });
    }

    public function scopeNotOverdue($query)
    {
        $now = Carbon::now(config('app.school_timezone', 'Asia/Manila'));

        return $query->where(function ($query) use ($now) {
            $query->whereDate('due_date', '>', $now->toDateString())
                ->orWhere(function ($today) use ($now) {
                    $today->whereDate('due_date', $now->toDateString())
                        ->where(function ($time) use ($now) {
                            $time->whereNull('due_time')
                                ->orWhere('due_time', '>=', $now->format('H:i:s'));
                        });
                });
        });
    }

    // Accessors
    public function getFileUrlAttribute()
    {
        return $this->file_path ? asset('storage/' . $this->file_path) : null;
    }

    public function getDueDateTimeAttribute()
    {
        $date = $this->getRawOriginal('due_date') ?? $this->attributes['due_date'] ?? null;
        if (! $date) {
            return null;
        }

        $time = $this->getRawOriginal('due_time') ?? $this->attributes['due_time'] ?? null;
        $time = $time ? substr((string) $time, 0, 8) : '23:59:59';

        return Carbon::createFromFormat(
            '!Y-m-d H:i:s',
            Carbon::parse($date)->format('Y-m-d').' '.$time,
            config('app.school_timezone', 'Asia/Manila')
        );
    }

    public function getIsOverdueAttribute()
    {
        return $this->dueDateTime && $this->dueDateTime->lt(now());
    }

    public function getIsDueSoonAttribute()
    {
        return $this->dueDateTime
            && $this->dueDateTime->lte(now(config('app.school_timezone', 'Asia/Manila'))->addDays(3))
            && ! $this->is_overdue;
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
    public function canSubmit(?Carbon $submittedAt = null)
    {
        if ($this->status !== 'published') {
            return false;
        }

        $deadline = $this->dueDateTime;

        return ! $deadline || ! ($submittedAt ?? now())->gt($deadline);
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

    /**
     * Extensions students may upload. If teacher requires file upload, only their selected types.
     */
    public function submissionAllowedExtensions(): array
    {
        $documents = ['pdf', 'docx'];
        $images = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'tif', 'tiff', 'heic', 'svg'];
        $all = array_merge($documents, $images);

        if (! $this->requires_file_upload) {
            return $all;
        }

        $types = is_array($this->allowed_file_types) ? $this->allowed_file_types : [];
        $types = array_values(array_unique(array_filter(array_map(
            static fn ($t) => strtolower(trim((string) $t)),
            $types
        ))));

        // Legacy DOC → treat as DOCX
        if (in_array('doc', $types, true)) {
            $types[] = 'docx';
            $types = array_values(array_diff($types, ['doc']));
        }

        if ($types === []) {
            return ['pdf', 'docx'];
        }

        if (in_array('jpg', $types, true) && ! in_array('jpeg', $types, true)) {
            $types[] = 'jpeg';
        }
        if (in_array('tif', $types, true) && ! in_array('tiff', $types, true)) {
            $types[] = 'tiff';
        }

        return array_values(array_intersect($types, $all));
    }

    public function submissionMaxMb(): int
    {
        return max(1, min((int) ($this->max_file_size ?: 10), 50));
    }

    public function submissionAllowedLabels(): string
    {
        $labels = [];
        foreach ($this->submissionAllowedExtensions() as $ext) {
            $labels[] = $ext === 'jpeg' ? 'JPG' : strtoupper($ext);
        }

        return implode(', ', array_unique($labels));
    }

    public function submissionAcceptAttribute(): string
    {
        return collect($this->submissionAllowedExtensions())
            ->map(fn ($ext) => '.' . $ext)
            ->unique()
            ->implode(',');
    }
}
