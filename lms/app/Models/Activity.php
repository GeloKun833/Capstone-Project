<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'activity_type',
        'quiz_questions',
        'instructions',
        'lesson_id',
        'due_date',
        'due_time',
        'available_at',
        'time_limit_minutes',
        'attempts_allowed',
        'passing_score',
        'shuffle_questions',
        'shuffle_choices',
        'show_correct_answers',
        'points',
        'attachments',
        'allows_submission',
        'status',
        'is_active'
    ];

    protected $casts = [
        'due_date' => 'date',
        'points' => 'integer',
        'attachments' => 'array',
        'quiz_questions' => 'array',
        'allows_submission' => 'boolean',
        'shuffle_questions' => 'boolean',
        'shuffle_choices' => 'boolean',
        'show_correct_answers' => 'boolean',
        'is_active' => 'boolean',
    ];

    // Relationships
    public function lesson()
    {
        return $this->belongsTo(Lesson::class);
    }

    public function submissions()
    {
        return $this->hasMany(ActivitySubmission::class);
    }

    public function rubrics()
    {
        return $this->hasMany(ActivityRubric::class);
    }

    public function quizQuestionItems()
    {
        return $this->hasMany(QuizQuestion::class)->orderBy('position');
    }

    public function quizAttempts()
    {
        return $this->hasMany(QuizAttempt::class);
    }

    /**
     * Quiz start is the clock time the teacher entered, on the school clock.
     */
    protected function availableAt(): \Illuminate\Database\Eloquent\Casts\Attribute
    {
        return \Illuminate\Database\Eloquent\Casts\Attribute::make(
            get: function ($value) {
                if ($value === null || $value === '') {
                    return null;
                }

                return \Carbon\Carbon::parse($value, config('app.school_timezone', 'Asia/Manila'));
            },
            set: function ($value) {
                if ($value === null || $value === '') {
                    return null;
                }

                $zone = config('app.school_timezone', 'Asia/Manila');
                $time = $value instanceof \DateTimeInterface
                    ? \Carbon\Carbon::instance($value)->timezone($zone)
                    : \Carbon\Carbon::parse($value, $zone);

                return $time->format('Y-m-d H:i:s');
            },
        );
    }

    public function dueAt(): ?\Carbon\Carbon
    {
        $day = substr((string) $this->getRawOriginal('due_date'), 0, 10);
        if ($day === '') {
            return null;
        }
        $clock = $this->getRawOriginal('due_time')
            ? substr((string) $this->getRawOriginal('due_time'), 0, 5)
            : '23:59';

        return \Carbon\Carbon::parse($day.' '.$clock, 'Asia/Manila');
    }

    public static function kindsFor(?string $gradeLevel): array
    {
        return [
            'quiz' => 'Quiz',
            'practice_exercise' => 'Practice Exercise',
            'project' => 'Project',
            'reflection' => 'Reflection',
        ];
    }

    public static function descriptions(): array
    {
        return [
            'quiz' => 'Teachers create interactive questions with answer keys, points, and automatic grading.',
            'practice_exercise' => 'Teachers provide exercises to reinforce lessons and help students practice specific skills.',
            'project' => 'Teachers assign larger tasks with instructions, required outputs, deadlines, and evaluation criteria.',
            'reflection' => 'Teachers ask students to write about what they learned, their experiences, or their understanding of a lesson.',
        ];
    }

    public function isQuiz(): bool
    {
        return $this->activity_type === 'quiz';
    }

    public function usesFileSubmission(): bool
    {
        return in_array($this->activity_type, ['practice_exercise', 'project', 'reflection'], true);
    }

    public function typeLabel(): string
    {
        $current = self::kindsFor(null);
        $legacy = [
            'hands_on' => 'Hands-on task',
            'oral' => 'Oral response',
            'portfolio' => 'Work sample or portfolio',
            'observation' => 'Teacher observation',
            'worksheet' => 'Worksheet',
            'performance' => 'Performance task',
        ];

        return $current[$this->activity_type] ?? $legacy[$this->activity_type] ?? 'Activity';
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published')->where('is_active', true);
    }

    public function isPublished(): bool
    {
        return $this->status === 'published' && $this->is_active;
    }

    public function isClosed(): bool
    {
        return $this->status === 'closed';
    }

    public function formattedDue(): string
    {
        if (! $this->due_date) {
            return 'No due date';
        }

        $label = $this->due_date->format('M j, Y');
        if ($this->due_time) {
            $label .= ' at '.$this->dueTimeLabel();
        }

        return $label;
    }

    public function dueTimeLabel(): ?string
    {
        if (! $this->due_time) {
            return null;
        }

        $value = strlen((string) $this->due_time) === 5 ? $this->due_time.':00' : (string) $this->due_time;

        return \Carbon\Carbon::createFromFormat('H:i:s', $value)->format('g:i A');
    }

    public function dueTimeInput(): string
    {
        return $this->due_time ? substr((string) $this->due_time, 0, 5) : '';
    }

    public function scopeByLesson($query, $lessonId)
    {
        return $query->where('lesson_id', $lessonId);
    }

    public function scopeAllowsSubmission($query)
    {
        return $query->where('allows_submission', true);
    }

    public function scopeOverdue($query)
    {
        return $query->where('due_date', '<', now());
    }

    // Accessors
    public function getIsOverdueAttribute()
    {
        $due = $this->dueAt();

        return $due ? $due->lt(now('Asia/Manila')) : false;
    }

    public function getSubmissionCountAttribute()
    {
        if (array_key_exists('submissions_count', $this->attributes)) {
            return (int) $this->attributes['submissions_count'];
        }
        if ($this->relationLoaded('submissions')) {
            return $this->submissions->count();
        }

        return $this->submissions()->count();
    }

    public function getGradedCountAttribute()
    {
        if (array_key_exists('graded_submissions_count', $this->attributes)) {
            return (int) $this->attributes['graded_submissions_count'];
        }
        if ($this->relationLoaded('submissions')) {
            return $this->submissions->where('status', 'graded')->count();
        }

        return $this->submissions()->where('status', 'graded')->count();
    }
} 