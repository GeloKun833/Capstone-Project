<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class ClassPost extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'content',
        'file_path',
        'file_name',
        'file_type',
        'teacher_id',
        'subject_id',
        'section_id',
        'academic_year_id',
        'semester_id',
        'type',
        'priority',
        'is_pinned',
        'allows_comments',
        'requires_confirmation',
        'is_active',
        'published_at',
        'expires_at'
    ];

    protected $casts = [
        'is_pinned' => 'boolean',
        'allows_comments' => 'boolean',
        'requires_confirmation' => 'boolean',
        'is_active' => 'boolean',
        'published_at' => 'datetime',
        'expires_at' => 'datetime'
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

    public function comments()
    {
        return $this->hasMany(ClassPostComment::class);
    }

    public function approvedComments()
    {
        return $this->hasMany(ClassPostComment::class)->where('is_approved', true);
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

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopePinned($query)
    {
        return $query->where('is_pinned', true);
    }

    public function scopePublished($query)
    {
        return $query->where('is_active', true)
                    ->where(function ($q) {
                        $q->whereNull('expires_at')
                          ->orWhere('expires_at', '>', now());
                    });
    }

    public function scopeByPriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    public function scopeHighPriority($query)
    {
        return $query->whereIn('priority', ['high', 'urgent']);
    }

    // Accessors
    public function getFileUrlAttribute()
    {
        return $this->file_path ? asset('storage/' . $this->file_path) : null;
    }

    public function getIsExpiredAttribute()
    {
        return $this->expires_at && $this->expires_at < now();
    }

    public function getIsPublishedAttribute()
    {
        return $this->is_active && !$this->is_expired;
    }

    public function getCommentCountAttribute()
    {
        return $this->approvedComments()->count();
    }

    public function getTypeIconAttribute()
    {
        return match($this->type) {
            'announcement' => 'fas fa-bullhorn',
            'resource' => 'fas fa-file-alt',
            'discussion' => 'fas fa-comments',
            'reminder' => 'fas fa-bell',
            default => 'fas fa-file-alt'
        };
    }

    public function getPriorityColorAttribute()
    {
        return match($this->priority) {
            'low' => 'info',
            'normal' => 'primary',
            'high' => 'warning',
            'urgent' => 'danger',
            default => 'primary'
        };
    }

    public function getTypeColorAttribute()
    {
        return match($this->type) {
            'announcement' => 'primary',
            'resource' => 'success',
            'discussion' => 'info',
            'reminder' => 'warning',
            default => 'secondary'
        };
    }

    // Methods
    public function canComment()
    {
        return $this->allows_comments && $this->is_published;
    }

    public function isVisibleTo($user)
    {
        // Check if post is active and not expired
        if (!$this->is_published) {
            return false;
        }

        // Check if user has access to this subject/section
        if ($user->role_name === 'Student') {
            $student = $user->student;
            if ($student && $student->sections->contains($this->section_id)) {
                return true;
            }
        } elseif ($user->role_name === 'Teacher') {
            $teacher = $user->teacher;
            if ($teacher && $teacher->subjects->contains($this->subject_id)) {
                return true;
            }
        } elseif ($user->role_name === 'Admin') {
            return true;
        }

        return false;
    }

    public function togglePin()
    {
        $this->update(['is_pinned' => !$this->is_pinned]);
        return $this->is_pinned;
    }

    public function publish()
    {
        $this->update([
            'is_active' => true,
            'published_at' => now()
        ]);
    }

    public function unpublish()
    {
        $this->update([
            'is_active' => false
        ]);
    }

    public function getFormattedContentAttribute()
    {
        // Convert markdown-like syntax to HTML
        $content = $this->content;
        
        // Convert **text** to <strong>text</strong>
        $content = preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', $content);
        
        // Convert *text* to <em>text</em>
        $content = preg_replace('/\*(.*?)\*/', '<em>$1</em>', $content);
        
        // Convert line breaks to <br>
        $content = nl2br($content);
        
        return $content;
    }
}
