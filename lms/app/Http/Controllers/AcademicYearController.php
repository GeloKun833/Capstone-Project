<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use Illuminate\Http\Request;

class AcademicYearController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:Admin|Registrar']);
    }

    public function index()
    {
        $academicYears = AcademicYear::withCount('semesters')
            ->orderByDesc('start_date')
            ->get();

        $stats = [
            'total' => $academicYears->count(),
            'current' => $academicYears->filter->isCurrent()->count(),
            'upcoming' => $academicYears->filter(fn ($y) => $y->statusLabel() === 'upcoming')->count(),
            'completed' => $academicYears->filter(fn ($y) => $y->statusLabel() === 'completed')->count(),
        ];

        return view('academic_years.index', compact('academicYears', 'stats'));
    }

    public function create()
    {
        return redirect()->route('academic_years.index');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:50', 'unique:academic_years,name', 'regex:/^\d{4}\s*[–\-]\s*\d{4}$/'],
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ], [
            'name.regex' => 'Academic year must look like 2026–2027 (years only).',
        ]);

        // Derive dates from year label when dates omitted (e.g. 2026-2027)
        if (empty($data['start_date']) || empty($data['end_date'])) {
            if (preg_match('/(\d{4})\s*[–\-]\s*(\d{4})/', $data['name'], $m)) {
                $data['start_date'] = $m[1].'-06-01';
                $data['end_date'] = $m[2].'-05-31';
            } else {
                return back()->withErrors(['name' => 'Invalid academic year format.'])->withInput();
            }
        }

        $data['name'] = preg_replace('/\s*[–\-]\s*/', '–', $data['name']);

        $year = AcademicYear::create($data);
        $year->loadCount('semesters');

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Academic year created.',
                'year' => $this->yearPayload($year),
            ]);
        }

        return redirect()->route('academic_years.index')->with('success', 'Academic year created.');
    }

    public function show(AcademicYear $academicYear)
    {
        return redirect()->route('academic_years.index');
    }

    public function edit(AcademicYear $academicYear)
    {
        return redirect()->route('academic_years.index');
    }

    public function update(Request $request, AcademicYear $academicYear)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255|unique:academic_years,name,' . $academicYear->id,
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $academicYear->update($data);
        $academicYear->loadCount('semesters');

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Academic year updated.',
                'year' => $this->yearPayload($academicYear->fresh()),
            ]);
        }

        return redirect()->route('academic_years.index')->with('success', 'Academic year updated.');
    }

    public function destroy(Request $request, AcademicYear $academicYear)
    {
        $name = $academicYear->name;
        $academicYear->delete();

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Academic year "' . $name . '" deleted.',
            ]);
        }

        return redirect()->route('academic_years.index')->with('success', 'Academic year deleted.');
    }

    protected function yearPayload(AcademicYear $year): array
    {
        return [
            'id' => $year->id,
            'name' => $year->name,
            'start_date' => optional($year->start_date)->format('Y-m-d'),
            'end_date' => optional($year->end_date)->format('Y-m-d'),
            'start_label' => optional($year->start_date)->format('M d, Y'),
            'end_label' => optional($year->end_date)->format('M d, Y'),
            'status' => $year->statusLabel(),
            'semesters_count' => (int) ($year->semesters_count ?? $year->semesters()->count()),
            'update_url' => route('academic_years.update', $year),
            'destroy_url' => route('academic_years.destroy', $year),
        ];
    }
}
