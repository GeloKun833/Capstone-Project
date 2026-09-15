<?php

namespace App\Http\Controllers;

use App\Services\AcademicAnalyticsService;
use App\Services\StudentPerformanceService;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
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
        $this->middleware(['auth']);
        $this->middleware('role:Admin')->only(['adminDashboard']);
    }

    /**
     * Admin Analytics Dashboard
     */
    public function adminDashboard(Request $request)
    {
        $academicYearId = $request->get('academic_year_id');
        $semesterId = $request->get('semester_id');

        $academicYears = AcademicYear::orderByDesc('start_date')->get();
        $semesters = Semester::with('academicYear')->orderByDesc('id')->get();

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
                    fputcsv($file, ['Total Parents', $data['school_overview']['total_parents'] ?? 0]);
                    fputcsv($file, ['Total Subjects', $data['school_overview']['total_subjects']]);
                    fputcsv($file, ['Total Sections', $data['school_overview']['total_sections'] ?? 0]);
                    fputcsv($file, ['Average Score', ($data['school_overview']['average_score'] ?? 0) . '%']);
                    fputcsv($file, ['Pass Rate', ($data['school_overview']['pass_rate'] ?? 0) . '%']);
                    fputcsv($file, ['Attendance Rate', ($data['school_overview']['attendance_rate'] ?? 0) . '%']);
                    fputcsv($file, []);
                    fputcsv($file, ['Enrollment Pipeline']);
                    fputcsv($file, ['Pending', $data['enrollment_overview']['pending'] ?? 0]);
                    fputcsv($file, ['Under Review', $data['enrollment_overview']['under_review'] ?? 0]);
                    fputcsv($file, ['Needs Documents', $data['enrollment_overview']['needs_documents'] ?? 0]);
                    fputcsv($file, ['Approved', $data['enrollment_overview']['approved'] ?? 0]);
                    fputcsv($file, ['Rejected', $data['enrollment_overview']['rejected'] ?? 0]);
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
        $student = Student::findOrFail($studentId);
        $this->authorizeStudentAnalytics($student);

        $academicYearId = $request->get('academic_year_id');
        $semesterId = $request->get('semester_id');

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
        $teacher = Teacher::findOrFail($teacherId);
        $this->authorizeTeacherAnalytics($teacher);

        $academicYearId = $request->get('academic_year_id');
        $semesterId = $request->get('semester_id');
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

    protected function authorizeStudentAnalytics(Student $student): void
    {
        $user = Auth::user();
        if (! $user) {
            abort(403);
        }

        if (in_array($user->role_name, [User::ROLE_ADMIN, User::ROLE_REGISTRAR], true)) {
            return;
        }

        if ($user->role_name === User::ROLE_TEACHER && $user->teacher) {
            $allowed = app(StudentPerformanceService::class)->studentIdsForTeacher($user->teacher);
            if (in_array((int) $student->id, $allowed, true)) {
                return;
            }
        }

        if ($user->role_name === User::ROLE_STUDENT && $user->student && (int) $user->student->id === (int) $student->id) {
            return;
        }

        if ($user->role_name === User::ROLE_PARENT) {
            $isChild = Student::query()->forParent($user)->where('id', $student->id)->exists();
            if ($isChild) {
                return;
            }
        }

        abort(403, 'You are not allowed to view this student analytics record.');
    }

    protected function authorizeTeacherAnalytics(Teacher $teacher): void
    {
        $user = Auth::user();
        if (! $user) {
            abort(403);
        }

        if (in_array($user->role_name, [User::ROLE_ADMIN, User::ROLE_REGISTRAR], true)) {
            return;
        }

        if ($user->role_name === User::ROLE_TEACHER && $user->teacher && (int) $user->teacher->id === (int) $teacher->id) {
            return;
        }

        abort(403, 'You are not allowed to view this teacher analytics record.');
    }
} 