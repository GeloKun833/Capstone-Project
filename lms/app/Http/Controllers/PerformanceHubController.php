<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\GradeAlert;
use App\Models\Semester;
use App\Models\Student;
use App\Services\StudentPerformanceService;
use App\Exports\FilteredGpaExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class PerformanceHubController extends Controller
{
    public function __construct(private StudentPerformanceService $performance)
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        if (! in_array($user->role_name, ['Teacher', 'Admin'], true)) {
            abort(403);
        }

        $tab = $request->get('tab', 'ranking');
        if (! in_array($tab, ['ranking', 'analytics', 'alerts'], true)) {
            $tab = 'ranking';
        }

        $academicYears = AcademicYear::orderByDesc('name')->get();
        $selectedAcademicYearId = (int) ($request->get('academic_year_id')
            ?: AcademicYear::orderByDesc('id')->value('id'));

        $semesters = Semester::where('academic_year_id', $selectedAcademicYearId)
            ->orderBy('id')
            ->get();
        if ($semesters->isEmpty()) {
            $semesters = Semester::orderByDesc('id')->get();
        }

        $selectedSemesterId = (int) ($request->get('semester_id')
            ?: ($semesters->first()->id ?? 0));

        $sections = $this->performance->sectionsForUser($user);
        $selectedSectionId = $request->filled('section_id') ? (int) $request->get('section_id') : null;
        if ($selectedSectionId && ! $sections->contains('id', $selectedSectionId)) {
            $selectedSectionId = null;
        }

        $allowedStudentIds = $this->performance->allowedStudentIdsForUser($user);

        $routePrefix = $user->role_name === 'Admin' ? 'admin' : 'teacher';

        $rankingRows = collect();
        $sectionSummary = null;
        $students = collect();
        $analytics = null;
        $selectedStudentId = $request->filled('student_id') ? (int) $request->get('student_id') : null;
        $alerts = null;
        $alertFilter = $request->get('alert_status', 'open');
        $alertType = $request->get('alert_type');

        if ($selectedAcademicYearId && $selectedSemesterId) {
            if ($tab === 'ranking') {
                $rankingRows = $this->performance->rankingRows(
                    $selectedAcademicYearId,
                    $selectedSemesterId,
                    $selectedSectionId,
                    $allowedStudentIds
                );
                $sectionSummary = $this->performance->sectionAnalyticsSummary(
                    $selectedAcademicYearId,
                    $selectedSemesterId,
                    $selectedSectionId,
                    $allowedStudentIds
                );
            }

            if ($tab === 'analytics') {
                $studentsQuery = Student::query()->orderBy('last_name')->orderBy('first_name');
                if ($allowedStudentIds !== null) {
                    $studentsQuery->whereIn('id', $allowedStudentIds);
                }
                if ($selectedSectionId) {
                    $studentsQuery->whereHas('sections', fn ($q) => $q->where('sections.id', $selectedSectionId));
                }
                $students = $studentsQuery->get(['id', 'first_name', 'last_name', 'middle_name']);

                $sectionSummary = $this->performance->sectionAnalyticsSummary(
                    $selectedAcademicYearId,
                    $selectedSemesterId,
                    $selectedSectionId,
                    $allowedStudentIds
                );

                if ($selectedStudentId && ($allowedStudentIds === null || in_array($selectedStudentId, $allowedStudentIds, true))) {
                    $student = Student::find($selectedStudentId);
                    if ($student) {
                        $analytics = $this->performance->studentAnalytics(
                            $student,
                            $selectedAcademicYearId,
                            $selectedSemesterId
                        );
                        $analytics['student'] = $student;
                    }
                }
            }

            if ($tab === 'alerts') {
                $alertsQuery = GradeAlert::with(['student', 'subject', 'academicYear', 'semester', 'resolvedBy'])
                    ->where('academic_year_id', $selectedAcademicYearId)
                    ->orderByDesc('created_at');

                if ($allowedStudentIds !== null) {
                    $alertsQuery->whereIn('student_id', $allowedStudentIds);
                }
                if ($selectedSectionId) {
                    $alertsQuery->whereHas('student.sections', fn ($q) => $q->where('sections.id', $selectedSectionId));
                }
                if ($alertFilter === 'open') {
                    $alertsQuery->where('is_resolved', false);
                } elseif ($alertFilter === 'resolved') {
                    $alertsQuery->where('is_resolved', true);
                }
                if ($alertType) {
                    $alertsQuery->where('alert_type', $alertType);
                }

                $alerts = $alertsQuery->paginate(20)->withQueryString();

                $sectionSummary = $this->performance->sectionAnalyticsSummary(
                    $selectedAcademicYearId,
                    $selectedSemesterId,
                    $selectedSectionId,
                    $allowedStudentIds
                );
            }
        }

        return view('grading.performance-hub', compact(
            'tab',
            'academicYears',
            'semesters',
            'sections',
            'selectedAcademicYearId',
            'selectedSemesterId',
            'selectedSectionId',
            'selectedStudentId',
            'rankingRows',
            'sectionSummary',
            'students',
            'analytics',
            'alerts',
            'alertFilter',
            'alertType',
            'routePrefix'
        ));
    }

    public function resolveAlert(Request $request, GradeAlert $alert)
    {
        $user = Auth::user();
        if (! in_array($user->role_name, ['Teacher', 'Admin'], true)) {
            abort(403);
        }

        $allowed = $this->performance->allowedStudentIdsForUser($user);
        if ($allowed !== null && ! in_array((int) $alert->student_id, $allowed, true)) {
            abort(403, 'You cannot resolve alerts for this student.');
        }

        $this->performance->resolveAlert($alert, $user);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Alert resolved.']);
        }

        return redirect()->back()->with('success', 'Alert resolved successfully.');
    }

    public function exportGpa(Request $request)
    {
        $user = Auth::user();
        if (! in_array($user->role_name, ['Teacher', 'Admin'], true)) {
            abort(403);
        }

        $academicYearId = (int) $request->get('academic_year_id');
        $semesterId = (int) $request->get('semester_id');
        $sectionId = $request->filled('section_id') ? (int) $request->get('section_id') : null;
        $format = $request->get('format', 'excel');

        $allowed = $this->performance->allowedStudentIdsForUser($user);
        // Recalculate before export so numbers are current
        if ($academicYearId && $semesterId) {
            $this->performance->rankingRows($academicYearId, $semesterId, $sectionId, $allowed);
        }

        $fileName = 'gpa_report_'.date('Y-m-d_H-i-s');

        $query = \App\Models\StudentGpa::with(['student', 'academicYear', 'semester'])
            ->where('academic_year_id', $academicYearId)
            ->where('semester_id', $semesterId)
            ->orderByDesc('gpa');

        if ($sectionId) {
            $query->whereHas('student.sections', fn ($q) => $q->where('sections.id', $sectionId));
        }
        if ($allowed !== null) {
            $query->whereIn('student_id', $allowed);
        }

        $gpaRecords = $query->get();

        if ($format === 'pdf') {
            $pdf = Pdf::loadView('exports.gpa-pdf', compact('gpaRecords'));

            return $pdf->download($fileName.'.pdf');
        }

        return Excel::download(new FilteredGpaExport($gpaRecords), $fileName.'.xlsx');
    }

    /** Legacy redirects keep old bookmarks working. */
    public function redirectRanking(Request $request)
    {
        return redirect()->route(
            Auth::user()->role_name === 'Admin' ? 'admin.grading.performance-hub' : 'teacher.grading.performance-hub',
            array_merge($request->query(), ['tab' => 'ranking'])
        );
    }

    public function redirectAnalytics(Request $request)
    {
        return redirect()->route(
            Auth::user()->role_name === 'Admin' ? 'admin.grading.performance-hub' : 'teacher.grading.performance-hub',
            array_merge($request->query(), ['tab' => 'analytics'])
        );
    }

    public function redirectAlerts(Request $request)
    {
        return redirect()->route(
            Auth::user()->role_name === 'Admin' ? 'admin.grading.performance-hub' : 'teacher.grading.performance-hub',
            array_merge($request->query(), ['tab' => 'alerts'])
        );
    }
}
