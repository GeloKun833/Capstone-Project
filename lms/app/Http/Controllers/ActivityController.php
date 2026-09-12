<?php

namespace App\Http\Controllers;

use App\Models\Lesson;
use App\Models\Activity;
use App\Models\ActivityRubric;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

        $activities = $lesson->activities()
            ->withCount([
                'submissions',
                'submissions as graded_submissions_count' => function ($query) {
                    $query->where('status', 'graded');
                },
            ])
            ->orderBy('due_date', 'asc')
            ->get();

        return view('activities.index', compact('lesson', 'activities'));
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

        return view('activities.create', compact('lesson'));
    }

    public function store(Request $request, Lesson $lesson)
    {
        // Check if teacher owns this lesson
        if (Auth::user()->role_name === 'Teacher') {
            $teacher = Auth::user()->teacher;
            if ($teacher && $lesson->teacher_id !== $teacher->id) {
                abort(403, 'Unauthorized action.');
            }
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'instructions' => 'required|string',
            'due_date' => 'required|date|after_or_equal:today',
            'allows_submission' => 'boolean',
        ]);

        $data = $request->all();
        $data['allows_submission'] = $request->has('allows_submission');

        $lesson->activities()->create($data);

        return redirect()->route('lessons.activities.index', $lesson)
            ->with('success', 'Activity created successfully.');
    }

    public function show(Lesson $lesson, Activity $activity)
    {
        // Check if teacher owns this lesson
        if (Auth::user()->role_name === 'Teacher') {
            $teacher = Auth::user()->teacher;
            if ($teacher && $lesson->teacher_id !== $teacher->id) {
                abort(403, 'Unauthorized action.');
            }
        }

        $activity->load(['submissions.student', 'rubrics']);

        return view('activities.show', compact('lesson', 'activity'));
    }

    public function edit(Lesson $lesson, Activity $activity)
    {
        // Check if teacher owns this lesson
        if (Auth::user()->role_name === 'Teacher') {
            $teacher = Auth::user()->teacher;
            if ($teacher && $lesson->teacher_id !== $teacher->id) {
                abort(403, 'Unauthorized action.');
            }
        }

        return view('activities.edit', compact('lesson', 'activity'));
    }

    public function update(Request $request, Lesson $lesson, Activity $activity)
    {
        // Check if teacher owns this lesson
        if (Auth::user()->role_name === 'Teacher') {
            $teacher = Auth::user()->teacher;
            if ($teacher && $lesson->teacher_id !== $teacher->id) {
                abort(403, 'Unauthorized action.');
            }
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'instructions' => 'required|string',
            'due_date' => 'required|date',
            'allows_submission' => 'boolean',
        ]);

        $data = $request->all();
        $data['allows_submission'] = $request->has('allows_submission');

        $activity->update($data);

        return redirect()->route('lessons.activities.index', $lesson)
            ->with('success', 'Activity updated successfully.');
    }

    public function destroy(Lesson $lesson, Activity $activity)
    {
        // Check if teacher owns this lesson
        if (Auth::user()->role_name === 'Teacher') {
            $teacher = Auth::user()->teacher;
            if ($teacher && $lesson->teacher_id !== $teacher->id) {
                abort(403, 'Unauthorized action.');
            }
        }

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
        $student = Auth::user()->student;
        if (!$student) {
            return redirect()->route('dashboard')->with('error', 'Student profile not found.');
        }

        $isEnrolled = $student->sections()->where('section_id', $lesson->section_id)->exists();
        if (!$isEnrolled) {
            abort(403, 'You are not enrolled in this lesson.');
        }

        $existingSubmission = $activity->submissions()->where('student_id', $student->id)->first();

        $lesson->loadMissing(['subject', 'section']);

        return view('activities.student-show', compact('lesson', 'activity', 'existingSubmission'));
    }
} 