<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuizResponse extends Model
{
    protected $fillable = [
        'quiz_attempt_id',
        'quiz_question_id',
        'answer',
        'points_earned',
        'is_correct',
        'feedback',
    ];

    protected $casts = [
        'answer' => 'array',
        'points_earned' => 'float',
    ];

    public function attempt()
    {
        return $this->belongsTo(QuizAttempt::class, 'quiz_attempt_id');
    }

    public function question()
    {
        return $this->belongsTo(QuizQuestion::class, 'quiz_question_id');
    }
}
