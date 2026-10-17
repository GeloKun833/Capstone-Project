<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuizAttempt extends Model
{
    protected $fillable = [
        'activity_id',
        'student_id',
        'attempt_number',
        'status',
        'question_order',
        'choice_orders',
        'started_at',
        'submitted_at',
        'expires_at',
        'score',
        'max_score',
        'percentage',
        'passed',
        'letter_grade',
        'feedback',
        'graded_by',
        'graded_at',
    ];

    protected $casts = [
        'question_order' => 'array',
        'choice_orders' => 'array',
        'started_at' => 'datetime',
        'submitted_at' => 'datetime',
        'expires_at' => 'datetime',
        'graded_at' => 'datetime',
        'score' => 'float',
        'max_score' => 'float',
        'percentage' => 'float',
        'attempt_number' => 'integer',
    ];

    public function activity()
    {
        return $this->belongsTo(Activity::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function responses()
    {
        return $this->hasMany(QuizResponse::class);
    }

    public function gradedBy()
    {
        return $this->belongsTo(User::class, 'graded_by');
    }

    public function isOpen(): bool
    {
        return $this->status === 'in_progress';
    }
}
