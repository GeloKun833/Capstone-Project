<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Lesson;
use App\Models\Activity;
use App\Models\ActivityRubric;
use App\Services\QuizService;
use App\Support\DescriptiveScale;
use App\Support\SafeUpload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class ActivityController extends Controller
{
    public function index(Lesson $lesson)
    {
        // Check if teacher owns this lesson
        if (Auth::user()->role_name === 'Teacher') {
            $teacher = Auth::user()->teacher;
            if ($teacher && $lesson->teacher_id !== $teacher->id) {
                abort(403, 'Unauthorized action.');
            }
        }

        $lesson->loadMissing(['subject', 'section']);

        $search = trim((string) request('q'));
        $status = request('status');

        $activities = $lesson->activities()
            ->withCount([
                'submissions',
                'submissions as graded_submissions_count' => function ($query) {
                    $query->where('status', 'graded');
                },
            ])
            ->when($search !== '', function ($query) use ($search) {
                $query->where('title', 'like', '%'.$search.'%');
            })
            ->when(in_array($status, ['draft', 'published'], true), function ($query) use ($status) {
                $query->where('status', $status);
            })
            ->orderByRaw("CASE WHEN status = 'draft' THEN 0 ELSE 1 END")
            ->orderBy('due_date')
            ->get();

        return view('activities.index', compact('lesson', 'activities', 'search', 'status'));
    }

    public function create(Lesson $lesson)
    {
        // Check if teacher owns this lesson
        if (Auth::user()->role_name === 'Teacher') {
            $teacher = Auth::user()->teacher;
            if ($teacher && $lesson->teacher_id !== $teacher->id) {
                abort(403, 'Unauthorized action.');
            }
        }

        $lesson->loadMissing(['subject', 'section']);
        $classLessons = $this->classChoices($lesson);
        $gradeLevel = $lesson->section?->grade_level;
        $activityKinds = Activity::kindsFor($gradeLevel);
        $descriptiveLevel = DescriptiveScale::isDescriptiveGradeLevel($gradeLevel);

        return view('activities.create', compact('lesson', 'classLessons', 'gradeLevel', 'activityKinds', 'descriptiveLevel'));
    }

    public function store(Request $request, Lesson $lesson)
    {
        $this->authorizeLesson($lesson);
        $target = $this->resolveTargetLesson($request, $lesson);
        $intent = $request->input('intent');
        if (is_array($intent)) {
            $intent = end($intent);
        }
        $publishing = ! $request->expectsJson() && $intent === 'publish';
        $payload = $this->activityPayload($request, $target, $publishing);

        $activity = DB::transaction(function () use ($target, $payload, $request, $publishing) {
            $activity = $target->activities()->create($payload);
            $this->syncQuiz($activity, $request, $publishing);

            return $activity;
        });

        if ($request->expectsJson()) {
            return $this->draftJson($target, $activity);
        }

        return redirect()->route('lessons.activities.index', $target)
            ->with('success', $publishing
                ? 'Activity posted. Students in this section can open it.'
                : 'Draft saved. Students cannot see it until you post it.');
    }

    public function show(Lesson $lesson, Activity $activity)
    {
        $this->authorizeLesson($lesson);
        $this->assertActivityOnLesson($lesson, $activity);

        $activity->load(['submissions.student', 'rubrics']);

        return view('activities.show', compact('lesson', 'activity'));
    }

    public function edit(Lesson $lesson, Activity $activity)
    {
        $this->authorizeLesson($lesson);
        $this->assertActivityOnLesson($lesson, $activity);

        $lesson->loadMissing(['subject', 'section']);
        $classLessons = $this->classChoices($lesson);
        $gradeLevel = $lesson->section?->grade_level;
        $activityKinds = Activity::kindsFor($gradeLevel);
        $descriptiveLevel = DescriptiveScale::isDescriptiveGradeLevel($gradeLevel);

        return view('activities.edit', compact('lesson', 'activity', 'classLessons', 'gradeLevel', 'activityKinds', 'descriptiveLevel'));
    }

    public function update(Request $request, Lesson $lesson, Activity $activity)
    {
        $this->authorizeLesson($lesson);
        $this->assertActivityOnLesson($lesson, $activity);
        $target = $this->resolveTargetLesson($request, $lesson);
        $intent = $request->input('intent');
        if (is_array($intent)) {
            $intent = end($intent);
        }
        $publishing = $request->expectsJson()
            ? $activity->isPublished()
            : $intent === 'publish';
        $payload = $this->activityPayload($request, $target, $publishing, $activity);
        $payload['lesson_id'] = $target->id;

        DB::transaction(function () use ($activity, $payload, $request, $publishing) {
            $activity->update($payload);
            $this->syncQuiz($activity->fresh(), $request, $publishing);
        });

        if ($request->expectsJson()) {
            return $this->draftJson($target, $activity);
        }

        return redirect()->route('lessons.activities.index', $target)
            ->with('success', $publishing
                ? 'Activity posted. Students in this section can open it.'
                : 'Draft saved. Students cannot see it until you post it.');
    }

    public function destroy(Lesson $lesson, Activity $activity)
    {
        $this->authorizeLesson($lesson);
        $this->assertActivityOnLesson($lesson, $activity);

        $this->deleteAttachmentFiles($activity->attachments ?? []);
        $activity->delete();

        return redirect()->route('lessons.activities.index', $lesson)
            ->with('success', 'Activity deleted successfully.');
    }

    public function rubric(Lesson $lesson, Activity $activity)
    {
        // Check if teacher owns this lesson
        if (Auth::user()->role_name === 'Teacher') {
            $teacher = Auth::user()->teacher;
            if ($teacher && $lesson->teacher_id !== $teacher->id) {
                abort(403, 'Unauthorized action.');
            }
        }

        $rubrics = $activity->rubrics()->orderBy('weight', 'desc')->get();

        return view('activities.rubric', compact('lesson', 'activity', 'rubrics'));
    }

    public function storeRubric(Request $request, Lesson $lesson, Activity $activity)
    {
        // Check if teacher owns this lesson
        if (Auth::user()->role_name === 'Teacher') {
            $teacher = Auth::user()->teacher;
            if ($teacher && $lesson->teacher_id !== $teacher->id) {
                abort(403, 'Unauthorized action.');
            }
        }

        $existingCount = $activity->rubrics()->count();
        $remaining = max(0, 100 - (int) $activity->rubrics()->sum('weight'));

        // First / only category → always 100%
        if ($existingCount === 0) {
            $request->merge(['weight' => 100]);
        } elseif ($remaining <= 0) {
            return redirect()->back()->withInput()->with(
                'error',
                'Rubric weights already total 100%. Edit or delete an existing category before adding another.'
            );
        } else {
            // Default leftover weight if teacher left it blank/invalid for multi-category
            $weight = (int) $request->input('weight', $remaining);
            if ($weight < 1 || $weight > $remaining) {
                $weight = $remaining;
            }
            $request->merge(['weight' => $weight]);
        }

        $request->validate([
            'category_name' => 'required|string|max:255',
            'description' => 'required|string|max:1000',
            'max_score' => 'required|integer|min:1|max:100',
            'weight' => 'required|integer|min:1|max:100',
        ]);

        $activity->rubrics()->create($request->only([
            'category_name',
            'description',
            'max_score',
            'weight',
        ]));

        return redirect()->route('lessons.activities.rubric', [$lesson, $activity])
            ->with('success', $existingCount === 0
                ? 'Rubric category added at 100% weight (single category).'
                : 'Rubric category added successfully!');
    }

    public function editRubric(Lesson $lesson, Activity $activity, ActivityRubric $rubric)
    {
        // Check if teacher owns this lesson
        if (Auth::user()->role_name === 'Teacher') {
            $teacher = Auth::user()->teacher;
            if ($teacher && $lesson->teacher_id !== $teacher->id) {
                abort(403, 'Unauthorized action.');
            }
        }

        return response()->json($rubric);
    }

    public function updateRubric(Request $request, Lesson $lesson, Activity $activity, ActivityRubric $rubric)
    {
        // Check if teacher owns this lesson
        if (Auth::user()->role_name === 'Teacher') {
            $teacher = Auth::user()->teacher;
            if ($teacher && $lesson->teacher_id !== $teacher->id) {
                abort(403, 'Unauthorized action.');
            }
        }

        $otherCount = $activity->rubrics()->where('id', '!=', $rubric->id)->count();
        $otherWeight = (int) $activity->rubrics()->where('id', '!=', $rubric->id)->sum('weight');
        $remaining = max(0, 100 - $otherWeight);

        // Sole category → always 100%
        if ($otherCount === 0) {
            $request->merge(['weight' => 100]);
        } else {
            $weight = (int) $request->input('weight', $remaining);
            if ($weight < 1 || $weight > $remaining) {
                $weight = $remaining > 0 ? $remaining : 1;
            }
            $request->merge(['weight' => $weight]);
        }

        $request->validate([
            'category_name' => 'required|string|max:255',
            'description' => 'required|string|max:1000',
            'max_score' => 'required|integer|min:1|max:100',
            'weight' => 'required|integer|min:1|max:100',
        ]);

        $rubric->update($request->only([
            'category_name',
            'description',
            'max_score',
            'weight',
        ]));

        return redirect()->route('lessons.activities.rubric', [$lesson, $activity])
            ->with('success', 'Rubric category updated successfully!');
    }

    public function destroyRubric(Lesson $lesson, Activity $activity, ActivityRubric $rubric)
    {
        // Check if teacher owns this lesson
        if (Auth::user()->role_name === 'Teacher') {
            $teacher = Auth::user()->teacher;
            if ($teacher && $lesson->teacher_id !== $teacher->id) {
                abort(403, 'Unauthorized action.');
            }
        }

        // Check if rubric has grades
        if ($rubric->grades()->count() > 0) {
            return redirect()->back()->with('error', 'Cannot delete rubric category that has grades assigned.');
        }

        $rubric->delete();

        // If only one category remains, force it to 100%
        $remaining = $activity->rubrics()->get();
        if ($remaining->count() === 1) {
            $remaining->first()->update(['weight' => 100]);
        }

        return redirect()->route('lessons.activities.rubric', [$lesson, $activity])
            ->with('success', 'Rubric category deleted successfully!');
    }

    public function studentShow(Lesson $lesson, Activity $activity)
    {
        $this->assertActivityOnLesson($lesson, $activity);

        if (! $activity->isPublished()) {
            abort(403, 'This activity is not available yet.');
        }

        $student = Auth::user()->student;
        if (!$student) {
            return redirect()->route('dashboard')->with('error', 'Student profile not found.');
        }

        $isEnrolled = $student->sections()->where('section_id', $lesson->section_id)->exists();
        if (!$isEnrolled) {
            abort(403, 'You are not enrolled in this section.');
        }

        if ($activity->isQuiz()) {
            return redirect()->route('student.quizzes.show', [$lesson, $activity]);
        }

        $existingSubmission = $activity->submissions()->where('student_id', $student->id)->first();

        $lesson->loadMissing(['subject', 'section']);

        return view('activities.student-show', compact('lesson', 'activity', 'existingSubmission'));
    }

    public function duplicate(Lesson $lesson, Activity $activity)
    {
        $this->authorizeLesson($lesson);
        $this->assertActivityOnLesson($lesson, $activity);

        $copy = $activity->replicate();
        $copy->title = 'Copy of '.$activity->title;
        $copy->status = 'draft';
        $copy->attachments = $this->copyAttachments($activity->attachments ?? []);
        $copy->save();
        foreach ($activity->quizQuestionItems as $question) {
            $cloned = $question->replicate();
            $cloned->activity_id = $copy->id;
            $cloned->save();
        }

        return redirect()->route('lessons.activities.edit', [$lesson, $copy])
            ->with('success', 'A draft copy was created. Students cannot see it until you post it.');
    }

    public function publish(Lesson $lesson, Activity $activity)
    {
        $this->authorizeLesson($lesson);
        $this->assertActivityOnLesson($lesson, $activity);
        $lesson->loadMissing('section');
        $kinds = array_keys(Activity::kindsFor($lesson->section?->grade_level));
        $missing = [];
        if (! $activity->instructions) {
            $missing[] = 'instructions';
        }
        if (! $activity->activity_type || ! in_array($activity->activity_type, $kinds, true)) {
            $missing[] = 'an activity type';
        }
        if (! $activity->due_date) {
            $missing[] = 'a due date';
        }
        if ($activity->isQuiz()) {
            $ready = $activity->quizQuestionItems()->count() > 0
                && $activity->due_date
                && $activity->due_time
                && $activity->attempts_allowed
                && $activity->passing_score;
            if (! $ready) {
                $missing[] = 'the quiz questions, deadline, attempts, and passing score';
            }
        }
        if ($missing) {
            return redirect()->route('lessons.activities.edit', [$lesson, $activity])
                ->with('error', 'Add '.implode(', ', $missing).' before posting.');
        }

        $activity->update(['status' => 'published']);

        return redirect()->route('lessons.activities.index', $lesson)
            ->with('success', 'Activity posted. Students in this section can open it.');
    }

    public function unpublish(Lesson $lesson, Activity $activity)
    {
        $this->authorizeLesson($lesson);
        $this->assertActivityOnLesson($lesson, $activity);
        $activity->update(['status' => 'draft']);

        return redirect()->route('lessons.activities.index', $lesson)
            ->with('success', 'Activity moved back to draft. Students can no longer open it.');
    }

    private function authorizeLesson(Lesson $lesson): void
    {
        $teacher = Auth::user()->teacher;
        if (! $teacher || (int) $lesson->teacher_id !== (int) $teacher->id) {
            abort(403, 'You can only manage activities for your own classes.');
        }
    }

    private function assertActivityOnLesson(Lesson $lesson, Activity $activity): void
    {
        if ((int) $activity->lesson_id !== (int) $lesson->id) {
            abort(404);
        }
    }

    private function classChoices(Lesson $current)
    {
        $current->loadMissing(['subject', 'section']);

        return collect([$current]);
    }

    private function resolveTargetLesson(Request $request, Lesson $routeLesson): Lesson
    {
        $id = (int) $request->input('lesson_id', $routeLesson->id);
        if ($id === (int) $routeLesson->id) {
            return $routeLesson;
        }

        $target = Lesson::query()->with(['subject', 'section'])->find($id);
        if (! $target) {
            throw ValidationException::withMessages([
                'lesson_id' => 'Choose a class you teach.',
            ]);
        }

        $this->authorizeLesson($target);
        $allowed = $this->classChoices($routeLesson)->pluck('id')->map(fn ($value) => (int) $value);
        if (! $allowed->contains((int) $target->id)) {
            throw ValidationException::withMessages([
                'lesson_id' => 'Choose one of your classes in the current academic year.',
            ]);
        }

        return $target;
    }

    private function activityPayload(Request $request, Lesson $lesson, bool $publishing, ?Activity $existing = null): array
    {
        $lesson->loadMissing('section');
        $gradeLevel = $lesson->section?->grade_level;
        $kinds = array_keys(Activity::kindsFor($gradeLevel));
        if ($existing && $existing->activity_type && ! in_array($existing->activity_type, $kinds, true)) {
            $kinds[] = $existing->activity_type;
        }
        $descriptive = DescriptiveScale::isDescriptiveGradeLevel($gradeLevel);

        $request->merge([
            'activity_type' => $request->input('activity_type') ?: null,
            'due_date' => $request->input('due_date') ?: null,
            'due_time' => $request->input('due_time') ?: null,
            'points' => $request->input('points') === '' || $request->input('points') === null ? null : $request->input('points'),
        ]);

        $freshPublish = $publishing && (! $existing || $existing->status !== 'published');
        $rules = [
            'title' => 'required|string|max:255',
            'activity_type' => ($publishing ? 'required' : 'nullable').'|in:'.implode(',', $kinds),
            'instructions' => ($publishing ? 'required' : 'nullable').'|string|max:10000',
            'due_date' => $freshPublish ? 'required|date' : ($publishing ? 'required|date' : 'nullable|date'),
            'due_time' => 'nullable|date_format:H:i',
            'attachments' => 'nullable|array|max:5',
            'attachments.*' => 'file|max:10240|mimes:pdf,doc,docx,ppt,pptx,jpg,jpeg,png,webp',
            'remove_attachments' => 'nullable|array',
            'remove_attachments.*' => 'string',
        ];
        if ($request->input('activity_type') === 'quiz') {
            $rules['due_time'] = ($publishing ? 'required' : 'nullable').'|date_format:H:i';
            $rules['attempts_allowed'] = ($publishing ? 'required' : 'nullable').'|integer|min:1|max:10';
            $rules['passing_score'] = ($publishing ? 'required' : 'nullable').'|integer|min:1|max:100';
            $rules['time_limit_minutes'] = 'nullable|integer|min:1|max:180';
            $rules['available_date'] = 'nullable|date';
            $rules['available_time'] = 'nullable|date_format:H:i';
        } elseif (! $descriptive) {
            $rules['points'] = 'nullable|integer|min:1|max:100';
        }

        $validated = $request->validate($rules, [
            'title.required' => 'Add a title before saving.',
            'instructions.required' => 'Write instructions before posting.',
            'activity_type.required' => 'Choose an activity type.',
            'activity_type.in' => 'Choose Quiz, Practice Exercise, Project, or Reflection.',
            'due_date.required' => 'Set a due date before posting.',
            'due_date.date' => 'Choose today or a later date.',
            'due_time.required' => 'Set a due time before posting the quiz.',
            'attempts_allowed.required' => 'Set how many attempts are allowed.',
            'passing_score.required' => 'Set the passing score as a percentage.',
            'time_limit_minutes.min' => 'Time limit must be from 1 to 180 minutes.',
            'time_limit_minutes.max' => 'Time limit must be from 1 to 180 minutes.',
            'points.min' => 'Points must be from 1 to 100.',
            'points.max' => 'Points must be from 1 to 100.',
            'attachments.max' => 'You can attach up to 5 files.',
            'attachments.*.mimes' => 'Attach a PDF, Word, PowerPoint, or image file.',
            'attachments.*.max' => 'Each file must be 10 MB or smaller.',
        ]);

        $type = $validated['activity_type'] ?? null;
        $isQuiz = $type === 'quiz';
        $isFile = in_array($type, ['practice_exercise', 'project', 'reflection'], true);

        if ($descriptive && ! $isQuiz && $request->filled('points')) {
            throw ValidationException::withMessages([
                'points' => 'Nursery through Grade 3 do not use a point score on activities.',
            ]);
        }

        $clock = now('Asia/Manila');
        $availableAt = null;
        $dueAt = null;
        if (! empty($validated['due_date'])) {
            $dueAt = \Carbon\Carbon::parse(
                $validated['due_date'].' '.($validated['due_time'] ?: '23:59'),
                'Asia/Manila'
            );
            $sameDue = $existing && $existing->dueAt() && $existing->dueAt()->equalTo($dueAt);
            if ($dueAt->lt($clock) && ! $sameDue) {
                throw ValidationException::withMessages([
                    'due_date' => 'The due date and time cannot be earlier than the time you create this activity.',
                ]);
            }
        }
        if ($isQuiz && $request->filled('available_date')) {
            $availableAt = \Carbon\Carbon::parse(
                $request->input('available_date').' '.($request->input('available_time') ?: $clock->format('H:i')),
                'Asia/Manila'
            );
            $sameStart = $existing
                && $existing->available_at
                && $existing->available_at->timezone('Asia/Manila')->equalTo($availableAt);
            if ($availableAt->lt($clock) && ! $sameStart) {
                throw ValidationException::withMessages([
                    'available_date' => 'The start of the quiz cannot be earlier than the time you create this activity.',
                ]);
            }
        }
        if ($publishing && $availableAt && $dueAt && $availableAt->greaterThanOrEqualTo($dueAt)) {
            throw ValidationException::withMessages([
                'available_date' => 'The quiz must start before the due date and time.',
            ]);
        }

        $attachments = $existing?->attachments ?? [];
        $remove = $request->input('remove_attachments', []);
        if ($remove) {
            $kept = [];
            foreach ($attachments as $file) {
                if (in_array($file['path'] ?? '', $remove, true)) {
                    $this->deleteAttachmentFiles([$file]);
                    continue;
                }
                $kept[] = $file;
            }
            $attachments = $kept;
        }

        foreach ($request->file('attachments', []) ?: [] as $file) {
            $stored = SafeUpload::store($file, 'activity-materials');
            $attachments[] = [
                'path' => $stored['path'],
                'name' => $stored['original_name'],
                'mime' => $file->getMimeType(),
                'size' => $file->getSize(),
            ];
        }

        if (count($attachments) > 5) {
            throw ValidationException::withMessages([
                'attachments' => 'You can attach up to 5 files.',
            ]);
        }

        return [
            'title' => $validated['title'],
            'activity_type' => $validated['activity_type'] ?? null,
            'instructions' => $validated['instructions'] ?? '',
            'due_date' => $validated['due_date'] ?? null,
            'due_time' => $validated['due_time'] ?? null,
            'available_at' => $isQuiz ? $availableAt : null,
            'time_limit_minutes' => $isQuiz && $request->filled('time_limit_minutes') ? (int) $request->input('time_limit_minutes') : null,
            'attempts_allowed' => $isQuiz && $request->filled('attempts_allowed') ? (int) $request->input('attempts_allowed') : null,
            'passing_score' => $isQuiz && $request->filled('passing_score') ? (int) $request->input('passing_score') : null,
            'shuffle_questions' => $isQuiz && $request->boolean('shuffle_questions'),
            'shuffle_choices' => $isQuiz && $request->boolean('shuffle_choices'),
            'show_correct_answers' => $isQuiz && $request->boolean('show_correct_answers'),
            'points' => $isQuiz ? ($existing?->points) : ($descriptive ? null : ($request->filled('points') ? (int) $request->input('points') : null)),
            'quiz_questions' => null,
            'attachments' => $attachments ?: null,
            'allows_submission' => $isFile,
            'status' => $publishing ? 'published' : 'draft',
            'is_active' => true,
        ];
    }

    private function draftJson(Lesson $lesson, Activity $activity)
    {
        return response()->json([
            'success' => true,
            'id' => $activity->id,
            'status' => $activity->status,
            'saved_at' => now('Asia/Manila')->format('g:i A'),
            'update_url' => route('lessons.activities.update', [$lesson, $activity]),
            'edit_url' => route('lessons.activities.edit', [$lesson, $activity]),
        ]);
    }

    private function copyAttachments(array $files): ?array
    {
        $copies = [];
        foreach ($files as $file) {
            $path = $file['path'] ?? null;
            if (! $path || ! Storage::disk('public')->exists($path)) {
                continue;
            }
            $extension = pathinfo($path, PATHINFO_EXTENSION);
            $newPath = 'activity-materials/'.time().'_'.\Illuminate\Support\Str::random(16).($extension ? '.'.$extension : '');
            Storage::disk('public')->copy($path, $newPath);
            $file['path'] = $newPath;
            $copies[] = $file;
        }

        return $copies ?: null;
    }

    private function syncQuiz(Activity $activity, Request $request, bool $publishing): void
    {
        if (! $activity->isQuiz()) {
            $activity->quizQuestionItems()->delete();

            return;
        }

        app(QuizService::class)->syncQuestions(
            $activity,
            $request,
            $publishing,
            $activity->quizAttempts()->exists()
        );
    }

    private function deleteAttachmentFiles(array $files): void
    {
        foreach ($files as $file) {
            if (! empty($file['path'])) {
                Storage::disk('public')->delete($file['path']);
            }
        }
    }
} 