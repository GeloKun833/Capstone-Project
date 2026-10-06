<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

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
        $yearName = trim((string) $request->input('name', ''));
        if (preg_match('/^(\d{4})\s*[-–]\s*(\d{4})$/u', $yearName, $yearParts)) {
            $request->merge(['name' => $yearParts[1].'-'.$yearParts[2]]);
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:50', 'unique:academic_years,name', 'regex:/^\d{4}-\d{4}$/'],
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
        $data['status'] = 'upcoming';
        $data['enrollment_open'] = false;

        $year = AcademicYear::create($data);
        $this->forgetYearCache();
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
        $yearName = trim((string) $request->input('name', ''));
        if (preg_match('/^(\d{4})\s*[-–]\s*(\d{4})$/u', $yearName, $yearParts)) {
            $request->merge(['name' => $yearParts[1].'-'.$yearParts[2]]);
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:50', 'unique:academic_years,name,' . $academicYear->id, 'regex:/^\d{4}-\d{4}$/'],
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ], [
            'name.regex' => 'Academic year must look like 2026–2027 (years only).',
        ]);

        if (empty($data['start_date'])) {
            $data['start_date'] = optional($academicYear->start_date)->format('Y-m-d');
        }
        if (empty($data['end_date'])) {
            $data['end_date'] = optional($academicYear->end_date)->format('Y-m-d');
        }
        if ((empty($data['start_date']) || empty($data['end_date'])) && preg_match('/^(\d{4})-(\d{4})$/', $data['name'], $m)) {
            $data['start_date'] = $data['start_date'] ?: $m[1].'-06-01';
            $data['end_date'] = $data['end_date'] ?: $m[2].'-05-31';
        }
        if (empty($data['start_date']) || empty($data['end_date'])) {
            $message = 'This academic year needs a start date and an end date.';
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $message], 422);
            }

            return back()->withErrors(['start_date' => $message])->withInput();
        }

        $data['name'] = preg_replace('/\s*[–\-]\s*/u', '–', $data['name']);

        $status = (string) $request->input('status', $academicYear->status);
        $data['status'] = in_array($status, ['upcoming', 'current', 'completed', 'archived'], true)
            ? $status
            : ($academicYear->status ?: 'upcoming');

        if ($data['status'] === 'current') {
            AcademicYear::query()
                ->where('id', '!=', $academicYear->id)
                ->where('status', 'current')
                ->get()
                ->each(function (AcademicYear $other) {
                    $other->update(['status' => 'completed', 'enrollment_open' => false]);
                    $other->syncTermRecords();
                });
        } else {
            $data['enrollment_open'] = false;
        }

        $academicYear->update($data);
        $academicYear->syncTermRecords();
        $this->forgetYearCache();
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
        if ($academicYear->statusLabel() === 'current') {
            $message = 'The current academic year cannot be archived. Set another year as Current first.';
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $message], 422);
            }

            return redirect()->route('academic_years.index')->with('error', $message);
        }

        $academicYear->update(['status' => 'archived', 'enrollment_open' => false]);
        $academicYear->syncTermRecords();
        $this->forgetYearCache();

        $message = 'Academic year "'.$name.'" was archived. Classes, subjects, and curriculum were kept.';
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $message,
            ]);
        }

        return redirect()->route('academic_years.index')->with('success', $message);
    }

    public function unarchive(Request $request, AcademicYear $academicYear)
    {
        $name = $academicYear->name;
        if ($academicYear->statusLabel() !== 'archived') {
            $message = 'Only an archived academic year can be restored.';
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $message], 422);
            }

            return redirect()->route('academic_years.index')->with('error', $message);
        }

        $academicYear->update(['status' => 'completed', 'enrollment_open' => false]);
        $academicYear->syncTermRecords();
        $this->forgetYearCache();

        $message = 'Academic year "'.$name.'" is Completed again.';
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $message,
            ]);
        }

        return redirect()->route('academic_years.index')->with('success', $message);
    }

    public function setEnrollment(Request $request, AcademicYear $academicYear)
    {
        $open = $request->boolean('enrollment_open');
        if ($open && $academicYear->statusLabel() !== 'current') {
            $message = 'Open enrollment only after this academic year is the active year.';
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $message], 422);
            }

            return redirect()->route('academic_years.index')->with('error', $message);
        }

        $academicYear->update(['enrollment_open' => $open && $academicYear->statusLabel() === 'current']);
        $this->forgetYearCache();

        $message = $academicYear->enrollment_open
            ? 'Enrollment is open for Academic Year '.$academicYear->displayName().'.'
            : 'Enrollment is currently closed for Academic Year '.$academicYear->displayName().'.';

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'message' => $message]);
        }

        return redirect()->route('academic_years.index')->with('success', $message);
    }

    protected function forgetYearCache(): void
    {
        Cache::forget('academic.year.current.shared');
        Cache::forget('academic.year.current');
        Cache::forget('academic.semester.current');
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
            'enrollment_open' => (bool) $year->enrollment_open,
            'enrollment_label' => $year->enrollment_open ? 'Open' : 'Closed',
            'archive_url' => route('academic_years.destroy', $year),
            'semesters_count' => (int) ($year->semesters_count ?? $year->semesters()->count()),
            'update_url' => route('academic_years.update', $year),
            'destroy_url' => route('academic_years.destroy', $year),
        ];
    }
}
