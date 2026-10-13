<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\ObservedValueIndicator;
use App\Models\Student;
use App\Models\StudentObservedValue;
use App\Services\ReportCardService;
use App\Services\TeacherClassAssignmentService;
use App\Support\GradeEncoding;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ObservedValuesController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        // Observed Values lives inside Grade Entry now
        return redirect()->route('teacher.grading.grade-entry', array_filter([
            'section_id' => $request->get('section_id'),
            'quarter' => $request->get('quarter'),
            'academic_year_id' => $request->get('academic_year_id'),
            'step' => 'observed',
        ]));
    }

    /**
     * Batch-save observed values for the selected quarter (from Grade Entry).
     */
    public function store(Request $request)
    {
        GradeEncoding::authorize($request);
        $teacher = Auth::user()->teacher;
        if (! $teacher) {
            abort(403, 'Teacher profile not found.');
        }

        $options = app(TeacherClassAssignmentService::class)->optionsFor($teacher, AcademicYear::active()?->id);
        $allowedSectionIds = $options['sections']->pluck('id')->map(fn ($id) => (int) $id)->all();

        $validated = $request->validate([
            'section_id' => ['required', Rule::in($allowedSectionIds ?: [0])],
            'ratings' => 'required|array',
            'ratings.*.student_id' => 'required|exists:students,id',
            'ratings.*.indicator_id' => 'required|exists:observed_value_indicators,id',
            'ratings.*.mark' => ['nullable', Rule::in(StudentObservedValue::RATINGS)],
        ]);

        $year = AcademicYear::active();
        $selectedPeriod = \App\Support\SchoolQuarter::find($year, (int) $request->input('quarter'));
        if (! $year || ! $selectedPeriod) {
            $message = 'Choose a term from the current academic year.';
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $message], 422);
            }

            return back()->with('error', $message);
        }
        $quarter = (int) $selectedPeriod['number'];
        $quarterField = 'quarter_' . $quarter;
        $section = \App\Models\Section::find($validated['section_id']);
        if (! \App\Support\GradeEncoding::isLevelOpen($section?->grade_level)) {
            $message = \App\Support\GradeEncoding::closedMessage($section?->grade_level);
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $message], 403);
            }

            return back()->with('error', $message);
        }
        $sectionStudentIds = Student::whereHas('sections', function ($q) use ($validated, $year) {
            $q->where('sections.id', $validated['section_id'])
                ->where('student_section_assignments.academic_year_id', $year->id);
        })->pluck('id')->map(fn ($id) => (int) $id)->all();

        foreach ($validated['ratings'] as $row) {
            if (! in_array((int) $row['student_id'], $sectionStudentIds, true)) {
                continue;
            }

            $record = StudentObservedValue::firstOrNew([
                'student_id' => $row['student_id'],
                'academic_year_id' => $year->id,
                'indicator_id' => $row['indicator_id'],
            ]);
            $record->teacher_id = $teacher->id;
            $record->{$quarterField} = $row['mark'] ?: null;
            $record->save();
        }

        $redirect = route('teacher.grading.grade-entry', [
            'section_id' => $validated['section_id'],
            'quarter' => $quarter,
            'step' => 'summary',
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Observed values saved. Quarter summary is ready.',
                'redirect' => $redirect,
            ]);
        }

        return redirect()->to($redirect)->with('success', 'Observed values saved. Quarter summary is ready.');
    }
}
