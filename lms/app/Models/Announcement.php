<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Announcement extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'content',
        'attachments',
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
        'created_by',
    ];

    protected $casts = [
        'target_roles' => 'array',
        'target_sections' => 'array',
        'attachments' => 'array',
        'is_pinned' => 'boolean',
        'is_scheduled' => 'boolean',
        'is_active' => 'boolean',
        'scheduled_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    /**
     * Map form audience keys → users.role_name values.
     */
    public static function audienceToRoleNames(?string $audience): array
    {
        return match ($audience) {
            'all' => ['Admin', 'Teacher', 'Student', 'Parent', 'Registrar'],
            'students', 'Student' => ['Student'],
            'teachers', 'Teacher' => ['Teacher'],
            'parents', 'Parent' => ['Parent'],
            'admins', 'Admin' => ['Admin', 'Registrar'],
            'Registrar' => ['Registrar'],
            default => [],
        };
    }

    /**
     * Audience keys that should match a logged-in role (for listing).
     */
    public static function audienceKeysForRole(?string $roleName): array
    {
        return match ($roleName) {
            'Student' => ['all', 'students', 'Student'],
            'Teacher' => ['all', 'teachers', 'Teacher'],
            'Parent' => ['all', 'parents', 'Parent'],
            'Admin' => ['all', 'admins', 'Admin'],
            'Registrar' => ['all', 'admins', 'Admin', 'Registrar'],
            default => ['all'],
        };
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeActive(Builder $query)
    {
        return $query->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->where(function ($q) {
                $q->where('is_scheduled', false)
                    ->orWhereNull('is_scheduled')
                    ->orWhereNull('scheduled_at')
                    ->orWhere('scheduled_at', '<=', now());
            });
    }

    public function scopeForRole(Builder $query, $role)
    {
        $keys = self::audienceKeysForRole($role);

        return $query->where(function ($q) use ($keys, $role) {
            $q->whereIn('target_audience', $keys);

            foreach ($keys as $key) {
                $q->orWhereJsonContains('target_roles', $key);
            }

            // Legacy rows that stored PascalCase role in target_audience
            if ($role) {
                $q->orWhere('target_audience', $role);
            }
        });
    }

    public function scopePinned(Builder $query)
    {
        return $query->where('is_pinned', true);
    }

    public function isVisibleTo(User $user): bool
    {
        if (!$this->is_active || ($this->expires_at && $this->expires_at->isPast())) {
            return false;
        }

        if ($this->is_scheduled && $this->scheduled_at && $this->scheduled_at->isFuture()) {
            return false;
        }

        $keys = self::audienceKeysForRole($user->role_name);

        if (in_array($this->target_audience, $keys, true)) {
            return true;
        }

        $roles = $this->target_roles ?? [];
        foreach ($roles as $r) {
            if (in_array($r, $keys, true)) {
                return true;
            }
            if (in_array($user->role_name, self::audienceToRoleNames((string) $r), true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Resolve recipient role_name list for notifications.
     */
    public function recipientRoleNames(): array
    {
        if ($this->target_audience === 'all') {
            // If specific roles also selected, narrow to those; otherwise everyone
            $extra = array_filter($this->target_roles ?? []);
            if (!empty($extra)) {
                $roles = [];
                foreach ($extra as $key) {
                    $roles = array_merge($roles, self::audienceToRoleNames((string) $key));
                }
                return array_values(array_unique($roles));
            }

            return self::audienceToRoleNames('all');
        }

        $roles = self::audienceToRoleNames($this->target_audience);
        foreach ($this->target_roles ?? [] as $key) {
            $roles = array_merge($roles, self::audienceToRoleNames((string) $key));
        }

        return array_values(array_unique($roles));
    }

    public function getPriorityColorAttribute()
    {
        return match ($this->priority) {
            'urgent' => 'danger',
            'high' => 'warning',
            'normal' => 'info',
            'low' => 'secondary',
            default => 'info',
        };
    }

    public function getTypeIconAttribute()
    {
        return match ($this->type) {
            'emergency' => 'fas fa-exclamation-triangle',
            'event' => 'fas fa-calendar-alt',
            'academic' => 'fas fa-graduation-cap',
            'reminder' => 'fas fa-bell',
            default => 'fas fa-bullhorn',
        };
    }
}
