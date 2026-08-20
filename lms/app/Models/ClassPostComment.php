<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ClassPostComment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'class_post_id',
        'user_id',
        'content',
        'file_path',
        'file_name',
        'file_type',
        'parent_id',
        'is_approved',
        'is_active'
    ];

    protected $casts = [
        'is_approved' => 'boolean',
        'is_active' => 'boolean'
    ];

    // Relationships
    public function classPost()
    {
        return $this->belongsTo(ClassPost::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function parent()
    {
        return $this->belongsTo(ClassPostComment::class, 'parent_id');
    }

    public function replies()
    {
        return $this->hasMany(ClassPostComment::class, 'parent_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeApproved($query)
    {
        return $query->where('is_approved', true);
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByPost($query, $postId)
    {
        return $query->where('class_post_id', $postId);
    }

    public function scopeTopLevel($query)
    {
        return $query->whereNull('parent_id');
    }

    public function scopeReplies($query)
    {
        return $query->whereNotNull('parent_id');
    }

    // Accessors
    public function getFileUrlAttribute()
    {
        return $this->file_path ? asset('storage/' . $this->file_path) : null;
    }

    public function getIsReplyAttribute()
    {
        return !is_null($this->parent_id);
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

    // Methods
    public function approve()
    {
        $this->update(['is_approved' => true]);
    }

    public function disapprove()
    {
        $this->update(['is_approved' => false]);
    }

    public function canEdit($user)
    {
        return $user->id === $this->user_id || 
               $user->role_name === 'Admin' || 
               $user->role_name === 'Teacher';
    }

    public function canDelete($user)
    {
        return $user->id === $this->user_id || 
               $user->role_name === 'Admin' || 
               $user->role_name === 'Teacher';
    }
}
