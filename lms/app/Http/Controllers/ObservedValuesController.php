<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\ObservedValueIndicator;
use App\Models\Student;
use App\Models\StudentObservedValue;
use App\Services\ReportCardService;
use App\Services\TeacherClassAssignmentService;
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
        $teacher = Auth::user()->teacher;
        if (! $teacher) {
            abort(403, 'Teacher profile not found.');
        }

        $options = app(TeacherClassAssignmentService::class)->optionsFor($teacher);
        $allowedSectionIds = $options['sections']->pluck('id')->map(fn ($id) => (int) $id)->all();

        $validated = $request->validate([
            'section_id' => ['required', Rule::in($allowedSectionIds)],
            'academic_year_id' => 'required|exists:academic_years,id',
            'quarter' => 'required|integer|in:1,2,3,4',
            'ratings' => 'required|array',
            'ratings.*.student_id' => 'required|exists:students,id',
            'ratings.*.indicator_id' => 'required|exists:observed_value_indicators,id',
            'ratings.*.mark' => ['nullable', Rule::in(StudentObservedValue::RATINGS)],
        ]);

        $quarterField = 'quarter_' . $validated['quarter'];
        $sectionStudentIds = Student::whereHas('sections', function ($q) use ($validated) {
            $q->where('sections.id', $validated['section_id']);
        })->pluck('id')->map(fn ($id) => (int) $id)->all();

        foreach ($validated['ratings'] as $row) {
            if (! in_array((int) $row['student_id'], $sectionStudentIds, true)) {
                continue;
            }

            $record = StudentObservedValue::firstOrNew([
                'student_id' => $row['student_id'],
                'academic_year_id' => $validated['academic_year_id'],
                'indicator_id' => $row['indicator_id'],
            ]);
            $record->teacher_id = $teacher->id;
            $record->{$quarterField} = $row['mark'] ?: null;
            $record->save();
        }

        return redirect()->route('teacher.grading.grade-entry', [
            'section_id' => $validated['section_id'],
            'quarter' => $validated['quarter'],
            'academic_year_id' => $validated['academic_year_id'],
            'step' => 'summary',
        ])->with('success', 'Observed values saved. Quarter summary is ready.');
    }
}
