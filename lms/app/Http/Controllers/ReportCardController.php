<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Student;
use App\Services\ParentPortalService;
use App\Services\ReportCardService;
use App\Services\StudentPerformanceService;
use App\Services\TeacherClassAssignmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReportCardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function teacherStudentReport(Request $request, Student $student)
    {
        $user = Auth::user();
        if ($user->role_name !== 'Teacher' && $user->role_name !== 'Admin') {
            abort(403);
        }

        if ($user->role_name === 'Teacher') {
            if (! $user->teacher) {
                abort(403, 'Teacher profile not found.');
            }
            $allowedIds = app(StudentPerformanceService::class)->studentIdsForTeacher($user->teacher);
            if (! in_array((int) $student->id, $allowedIds, true)) {
                abort(403, 'This student is not in your assigned classes.');
            }
        }

        $academicYear = AcademicYear::findOrFail($request->get('academic_year_id'));
        $quarter = (int) $request->get('quarter', 0);
        $quarter = in_array($quarter, [1, 2, 3, 4], true) ? $quarter : null;

        $data = app(ReportCardService::class)->forStudent($student, $academicYear, $quarter);

        return view('reports.report-card-print', $data + ['viewer' => 'teacher']);
    }

    public function teacherSectionReport(Request $request)
    {
        $user = Auth::user();
        if ($user->role_name !== 'Teacher' && $user->role_name !== 'Admin') {
            abort(403);
        }

        $sectionId = (int) $request->get('section_id');
        $quarter = (int) $request->get('quarter');
        $academicYear = AcademicYear::findOrFail($request->get('academic_year_id'));

        if ($user->role_name === 'Teacher' && $user->teacher) {
            $allowed = app(TeacherClassAssignmentService::class)->optionsFor($user->teacher);
            $allowedIds = $allowed['sections']->pluck('id')->map(fn ($id) => (int) $id)->all();
            if (! in_array($sectionId, $allowedIds, true)) {
                abort(403, 'Section not assigned to you.');
            }
        }

        $students = Student::whereHas('sections', fn ($q) => $q->where('sections.id', $sectionId))
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        $cards = $students->map(
            fn ($student) => app(ReportCardService::class)->forStudent($student, $academicYear, $quarter)
        );

        return view('reports.report-card-section-print', [
            'cards' => $cards,
            'quarter' => $quarter,
            'academicYear' => $academicYear,
            'sectionId' => $sectionId,
        ]);
    }

    public function studentReport(Request $request)
    {
        $student = Auth::user()->student;
        if (! $student) {
            abort(403);
        }

        $academicYear = AcademicYear::find($request->get('academic_year_id'))
            ?: AcademicYear::orderByDesc('name')->first();
        $quarter = (int) $request->get('quarter', 0);
        $quarter = in_array($quarter, [1, 2, 3, 4], true) ? $quarter : null;

        $data = app(ReportCardService::class)->forStudent($student, $academicYear, $quarter);

        return view('reports.report-card-print', $data + ['viewer' => 'student']);
    }

    public function parentChildReport(Request $request, $childId)
    {
        $parent = Auth::user();
        $student = app(ParentPortalService::class)->resolveChildForParent($parent, (int) $childId);

        $academicYear = AcademicYear::find($request->get('academic_year_id'))
            ?: AcademicYear::orderByDesc('name')->first();
        $quarter = (int) $request->get('quarter', 0);
        $quarter = in_array($quarter, [1, 2, 3, 4], true) ? $quarter : null;

        $data = app(ReportCardService::class)->forStudent($student, $academicYear, $quarter);

        return view('reports.report-card-print', $data + ['viewer' => 'parent']);
    }
}
