<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class Message extends Model
{
    use HasFactory;

    protected $fillable = [
        'subject',
        'content',
        'sender_id',
        'recipient_id',
        'student_id',
        'type',
        'priority',
        'is_read',
        'read_at',
        'is_archived',
        'archived_at'
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'is_archived' => 'boolean',
        'read_at' => 'datetime',
        'archived_at' => 'datetime'
    ];

    /**
     * Get the sender of the message
     */
    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    /**
     * Get the recipient of the message
     */
    public function recipient()
    {
        return $this->belongsTo(User::class, 'recipient_id');
    }

    /**
     * Get the student associated with the message
     */
    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Scope to get unread messages
     */
    public function scopeUnread(Builder $query)
    {
        return $query->where('is_read', false);
    }

    /**
     * Scope to get read messages
     */
    public function scopeRead(Builder $query)
    {
        return $query->where('is_read', true);
    }

    /**
     * Scope to get archived messages
     */
    public function scopeArchived(Builder $query)
    {
        return $query->where('is_archived', true);
    }

    /**
     * Scope to get non-archived messages
     */
    public function scopeNotArchived(Builder $query)
    {
        return $query->where('is_archived', false);
    }

    /**
     * Scope to get messages for a specific user (as recipient)
     */
    public function scopeForUser(Builder $query, $userId)
    {
        return $query->where('recipient_id', $userId);
    }

    /**
     * Scope to get messages from a specific user (as sender)
     */
    public function scopeFromUser(Builder $query, $userId)
    {
        return $query->where('sender_id', $userId);
    }

    /**
     * Mark message as read
     */
    public function markAsRead()
    {
        if (!$this->is_read) {
            $this->update([
                'is_read' => true,
                'read_at' => now()
            ]);
        }
    }

    /**
     * Mark message as unread
     */
    public function markAsUnread()
    {
        $this->update([
            'is_read' => false,
            'read_at' => null
        ]);
    }

    /**
     * Archive message
     */
    public function archive()
    {
        if (!$this->is_archived) {
            $this->update([
                'is_archived' => true,
                'archived_at' => now()
            ]);
        }
    }

    /**
     * Unarchive message
     */
    public function unarchive()
    {
        $this->update([
            'is_archived' => false,
            'archived_at' => null
        ]);
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
            'academic' => 'fas fa-graduation-cap',
            'behavioral' => 'fas fa-exclamation-circle',
            'attendance' => 'fas fa-calendar-check',
            'grade' => 'fas fa-chart-line',
            default => 'fas fa-envelope'
        };
    }

    /**
     * Get conversation between two users
     */
    public static function getConversation($user1Id, $user2Id)
    {
        return static::where(function ($query) use ($user1Id, $user2Id) {
            $query->where('sender_id', $user1Id)
                  ->where('recipient_id', $user2Id);
        })->orWhere(function ($query) use ($user1Id, $user2Id) {
            $query->where('sender_id', $user2Id)
                  ->where('recipient_id', $user1Id);
        })->orderBy('created_at', 'asc');
    }
}
