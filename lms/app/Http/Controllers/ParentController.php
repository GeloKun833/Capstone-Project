<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Semester;
use App\Models\Subject;
use App\Services\ParentPortalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ParentController extends Controller
{
    public function __construct(protected ParentPortalService $portalService)
    {
        $this->middleware(['auth', 'role:Parent']);
    }

    public function index()
    {
        $parent = Auth::user();
        $children = $this->portalService->getChildrenForParent($parent);

        if ($children->count() === 1) {
            return redirect()->route('parent.child.hub', [
                'childId' => $children->first()->id,
                'tab' => 'overview',
            ]);
        }

        return view('parent.index', compact('children', 'parent'));
    }

    public function childHub($childId, Request $request)
    {
        $parent = Auth::user();
        $child = $this->portalService->resolveChildForParent($parent, (int) $childId);
        $children = $this->portalService->getChildrenForParent($parent);
        $tab = $request->get('tab', 'overview');

        $academicYear = $this->portalService->getAcademicYear(
            $request->integer('academic_year_id') ?: null
        );
        $semester = Semester::orderByDesc('id')->first();
        $academicYears = AcademicYear::orderByDesc('name')->get();

        $overview = $this->portalService->getChildOverview($child);
        $grades = $this->portalService->getChildGrades($child, $academicYear, $semester);
        $quarterlyGrades = $this->portalService->getChildQuarterlyGrades(
            $child,
            $academicYear?->id
        );

        $reportCard = app(\App\Services\ReportCardService::class)->forStudent($child, $academicYear);
        $observedIndicators = $reportCard['observedGrouped'];
        $observedRatings = $reportCard['observedRatings'];
        $generalAverages = $reportCard['generalAverages'];

        $attendance = $this->portalService->getChildAttendance($child, $request);
        $activities = $this->portalService->getChildActivities($child, $academicYear, $semester);
        $submissions = $this->portalService->getChildSubmissions($child, $academicYear, $semester);
        $assignmentSubmissions = $this->portalService->getChildAssignmentSubmissions($child);
        $feedbackItems = $this->portalService->getChildFeedback($child);

        $total = $attendance->count();
        $present = $attendance->where('status', 'present')->count();
        $attendanceSummary = [
            'total' => $total,
            'present' => $present,
            'absent' => $total - $present,
            'percentage' => $total > 0 ? round(($present / $total) * 100, 2) : 0,
        ];

        return view('parent.child_hub', compact(
            'parent',
            'child',
            'children',
            'tab',
            'academicYear',
            'academicYears',
            'semester',
            'overview',
            'grades',
            'quarterlyGrades',
            'observedIndicators',
            'observedRatings',
            'generalAverages',
            'attendance',
            'attendanceSummary',
            'activities',
            'submissions',
            'assignmentSubmissions',
            'feedbackItems'
        ));
    }

    public function childProfile($childId, Request $request)
    {
        return redirect()->route('parent.child.hub', [
            'childId' => $childId,
            'tab' => 'overview',
        ]);
    }

    public function childGrades($childId, Request $request)
    {
        return redirect()->route('parent.child.hub', array_merge(
            ['childId' => $childId, 'tab' => 'grades'],
            $request->only('academic_year_id')
        ));
    }

    public function childAttendance($childId, Request $request)
    {
        return redirect()->route('parent.child.hub', array_merge(
            ['childId' => $childId, 'tab' => 'attendance'],
            $request->only('date', 'month', 'year')
        ));
    }

    public function childActivities($childId, Request $request)
    {
        return redirect()->route('parent.child.hub', [
            'childId' => $childId,
            'tab' => 'activities',
        ]);
    }
}
