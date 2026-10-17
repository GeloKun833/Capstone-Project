<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Lesson;
use App\Models\QuizAttempt;
use App\Services\QuizService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class QuizController extends Controller
{
    public function __construct(private QuizService $quizzes)
    {
    }

    public function preview(Lesson $lesson, Activity $activity)
    {
        $this->authorizeTeacher($lesson, $activity);
        $activity->load('quizQuestionItems', 'lesson.section', 'lesson.subject');
        $questions = $activity->quizQuestionItems->map(fn ($question) => $question->forStudent())->values();

        return view('quizzes.preview', compact('lesson', 'activity', 'questions'));
    }

    public function results(Lesson $lesson, Activity $activity)
    {
        $this->authorizeTeacher($lesson, $activity);
        $attempts = $activity->quizAttempts()
            ->with('student')
            ->orderByDesc('submitted_at')
            ->orderByDesc('id')
            ->get();

        return view('quizzes.results', compact('lesson', 'activity', 'attempts'));
    }

    public function grade(Lesson $lesson, Activity $activity, QuizAttempt $attempt)
    {
        $this->authorizeTeacher($lesson, $activity);
        $this->assertAttempt($activity, $attempt);
        $attempt->load('responses', 'student', 'activity.quizQuestionItems');

        return view('quizzes.grade', compact('lesson', 'activity', 'attempt'));
    }

    public function saveGrade(Request $request, Lesson $lesson, Activity $activity, QuizAttempt $attempt)
    {
        $this->authorizeTeacher($lesson, $activity);
        $this->assertAttempt($activity, $attempt);
        if ($attempt->status === 'in_progress') {
            return back()->with('error', 'This attempt has not been submitted.');
        }

        $scores = $request->input('scores', []);
        $feedback = $request->input('question_feedback', []);
        if (! is_array($scores)) {
            $scores = [];
        }
        if (! is_array($feedback)) {
            $feedback = [];
        }

        $this->quizzes->gradeManual(
            $attempt,
            $scores,
            $feedback,
            $request->input('feedback'),
            (int) Auth::id()
        );

        return redirect()->route('lessons.activities.quiz-grade', [$lesson, $activity, $attempt])
            ->with('success', 'Grading saved. The term grade is unchanged until you encode it.');
    }

    public function show(Lesson $lesson, Activity $activity)
    {
        $student = $this->student($lesson, $activity);
        $activity->load('lesson.section', 'lesson.subject', 'quizQuestionItems');
        $attempt = $activity->quizAttempts()
            ->where('student_id', $student->id)
            ->where('status', 'in_progress')
            ->latest('id')
            ->first();
        if ($attempt) {
            $attempt = $this->quizzes->expireIfNeeded($attempt);
            if ($attempt->status !== 'in_progress') {
                $attempt = null;
            }
        }

        $used = $activity->quizAttempts()
            ->where('student_id', $student->id)
            ->whereIn('status', ['submitted', 'graded'])
            ->count();
        $latest = $activity->quizAttempts()
            ->with('responses')
            ->where('student_id', $student->id)
            ->whereIn('status', ['submitted', 'graded'])
            ->latest('id')
            ->first();

        $questions = collect();
        $answers = [];
        if ($attempt && $attempt->status === 'in_progress') {
            $attempt->load('responses');
            $byId = $activity->quizQuestionItems->keyBy('id');
            $questions = collect($attempt->question_order ?? [])->map(function ($id) use ($byId, $attempt) {
                $question = $byId->get($id);
                if (! $question) {
                    return null;
                }

                return $question->forStudent($attempt->choice_orders[$question->id] ?? []);
            })->filter()->values();
            foreach ($attempt->responses as $response) {
                $answers[$response->quiz_question_id] = [
                    'selected' => $response->answer['selected'] ?? [],
                    'text' => $response->answer['text'] ?? '',
                ];
            }
        }

        $closed = $this->quizzes->availabilityMessage($activity);

        return view('quizzes.take', [
            'lesson' => $lesson,
            'activity' => $activity,
            'attempt' => $attempt && $attempt->status === 'in_progress' ? $attempt : null,
            'questions' => $questions,
            'answers' => $answers,
            'used' => $used,
            'latest' => $latest,
            'closed' => $closed,
            'remaining' => $attempt && $attempt->status === 'in_progress' ? $this->quizzes->remainingSeconds($attempt) : null,
        ]);
    }

    public function start(Lesson $lesson, Activity $activity)
    {
        $student = $this->student($lesson, $activity);
        try {
            $this->quizzes->start($activity, $student);
        } catch (ValidationException $exception) {
            return back();
        }

        return redirect()->route('student.quizzes.show', [$lesson, $activity]);
    }

    public function save(Request $request, Lesson $lesson, Activity $activity)
    {
        $student = $this->student($lesson, $activity);
        $attempt = $this->openAttempt($activity, $student->id);
        $answers = $request->input('answers', []);
        if (! is_array($answers)) {
            $answers = [];
        }
        $attempt = $this->quizzes->saveAnswers($attempt, $answers);

        return response()->json([
            'saved' => $attempt->status === 'in_progress',
            'submitted' => $attempt->status !== 'in_progress',
            'remaining' => $this->quizzes->remainingSeconds($attempt),
        ]);
    }

    public function submit(Request $request, Lesson $lesson, Activity $activity)
    {
        $student = $this->student($lesson, $activity);
        $attempt = $this->openAttempt($activity, $student->id);
        $answers = $request->input('answers', []);
        if (is_array($answers) && $answers !== []) {
            $attempt = $this->quizzes->saveAnswers($attempt, $answers);
        }
        if ($attempt->status === 'in_progress') {
            $this->quizzes->submit($attempt);
        }

        return redirect()->route('student.quizzes.show', [$lesson, $activity])
            ->with('success', 'Quiz submitted.');
    }

    private function student(Lesson $lesson, Activity $activity)
    {
        $this->assertActivityOnLesson($lesson, $activity);
        $student = Auth::user()?->student;
        if (! $student) {
            abort(403, 'Student profile not found.');
        }
        $this->quizzes->studentForLesson($activity, $student);

        return $student;
    }

    private function openAttempt(Activity $activity, int $studentId): QuizAttempt
    {
        $attempt = $activity->quizAttempts()
            ->where('student_id', $studentId)
            ->where('status', 'in_progress')
            ->latest('id')
            ->first();
        if (! $attempt) {
            abort(403, 'There is no open attempt for this quiz.');
        }

        return $attempt;
    }

    private function authorizeTeacher(Lesson $lesson, Activity $activity): void
    {
        $this->assertActivityOnLesson($lesson, $activity);
        $teacher = Auth::user()?->teacher;
        if (! $teacher || (int) $lesson->teacher_id !== (int) $teacher->id) {
            abort(403, 'You can only manage quizzes for your own classes.');
        }
    }

    private function assertActivityOnLesson(Lesson $lesson, Activity $activity): void
    {
        if ((int) $activity->lesson_id !== (int) $lesson->id || ! $activity->isQuiz()) {
            abort(404);
        }
    }

    private function assertAttempt(Activity $activity, QuizAttempt $attempt): void
    {
        if ((int) $attempt->activity_id !== (int) $activity->id) {
            abort(404);
        }
    }
}
