<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class Announcement extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'content',
        'type',
        'priority',
        'target_audience',
        'target_roles',
        'target_sections',
        'is_pinned',
        'is_scheduled',
        'scheduled_at',
        'expires_at',
        'is_active',
        'created_by'
    ];

    protected $casts = [
        'target_roles' => 'array',
        'target_sections' => 'array',
        'is_pinned' => 'boolean',
        'is_scheduled' => 'boolean',
        'is_active' => 'boolean',
        'scheduled_at' => 'datetime',
        'expires_at' => 'datetime'
    ];

    /**
     * Get the user who created the announcement
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope to get active announcements
     */
    public function scopeActive(Builder $query)
    {
        return $query->where('is_active', true)
                    ->where(function ($q) {
                        $q->whereNull('expires_at')
                          ->orWhere('expires_at', '>', now());
                    });
    }

    /**
     * Scope to get announcements for a specific user role
     */
    public function scopeForRole(Builder $query, $role)
    {
        return $query->where(function ($q) use ($role) {
            $q->where('target_audience', 'all')
              ->orWhere('target_audience', $role)
              ->orWhereJsonContains('target_roles', $role);
        });
    }

    /**
     * Scope to get pinned announcements
     */
    public function scopePinned(Builder $query)
    {
        return $query->where('is_pinned', true);
    }

    /**
     * Check if announcement is visible to a specific user
     */
    public function isVisibleTo(User $user)
    {
        // Check if announcement is active and not expired
        if (!$this->is_active || ($this->expires_at && $this->expires_at->isPast())) {
            return false;
        }

        // Check if scheduled announcement should be shown
        if ($this->is_scheduled && $this->scheduled_at && $this->scheduled_at->isFuture()) {
            return false;
        }

        // Check target audience
        if ($this->target_audience === 'all') {
            return true;
        }

        if ($this->target_audience === $user->role_name) {
            return true;
        }

        // Check specific roles
        if ($this->target_roles && in_array($user->role_name, $this->target_roles)) {
            return true;
        }

        return false;
    }

    /**
     * Get priority color class
     */
    public function getPriorityColorAttribute()
    {
        return match($this->priority) {
            'urgent' => 'danger',
            'high' => 'warning',
            'normal' => 'info',
            'low' => 'secondary',
            default => 'info'
        };
    }

    /**
     * Get type icon
     */
    public function getTypeIconAttribute()
    {
        return match($this->type) {
            'emergency' => 'fas fa-exclamation-triangle',
            'event' => 'fas fa-calendar-alt',
            'academic' => 'fas fa-graduation-cap',
            'reminder' => 'fas fa-bell',
            default => 'fas fa-bullhorn'
        };
    }
}
