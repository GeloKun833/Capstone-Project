<?php

namespace App\Services;

use App\Models\Activity;
use App\Models\ActivitySubmission;
use App\Models\QuizAttempt;
use App\Models\QuizQuestion;
use App\Models\QuizResponse;
use App\Models\Student;
use App\Support\SafeUpload;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class QuizService
{
    public function syncQuestions(Activity $activity, Request $request, bool $publishing, bool $locked): void
    {
        $questions = $this->normalizeQuestions($request, $publishing);
        $existing = $activity->quizQuestionItems()->orderBy('position')->get()->keyBy('id');

        if ($locked) {
            $this->assertStructureUnchanged($existing, $questions);
        }

        $keep = [];
        foreach ($questions as $index => $question) {
            $image = $request->file('questions.'.$index.'.image');
            $path = $question['existing_image'] ?: null;
            $name = $question['existing_name'] ?: null;
            if ($image) {
                $stored = SafeUpload::store($image, 'quiz-questions');
                $path = $stored['path'];
                $name = $stored['original_name'];
            }

            $payload = [
                'position' => $index + 1,
                'prompt' => $question['prompt'],
                'explanation' => $question['explanation'] ?: null,
                'attachment_path' => $path,
                'attachment_name' => $name,
            ];

            if (! $locked) {
                $payload['question_type'] = $question['type'];
                $payload['points'] = $question['points'];
                $payload['choices'] = $question['choices'];
                $payload['answer_key'] = $question['answer_key'];
            }

            $row = null;
            if (! empty($question['id'])) {
                $row = $existing->get((int) $question['id']);
            }
            if ($row) {
                $row->update($payload);
            } else {
                if ($locked) {
                    throw ValidationException::withMessages([
                        'questions' => 'Students have already started this quiz, so questions cannot be added.',
                    ]);
                }
                $row = $activity->quizQuestionItems()->create($payload + [
                    'question_type' => $question['type'],
                    'points' => $question['points'],
                    'choices' => $question['choices'],
                    'answer_key' => $question['answer_key'],
                ]);
            }
            $keep[] = $row->id;
        }

        if ($locked && count($keep) !== $existing->count()) {
            throw ValidationException::withMessages([
                'questions' => 'Students have already started this quiz, so questions cannot be removed.',
            ]);
        }

        if (! $locked) {
            $activity->quizQuestionItems()->whereNotIn('id', $keep ?: [0])->delete();
        }

        $total = (int) $activity->quizQuestionItems()->sum('points');
        $activity->update([
            'points' => $total > 0 ? $total : null,
            'quiz_questions' => null,
        ]);
    }

    public function normalizeQuestions(Request $request, bool $publishing): array
    {
        $raw = $request->input('questions', []);
        if (! is_array($raw)) {
            $raw = [];
        }

        $questions = [];
        $errors = [];
        foreach (array_values($raw) as $index => $question) {
            if (! is_array($question)) {
                continue;
            }
            $type = (string) ($question['type'] ?? 'multiple_choice');
            $prompt = trim((string) ($question['prompt'] ?? ''));
            $points = (int) ($question['points'] ?? 0);
            $explanation = trim((string) ($question['explanation'] ?? ''));
            if ($prompt === '' && $points === 0 && trim((string) ($question['choices'][0] ?? '')) === '') {
                continue;
            }
            $number = $index + 1;
            if (! array_key_exists($type, QuizQuestion::TYPES)) {
                $errors["questions.$index.type"] = "Question {$number} must be Multiple Choice or True or False.";
                $type = 'multiple_choice';
            }
            $choices = [];
            $answerKey = [];
            if ($type === 'true_false') {
                $choices = ['True', 'False'];
                $marked = array_map('intval', (array) ($question['correct'] ?? []));
                $answerKey = $marked === [] ? [] : [(int) $marked[0]];
            } elseif ($type === 'multiple_choice') {
                $marked = array_map('intval', (array) ($question['correct'] ?? []));
                foreach (array_values((array) ($question['choices'] ?? [])) as $slot => $choice) {
                    $choice = trim((string) $choice);
                    if ($choice === '') {
                        continue;
                    }
                    if (in_array($slot, $marked, true)) {
                        $answerKey[] = count($choices);
                    }
                    $choices[] = $choice;
                    if (count($choices) === 6) {
                        break;
                    }
                }
            }

            if ($publishing) {
                if ($prompt === '') {
                    $errors["questions.$index.prompt"] = "Write question {$number}.";
                }
                if ($points < 1 || $points > 100) {
                    $errors["questions.$index.points"] = "Question {$number} points must be from 1 to 100.";
                }
                if ($type === 'multiple_choice' && (count($choices) < 2 || count($answerKey) !== 1)) {
                    $errors["questions.$index.correct"] = "Question {$number} needs at least two choices and one correct answer.";
                }
                if ($type === 'true_false' && ! in_array($answerKey[0] ?? -1, [0, 1], true)) {
                    $errors["questions.$index.correct"] = "Mark True or False for question {$number}.";
                }
            }

            $image = $request->file('questions.'.$index.'.image');
            if ($image && ! in_array(strtolower($image->getClientOriginalExtension()), ['jpg', 'jpeg', 'png', 'webp'], true)) {
                $errors["questions.$index.image"] = "Question {$number} image must be a JPG, PNG, or WebP file.";
            }
            if ($image && $image->getSize() > 5 * 1024 * 1024) {
                $errors["questions.$index.image"] = "Question {$number} image must be 5 MB or smaller.";
            }

            $questions[] = [
                'id' => isset($question['id']) && $question['id'] !== '' ? (int) $question['id'] : null,
                'type' => $type,
                'prompt' => $prompt,
                'points' => max(0, min(100, $points)),
                'explanation' => $explanation,
                'choices' => in_array($type, ['short_answer', 'essay'], true) ? null : $choices,
                'answer_key' => in_array($type, ['short_answer', 'essay'], true) ? null : $answerKey,
                'existing_image' => $question['existing_image'] ?? null,
                'existing_name' => $question['existing_name'] ?? null,
            ];
        }

        if ($publishing && $questions === []) {
            $errors['questions'] = 'Add at least one quiz question before posting.';
        }
        if (count($questions) > 30) {
            $errors['questions'] = 'A quiz can have up to 30 questions.';
        }
        if ($errors) {
            throw ValidationException::withMessages($errors);
        }

        return $questions;
    }

    public function formSeed(Activity $activity): array
    {
        $rows = $activity->quizQuestionItems()->orderBy('position')->get();
        if ($rows->isEmpty() && is_array($activity->quiz_questions)) {
            return collect($activity->quiz_questions)->map(function ($question) {
                return [
                    'id' => null,
                    'type' => 'multiple_choice',
                    'prompt' => $question['prompt'] ?? '',
                    'points' => (int) ($question['points'] ?? 1),
                    'explanation' => '',
                    'choices' => array_values($question['choices'] ?? []),
                    'correct' => [(int) ($question['answer'] ?? 0)],
                    'image' => null,
                    'image_name' => null,
                ];
            })->all();
        }

        return $rows->map(function (QuizQuestion $question) {
            return [
                'id' => $question->id,
                'type' => $question->question_type,
                'prompt' => $question->prompt,
                'points' => (int) $question->points,
                'explanation' => $question->explanation ?? '',
                'choices' => array_values($question->choices ?? []),
                'correct' => array_map('intval', $question->answer_key ?? []),
                'image' => $question->attachment_path,
                'image_name' => $question->attachment_name,
            ];
        })->all();
    }

    public function studentForLesson(Activity $activity, Student $student): void
    {
        $activity->loadMissing('lesson');
        if (! $activity->isQuiz() || ! $activity->isPublished()) {
            abort(403, 'This quiz is not available.');
        }
        $enrolled = $student->sections()->where('section_id', $activity->lesson->section_id)->exists();
        if (! $enrolled) {
            abort(403, 'You are not enrolled in this class.');
        }
    }

    public function availabilityMessage(Activity $activity): ?string
    {
        $now = now('Asia/Manila');
        if ($activity->available_at && $activity->available_at->timezone('Asia/Manila')->gt($now)) {
            return 'This quiz starts '.$activity->available_at->timezone('Asia/Manila')->format('M j, Y g:i A').'. You can see it now. The questions start at that time.';
        }
        $due = $activity->dueAt();
        if ($due && $due->lt($now)) {
            return 'The deadline for this quiz has passed. It was due '.$due->format('M j, Y g:i A').'.';
        }

        return null;
    }

    public function start(Activity $activity, Student $student): QuizAttempt
    {
        $this->studentForLesson($activity, $student);

        return DB::transaction(function () use ($activity, $student) {
            Activity::where('id', $activity->id)->lockForUpdate()->first();
            $open = QuizAttempt::where('activity_id', $activity->id)
                ->where('student_id', $student->id)
                ->where('status', 'in_progress')
                ->lockForUpdate()
                ->first();
            if ($open) {
                $open = $this->expireIfNeeded($open);
                if ($open->status === 'in_progress') {
                    return $open->fresh();
                }
            }

            $message = $this->availabilityMessage($activity);
            if ($message) {
                throw ValidationException::withMessages(['quiz' => $message]);
            }

            $used = QuizAttempt::where('activity_id', $activity->id)
                ->where('student_id', $student->id)
                ->whereIn('status', ['submitted', 'graded'])
                ->count();
            $allowed = max(1, (int) $activity->attempts_allowed);
            if ($used >= $allowed) {
                throw ValidationException::withMessages([
                    'quiz' => 'You have used all '.$allowed.' attempt'.($allowed === 1 ? '' : 's').' for this quiz.',
                ]);
            }

            $questions = $activity->quizQuestionItems()->orderBy('position')->get();
            if ($questions->isEmpty()) {
                throw ValidationException::withMessages(['quiz' => 'This quiz has no questions yet.']);
            }

            $order = $questions->pluck('id')->all();
            if ($activity->shuffle_questions) {
                shuffle($order);
            }
            $choiceOrders = [];
            foreach ($questions as $question) {
                $indexes = array_keys(array_values($question->choices ?? []));
                if ($activity->shuffle_choices && count($indexes) > 1) {
                    shuffle($indexes);
                }
                $choiceOrders[$question->id] = $indexes;
            }

            $started = now();
            $expires = $activity->time_limit_minutes
                ? $started->copy()->addMinutes((int) $activity->time_limit_minutes)
                : null;

            return QuizAttempt::create([
                'activity_id' => $activity->id,
                'student_id' => $student->id,
                'attempt_number' => $used + 1,
                'status' => 'in_progress',
                'question_order' => $order,
                'choice_orders' => $choiceOrders,
                'started_at' => $started,
                'expires_at' => $expires,
                'max_score' => (int) $questions->sum('points'),
            ]);
        });
    }

    public function saveAnswers(QuizAttempt $attempt, array $answers): QuizAttempt
    {
        if ($attempt->status !== 'in_progress') {
            return $attempt;
        }
        $attempt = $this->expireIfNeeded($attempt);
        if ($attempt->status !== 'in_progress') {
            return $attempt;
        }

        $questions = $attempt->activity->quizQuestionItems()->get()->keyBy('id');
        DB::transaction(function () use ($attempt, $answers, $questions) {
            foreach ($answers as $questionId => $answer) {
                $question = $questions->get((int) $questionId);
                $order = array_map('intval', $attempt->question_order ?? []);
                if (! $question || ! in_array((int) $questionId, $order, true)) {
                    continue;
                }
                QuizResponse::updateOrCreate(
                    ['quiz_attempt_id' => $attempt->id, 'quiz_question_id' => $question->id],
                    ['answer' => $this->cleanAnswer($question, $answer)]
                );
            }
        });

        return $attempt->fresh('responses');
    }

    public function submit(QuizAttempt $attempt): QuizAttempt
    {
        if ($attempt->status !== 'in_progress') {
            return $attempt->fresh('responses');
        }

        return DB::transaction(function () use ($attempt) {
            $attempt = QuizAttempt::where('id', $attempt->id)->lockForUpdate()->first();
            if ($attempt->status !== 'in_progress') {
                return $attempt;
            }
            $this->gradeObjective($attempt);
            $this->syncActivitySubmission($attempt->activity_id, $attempt->student_id);

            return $attempt->fresh(['responses.question']);
        });
    }

    public function gradeManual(QuizAttempt $attempt, array $scores, array $questionFeedback, ?string $feedback, int $teacherUserId): QuizAttempt
    {
        return DB::transaction(function () use ($attempt, $scores, $questionFeedback, $feedback, $teacherUserId) {
            $attempt->load('responses.question', 'activity.quizQuestionItems');
            foreach ($attempt->activity->quizQuestionItems as $question) {
                if ($question->isAutoGraded()) {
                    continue;
                }
                if (! array_key_exists($question->id, $scores)) {
                    continue;
                }
                $earned = $scores[$question->id];
                if ($earned === null || $earned === '') {
                    continue;
                }
                $earned = round((float) $earned, 2);
                if ($earned < 0 || $earned > $question->points) {
                    throw ValidationException::withMessages([
                        'scores.'.$question->id => 'Score for “'.$question->prompt.'” must be from 0 to '.$question->points.'.',
                    ]);
                }
                $response = $attempt->responses->firstWhere('quiz_question_id', $question->id);
                if (! $response) {
                    $response = $attempt->responses()->create([
                        'quiz_question_id' => $question->id,
                        'answer' => ['text' => ''],
                    ]);
                }
                $response->update([
                    'points_earned' => $earned,
                    'is_correct' => $earned >= $question->points ? true : ($earned <= 0 ? false : null),
                    'feedback' => $questionFeedback[$question->id] ?? $response->feedback,
                ]);
            }

            $attempt->feedback = $feedback;
            $attempt->graded_by = $teacherUserId;
            $this->finalizeTotals($attempt);
            $attempt->save();
            $this->syncActivitySubmission($attempt->activity_id, $attempt->student_id);

            return $attempt->fresh(['responses.question', 'student']);
        });
    }

    public function multipleSelectPoints(array $correct, array $selected, float $points): float
    {
        $correct = array_values(array_unique(array_map('intval', $correct)));
        $selected = array_values(array_unique(array_map('intval', $selected)));
        $correctCount = count($correct);
        if ($correctCount === 0 || $points <= 0) {
            return 0.0;
        }
        $right = count(array_intersect($selected, $correct));
        $wrong = count(array_diff($selected, $correct));

        return round(max(0, (($right - $wrong) / $correctCount) * $points), 2);
    }

    public function expireIfNeeded(QuizAttempt $attempt): QuizAttempt
    {
        if ($attempt->status !== 'in_progress') {
            return $attempt;
        }
        $attempt->loadMissing('activity');
        $now = now();
        $due = $attempt->activity->dueAt();
        $expired = ($attempt->expires_at && $attempt->expires_at->lte($now))
            || ($due && $due->lte(now('Asia/Manila')));
        if (! $expired) {
            return $attempt;
        }

        return $this->submit($attempt);
    }

    public function remainingSeconds(QuizAttempt $attempt): ?int
    {
        if (! $attempt->expires_at || $attempt->status !== 'in_progress') {
            return null;
        }

        return max(0, $attempt->expires_at->getTimestamp() - now()->getTimestamp());
    }

    private function gradeObjective(QuizAttempt $attempt): void
    {
        $attempt->load('activity.quizQuestionItems', 'responses');
        $responses = $attempt->responses->keyBy('quiz_question_id');
        $needsManual = false;

        foreach ($attempt->activity->quizQuestionItems as $question) {
            $response = $responses->get($question->id);
            $answer = $response?->answer ?? [];
            if (! $question->isAutoGraded()) {
                $needsManual = true;
                if ($response && $response->points_earned === null) {
                    continue;
                }
                if (! $response) {
                    QuizResponse::create([
                        'quiz_attempt_id' => $attempt->id,
                        'quiz_question_id' => $question->id,
                        'answer' => ['text' => ''],
                        'points_earned' => null,
                    ]);
                }
                continue;
            }

            $earned = $this->autoPoints($question, $answer);
            $payload = [
                'answer' => $answer,
                'points_earned' => $earned,
                'is_correct' => $earned >= $question->points ? true : ($earned <= 0 ? false : null),
            ];
            if ($response) {
                $response->update($payload);
            } else {
                QuizResponse::create($payload + [
                    'quiz_attempt_id' => $attempt->id,
                    'quiz_question_id' => $question->id,
                ]);
            }
        }

        $attempt->submitted_at = $attempt->submitted_at ?: now();
        $attempt->status = $needsManual ? 'submitted' : 'graded';
        if (! $needsManual) {
            $attempt->graded_at = now();
        }
        $this->finalizeTotals($attempt);
        $attempt->save();
    }

    private function autoPoints(QuizQuestion $question, array $answer): float
    {
        $selected = array_map('intval', (array) ($answer['selected'] ?? []));
        $key = array_map('intval', $question->answer_key ?? []);
        if ($question->question_type === 'multiple_select') {
            return $this->multipleSelectPoints($key, $selected, (float) $question->points);
        }
        $picked = $selected[0] ?? null;

        return $picked !== null && in_array((int) $picked, $key, true) ? (float) $question->points : 0.0;
    }

    private function finalizeTotals(QuizAttempt $attempt): void
    {
        $attempt->load('responses', 'activity.quizQuestionItems');
        $max = (float) $attempt->activity->quizQuestionItems->sum('points');
        $pending = $attempt->activity->quizQuestionItems->contains(function (QuizQuestion $question) use ($attempt) {
            if ($question->isAutoGraded()) {
                return false;
            }
            $response = $attempt->responses->firstWhere('quiz_question_id', $question->id);

            return ! $response || $response->points_earned === null;
        });
        $score = round((float) $attempt->responses->sum(fn ($response) => (float) ($response->points_earned ?? 0)), 2);
        $attempt->max_score = $max;
        $attempt->score = $pending ? null : $score;
        if ($pending || $max <= 0) {
            $attempt->percentage = null;
            $attempt->passed = null;
            $attempt->letter_grade = null;
            $attempt->status = 'submitted';

            return;
        }
        $percentage = round(($score / $max) * 100, 2);
        $attempt->score = $score;
        $attempt->percentage = $percentage;
        $attempt->passed = $attempt->activity->passing_score === null
            ? null
            : $percentage >= (int) $attempt->activity->passing_score;
        $attempt->letter_grade = $this->letterFromPercentage($percentage);
        $attempt->status = 'graded';
        $attempt->graded_at = $attempt->graded_at ?: now();
    }

    public function syncActivitySubmission(int $activityId, int $studentId): void
    {
        $best = QuizAttempt::where('activity_id', $activityId)
            ->where('student_id', $studentId)
            ->where('status', 'graded')
            ->orderByDesc('percentage')
            ->orderByDesc('id')
            ->first();
        $source = $best ?: QuizAttempt::where('activity_id', $activityId)
            ->where('student_id', $studentId)
            ->where('status', 'submitted')
            ->latest('id')
            ->first();
        if (! $source) {
            return;
        }

        $submission = ActivitySubmission::where('activity_id', $activityId)
            ->where('student_id', $studentId)
            ->first();
        $complete = $source->status === 'graded';
        $payload = [
            'submitted_at' => $source->submitted_at,
            'status' => $complete ? 'graded' : 'submitted',
            'total_score' => $complete ? $source->score : null,
            'max_possible_score' => $source->max_score,
            'percentage' => $complete ? $source->percentage : null,
            'letter_grade' => $complete ? $source->letter_grade : null,
            'feedback' => $source->feedback,
            'graded_by' => $complete ? $source->graded_by : null,
            'graded_at' => $complete ? $source->graded_at : null,
        ];
        if ($submission) {
            $submission->update($payload);
        } else {
            ActivitySubmission::create($payload + [
                'activity_id' => $activityId,
                'student_id' => $studentId,
            ]);
        }
    }

    public function letterFromPercentage(float $percentage): string
    {
        return match (true) {
            $percentage >= 90 => 'A',
            $percentage >= 85 => 'B+',
            $percentage >= 80 => 'B',
            $percentage >= 75 => 'C',
            $percentage >= 70 => 'D',
            default => 'F',
        };
    }

    private function cleanAnswer(QuizQuestion $question, mixed $answer): array
    {
        if (! is_array($answer)) {
            $answer = [];
        }
        if (in_array($question->question_type, ['short_answer', 'essay'], true)) {
            return ['text' => mb_substr(trim((string) ($answer['text'] ?? '')), 0, $question->question_type === 'essay' ? 10000 : 1000)];
        }
        $allowed = array_keys(array_values($question->choices ?? []));
        $selected = array_values(array_unique(array_map('intval', (array) ($answer['selected'] ?? []))));
        $selected = array_values(array_filter($selected, fn ($index) => in_array($index, $allowed, true)));
        if ($question->question_type !== 'multiple_select') {
            $selected = array_slice($selected, 0, 1);
        }

        return ['selected' => $selected];
    }

    private function assertStructureUnchanged($existing, array $questions): void
    {
        if (count($questions) !== $existing->count()) {
            throw ValidationException::withMessages([
                'questions' => 'Students have already started this quiz. You can edit wording, dates, and instructions, but not the questions, points, or answer key.',
            ]);
        }
        foreach ($questions as $question) {
            $row = $existing->get((int) ($question['id'] ?? 0));
            if (! $row || $row->question_type !== $question['type'] || (int) $row->points !== (int) $question['points']) {
                throw ValidationException::withMessages([
                    'questions' => 'Students have already started this quiz. Points and question types stay locked.',
                ]);
            }
            $sameChoices = json_encode(array_values($row->choices ?? [])) === json_encode(array_values($question['choices'] ?? []));
            $sameKey = json_encode(array_map('intval', $row->answer_key ?? [])) === json_encode(array_map('intval', $question['answer_key'] ?? []));
            if (! $sameChoices || ! $sameKey) {
                throw ValidationException::withMessages([
                    'questions' => 'Students have already started this quiz. The answer key stays locked.',
                ]);
            }
        }
    }
}
