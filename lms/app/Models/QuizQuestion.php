<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuizQuestion extends Model
{
    public const TYPES = [
        'multiple_choice' => 'Multiple Choice',
        'true_false' => 'True or False',
    ];

    protected $fillable = [
        'activity_id',
        'position',
        'question_type',
        'prompt',
        'attachment_path',
        'attachment_name',
        'points',
        'explanation',
        'choices',
        'answer_key',
    ];

    protected $casts = [
        'choices' => 'array',
        'answer_key' => 'array',
        'points' => 'integer',
        'position' => 'integer',
    ];

    public function activity()
    {
        return $this->belongsTo(Activity::class);
    }

    public function responses()
    {
        return $this->hasMany(QuizResponse::class);
    }

    public function typeLabel(): string
    {
        return self::TYPES[$this->question_type] ?? 'Question';
    }

    public function isAutoGraded(): bool
    {
        return in_array($this->question_type, ['multiple_choice', 'true_false', 'multiple_select'], true);
    }

    public function forStudent(array $choiceOrder = []): array
    {
        $choices = array_values($this->choices ?? []);
        $order = $choiceOrder !== [] ? $choiceOrder : array_keys($choices);
        $visible = [];
        foreach ($order as $index) {
            $index = (int) $index;
            if (! array_key_exists($index, $choices)) {
                continue;
            }
            $visible[] = ['index' => $index, 'text' => $choices[$index]];
        }

        return [
            'id' => $this->id,
            'type' => $this->question_type,
            'type_label' => $this->typeLabel(),
            'prompt' => $this->prompt,
            'points' => (int) $this->points,
            'image' => $this->attachment_path ? asset('storage/'.$this->attachment_path) : null,
            'choices' => $visible,
        ];
    }
}
