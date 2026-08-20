<?php

namespace App\Http\Controllers;

use App\Services\AcademicAnalyticsService;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\AcademicYear;
use App\Models\Semester;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AnalyticsController extends Controller
{
    protected $analyticsService;

    public function __construct(AcademicAnalyticsService $analyticsService)
    {
        $this->analyticsService = $analyticsService;
    }

    /**
     * Student Analytics Dashboard
     */
    public function studentDashboard(Request $request)
    {
        $student = Auth::user()->student;
        if (!$student) {
            return redirect()->back()->with('error', 'Student profile not found.');
        }

        $academicYearId = $request->get('academic_year_id');
        $semesterId = $request->get('semester_id');

        $academicYears = AcademicYear::all();
        $semesters = Semester::all();

        $analytics = $this->analyticsService->getStudentAnalytics(
            $student->id, 
            $academicYearId, 
            $semesterId
        );

        return view('analytics.student-dashboard', compact(
            'analytics',
            'academicYears',
            'semesters',
            'academicYearId',
            'semesterId'
        ));
    }

    /**
     * Teacher Analytics Dashboard
     */
    public function teacherDashboard(Request $request)
    {
        $teacher = Auth::user()->teacher;
        if (!$teacher) {
            return redirect()->back()->with('error', 'Teacher profile not found.');
        }

        $academicYearId = $request->get('academic_year_id');
        $semesterId = $request->get('semester_id');

        $academicYears = AcademicYear::all();
        $semesters = Semester::all();

        $analytics = $this->analyticsService->getTeacherAnalytics(
            $teacher->id, 
            $academicYearId, 
            $semesterId
        );

        return view('analytics.teacher-dashboard', compact(
            'analytics',
            'academicYears',
            'semesters',
            'academicYearId',
            'semesterId'
        ));
    }

    /**
     * Admin Analytics Dashboard
     */
    public function adminDashboard(Request $request)
    {
        $academicYearId = $request->get('academic_year_id');
        $semesterId = $request->get('semester_id');

        $academicYears = AcademicYear::all();
        $semesters = Semester::all();

        $analytics = $this->analyticsService->getAdminAnalytics(
            $academicYearId, 
            $semesterId
        );

        return view('analytics.admin-dashboard', compact(
            'analytics',
            'academicYears',
            'semesters',
            'academicYearId',
            'semesterId'
        ));
    }

    /**
     * API endpoint for chart data
     */
    public function getChartData(Request $request)
    {
        $type = $request->get('type');
        $role = Auth::user()->role_name;

        switch ($type) {
            case 'grade_trends':
                if ($role === 'Student') {
                    $student = Auth::user()->student;
                    $data = $this->analyticsService->getStudentAnalytics($student->id);
                    return response()->json($data['grade_trends']);
                }
                break;

            case 'attendance_summary':
                if ($role === 'Student') {
                    $student = Auth::user()->student;
                    $data = $this->analyticsService->getStudentAnalytics($student->id);
                    return response()->json($data['attendance_summary']);
                }
                break;

            case 'class_averages':
                if ($role === 'Teacher') {
                    $teacher = Auth::user()->teacher;
                    $data = $this->analyticsService->getTeacherAnalytics($teacher->id);
                    return response()->json($data['class_averages']);
                }
                break;

            case 'school_overview':
                if ($role === 'Admin') {
                    $data = $this->analyticsService->getAdminAnalytics();
                    return response()->json($data['school_overview']);
                }
                break;

            case 'gpa_comparison':
                if ($role === 'Admin') {
                    $data = $this->analyticsService->getAdminAnalytics();
                    return response()->json($data['gpa_comparison']);
                }
                break;

            case 'pass_fail_rates':
                if ($role === 'Admin') {
                    $data = $this->analyticsService->getAdminAnalytics();
                    return response()->json($data['pass_fail_rates']);
                }
                break;
        }

        return response()->json(['error' => 'Invalid chart type or insufficient permissions']);
    }

    /**
     * Export analytics report
     */
    public function exportReport(Request $request)
    {
        $role = Auth::user()->role_name;
        $academicYearId = $request->get('academic_year_id');
        $semesterId = $request->get('semester_id');

        $filename = 'analytics_report_' . $role . '_' . date('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($role, $academicYearId, $semesterId) {
            $file = fopen('php://output', 'w');
            
            switch ($role) {
                case 'Student':
                    $student = Auth::user()->student;
                    $data = $this->analyticsService->getStudentAnalytics($student->id, $academicYearId, $semesterId);
                    
                    // Student Performance Report
                    fputcsv($file, ['Student Performance Report']);
                    fputcsv($file, ['Student', $data['student']->first_name . ' ' . $data['student']->last_name]);
                    fputcsv($file, ['Average Score', $data['performance_indicators']['average_score'] . '%']);
                    fputcsv($file, ['Total Assignments', $data['performance_indicators']['total_assignments']]);
                    fputcsv($file, ['Performance Level', $data['performance_indicators']['performance_level']]);
                    fputcsv($file, []);
                    
                    // Subject Performance
                    fputcsv($file, ['Subject Performance']);
                    fputcsv($file, ['Subject', 'Average Score', 'Assignments', 'Highest Score', 'Lowest Score']);
                    foreach ($data['subject_performance'] as $subject) {
                        fputcsv($file, [
                            $subject['subject'],
                            $subject['average_score'] . '%',
                            $subject['assignments_count'],
                            $subject['highest_score'] . '%',
                            $subject['lowest_score'] . '%'
                        ]);
                    }
                    break;

                case 'Teacher':
                    $teacher = Auth::user()->teacher;
                    $data = $this->analyticsService->getTeacherAnalytics($teacher->id, $academicYearId, $semesterId);
                    
                    // Teacher Analytics Report
                    fputcsv($file, ['Teacher Analytics Report']);
                    fputcsv($file, ['Teacher', $data['teacher']->full_name]);
                    fputcsv($file, []);
                    
                    // Class Averages
                    fputcsv($file, ['Class Averages by Subject']);
                    fputcsv($file, ['Subject', 'Average Score', 'Total Students', 'Assignments']);
                    foreach ($data['class_averages'] as $average) {
                        fputcsv($file, [
                            $average['subject'],
                            $average['average_score'] . '%',
                            $average['total_students'],
                            $average['assignments_count']
                        ]);
                    }
                    break;

                case 'Admin':
                    $data = $this->analyticsService->getAdminAnalytics($academicYearId, $semesterId);
                    
                    // School Analytics Report
                    fputcsv($file, ['School Analytics Report']);
                    fputcsv($file, ['Total Students', $data['school_overview']['total_students']]);
                    fputcsv($file, ['Total Teachers', $data['school_overview']['total_teachers']]);
                    fputcsv($file, ['Average Score', $data['school_overview']['average_score'] . '%']);
                    fputcsv($file, ['Pass Rate', $data['school_overview']['pass_rate'] . '%']);
                    fputcsv($file, []);
                    
                    // Subject Performance
                    fputcsv($file, ['Subject Performance']);
                    fputcsv($file, ['Subject', 'Average Score', 'Students', 'Assignments']);
                    foreach ($data['subject_performance'] as $subject) {
                        fputcsv($file, [
                            $subject['subject'],
                            $subject['average_score'] . '%',
                            $subject['students_count'],
                            $subject['assignments_count']
                        ]);
                    }
                    break;
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Get student-specific analytics (for teachers/admins)
     */
    public function getStudentAnalytics($studentId, Request $request)
    {
        $academicYearId = $request->get('academic_year_id');
        $semesterId = $request->get('semester_id');

        $student = Student::findOrFail($studentId);
        $academicYears = AcademicYear::all();
        $semesters = Semester::all();

        $analytics = $this->analyticsService->getStudentAnalytics(
            $studentId, 
            $academicYearId, 
            $semesterId
        );

        return view('analytics.student-detail', compact(
            'student',
            'analytics',
            'academicYears',
            'semesters',
            'academicYearId',
            'semesterId'
        ));
    }

    /**
     * Get teacher-specific analytics (for admins)
     */
    public function getTeacherAnalytics($teacherId, Request $request)
    {
        $academicYearId = $request->get('academic_year_id');
        $semesterId = $request->get('semester_id');

        $teacher = Teacher::findOrFail($teacherId);
        $academicYears = AcademicYear::all();
        $semesters = Semester::all();

        $analytics = $this->analyticsService->getTeacherAnalytics(
            $teacherId, 
            $academicYearId, 
            $semesterId
        );

        return view('analytics.teacher-detail', compact(
            'teacher',
            'analytics',
            'academicYears',
            'semesters',
            'academicYearId',
            'semesterId'
        ));
    }
} 