<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Semester;
use Illuminate\Http\Request;

class SemesterController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:Admin|Registrar']);
    }

    public function index()
    {
        $semesters = Semester::with('academicYear')
            ->orderByDesc('id')
            ->get();

        $academicYears = AcademicYear::orderByDesc('start_date')->get();

        return view('semesters.index', compact('semesters', 'academicYears'));
    }

    public function create()
    {
        return redirect()->route('semesters.index');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'academic_year_id' => 'required|exists:academic_years,id',
        ]);

        $semester = Semester::create($data);
        $semester->load('academicYear');

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Semester created.',
                'semester' => $this->semesterPayload($semester),
            ]);
        }

        return redirect()->route('semesters.index')->with('success', 'Semester created.');
    }

    public function show(Semester $semester)
    {
        return redirect()->route('semesters.index');
    }

    public function edit(Semester $semester)
    {
        return redirect()->route('semesters.index');
    }

    public function update(Request $request, Semester $semester)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'academic_year_id' => 'required|exists:academic_years,id',
        ]);

        $semester->update($data);
        $semester->load('academicYear');

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Semester updated.',
                'semester' => $this->semesterPayload($semester),
            ]);
        }

        return redirect()->route('semesters.index')->with('success', 'Semester updated.');
    }

    public function destroy(Request $request, Semester $semester)
    {
        $name = $semester->name;
        $semester->delete();

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Semester "' . $name . '" deleted.',
            ]);
        }

        return redirect()->route('semesters.index')->with('success', 'Semester deleted.');
    }

    protected function semesterPayload(Semester $semester): array
    {
        return [
            'id' => $semester->id,
            'name' => $semester->name,
            'academic_year_id' => $semester->academic_year_id,
            'academic_year_name' => optional($semester->academicYear)->name ?? '—',
            'update_url' => route('semesters.update', $semester),
            'destroy_url' => route('semesters.destroy', $semester),
        ];
    }
}
