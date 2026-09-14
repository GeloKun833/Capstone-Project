<?php

namespace App\Http\Controllers;

use App\Models\Section;
use App\Models\Teacher;
use App\Services\GradeSubjectCatalogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class SectionController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:Admin|Registrar']);
    }

    public function index()
    {
        $catalog = app(GradeSubjectCatalogService::class);
        // Normalize once per day — never on every page view (was writing to DB on GET).
        Cache::remember('sections.grade_labels.normalized', 86400, function () use ($catalog) {
            $catalog->normalizeSectionGradeLabels();
            return 1;
        });

        $sectionsByGrade = Cache::remember('sections.grouped.by.grade.v2', 120, function () use ($catalog) {
            return $catalog->sectionsGroupedByGrade();
        });
        $sections = $sectionsByGrade->flatten();
        $gradeLevels = GradeSubjectCatalogService::gradeLevels();
        $teachers = Teacher::with('user')
            ->whereHas('user', function ($query) {
                $query->where('role_name', 'Teacher')
                    ->where('status', 'active');
            })
            ->orderBy('full_name')
            ->get();

        return view('sections.index', compact('sections', 'sectionsByGrade', 'gradeLevels', 'teachers'));
    }

    public function create()
    {
        $teachers = Teacher::with('user')
            ->whereHas('user', function ($query) {
                $query->where('role_name', 'Teacher')
                    ->where('status', 'active');
            })
            ->orderBy('full_name')
            ->get();

        $gradeLevels = GradeSubjectCatalogService::gradeLevels();

        return view('sections.create', compact('teachers', 'gradeLevels'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'grade_level' => 'required|string|in:' . implode(',', GradeSubjectCatalogService::gradeLevels()),
            'adviser_id' => 'nullable|exists:teachers,id',
            'capacity' => 'nullable|integer|min:1',
            'description' => 'nullable|string',
        ]);

        Section::create([
            'name' => $request->name,
            'grade_level' => $request->grade_level,
            'adviser_id' => $request->adviser_id,
            'capacity' => $request->capacity ?: 25,
            'description' => $request->description,
        ]);

        Cache::forget('sections.grouped.by.grade');
        Cache::forget('sections.grouped.by.grade.v2');

        return redirect()->route('sections.index')
            ->with('success', 'Section created. It will appear as a Block Section option for ' . $request->grade_level . ' on enrollment.');
    }

    public function show(Section $section)
    {
        $section->load('adviser');
        return view('sections.show', compact('section'));
    }

    public function edit(Section $section)
    {
        $teachers = Teacher::with('user')
            ->whereHas('user', function ($query) {
                $query->where('role_name', 'Teacher')
                    ->where('status', 'active');
            })
            ->orderBy('full_name')
            ->get();

        $gradeLevels = GradeSubjectCatalogService::gradeLevels();

        return view('sections.edit', compact('section', 'teachers', 'gradeLevels'));
    }

    public function update(Request $request, Section $section)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'grade_level' => 'required|string|in:' . implode(',', GradeSubjectCatalogService::gradeLevels()),
            'adviser_id' => 'nullable|exists:teachers,id',
            'capacity' => 'nullable|integer|min:1',
            'description' => 'nullable|string',
        ]);

        $section->update([
            'name' => $request->name,
            'grade_level' => $request->grade_level,
            'adviser_id' => $request->adviser_id,
            'capacity' => $request->capacity ?: 25,
            'description' => $request->description,
        ]);

        Cache::forget('sections.grouped.by.grade');
        Cache::forget('sections.grouped.by.grade.v2');

        return redirect()->route('sections.index')
            ->with('success', 'Section updated. Enrollment Block Section list uses this catalog.');
    }

    public function destroy(Section $section)
    {
        $section->delete();
        Cache::forget('sections.grouped.by.grade');
        Cache::forget('sections.grouped.by.grade.v2');
        return redirect()->route('sections.index')->with('success', 'Section deleted successfully.');
    }

    /**
     * Assign a teacher to a block section (and optionally as adviser).
     */
    public function assignTeacher(Request $request, Section $section)
    {
        $request->validate([
            'teacher_id' => 'required|exists:teachers,id',
        ]);

        $teacherId = (int) $request->teacher_id;
        $section->teachers()->syncWithoutDetaching([$teacherId]);

        if ($request->has('set_as_adviser')) {
            $section->adviser_id = $teacherId;
            $section->save();
        }

        $catalog = app(GradeSubjectCatalogService::class);
        foreach ($catalog->subjectsForGrade($section->grade_level) as $subject) {
            if (!$subject->teachers()->where('teacher_id', $teacherId)->exists()) {
                $subject->teachers()->attach($teacherId);
            }
        }

        Cache::forget('sections.grouped.by.grade');
        Cache::forget('sections.grouped.by.grade.v2');

        $teacher = Teacher::find($teacherId);
        $name = $teacher?->full_name ?: 'Teacher';

        return redirect()->route('sections.index')
            ->with('success', $name . ' assigned to ' . $section->name . ($request->has('set_as_adviser') ? ' as adviser.' : '.'));
    }

    /**
     * Remove a teacher from a block section.
     */
    public function unassignTeacher(Request $request, Section $section)
    {
        $request->validate([
            'teacher_id' => 'required|exists:teachers,id',
        ]);

        $teacherId = (int) $request->teacher_id;
        $section->teachers()->detach($teacherId);

        if ((int) $section->adviser_id === $teacherId) {
            $section->adviser_id = null;
            $section->save();
        }

        Cache::forget('sections.grouped.by.grade');
        Cache::forget('sections.grouped.by.grade.v2');

        return redirect()->route('sections.index')
            ->with('success', 'Teacher unassigned from ' . $section->name . '.');
    }

    public function assignStudentsForm($id)
    {
        $section = Section::findOrFail($id);
        $students = \App\Models\Student::select('id', 'first_name', 'last_name', 'class')
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();
        $assigned = $section->students()->pluck('students.id')->toArray();
        return view('sections.assign_students', compact('section', 'students', 'assigned'));
    }

    public function assignStudents(Request $request, $id)
    {
        $section = Section::findOrFail($id);
        $studentIds = $request->input('student_ids', []);
        $section->students()->sync($studentIds);
        return redirect()->route('sections.index')->with('success', 'Students assigned successfully.');
    }
}
