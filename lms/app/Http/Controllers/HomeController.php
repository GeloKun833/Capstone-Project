<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\Section;
use App\Models\Enrollment;
use App\Models\CalendarEvent;
use App\Models\Attendance;
use App\Models\Subject;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
    * Show the application dashboard.
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    /** home dashboard */
    public function index()
    {
        return $this->dashboard();
    }

    /** profile user */
    public function userProfile()
    {
        $user = auth()->user();
        $data = ['user' => $user];

        // If student, load comprehensive SIS data
        if ($user->role_name === User::ROLE_STUDENT) {
            $student = $user->student;
            
            if (!$student) {
                $student = Student::where('user_id', $user->user_id)->first();
            }

            if ($student) {
                // Get current enrollments
                $currentEnrollments = $student->enrollments()
                    ->with(['subject', 'academicYear', 'semester'])
                    ->where('status', 'active')
                    ->get();

                // Get all grades
                $grades = \App\Models\Grade::where('student_id', $student->id)
                    ->with(['subject', 'academicYear', 'semester'])
                    ->orderBy('created_at', 'desc')
                    ->get();

                // Get GPA records
                $gpaRecords = \App\Models\StudentGpa::where('student_id', $student->id)
                    ->with(['academicYear', 'semester'])
                    ->orderBy('created_at', 'desc')
                    ->get();

                $currentGPA = $gpaRecords->first();

                // Get attendance summary
                $totalAttendance = \App\Models\Attendance::where('student_id', $student->id)->count();
                $presentCount = \App\Models\Attendance::where('student_id', $student->id)
                    ->where('status', 'present')->count();
                $absentCount = \App\Models\Attendance::where('student_id', $student->id)
                    ->where('status', 'absent')->count();
                $attendancePercentage = $totalAttendance > 0 ? round(($presentCount / $totalAttendance) * 100, 1) : 0;

                // Get section assignment
                $sectionAssignment = \DB::table('student_section_assignments')
                    ->join('sections', 'student_section_assignments.section_id', '=', 'sections.id')
                    ->join('academic_years', 'student_section_assignments.academic_year_id', '=', 'academic_years.id')
                    ->join('semesters', 'student_section_assignments.semester_id', '=', 'semesters.id')
                    ->where('student_section_assignments.student_id', $student->id)
                    ->select('sections.*', 'academic_years.name as academic_year_name', 'semesters.name as semester_name')
                    ->first();

                // Get promotion history
                $promotionHistory = \App\Models\StudentPromotion::where('student_id', $student->id)
                    ->with(['fromAcademicYear', 'toAcademicYear', 'promoter'])
                    ->orderBy('promotion_date', 'desc')
                    ->get();

                // Get enrollment documents
                $enrollmentDocuments = [];
                if ($student->enrollment_application_id) {
                    $enrollmentDocuments = \App\Models\EnrollmentDocument::where('enrollment_application_id', $student->enrollment_application_id)
                        ->get();
                }

                $data['student'] = $student;
                $data['currentEnrollments'] = $currentEnrollments;
                $data['grades'] = $grades;
                $data['gpaRecords'] = $gpaRecords;
                $data['currentGPA'] = $currentGPA;
                $data['totalAttendance'] = $totalAttendance;
                $data['presentCount'] = $presentCount;
                $data['absentCount'] = $absentCount;
                $data['attendancePercentage'] = $attendancePercentage;
                $data['sectionAssignment'] = $sectionAssignment;
                $data['promotionHistory'] = $promotionHistory;
                $data['enrollmentDocuments'] = $enrollmentDocuments;
            }
        }

        return view('dashboard.profile', $data);
    }

    public function teacherClasses()
    {
        $user = auth()->user();
        $teacher = $user->teacher;
        
        if (!$teacher) {
            return redirect()->back()->with('error', 'Teacher profile not found.');
        }
        
        // Get teacher's subjects with enrollments
        $teacherSubjects = $teacher->subjects()
            ->with(['enrollments' => function($query) {
                $query->where('status', 'active')->with(['student', 'academicYear', 'semester']);
            }])
            ->get()
            ->map(function($subject) {
                $subject->enrollments_count = $subject->enrollments ? $subject->enrollments->count() : 0;
                return $subject;
            });
        
        // Get teacher's sections (where teacher is adviser)
        $teacherSections = Section::where('adviser_id', $teacher->id)
            ->with(['students'])
            ->get()
            ->map(function($section) {
                $section->students_count = $section->students->count();
                return $section;
            });
        
        // Get attendance statistics for teacher's subjects
        $attendanceStats = collect();
        foreach ($teacherSubjects as $subject) {
            $stats = \App\Models\Attendance::where('subject_id', $subject->id)
                ->selectRaw('
                    subject_id,
                    COUNT(*) as total_records,
                    SUM(CASE WHEN status = "present" THEN 1 ELSE 0 END) as present_count,
                    SUM(CASE WHEN status = "absent" THEN 1 ELSE 0 END) as absent_count
                ')
                ->groupBy('subject_id')
                ->first();
            
            if ($stats) {
                $attendanceStats->put($subject->id, $stats);
            }
        }
        
        // Get recent enrollments for teacher's subjects
        $recentEnrollments = collect();
        if ($teacherSubjects->count() > 0) {
            $recentEnrollments = \App\Models\Enrollment::whereIn('subject_id', $teacherSubjects->pluck('id'))
                ->where('status', 'active')
                ->whereHas('student')  // Only get enrollments with valid students
                ->whereHas('subject')  // Only get enrollments with valid subjects
                ->with(['student', 'subject'])
                ->orderBy('created_at', 'desc')
                ->take(10)
                ->get();
        }
        
        return view('teacher.classes', compact('teacher', 'teacherSubjects', 'teacherSections', 'attendanceStats', 'recentEnrollments'));
    }

    public function teacherSubjects()
    {
        $user = auth()->user();
        $teacher = $user->teacher;
        
        if (!$teacher) {
            return redirect()->back()->with('error', 'Teacher profile not found.');
        }
        
        // Get teacher's subjects with detailed information
        $teacherSubjects = $teacher->subjects()
            ->with(['enrollments' => function($query) {
                $query->where('status', 'active')->with(['student', 'academicYear', 'semester']);
            }])
            ->get();
        
        // Get statistics
        $totalSubjects = $teacherSubjects->count();
        $totalStudents = $teacherSubjects->sum(function($subject) {
            return $subject->enrollments->count();
        });
        
        // Since subjects don't have direct sections relationship, we'll calculate this differently
        // Get sections where this teacher is the adviser
        $teacherSections = Section::where('adviser_id', $teacher->id)->count();
        
        return view('teacher.subjects', compact('teacher', 'teacherSubjects', 'totalSubjects', 'totalStudents', 'teacherSections'));
    }

    /**
     * Unified dashboard route that returns the correct dashboard for each role.
     */
    public function dashboard()
    {
        $user = auth()->user();
        
        if (!$user) {
            return redirect()->route('login');
        }

        // Initialize variables for all roles
        $data = [
            'user' => $user,
            'admin' => null,
            'teacher' => null,
            'student' => null,
            'parent' => null,
            'registrar' => null
        ];

        // Load data based on user role
        if ($user->role_name === User::ROLE_ADMIN) {
            $data['admin'] = $this->loadAdminData();
        } elseif ($user->role_name === User::ROLE_TEACHER) {
            $data['teacher'] = $this->loadTeacherData();
        } elseif ($user->role_name === User::ROLE_STUDENT) {
            $data['student'] = $this->loadStudentData();
        } elseif ($user->role_name === User::ROLE_PARENT) {
            $data['parent'] = $this->loadParentData();
        } elseif ($user->role_name === User::ROLE_REGISTRAR) {
            $data['registrar'] = $this->loadRegistrarData();
        } else {
            abort(403);
        }

        return view('dashboard', $data);
    }

    /**
     * Load admin dashboard data (cached briefly to keep the dashboard snappy)
     */
    private function loadAdminData()
    {
        return \Illuminate\Support\Facades\Cache::remember('admin.dashboard.data', 120, function () {
            $totalStudents = \App\Models\Student::count();
            $totalTeachers = \App\Models\Teacher::count();
            $totalSubjects = \App\Models\Subject::count();
            $totalSections = \App\Models\Section::count();
            $totalEnrollments = \App\Models\Enrollment::where('status', 'active')->count();
            $totalAttendance = \App\Models\Attendance::count();
            $totalGrades = \App\Models\Grade::count();
            $totalAnnouncements = \App\Models\Announcement::count();

            $recentEnrollments = \App\Models\Enrollment::with(['student', 'subject'])
                ->where('status', 'active')
                ->orderByDesc('created_at')
                ->take(5)
                ->get();

            $recentAnnouncements = \App\Models\Announcement::with(['creator'])
                ->orderByDesc('created_at')
                ->take(5)
                ->get();

            $topStudents = \App\Models\StudentGpa::with(['student', 'academicYear', 'semester'])
                ->orderByDesc('gpa')
                ->take(5)
                ->get();

            $attendanceStats = \App\Models\Attendance::selectRaw('
                COUNT(*) as total_records,
                SUM(CASE WHEN status = "present" THEN 1 ELSE 0 END) as present_count,
                SUM(CASE WHEN status = "absent" THEN 1 ELSE 0 END) as absent_count
            ')->first();

            $attendancePercentage = ($attendanceStats && $attendanceStats->total_records > 0)
                ? round(($attendanceStats->present_count / $attendanceStats->total_records) * 100, 1)
                : 0;

            $genderCounts = \App\Models\Student::selectRaw('gender, COUNT(*) as total')
                ->groupBy('gender')
                ->pluck('total', 'gender');

            $maleStudents = (int) ($genderCounts['Male'] ?? $genderCounts['male'] ?? 0);
            $femaleStudents = (int) ($genderCounts['Female'] ?? $genderCounts['female'] ?? 0);

            $recentEvents = \App\Models\CalendarEvent::with(['subject', 'teacher'])
                ->where('start_time', '>=', now())
                ->orderBy('start_time')
                ->take(5)
                ->get();

            $performanceData = $this->getStudentPerformanceChartData();
            $studentsChartData = $this->getStudentsByGradeLevelChartData();

            return compact(
                'totalStudents',
                'totalTeachers',
                'totalSubjects',
                'totalSections',
                'totalEnrollments',
                'totalAttendance',
                'totalGrades',
                'totalAnnouncements',
                'recentEnrollments',
                'recentAnnouncements',
                'topStudents',
                'attendanceStats',
                'attendancePercentage',
                'maleStudents',
                'femaleStudents',
                'recentEvents',
                'performanceData',
                'studentsChartData'
            );
        });
    }
    
    /**
     * Get student performance chart data (Teacher vs Student average grades)
     * Single grouped query instead of 6 monthly queries.
     */
    private function getStudentPerformanceChartData()
    {
        try {
            $start = now()->subMonths(5)->startOfMonth();
            $rows = \App\Models\Grade::query()
                ->where('created_at', '>=', $start)
                ->selectRaw('YEAR(created_at) as y, MONTH(created_at) as m, AVG(percentage) as avg_pct')
                ->groupBy('y', 'm')
                ->get()
                ->keyBy(fn ($r) => sprintf('%04d-%02d', $r->y, $r->m));

            $months = [];
            $teacherData = [];
            $studentData = [];

            for ($i = 5; $i >= 0; $i--) {
                $month = now()->subMonths($i);
                $key = $month->format('Y-m');
                $months[] = $month->format('M Y');
                $avg = isset($rows[$key]) ? round((float) $rows[$key]->avg_pct, 1) : 0;
                $studentData[] = $avg;
                $teacherData[] = $avg > 0 ? min(round($avg * 1.1, 1), 100) : 85;
            }

            return compact('months', 'teacherData', 'studentData');
        } catch (\Exception $e) {
            \Log::error('Chart data error: ' . $e->getMessage());
            return [
                'months' => [],
                'teacherData' => [],
                'studentData' => [],
            ];
        }
    }
    
    /**
     * Get students by grade level chart data (Boys vs Girls)
     * One grouped query instead of 24 count queries.
     */
    private function getStudentsByGradeLevelChartData()
    {
        try {
            $gradeLevels = [
                'Nursery', 'Kindergarten', 'Grade 1', 'Grade 2', 'Grade 3', 'Grade 4',
                'Grade 5', 'Grade 6', 'Grade 7', 'Grade 8', 'Grade 9', 'Grade 10',
            ];

            $rows = \App\Models\Student::query()
                ->selectRaw('year_level, gender, COUNT(*) as total')
                ->whereIn('year_level', $gradeLevels)
                ->groupBy('year_level', 'gender')
                ->get();

            $map = [];
            foreach ($rows as $row) {
                $gender = strtolower((string) $row->gender);
                $map[$row->year_level][$gender] = (int) $row->total;
            }

            $labels = [];
            $boysData = [];
            $girlsData = [];

            foreach ($gradeLevels as $gradeLevel) {
                $boys = $map[$gradeLevel]['male'] ?? 0;
                $girls = $map[$gradeLevel]['female'] ?? 0;
                if ($boys > 0 || $girls > 0) {
                    $labels[] = $gradeLevel;
                    $boysData[] = $boys;
                    $girlsData[] = $girls;
                }
            }

            return compact('labels', 'boysData', 'girlsData');
        } catch (\Exception $e) {
            \Log::error('Student chart data error: ' . $e->getMessage());
            return [
                'labels' => [],
                'boysData' => [],
                'girlsData' => [],
            ];
        }
    }

    /**
     * Load teacher dashboard data
     */
    private function loadTeacherData()
    {
        $user = auth()->user();
        $teacher = $user->teacher;
        
        if (!$teacher) {
            return [
                'teacher' => null,
                'totalClasses' => 0,
                'totalStudents' => 0,
                'totalLessons' => 0,
                'totalHours' => 0,
                'upcomingLessons' => collect(),
                'semesterProgress' => 0,
                'teachingHistory' => collect(),
                'upcomingEvents' => collect(),
                'attendanceStats' => (object)['total_records' => 0, 'present_count' => 0, 'absent_count' => 0],
                'attendancePercentage' => 0
            ];
        }
        
        // Get teacher's subjects with sections, grouped by grade level
        $teacherSubjects = $teacher->subjects()
            ->with(['sections'])
            ->get()
            ->groupBy('class') // Group by grade level (class field)
            ->sortKeys(); // Sort by grade level
        
        // Get total classes (sections where teacher is adviser)
        $totalClasses = Section::where('adviser_id', $teacher->id)->count();
        
        // Get total students across all teacher's subjects
        $totalStudents = Enrollment::whereIn('subject_id', $teacherSubjects->pluck('id'))
            ->where('status', 'active')
            ->distinct('student_id')
            ->count('student_id');
        
        // Get total lessons (subjects taught by teacher)
        $totalLessons = $teacherSubjects->count();
        
        // Get total hours (calculate from calendar events or use a default)
        $totalHours = CalendarEvent::where('teacher_id', $teacher->id)
            ->where('start_time', '>=', now()->startOfMonth())
            ->where('start_time', '<=', now()->endOfMonth())
            ->count();
        
        // Get upcoming lessons (calendar events)
        $upcomingLessons = CalendarEvent::with(['subject'])
            ->where('teacher_id', $teacher->id)
            ->where('start_time', '>=', now())
            ->orderBy('start_time', 'asc')
            ->take(5)
            ->get();
        
        // Get semester progress (calculate based on completed vs total events)
        $totalEventsThisMonth = CalendarEvent::where('teacher_id', $teacher->id)
            ->where('start_time', '>=', now()->startOfMonth())
            ->where('start_time', '<=', now()->endOfMonth())
            ->count();
        
        $completedEventsThisMonth = CalendarEvent::where('teacher_id', $teacher->id)
            ->where('start_time', '>=', now()->startOfMonth())
            ->where('start_time', '<=', now()->endOfMonth())
            ->where('start_time', '<=', now())
            ->count();
        
        $semesterProgress = $totalEventsThisMonth > 0 
            ? round(($completedEventsThisMonth / $totalEventsThisMonth) * 100, 1)
            : 0;
        
        // Get teaching history (recent calendar events)
        $teachingHistory = CalendarEvent::with(['subject'])
            ->where('teacher_id', $teacher->id)
            ->where('start_time', '<=', now())
            ->orderBy('start_time', 'desc')
            ->take(10)
            ->get();
        
        // Get upcoming events for calendar
        $upcomingEvents = CalendarEvent::with(['subject'])
            ->where('teacher_id', $teacher->id)
            ->where('start_time', '>=', now())
            ->orderBy('start_time', 'asc')
            ->take(10)
            ->get();
        
        // Get attendance statistics for teacher's students
        $attendanceStats = Attendance::whereIn('subject_id', $teacherSubjects->pluck('id'))
            ->selectRaw('
                COUNT(*) as total_records,
                SUM(CASE WHEN status = "present" THEN 1 ELSE 0 END) as present_count,
                SUM(CASE WHEN status = "absent" THEN 1 ELSE 0 END) as absent_count
            ')->first();
        
        $attendancePercentage = $attendanceStats->total_records > 0 
            ? round(($attendanceStats->present_count / $attendanceStats->total_records) * 100, 1)
            : 0;
        
        // Get teacher's assigned sections (both as adviser and as subject teacher)
        $teacherSections = $teacher->sections()->get();
        
        return compact(
            'teacher',
            'teacherSubjects', // Now grouped by grade level
            'teacherSections', // Teacher's assigned sections
            'totalClasses',
            'totalStudents', 
            'totalLessons',
            'totalHours',
            'upcomingLessons',
            'semesterProgress',
            'teachingHistory',
            'upcomingEvents',
            'attendanceStats',
            'attendancePercentage'
        );
    }

    /**
     * Load student dashboard data
     */
    private function loadStudentData()
    {
        $user = auth()->user();
        $student = $user->student;
        
        if (!$student) {
            // Try to find student by user_id as fallback
            $student = Student::where('user_id', $user->user_id)->first();
            
            if (!$student) {
                // Return empty data structure instead of null
                return [
                    'student' => null,
                    'enrollments' => collect(),
                    'hasStudent' => false
                ];
            }
        }
        
        // Get student's enrollments with related data
        $enrollments = $student->enrollments()
            ->with(['subject', 'academicYear', 'semester'])
            ->where('status', 'active')
            ->get();
        
        return [
            'student' => $student,
            'enrollments' => $enrollments,
            'hasStudent' => true
        ];
    }

    /**
     * Load parent dashboard data
     */
    private function loadParentData()
    {
        try {
            $parent = auth()->user();
            
            if ($parent->role_name !== 'Parent') {
                return null;
            }

            // Get all children linked to this parent (with sections and adviser)
            $children = Student::where('parent_email', $parent->email)
                ->with(['sections.adviser'])
                ->get();
            
            if ($children->isEmpty()) {
                return [
                    'children' => collect(),
                    'selectedChild' => null,
                    'grades' => collect(),
                    'attendance' => collect(),
                    'lessons' => collect(),
                    'activities' => collect(),
                    'submissions' => collect(),
                    'performanceInsights' => [],
                    'currentAcademicYear' => null,
                    'currentSemester' => null,
                    'noChildren' => true
                ];
            }

            // Get selected child (default to first child)
            $selectedChildId = request()->input('child_id', $children->first()->id);
            $selectedChild = $children->find($selectedChildId);
            
            if (!$selectedChild) {
                $selectedChild = $children->first();
            }

            // Get current academic year and semester (fallback to latest if no active ones)
            $currentAcademicYear = \App\Models\AcademicYear::latest()->first();
            $currentSemester = \App\Models\Semester::latest()->first();

            // Get grades for selected child
            $grades = $this->getChildGrades($selectedChild, $currentAcademicYear, $currentSemester);
            
            // Get attendance for selected child
            $attendance = $this->getChildAttendance($selectedChild, request());
            
            // Get lessons and activities for selected child
            $lessons = $this->getChildLessons($selectedChild, $currentAcademicYear, $currentSemester);
            $activities = $this->getChildActivities($selectedChild, $currentAcademicYear, $currentSemester);
            
            // Get submissions for selected child
            $submissions = $this->getChildSubmissions($selectedChild, $currentAcademicYear, $currentSemester);
            
            // Get performance insights
            $performanceInsights = $this->getPerformanceInsights($selectedChild, $currentAcademicYear, $currentSemester);
            
            // Get attendance statistics
            $attendanceStats = \App\Models\Attendance::where('student_id', $selectedChild->id)
                ->selectRaw('
                    COUNT(*) as total_records,
                    SUM(CASE WHEN status = "present" THEN 1 ELSE 0 END) as present_count,
                    SUM(CASE WHEN status = "absent" THEN 1 ELSE 0 END) as absent_count
                ')->first();
            
            // Get enrollments for selected child
            $enrollments = \App\Models\Enrollment::where('student_id', $selectedChild->id)
                ->where('status', 'active')
                ->with(['subject', 'academicYear', 'semester'])
                ->get();
            
            // Get upcoming events for selected child
            $upcomingEvents = \App\Models\CalendarEvent::whereHas('subject', function($q) use ($selectedChild) {
                $q->whereHas('enrollments', function($enrollmentQ) use ($selectedChild) {
                    $enrollmentQ->where('student_id', $selectedChild->id);
                });
            })->where('start_time', '>=', now())
            ->orderBy('start_time', 'asc')
            ->take(5)
            ->get();

            return compact(
                'children',
                'selectedChild',
                'grades',
                'attendance',
                'lessons',
                'activities',
                'submissions',
                'performanceInsights',
                'currentAcademicYear',
                'currentSemester',
                'attendanceStats',
                'enrollments',
                'upcomingEvents'
            );
        } catch (\Exception $e) {
            // Log the error for debugging
            Log::error('Parent Dashboard Error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Return a simple error state
            return [
                'children' => collect(),
                'selectedChild' => null,
                'grades' => collect(),
                'attendance' => collect(),
                'lessons' => collect(),
                'activities' => collect(),
                'submissions' => collect(),
                'performanceInsights' => [],
                'currentAcademicYear' => null,
                'currentSemester' => null,
                'error' => 'An error occurred while loading the dashboard.'
            ];
        }
    }




    /**
     * Get child grades for parent dashboard
     */
    private function getChildGrades($child, $academicYear, $semester)
    {
        if (!$academicYear || !$semester) {
            return collect();
        }

        return \App\Models\Grade::with(['subject'])
            ->where('student_id', $child->id)
            ->where('academic_year_id', $academicYear->id)
            ->where('semester_id', $semester->id)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get child attendance for parent dashboard
     */
    private function getChildAttendance($child, $request)
    {
        $date = $request->input('date', now()->format('Y-m-d'));
        
        return \App\Models\Attendance::with(['subject'])
            ->where('student_id', $child->id)
            ->whereDate('date', $date)
            ->get();
    }

    /**
     * Get child lessons for parent dashboard
     */
    private function getChildLessons($child, $academicYear, $semester)
    {
        if (!$academicYear || !$semester) {
            return collect();
        }

        // Get subjects the child is enrolled in
        $enrolledSubjectIds = \App\Models\Enrollment::where('student_id', $child->id)
            ->where('status', 'active')
            ->pluck('subject_id');

        return \App\Models\Lesson::with(['subject'])
            ->whereIn('subject_id', $enrolledSubjectIds)
            ->where('academic_year_id', $academicYear->id)
            ->where('semester_id', $semester->id)
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();
    }

    /**
     * Get child activities for parent dashboard
     */
    private function getChildActivities($child, $academicYear, $semester)
    {
        if (!$academicYear || !$semester) {
            return collect();
        }

        // Get subjects the child is enrolled in
        $enrolledSubjectIds = \App\Models\Enrollment::where('student_id', $child->id)
            ->where('status', 'active')
            ->pluck('subject_id');

        // Get activities that belong to lessons of enrolled subjects
        return \App\Models\Activity::with(['lesson.subject'])
            ->whereHas('lesson', function($query) use ($enrolledSubjectIds, $academicYear, $semester) {
                $query->whereIn('subject_id', $enrolledSubjectIds)
                      ->where('academic_year_id', $academicYear->id)
                      ->where('semester_id', $semester->id);
            })
            ->orderBy('due_date', 'asc')
            ->take(5)
            ->get();
    }

    /**
     * Get child submissions for parent dashboard
     */
    private function getChildSubmissions($child, $academicYear, $semester)
    {
        if (!$academicYear || !$semester) {
            return collect();
        }

        // Get subjects the child is enrolled in
        $enrolledSubjectIds = \App\Models\Enrollment::where('student_id', $child->id)
            ->where('status', 'active')
            ->pluck('subject_id');

        // Get submissions that belong to activities of lessons of enrolled subjects
        return \App\Models\ActivitySubmission::with(['activity.lesson.subject'])
            ->where('student_id', $child->id)
            ->whereHas('activity.lesson', function($query) use ($enrolledSubjectIds, $academicYear, $semester) {
                $query->whereIn('subject_id', $enrolledSubjectIds)
                      ->where('academic_year_id', $academicYear->id)
                      ->where('semester_id', $semester->id);
            })
            ->orderBy('submitted_at', 'desc')
            ->take(5)
            ->get();
    }

    /**
     * Get performance insights for parent dashboard
     */
    private function getPerformanceInsights($child, $academicYear, $semester)
    {
        if (!$academicYear || !$semester) {
            return [];
        }

        // Get GPA for current academic year and semester
        $gpa = \App\Models\StudentGpa::where('student_id', $child->id)
            ->where('academic_year_id', $academicYear->id)
            ->where('semester_id', $semester->id)
            ->first();

        // Get subjects the child is enrolled in for current academic year and semester
        $enrolledSubjectIds = \App\Models\Enrollment::where('student_id', $child->id)
            ->where('academic_year_id', $academicYear->id)
            ->where('semester_id', $semester->id)
            ->where('status', 'active')
            ->pluck('subject_id');

        // Get attendance percentage for enrolled subjects
        $attendanceStats = \App\Models\Attendance::where('student_id', $child->id)
            ->whereIn('subject_id', $enrolledSubjectIds)
            ->selectRaw('
                COUNT(*) as total_records,
                SUM(CASE WHEN status = "present" THEN 1 ELSE 0 END) as present_count
            ')->first();

        $attendancePercentage = $attendanceStats && $attendanceStats->total_records > 0 
            ? round(($attendanceStats->present_count / $attendanceStats->total_records) * 100, 1)
            : 0;

        return [
            'gpa' => $gpa ? $gpa->gpa : null,
            'attendancePercentage' => $attendancePercentage,
            'academicYear' => $academicYear->name,
            'semester' => $semester->name
        ];
    }

    /**
     * Show the form for editing the authenticated user's profile.
     */
    public function editProfile()
    {
        $user = auth()->user();
        $user = \App\Models\User::find($user->id);
        $student = $user->student;
        $teacher = $user->teacher;
        return view('dashboard.edit_profile', compact('user', 'student', 'teacher'));
    }

    /**
     * Update the authenticated user's profile.
     */
    public function updateProfile(Request $request)
    {
        $user = auth()->user();
        $user = \App\Models\User::find($user->id);
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
        ]);
        $user->name = $request->name;
        $user->email = $request->email;
        $user->save();
        activity()
            ->causedBy($user)
            ->performedOn($user)
            ->withProperties(['attributes' => $request->only(['name', 'email'])])
            ->log('updated profile');
        // Role-specific updates (future extension)
        if ($user->role_name === \App\Models\User::ROLE_STUDENT && $user->student) {
            // Example: $user->student->admission_id = $request->student_id; (if editable)
            $user->student->save();
        }
        if ($user->role_name === \App\Models\User::ROLE_TEACHER && $user->teacher) {
            // Example: $user->teacher->teacher_id = $request->teacher_id; (if editable)
            $user->teacher->save();
        }
        return redirect()->route('user/profile/page')->with('success', 'Profile updated successfully.');
    }

    /**
     * Update the authenticated user's password.
     */
    public function updatePassword(Request $request)
    {
        $user = auth()->user();
        $user = \App\Models\User::find($user->id);
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|string|min:8|confirmed',
        ]);
        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect.']);
        }
        $user->password = bcrypt($request->new_password);
        $user->save();
        activity()
            ->causedBy($user)
            ->performedOn($user)
            ->log('changed password');
        return redirect()->route('user/profile/page')->with('success', 'Password updated successfully.');
    }

    /**
     * Display the current user's activity logs.
     */
    public function activityLog()
    {
        $user = auth()->user();
        $activities = \Spatie\Activitylog\Models\Activity::where('causer_id', $user->id)
            ->where('causer_type', get_class($user))
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        return view('dashboard.activity_log', compact('activities'));
    }

    public function adminActivityLog()
    {
        if (auth()->user()->role_name !== 'Admin') {
            abort(403);
        }
        $activities = \Spatie\Activitylog\Models\Activity::with('causer')
            ->orderBy('created_at', 'desc')
            ->paginate(30);
        return view('dashboard.admin_activity_log', compact('activities'));
    }

    /**
     * Load registrar dashboard data
     */
    private function loadRegistrarData()
    {
        // Get enrollment application statistics
        $totalApplications = \App\Models\EnrollmentApplication::count();
        $pendingApplications = \App\Models\EnrollmentApplication::where('status', 'pending')->count();
        $approvedApplications = \App\Models\EnrollmentApplication::where('status', 'approved')->count();
        $rejectedApplications = \App\Models\EnrollmentApplication::where('status', 'rejected')->count();
        $underReviewApplications = \App\Models\EnrollmentApplication::where('status', 'under_review')->count();
        $needsDocumentsApplications = \App\Models\EnrollmentApplication::where('status', 'needs_documents')->count();
        
        // Get recent applications
        $recentApplications = \App\Models\EnrollmentApplication::orderBy('created_at', 'desc')->take(5)->get();
        
        // Get applications by grade level
        $applicationsByGrade = \App\Models\EnrollmentApplication::selectRaw('grade_level_applying_for, COUNT(*) as count')
            ->groupBy('grade_level_applying_for')
            ->orderBy('count', 'desc')
            ->get();
            
        // Get applications by status
        $applicationsByStatus = \App\Models\EnrollmentApplication::selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->get();
            
        // Get this month's applications
        $thisMonthApplications = \App\Models\EnrollmentApplication::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();
            
        // Get last month's applications for comparison
        $lastMonthApplications = \App\Models\EnrollmentApplication::whereMonth('created_at', now()->subMonth()->month)
            ->whereYear('created_at', now()->subMonth()->year)
            ->count();
            
        // Calculate growth percentage
        $growthPercentage = $lastMonthApplications > 0 
            ? round((($thisMonthApplications - $lastMonthApplications) / $lastMonthApplications) * 100, 1)
            : 0;
            
        return compact(
            'totalApplications',
            'pendingApplications',
            'approvedApplications',
            'rejectedApplications',
            'underReviewApplications',
            'needsDocumentsApplications',
            'recentApplications',
            'applicationsByGrade',
            'applicationsByStatus',
            'thisMonthApplications',
            'lastMonthApplications',
            'growthPercentage'
        );
    }
}
