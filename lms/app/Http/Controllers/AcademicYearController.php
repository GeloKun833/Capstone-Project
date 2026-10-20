<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

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
            'name' => ['required', 'string', 'max:50', 'regex:/^\d{4}-\d{4}$/'],
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
        ], [
            'name.regex' => 'Academic year must look like 2026–2027 (years only).',
            'start_date.required' => 'Enter the day the school year starts.',
            'end_date.required' => 'Enter the day the school year ends.',
            'end_date.after' => 'School year ends must be after school year starts.',
        ]);

        $data['name'] = preg_replace('/\s*[–\-]\s*/', '–', $data['name']);
        if (AcademicYear::findByNameKey($data['name'])) {
            throw ValidationException::withMessages([
                'name' => 'This academic year already exists.',
            ]);
        }
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
            'name' => ['required', 'string', 'max:50', 'regex:/^\d{4}-\d{4}$/'],
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
        ], [
            'name.regex' => 'Academic year must look like 2026–2027 (years only).',
            'start_date.required' => 'Enter the day the school year starts.',
            'end_date.required' => 'Enter the day the school year ends.',
            'end_date.after' => 'School year ends must be after school year starts.',
        ]);

        $data['name'] = preg_replace('/\s*[–\-]\s*/u', '–', $data['name']);
        if (AcademicYear::findByNameKey($data['name'], $academicYear->id)) {
            throw ValidationException::withMessages([
                'name' => 'This academic year already exists.',
            ]);
        }

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

    public function forceDelete(Request $request, AcademicYear $academicYear)
    {
        $name = $academicYear->displayName();

        try {
            DB::transaction(function () use ($academicYear) {
                $academicYear->delete();
            });
        } catch (QueryException $exception) {
            $message = 'Academic year '.$name.' could not be deleted because other records still use it.';
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $message], 422);
            }

            return redirect()->route('academic_years.index')->with('error', $message);
        }

        $this->forgetYearCache();
        $message = 'Academic year '.$name.' was deleted.';
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'message' => $message]);
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
        if ($open) {
            $message = app(\App\Services\EnrollmentReadiness::class)->openBlock($academicYear);
            if ($message) {
                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json(['success' => false, 'message' => $message], 422);
                }

                return redirect()->back()->with('error', $message);
            }
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

    public function updateEnrollmentSettings(Request $request, AcademicYear $academicYear)
    {
        if ($academicYear->statusLabel() !== 'current') {
            return redirect()->back()->with('error', 'Enrollment settings can only be changed for the current academic year.');
        }

        $validated = $request->validate([
            'enrollment_starts_at' => 'required|date',
            'enrollment_ends_at' => 'required|date|after_or_equal:enrollment_starts_at',
            'enrollment_open' => 'nullable|boolean',
        ]);

        $start = \Carbon\Carbon::parse($validated['enrollment_starts_at'])->startOfDay();
        $end = \Carbon\Carbon::parse($validated['enrollment_ends_at'])->startOfDay();
        $yearStart = $academicYear->start_date?->copy()->startOfDay();
        $yearEnd = $academicYear->end_date?->copy()->startOfDay();
        $outside = ($yearStart && ($start->lt($yearStart) || $end->lt($yearStart)))
            || ($yearEnd && ($start->gt($yearEnd) || $end->gt($yearEnd)));
        if ($outside) {
            return redirect()->back()->withInput()->with('error', 'Enrollment dates must fall inside Academic Year '.$academicYear->displayName().'.');
        }

        $academicYear->enrollment_starts_at = $start->toDateString();
        $academicYear->enrollment_ends_at = $end->toDateString();

        $open = $request->boolean('enrollment_open');
        if ($open) {
            $block = app(\App\Services\EnrollmentReadiness::class)->openBlock($academicYear);
            if ($block) {
                $academicYear->enrollment_open = false;
                $academicYear->save();
                $this->forgetYearCache();

                return redirect()->back()->with('error', $block);
            }
        }

        $academicYear->enrollment_open = $open;
        $academicYear->save();
        $this->forgetYearCache();

        $fresh = $academicYear->fresh();
        $available = $fresh->enrollmentIsOpen();
        $message = $available
            ? 'Enrollment is available for Academic Year '.$fresh->displayName().'.'
            : ($open
                ? app(\App\Services\EnrollmentReadiness::class)->studentMessage($fresh)
                : 'Enrollment period saved for Academic Year '.$fresh->displayName().'.');

        $redirect = redirect()->route('academic_years.index')->with($available || ! $open ? 'success' : 'error', $message);
        if (! $fresh->class_starts_at || ! $fresh->class_ends_at) {
            $redirect->with('ask_class_start', $fresh->id);
        }

        return $redirect;
    }

    public function updateClassStart(Request $request, AcademicYear $academicYear)
    {
        $validated = $request->validate([
            'class_starts_at' => 'required|date',
            'class_ends_at' => 'required|date|after_or_equal:class_starts_at',
        ], [
            'class_ends_at.after_or_equal' => 'End of class must be on or after the start of class.',
        ]);

        $classStart = \Carbon\Carbon::parse($validated['class_starts_at'], 'Asia/Manila')->startOfDay();
        $classEnd = \Carbon\Carbon::parse($validated['class_ends_at'], 'Asia/Manila')->startOfDay();
        if ($academicYear->enrollment_ends_at && $classStart->lte($academicYear->enrollment_ends_at->copy()->startOfDay())) {
            return redirect()->back()->withInput()->with('error', 'Start of class must be after the enrollment period ends.');
        }
        if ($academicYear->end_date && $classEnd->gt($academicYear->end_date->copy()->endOfDay())) {
            return redirect()->back()->withInput()->with('error', 'End of class must be on or before the end of the school year.');
        }

        $academicYear->class_starts_at = $classStart->toDateString();
        $academicYear->class_ends_at = $classEnd->toDateString();
        $academicYear->save();

        return redirect()->route('academic_years.index')->with(
            'success',
            'Classes for '.$academicYear->displayName().' run from '.$classStart->format('M d, Y').' to '.$classEnd->format('M d, Y').'.'
        );
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
