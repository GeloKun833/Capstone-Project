<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Grade;
use App\Models\Attendance;
use App\Models\AcademicYear;
use App\Models\Semester;
use App\Models\Subject;
use App\Services\ParentPortalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ParentController extends Controller
{
    protected ParentPortalService $portalService;

    public function __construct(ParentPortalService $portalService)
    {
        $this->portalService = $portalService;
    }

    public function childGrades($childId, Request $request)
    {
        $parent = Auth::user();
        $child = Student::where('parent_email', $parent->email)
            ->where('id', $childId)
            ->firstOrFail();

        $academicYear = AcademicYear::latest()->first();
        $semester = Semester::latest()->first();

        $grades = $this->portalService->getChildGrades($child, $academicYear, $semester);
        $subjects = Subject::all();

        return view('parent.child_grades', compact('child', 'grades', 'subjects', 'academicYear', 'semester'));
    }

    public function childAttendance($childId, Request $request)
    {
        $parent = Auth::user();
        $child = Student::where('parent_email', $parent->email)
            ->where('id', $childId)
            ->firstOrFail();

        $attendance = $this->portalService->getChildAttendance($child, $request);
        $subjects = Subject::all();

        $total = $attendance->count();
        $present = $attendance->where('status', 'present')->count();
        $summary = [
            'total' => $total,
            'present' => $present,
            'absent' => $total - $present,
            'percentage' => $total > 0 ? round(($present / $total) * 100, 2) : 0,
        ];

        return view('parent.child_attendance', compact('child', 'attendance', 'subjects', 'summary'));
    }

    public function childActivities($childId, Request $request)
    {
        $parent = Auth::user();
        $child = Student::where('parent_email', $parent->email)
            ->where('id', $childId)
            ->firstOrFail();

        $academicYear = AcademicYear::latest()->first();
        $semester = Semester::latest()->first();

        $activities = $this->portalService->getChildActivities($child, $academicYear, $semester);
        $submissions = $this->portalService->getChildSubmissions($child, $academicYear, $semester);

        return view('parent.child_activities', compact('child', 'activities', 'submissions', 'academicYear', 'semester'));
    }

    public function childProfile($childId, Request $request)
    {
        $parent = Auth::user();
        $child = Student::where('parent_email', $parent->email)
            ->where('id', $childId)
            ->firstOrFail();

        $academicYear = AcademicYear::latest()->first();
        $semester = Semester::latest()->first();

        $enrollments = $child->enrollments()
            ->with(['subject', 'academicYear', 'semester'])
            ->where('status', 'active')
            ->get();

        $totalGrades = Grade::where('student_id', $child->id)->count();
        $averageGrade = Grade::where('student_id', $child->id)->avg('percentage') ?? 0;
        $totalAttendance = Attendance::where('student_id', $child->id)->count();
        $presentAttendance = Attendance::where('student_id', $child->id)
            ->where('status', 'present')
            ->count();
        $attendancePercentage = $totalAttendance > 0 ? round(($presentAttendance / $totalAttendance) * 100, 2) : 0;

        $recentGrades = Grade::where('student_id', $child->id)
            ->with('subject')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $recentAttendance = Attendance::where('student_id', $child->id)
            ->with('subject')
            ->orderBy('date', 'desc')
            ->limit(10)
            ->get();

        return view('parent.child_profile', compact(
            'child',
            'enrollments',
            'academicYear',
            'semester',
            'totalGrades',
            'averageGrade',
            'totalAttendance',
            'presentAttendance',
            'attendancePercentage',
            'recentGrades',
            'recentAttendance'
        ));
    }
}
