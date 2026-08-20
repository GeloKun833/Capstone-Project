<?php

namespace App\Http\Controllers;

use App\Models\Lesson;
use App\Models\Activity;
use App\Models\Subject;
use App\Models\Section;
use App\Models\CurriculumObjective;
use App\Models\AcademicYear;
use App\Models\Semester;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class LessonController extends Controller
{
    public function index(Request $request)
    {
        $query = Lesson::with(['teacher', 'subject', 'section', 'academicYear', 'semester'])
            ->active();

        // Filter by teacher (if teacher role)
        if (Auth::user()->role_name === 'Teacher') {
            $teacher = Auth::user()->teacher;
            if ($teacher) {
                $query->where('teacher_id', $teacher->id);
            }
        }

        // Apply filters
        if ($request->filled('subject_id')) {
            $query->where('subject_id', $request->subject_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $lessons = $query->orderBy('lesson_date', 'desc')->paginate(15);

        // Debug: Log the lessons query
        Log::info('Lessons index loaded', [
            'total_lessons' => $lessons->total(),
            'current_page' => $lessons->currentPage(),
            'user_role' => Auth::user()->role_name,
            'teacher_id' => Auth::user()->teacher ? Auth::user()->teacher->id : null
        ]);

        $subjects = Subject::all();
        $sections = Section::all();
        $academicYears = AcademicYear::all();
        $semesters = Semester::all();

        return view('lessons.index', compact(
            'lessons',
            'subjects',
            'sections',
            'academicYears',
            'semesters'
        ));
    }

    public function create()
    {
        $subjects = Subject::all();
        $sections = Section::all();
        $academicYears = AcademicYear::all();
        $semesters = Semester::all();

        return view('lessons.create', compact(
            'subjects',
            'sections',
            'academicYears',
            'semesters'
        ));
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'required|string',
                'subject_id' => 'required|exists:subjects,id',
                'section_id' => 'required|exists:sections,id',
                'academic_year_id' => 'required|exists:academic_years,id',
                'semester_id' => 'required|exists:semesters,id',
                'lesson_date' => 'required|date',
                'file' => 'nullable|file|mimes:pdf,doc,docx,ppt,pptx|max:10240',
            ]);

            $data = $request->all();
            $teacher = Auth::user()->teacher;
            if (!$teacher) {
                Log::error('Lesson creation failed: Teacher profile not found.', ['user_id' => Auth::id()]);
                return redirect()->back()->withInput()->with('error', 'Teacher profile not found. Please contact admin.');
            }
            $data['teacher_id'] = $teacher->id;
            $data['is_active'] = true;
            $data['status'] = 'published';

            // Handle file upload
            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $fileName = time() . '_' . $file->getClientOriginalName();
                $filePath = $file->storeAs('lessons', $fileName, 'public');
                $data['file_path'] = $filePath;
                $data['file_name'] = $fileName;
            }

            $lesson = Lesson::create($data);
            Log::info('Lesson created successfully', [
                'lesson_id' => $lesson->id,
                'title' => $lesson->title,
                'teacher_id' => $lesson->teacher_id,
                'subject_id' => $lesson->subject_id,
                'section_id' => $lesson->section_id,
                'is_active' => $lesson->is_active,
                'status' => $lesson->status
            ]);
            return redirect()->route('lessons.index')
                ->with('success', 'Lesson created successfully.');
        } catch (\Exception $e) {
            Log::error('Lesson creation error', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return redirect()->back()->withInput()->with('error', 'Failed to create lesson: ' . $e->getMessage());
        }
    }

    public function show(Lesson $lesson)
    {
        $lesson->load(['teacher', 'subject', 'section', 'academicYear', 'semester', 'activities']);
        
        return view('lessons.show', compact('lesson'));
    }

    public function edit(Lesson $lesson)
    {
        // Check if teacher owns this lesson
        if (Auth::user()->role_name === 'Teacher') {
            $teacher = Auth::user()->teacher;
            if ($teacher && $lesson->teacher_id !== $teacher->id) {
                abort(403, 'Unauthorized action.');
            }
        }

        $subjects = Subject::all();
        $sections = Section::all();
        $academicYears = AcademicYear::all();
        $semesters = Semester::all();

        return view('lessons.edit', compact(
            'lesson',
            'subjects',
            'sections',
            'academicYears',
            'semesters'
        ));
    }

    public function update(Request $request, Lesson $lesson)
    {
        try {
            // Check if teacher owns this lesson
            if (Auth::user()->role_name === 'Teacher') {
                $teacher = Auth::user()->teacher;
                if ($teacher && $lesson->teacher_id !== $teacher->id) {
                    abort(403, 'Unauthorized action.');
                }
            }
            $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'required|string',
                'subject_id' => 'required|exists:subjects,id',
                'section_id' => 'required|exists:sections,id',
                'academic_year_id' => 'required|exists:academic_years,id',
                'semester_id' => 'required|exists:semesters,id',
                'lesson_date' => 'required|date',
                'file' => 'nullable|file|mimes:pdf,doc,docx,ppt,pptx|max:10240',
            ]);
            $data = $request->all();
            // Handle file upload
            if ($request->hasFile('file')) {
                // Delete old file if exists
                if ($lesson->file_path) {
                    Storage::disk('public')->delete($lesson->file_path);
                }
                $file = $request->file('file');
                $fileName = time() . '_' . $file->getClientOriginalName();
                $filePath = $file->storeAs('lessons', $fileName, 'public');
                $data['file_path'] = $filePath;
                $data['file_name'] = $fileName;
            }
            $lesson->update($data);
            return redirect()->route('lessons.index')
                ->with('success', 'Lesson updated successfully.');
        } catch (\Exception $e) {
            Log::error('Lesson update error', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return redirect()->back()->withInput()->with('error', 'Failed to update lesson: ' . $e->getMessage());
        }
    }

    public function publish(Lesson $lesson)
    {
        try {
            // Check if teacher owns this lesson
            if (Auth::user()->role_name === 'Teacher') {
                $teacher = Auth::user()->teacher;
                if ($teacher && $lesson->teacher_id !== $teacher->id) {
                    abort(403, 'Unauthorized action.');
                }
            }

            $lesson->update(['status' => 'published']);
            
            Log::info('Lesson published successfully', [
                'lesson_id' => $lesson->id,
                'title' => $lesson->title,
                'teacher_id' => $lesson->teacher_id
            ]);

            return redirect()->back()->with('success', 'Lesson published successfully. Students can now see this lesson.');
        } catch (\Exception $e) {
            Log::error('Lesson publish error', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return redirect()->back()->with('error', 'Failed to publish lesson: ' . $e->getMessage());
        }
    }

    public function destroy(Lesson $lesson)
    {
        try {
            // Check if teacher owns this lesson
            if (Auth::user()->role_name === 'Teacher') {
                $teacher = Auth::user()->teacher;
                if ($teacher && $lesson->teacher_id !== $teacher->id) {
                    abort(403, 'Unauthorized action.');
                }
            }
            // Delete file if exists
            if ($lesson->file_path) {
                Storage::disk('public')->delete($lesson->file_path);
            }
            $lesson->delete();
            return redirect()->route('lessons.index')
                ->with('success', 'Lesson deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Lesson delete error', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return redirect()->back()->with('error', 'Failed to delete lesson: ' . $e->getMessage());
        }
    }

    public function complete(Lesson $lesson)
    {
        try {
            // Check if teacher owns this lesson
            if (Auth::user()->role_name === 'Teacher') {
                $teacher = Auth::user()->teacher;
                if ($teacher && $lesson->teacher_id !== $teacher->id) {
                    abort(403, 'Unauthorized action.');
                }
            }

            $lesson->update(['status' => 'completed']);
            
            Log::info('Lesson marked as completed', [
                'lesson_id' => $lesson->id,
                'title' => $lesson->title,
                'teacher_id' => $lesson->teacher_id
            ]);

            return redirect()->back()->with('success', 'Lesson marked as completed.');
        } catch (\Exception $e) {
            Log::error('Lesson complete error', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return redirect()->back()->with('error', 'Failed to mark lesson as completed: ' . $e->getMessage());
        }
    }
} 