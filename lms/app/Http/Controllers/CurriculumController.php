<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Curriculum;
use App\Models\Subject;

class CurriculumController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Curriculum::class, 'curriculum');
    }

    public function index()
    {
        $curricula = Curriculum::with('subjects')->orderBy('grade_level')->get();
        return view('curriculum.index', compact('curricula'));
    }

    public function create()
    {
        return view('curriculum.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'grade_level' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);
        Curriculum::create($request->only(['grade_level', 'description']));
        return redirect()->route('curriculum.index')->with('success', 'Curriculum created successfully.');
    }

    public function show(Curriculum $curriculum)
    {
        $curriculum->load('subjects');
        $allSubjects = Subject::orderBy('subject_name')->get();
        return view('curriculum.show', compact('curriculum', 'allSubjects'));
    }

    public function edit(Curriculum $curriculum)
    {
        return view('curriculum.edit', compact('curriculum'));
    }

    public function update(Request $request, Curriculum $curriculum)
    {
        $request->validate([
            'grade_level' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);
        $curriculum->update($request->only(['grade_level', 'description']));
        return redirect()->route('curriculum.index')->with('success', 'Curriculum updated successfully.');
    }

    public function destroy(Curriculum $curriculum)
    {
        $curriculum->delete();
        return redirect()->route('curriculum.index')->with('success', 'Curriculum deleted successfully.');
    }

    public function assignSubjectsForm(Curriculum $curriculum)
    {
        $curriculum->load('subjects');
        $subjects = Subject::orderBy('subject_name')->get();
        $assigned = $curriculum->subjects->pluck('id')->toArray();
        return view('curriculum.assign_subjects', compact('curriculum', 'subjects', 'assigned'));
    }

    public function assignSubjects(Request $request, Curriculum $curriculum)
    {
        $subjectIds = $request->input('subject_ids', []);
        $curriculum->subjects()->sync($subjectIds);
        return redirect()->route('curriculum.show', $curriculum)->with('success', 'Subjects assigned successfully.');
    }
}
