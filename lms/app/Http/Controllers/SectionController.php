<?php

namespace App\Http\Controllers;

use App\Models\Section;
use Illuminate\Http\Request;

class SectionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $sections = \App\Models\Section::with('adviser')->orderBy('grade_level')->orderBy('name')->get();
        return view('sections.index', compact('sections'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Get all teachers with their user information
        $teachers = \App\Models\Teacher::with('user')
            ->whereHas('user', function($query) {
                $query->where('role_name', 'Teacher')
                      ->where('status', 'active');
            })
            ->orderBy('full_name')
            ->get();
        
        return view('sections.create', compact('teachers'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'grade_level' => 'required|string|max:255',
            'adviser_id' => 'nullable|exists:teachers,id',
            'capacity' => 'nullable|integer|min:1',
            'description' => 'nullable|string',
        ]);
        \App\Models\Section::create($request->all());
        return redirect()->route('sections.index')->with('success', 'Section created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(\App\Models\Section $section)
    {
        $section->load('adviser');
        return view('sections.show', compact('section'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(\App\Models\Section $section)
    {
        // Get all teachers with their user information
        $teachers = \App\Models\Teacher::with('user')
            ->whereHas('user', function($query) {
                $query->where('role_name', 'Teacher')
                      ->where('status', 'active');
            })
            ->orderBy('full_name')
            ->get();
        
        return view('sections.edit', compact('section', 'teachers'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, \App\Models\Section $section)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'grade_level' => 'required|string|max:255',
            'adviser_id' => 'nullable|exists:teachers,id',
            'capacity' => 'nullable|integer|min:1',
            'description' => 'nullable|string',
        ]);
        $section->update($request->all());
        return redirect()->route('sections.index')->with('success', 'Section updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(\App\Models\Section $section)
    {
        $section->delete();
        return redirect()->route('sections.index')->with('success', 'Section deleted successfully.');
    }

    /** Show form to assign students to a section */
    public function assignStudentsForm($id)
    {
        $section = \App\Models\Section::findOrFail($id);
        $students = \App\Models\Student::select('id', 'first_name', 'last_name', 'class')
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();
        $assigned = $section->students()->pluck('students.id')->toArray();
        return view('sections.assign_students', compact('section', 'students', 'assigned'));
    }

    /** Handle assignment of students to a section */
    public function assignStudents(Request $request, $id)
    {
        $section = \App\Models\Section::findOrFail($id);
        $studentIds = $request->input('student_ids', []);
        $section->students()->sync($studentIds);
        return redirect()->route('sections.index')->with('success', 'Students assigned successfully.');
    }
}
