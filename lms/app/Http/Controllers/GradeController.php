<?php

namespace App\Http\Controllers;

use App\Models\Grade;
use App\Models\Student;
use App\Models\Subject;
use App\Models\SubjectComponent;
use App\Models\Section;
use App\Models\AcademicYear;
use App\Models\Semester;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class GradeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            if (!auth()->user()->hasRole(User::ROLE_TEACHER) && !auth()->user()->hasRole(User::ROLE_ADMIN)) {
                abort(403, 'Only teachers and administrators can manage grades.');
            }
            return $next($request);
        });
    }

    public function index(Request $request)
    {
        $teacher = auth()->user()->teacher;
        $subjects = $teacher ? $teacher->subjects : Subject::all();
        $sections = $teacher ? Section::where('adviser_id', $teacher->id)->get() : Section::all();
        $academicYears = AcademicYear::orderBy('start_date', 'desc')->get();
        $semesters = Semester::orderBy('name')->get();

        $subjectId = $request->input('subject_id');
        $sectionId = $request->input('section_id');
        $academicYearId = $request->input('academic_year_id');
        $semesterId = $request->input('semester_id');

        $students = collect();
        $grades = collect();
        $gpaData = [];

        if ($subjectId && $sectionId) {
            $students = Student::whereHas('sections', function($q) use ($sectionId) {
                $q->where('sections.id', $sectionId);
            })->whereHas('subjects', function($q) use ($subjectId) {
                $q->where('subjects.id', $subjectId);
            })->orderBy('first_name')->get();

            $query = Grade::where('subject_id', $subjectId)
                ->with(['student', 'component']);

            if ($academicYearId) {
                $query->where('academic_year_id', $academicYearId);
            }
            if ($semesterId) {
                $query->where('semester_id', $semesterId);
            }
            if ($teacher) {
                $query->where('teacher_id', $teacher->id);
            }

            $grades = $query->get();

            // Calculate GPA for each student
            foreach ($students as $student) {
                $gpaData[$student->id] = $this->calculateGPA($student->id, $academicYearId, $semesterId);
            }
        }

        return view('grades.index', compact(
            'subjects', 'sections', 'academicYears', 'semesters',
            'students', 'grades', 'gpaData',
            'subjectId', 'sectionId', 'academicYearId', 'semesterId'
        ));
    }

    public function create(Request $request)
    {
        $teacher = auth()->user()->teacher;
        $subjects = $teacher ? $teacher->subjects : Subject::all();
        $sections = $teacher ? Section::where('adviser_id', $teacher->id)->get() : Section::all();
        $academicYears = AcademicYear::orderBy('start_date', 'desc')->get();
        $semesters = Semester::orderBy('name')->get();

        $subjectId = $request->input('subject_id');
        $sectionId = $request->input('section_id');
        $academicYearId = $request->input('academic_year_id');
        $semesterId = $request->input('semester_id');

        $students = collect();
        $components = collect();
        $existingGrades = [];

        if ($subjectId && $sectionId) {
            $students = Student::whereHas('sections', function($q) use ($sectionId) {
                $q->where('sections.id', $sectionId);
            })->whereHas('subjects', function($q) use ($subjectId) {
                $q->where('subjects.id', $subjectId);
            })->orderBy('first_name')->get();

            $components = SubjectComponent::where('subject_id', $subjectId)
                ->where('is_active', true)
                ->orderBy('name')
                ->get();

            // Get existing grades for the selected period
            if ($academicYearId && $semesterId) {
                $existingGrades = Grade::where('subject_id', $subjectId)
                    ->where('academic_year_id', $academicYearId)
                    ->where('semester_id', $semesterId)
                    ->get()
                    ->groupBy(['student_id', 'component_id']);
            }
        }

        return view('grades.create', compact(
            'subjects', 'sections', 'academicYears', 'semesters',
            'students', 'components', 'existingGrades',
            'subjectId', 'sectionId', 'academicYearId', 'semesterId'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'section_id' => 'required|exists:sections,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'semester_id' => 'required|exists:semesters,id',
            'grades' => 'required|array',
            'grades.*.student_id' => 'required|exists:students,id',
            'grades.*.component_id' => 'required|exists:subject_components,id',
            'grades.*.score' => 'nullable|numeric|min:0',
            'grades.*.max_score' => 'required|numeric|min:1',
            'grades.*.remarks' => 'nullable|string|max:255',
        ]);

        $teacher = auth()->user()->teacher;
        if (!$teacher) {
            return back()->with('error', 'Only teachers can enter grades.');
        }

        foreach ($request->grades as $gradeData) {
            if (isset($gradeData['score'])) {
                Grade::updateOrCreate(
                    [
                        'student_id' => $gradeData['student_id'],
                        'subject_id' => $request->subject_id,
                        'component_id' => $gradeData['component_id'],
                        'academic_year_id' => $request->academic_year_id,
                        'semester_id' => $request->semester_id,
                    ],
                    [
                        'score' => $gradeData['score'],
                        'max_score' => $gradeData['max_score'],
                        'remarks' => $gradeData['remarks'] ?? null,
                        'teacher_id' => $teacher->id,
                    ]
                );
            }
        }

        return redirect()->route('grades.create', [
            'subject_id' => $request->subject_id,
            'section_id' => $request->section_id,
            'academic_year_id' => $request->academic_year_id,
            'semester_id' => $request->semester_id,
        ])->with('success', 'Grades saved successfully.');
    }

    public function show(Grade $grade)
    {
        return view('grades.show', compact('grade'));
    }

    public function edit(Grade $grade)
    {
        $teacher = auth()->user()->teacher;
        if ($teacher && $grade->teacher_id !== $teacher->id && !auth()->user()->hasRole(User::ROLE_ADMIN)) {
            abort(403, 'You can only edit your own grades.');
        }

        return view('grades.edit', compact('grade'));
    }

    public function update(Request $request, Grade $grade)
    {
        $request->validate([
            'score' => 'required|numeric|min:0',
            'max_score' => 'required|numeric|min:1',
            'remarks' => 'nullable|string|max:255',
        ]);

        $teacher = auth()->user()->teacher;
        if ($teacher && $grade->teacher_id !== $teacher->id && !auth()->user()->hasRole(User::ROLE_ADMIN)) {
            abort(403, 'You can only edit your own grades.');
        }

        $grade->update($request->only(['score', 'max_score', 'remarks']));

        return redirect()->route('grades.index')->with('success', 'Grade updated successfully.');
    }

    public function destroy(Grade $grade)
    {
        $teacher = auth()->user()->teacher;
        if ($teacher && $grade->teacher_id !== $teacher->id && !auth()->user()->hasRole(User::ROLE_ADMIN)) {
            abort(403, 'You can only delete your own grades.');
        }

        $grade->delete();
        return redirect()->route('grades.index')->with('success', 'Grade deleted successfully.');
    }

    public function export(Request $request)
    {
        $teacher = auth()->user()->teacher;
        if (!$teacher && !auth()->user()->hasRole(User::ROLE_ADMIN)) {
            return back()->with('error', 'You do not have permission to export grades.');
        }

        $subjectId = $request->input('subject_id');
        $sectionId = $request->input('section_id');
        $academicYearId = $request->input('academic_year_id');
        $semesterId = $request->input('semester_id');

        if (!$subjectId || !$sectionId) {
            return back()->with('error', 'Please select subject and section.');
        }

        $students = Student::whereHas('sections', function($q) use ($sectionId) {
            $q->where('sections.id', $sectionId);
        })->whereHas('subjects', function($q) use ($subjectId) {
            $q->where('subjects.id', $subjectId);
        })->orderBy('first_name')->get();

        $components = SubjectComponent::where('subject_id', $subjectId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $grades = Grade::where('subject_id', $subjectId)
            ->with(['student', 'component']);

        if ($academicYearId) {
            $grades->where('academic_year_id', $academicYearId);
        }
        if ($semesterId) {
            $grades->where('semester_id', $semesterId);
        }
        if ($teacher) {
            $grades->where('teacher_id', $teacher->id);
        }

        $grades = $grades->get()->groupBy(['student_id', 'component_id']);

        $format = $request->input('format');
        $filename = 'grades_' . $subjectId . '_' . $sectionId . '.' . ($format === 'excel' ? 'xlsx' : 'pdf');

        if ($format === 'excel') {
            return Excel::download(new \App\Exports\GradeExport($students, $components, $grades), $filename);
        } elseif ($format === 'pdf') {
            $pdf = Pdf::loadView('grades.export_pdf', [
                'students' => $students,
                'components' => $components,
                'grades' => $grades,
            ]);
            return $pdf->download($filename);
        }

        return back()->with('error', 'Export format not supported.');
    }

    private function calculateGPA($studentId, $academicYearId = null, $semesterId = null)
    {
        $query = Grade::where('student_id', $studentId);
        
        if ($academicYearId) {
            $query->where('academic_year_id', $academicYearId);
        }
        if ($semesterId) {
            $query->where('semester_id', $semesterId);
        }

        $grades = $query->with(['component'])->get();

        if ($grades->isEmpty()) {
            return ['gpa' => 0, 'total_units' => 0];
        }

        $totalWeightedScore = 0;
        $totalWeight = 0;

        foreach ($grades as $grade) {
            if ($grade->percentage !== null) {
                $weight = $grade->component->weight ?? 1;
                $totalWeightedScore += ($grade->percentage * $weight);
                $totalWeight += $weight;
            }
        }

        $gpa = $totalWeight > 0 ? $totalWeightedScore / $totalWeight : 0;

        return [
            'gpa' => round($gpa, 2),
            'total_units' => $totalWeight,
        ];
    }
} 