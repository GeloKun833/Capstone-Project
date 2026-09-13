<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use App\Support\SafeSchema;
use App\Models\User;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\Section;
use App\Models\Enrollment;
use App\Models\CalendarEvent;
use App\Models\Attendance;
use App\Models\Subject;
use App\Models\ClassSchedule;
use App\Models\Lesson;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\ClassPost;
use App\Services\GradeSubjectCatalogService;
use App\Services\TeacherClassAssignmentService;
use Carbon\Carbon;

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
                $attendanceRow = \App\Models\Attendance::where('student_id', $student->id)
                    ->selectRaw('
                        COUNT(*) as total_records,
                        SUM(CASE WHEN status = "present" THEN 1 ELSE 0 END) as present_count,
                        SUM(CASE WHEN status = "absent" THEN 1 ELSE 0 END) as absent_count
                    ')
                    ->first();
                $totalAttendance = (int) ($attendanceRow->total_records ?? 0);
                $presentCount = (int) ($attendanceRow->present_count ?? 0);
                $absentCount = (int) ($attendanceRow->absent_count ?? 0);
                $attendancePercentage = $totalAttendance > 0 ? round(($presentCount / $totalAttendance) * 100, 1) : 0;

                // Get section assignment
                $sectionAssignment = DB::table('student_section_assignments')
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

        $teacher->load(['subjects', 'sections', 'gradeLevels']);

        $assignedSubjects = $teacher->subjects->sortBy(['class', 'subject_name'])->values();
        $assignedSections = $teacher->sections->sortBy(['grade_level', 'name'])->values();

        // Fallback sections from grade-level assignment (same logic as admin schedules)
        if ($assignedSections->isEmpty() && $teacher->gradeLevels->isNotEmpty()) {
            $expanded = collect();
            foreach ($teacher->gradeLevels->pluck('grade_level')->filter()->unique() as $grade) {
                foreach (GradeSubjectCatalogService::gradeAliases($grade) as $alias) {
                    $expanded->push($alias);
                }
            }
            $assignedSections = Section::query()
                ->whereIn('grade_level', $expanded->unique()->all())
                ->orderBy('grade_level')
                ->orderBy('name')
                ->get();
        }

        $sectionIds = $assignedSections->pluck('id');
        $studentCounts = collect();
        if ($sectionIds->isNotEmpty()) {
            $studentCounts = DB::table('section_student')
                ->whereIn('section_id', $sectionIds)
                ->selectRaw('section_id, COUNT(*) as students_count')
                ->groupBy('section_id')
                ->pluck('students_count', 'section_id');
        }

        $schedules = ClassSchedule::query()
            ->where('teacher_id', $teacher->id)
            ->where('is_active', true)
            ->with(['subject', 'section', 'room'])
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get();

        $assignments = collect();

        $pushAssignment = function (
            $subject,
            $section,
            string $source,
            $scheduleGroup = null
        ) use (&$assignments, $studentCounts, $teacher) {
            if (!$subject) {
                return;
            }

            $key = $subject->id . ':' . ($section?->id ?? 'none');
            if ($assignments->has($key)) {
                if ($scheduleGroup) {
                    $existing = $assignments->get($key);
                    if ($existing['schedules']->isEmpty()) {
                        $existing['schedules'] = $scheduleGroup->values();
                        $days = $existing['schedules']->pluck('day_of_week')->unique()->filter()->map(fn ($d) => ucfirst($d))->values();
                        $existing['schedule_summary'] = $days->isNotEmpty() ? $days->implode(', ') : 'No schedule yet';
                        $assignments->put($key, $existing);
                    }
                }
                return;
            }

            $scheduleList = $scheduleGroup ? $scheduleGroup->values() : collect();
            $days = $scheduleList->pluck('day_of_week')->unique()->filter()->map(fn ($d) => ucfirst($d))->values();

            $assignments->put($key, [
                'key' => $key,
                'subject' => $subject,
                'section' => $section,
                'source' => $source,
                'students_count' => $section ? (int) ($studentCounts[$section->id] ?? 0) : 0,
                'schedules' => $scheduleList,
                'schedule_summary' => $days->isNotEmpty()
                    ? $days->implode(', ')
                    : 'No schedule yet',
                'is_adviser' => $section && (int) $section->adviser_id === (int) $teacher->id,
            ]);
        };

        // 1) Active class schedules created by admin
        foreach ($schedules->groupBy(fn ($s) => $s->subject_id . ':' . $s->section_id) as $group) {
            $first = $group->first();
            $pushAssignment($first->subject, $first->section, 'schedule', $group);
        }

        // 2) section_subject links where teacher is assigned to both subject and section
        $subjectIds = $assignedSubjects->pluck('id');
        if ($subjectIds->isNotEmpty() && $sectionIds->isNotEmpty()) {
            $links = DB::table('section_subject')
                ->whereIn('subject_id', $subjectIds)
                ->whereIn('section_id', $sectionIds)
                ->get();

            $subjectsById = $assignedSubjects->keyBy('id');
            $sectionsById = $assignedSections->keyBy('id');

            foreach ($links as $link) {
                $pushAssignment(
                    $subjectsById->get($link->subject_id),
                    $sectionsById->get($link->section_id),
                    'section_subject'
                );
            }
        }

        // 3) Grade-matched subject × section from Classes & Subjects assignment
        if ($assignedSubjects->isNotEmpty() && $assignedSections->isNotEmpty()) {
            foreach ($assignedSubjects as $subject) {
                $subjectAliases = GradeSubjectCatalogService::gradeAliases($subject->class);
                foreach ($assignedSections as $section) {
                    $sectionAliases = GradeSubjectCatalogService::gradeAliases($section->grade_level);
                    $gradesMatch = empty($subjectAliases)
                        || empty($sectionAliases)
                        || count(array_intersect($subjectAliases, $sectionAliases)) > 0
                        || strcasecmp((string) $subject->class, (string) $section->grade_level) === 0;

                    if ($gradesMatch) {
                        $pushAssignment($subject, $section, 'assignment');
                    }
                }
            }
        }

        // 4) Subjects assigned without a matching section yet
        if ($assignments->isEmpty() && $assignedSubjects->isNotEmpty()) {
            foreach ($assignedSubjects as $subject) {
                $pushAssignment($subject, null, 'subject_only');
            }
        }

        $assignments = $assignments->values()->sortBy(function ($row) {
            return strtolower(
                ($row['section']->grade_level ?? '') . ' ' .
                ($row['section']->name ?? '') . ' ' .
                ($row['subject']->subject_name ?? '')
            );
        })->values();

        // Homeroom / adviser sections (separate from teaching load)
        $adviserSections = Section::where('adviser_id', $teacher->id)
            ->withCount('students')
            ->orderBy('grade_level')
            ->orderBy('name')
            ->get();

        $stats = [
            'subjects' => $assignedSubjects->count(),
            'sections' => $assignedSections->count(),
            'classes' => $assignments->count(),
            'students' => (int) $assignments->sum('students_count'),
            'scheduled' => $assignments->filter(fn ($a) => $a['schedules']->isNotEmpty())->count(),
        ];

        return view('teacher.classes', compact(
            'teacher',
            'assignments',
            'assignedSubjects',
            'assignedSections',
            'adviserSections',
            'stats'
        ));
    }

    public function teacherSubjects()
    {
        // Merged into My Classes & Subjects
        return redirect()->route('teacher.classes');
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
        if (!in_array($user->role_name, [
            User::ROLE_ADMIN,
            User::ROLE_TEACHER,
            User::ROLE_STUDENT,
            User::ROLE_PARENT,
            User::ROLE_REGISTRAR,
        ], true)) {
            abort(403);
        }

        try {
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
            }
        } catch (\Throwable $e) {
            Log::error('Dashboard load failed: '.$e->getMessage(), [
                'user_id' => $user->id ?? null,
                'role' => $user->role_name ?? null,
            ]);
            $data['admin'] = $data['admin'] ?? $this->emptyAdminDashboard();
            $data['dashboard_error'] = 'Some dashboard statistics could not be loaded.';
        }

        return view('dashboard', $data);
    }

    /**
     * Load admin dashboard data (cached briefly to keep the dashboard snappy)
     */
    private function loadAdminData()
    {
        try {
            return Cache::remember('admin.dashboard.data.v3', 300, function () {
                return $this->queryAdminDashboardData();
            });
        } catch (\Throwable $e) {
            Log::error('Admin dashboard failed: '.$e->getMessage());
            return $this->emptyAdminDashboard();
        }
    }

    private function emptyAdminDashboard(): array
    {
        return [
            'totalStudents' => 0,
            'totalTeachers' => 0,
            'totalSubjects' => 0,
            'totalSections' => 0,
            'totalEnrollments' => 0,
            'totalAttendance' => 0,
            'totalGrades' => 0,
            'totalAnnouncements' => 0,
            'recentEnrollments' => collect(),
            'recentAnnouncements' => collect(),
            'topStudents' => collect(),
            'attendanceStats' => (object) [
                'total_records' => 0,
                'present_count' => 0,
                'absent_count' => 0,
                'late_count' => 0,
            ],
            'attendancePercentage' => 0,
            'attendanceBreakdown' => [
                'present' => 0,
                'absent' => 0,
                'late' => 0,
            ],
            'maleStudents' => 0,
            'femaleStudents' => 0,
            'recentEvents' => collect(),
            'recentActivities' => [],
            'performanceData' => [
                'labels' => [],
                'averages' => [],
                'mode' => 'empty',
                'title' => 'Academic Performance Overview',
            ],
            'studentsChartData' => [
                'labels' => [],
                'totals' => [],
                'boysData' => [],
                'girlsData' => [],
            ],
        ];
    }

    private function queryAdminDashboardData(): array
    {
        $enrollmentCountSql = "(SELECT COUNT(*) FROM enrollments WHERE status = 'active')";
        $studentDeletedClause = 'WHERE deleted_at IS NULL';

        $counts = DB::selectOne("
            SELECT
                (SELECT COUNT(*) FROM students {$studentDeletedClause}) AS total_students,
                (SELECT COUNT(*) FROM teachers) AS total_teachers,
                (SELECT COUNT(*) FROM subjects) AS total_subjects,
                (SELECT COUNT(*) FROM sections) AS total_sections,
                {$enrollmentCountSql} AS total_enrollments,
                (SELECT COUNT(*) FROM grades) AS total_grades,
                (SELECT COUNT(*) FROM announcements) AS total_announcements,
                (SELECT COUNT(*) FROM students WHERE gender IN ('Male', 'male') ".($studentDeletedClause ? 'AND deleted_at IS NULL' : '').") AS male_students,
                (SELECT COUNT(*) FROM students WHERE gender IN ('Female', 'female') ".($studentDeletedClause ? 'AND deleted_at IS NULL' : '').") AS female_students
        ");

        // One attendance scan with conditional aggregates (was 4 full-table subqueries).
        $attendanceAgg = DB::selectOne("
            SELECT
                COUNT(*) AS total_attendance,
                SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) AS present_count,
                SUM(CASE WHEN status = 'absent' THEN 1 ELSE 0 END) AS absent_count,
                SUM(CASE WHEN status = 'late' THEN 1 ELSE 0 END) AS late_count
            FROM attendances
        ");

        $totalStudents = (int) ($counts->total_students ?? 0);
        $totalTeachers = (int) ($counts->total_teachers ?? 0);
        $totalSubjects = (int) ($counts->total_subjects ?? 0);
        $totalSections = (int) ($counts->total_sections ?? 0);
        $totalEnrollments = (int) ($counts->total_enrollments ?? 0);
        $totalAttendance = (int) ($attendanceAgg->total_attendance ?? 0);
        $totalGrades = (int) ($counts->total_grades ?? 0);
        $totalAnnouncements = (int) ($counts->total_announcements ?? 0);
        $maleStudents = (int) ($counts->male_students ?? 0);
        $femaleStudents = (int) ($counts->female_students ?? 0);
        $presentCount = (int) ($attendanceAgg->present_count ?? 0);
        $absentCount = (int) ($attendanceAgg->absent_count ?? 0);
        $lateCount = (int) ($attendanceAgg->late_count ?? 0);

        $attendanceStats = (object) [
            'total_records' => $totalAttendance,
            'present_count' => $presentCount,
            'absent_count' => $absentCount,
            'late_count' => $lateCount,
        ];

        $attendancePercentage = $totalAttendance > 0
            ? round(($presentCount / $totalAttendance) * 100, 1)
            : 0;

        $attendanceBreakdown = [
            'present' => $totalAttendance > 0 ? round(($presentCount / $totalAttendance) * 100, 1) : 0,
            'absent' => $totalAttendance > 0 ? round(($absentCount / $totalAttendance) * 100, 1) : 0,
            'late' => $totalAttendance > 0 ? round(($lateCount / $totalAttendance) * 100, 1) : 0,
        ];

        $recentEnrollments = \App\Models\Enrollment::with(['student', 'subject'])
            ->where('status', 'active')
            ->orderByDesc('created_at')
            ->take(5)
            ->get();

        $recentAnnouncements = \App\Models\Announcement::with(['creator'])
            ->orderByDesc('created_at')
            ->take(5)
            ->get();

        $topStudents = \App\Models\StudentGpa::with(['student:id,first_name,last_name,admission_id', 'academicYear:id,name', 'semester:id,name'])
            ->orderByDesc('gpa')
            ->take(5)
            ->get();

        $recentEvents = \App\Models\CalendarEvent::with(['subject:id,subject_name', 'teacher:id,full_name'])
            ->where('start_time', '>=', now())
            ->orderBy('start_time')
            ->take(5)
            ->get();

        $performanceData = $this->getAcademicPerformanceOverview();
        $studentsChartData = $this->getStudentsByGradeLevelChartData();
        $recentActivities = $this->buildAdminRecentActivities($recentEnrollments, $recentAnnouncements);

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
            'attendanceBreakdown',
            'maleStudents',
            'femaleStudents',
            'recentEvents',
            'recentActivities',
            'performanceData',
            'studentsChartData'
        );
    }

    /**
     * Real grade averages only — no fabricated "expected" series.
     * Prefer year level; fall back to subject when year-level averages are unavailable.
     */
    private function getAcademicPerformanceOverview(): array
    {
        $empty = [
            'labels' => [],
            'averages' => [],
            'mode' => 'empty',
            'title' => 'Academic Performance Overview',
        ];

        try {
            $rows = DB::table('grades')
                ->join('students', 'grades.student_id', '=', 'students.id')
                ->whereNotNull('grades.percentage')
                ->whereNull('students.deleted_at')
                ->selectRaw('students.year_level as label, ROUND(AVG(grades.percentage), 1) as avg_pct, COUNT(*) as grade_count')
                ->groupBy('students.year_level')
                ->havingRaw('COUNT(*) > 0')
                ->get()
                ->filter(fn ($row) => filled($row->label));

            $gradeOrder = [
                'Nursery', 'Kindergarten', 'Grade 1', 'Grade 2', 'Grade 3', 'Grade 4',
                'Grade 5', 'Grade 6', 'Grade 7', 'Grade 8', 'Grade 9', 'Grade 10',
            ];

            if ($rows->isNotEmpty()) {
                $map = $rows->keyBy('label');
                $labels = [];
                $averages = [];

                foreach ($gradeOrder as $level) {
                    if (isset($map[$level])) {
                        $labels[] = $level;
                        $averages[] = (float) $map[$level]->avg_pct;
                    }
                }

                foreach ($map as $label => $row) {
                    if (! in_array($label, $labels, true)) {
                        $labels[] = $label;
                        $averages[] = (float) $row->avg_pct;
                    }
                }

                return [
                    'labels' => $labels,
                    'averages' => $averages,
                    'mode' => 'year_level',
                    'title' => 'Average Grade by Level',
                ];
            }

            $bySubject = DB::table('grades')
                ->join('subjects', 'grades.subject_id', '=', 'subjects.id')
                ->whereNotNull('grades.percentage')
                ->selectRaw('subjects.subject_name as label, ROUND(AVG(grades.percentage), 1) as avg_pct, COUNT(*) as grade_count')
                ->groupBy('subjects.subject_name')
                ->havingRaw('COUNT(*) > 0')
                ->orderByDesc('avg_pct')
                ->limit(10)
                ->get();

            if ($bySubject->isEmpty()) {
                return $empty;
            }

            return [
                'labels' => $bySubject->pluck('label')->values()->all(),
                'averages' => $bySubject->pluck('avg_pct')->map(fn ($v) => (float) $v)->values()->all(),
                'mode' => 'subject',
                'title' => 'Average Grade by Subject',
            ];
        } catch (\Exception $e) {
            Log::error('Academic performance chart error: '.$e->getMessage());
            return $empty;
        }
    }

    /**
     * Student counts by grade level (totals + gender when available).
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
            $totals = [];

            foreach ($gradeLevels as $gradeLevel) {
                $boys = $map[$gradeLevel]['male'] ?? 0;
                $girls = $map[$gradeLevel]['female'] ?? 0;
                $total = $boys + $girls;
                if ($total > 0) {
                    $labels[] = $gradeLevel;
                    $boysData[] = $boys;
                    $girlsData[] = $girls;
                    $totals[] = $total;
                }
            }

            return compact('labels', 'boysData', 'girlsData', 'totals');
        } catch (\Exception $e) {
            Log::error('Student chart data error: '.$e->getMessage());
            return [
                'labels' => [],
                'boysData' => [],
                'girlsData' => [],
                'totals' => [],
            ];
        }
    }

    /**
     * Build a lightweight activity feed from activity_log, with enrollment/announcement fallback.
     */
    private function buildAdminRecentActivities($recentEnrollments, $recentAnnouncements): array
    {
        $items = collect();

        try {
            $logs = \Spatie\Activitylog\Models\Activity::query()
                ->with('causer:id,name')
                ->orderByDesc('created_at')
                ->take(8)
                ->get(['id', 'description', 'event', 'log_name', 'causer_id', 'causer_type', 'created_at']);

            foreach ($logs as $log) {
                $items->push([
                    'title' => ucfirst(trim((string) ($log->description ?: $log->event ?: 'System activity'))),
                    'meta' => optional($log->causer)->name ?? ($log->log_name ?: 'System'),
                    'at' => optional($log->created_at)?->toIso8601String(),
                    'icon' => 'fa-history',
                    'tone' => 'neutral',
                ]);
            }
        } catch (\Throwable $e) {
            // Fall through to enrollment/announcement feed
        }

        if ($items->isEmpty()) {
            foreach ($recentEnrollments as $enrollment) {
                $studentName = trim(($enrollment->student->first_name ?? '').' '.($enrollment->student->last_name ?? ''));
                $items->push([
                    'title' => 'New student enrollment',
                    'meta' => trim(($studentName ?: 'Student').' · '.($enrollment->subject->subject_name ?? 'Subject')),
                    'at' => optional($enrollment->created_at)?->toIso8601String(),
                    'icon' => 'fa-user-graduate',
                    'tone' => 'success',
                ]);
            }

            foreach ($recentAnnouncements as $announcement) {
                $items->push([
                    'title' => 'Announcement posted',
                    'meta' => $announcement->title ?? (optional($announcement->creator)->name ?? 'Administrator'),
                    'at' => optional($announcement->created_at)?->toIso8601String(),
                    'icon' => 'fa-bullhorn',
                    'tone' => 'info',
                ]);
            }
        }

        return $items
            ->filter(fn ($item) => ! empty($item['at']))
            ->sortByDesc('at')
            ->take(8)
            ->values()
            ->all();
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
                'attendancePercentage' => 0,
                'teacherSubjects' => collect(),
                'teacherSections' => collect(),
                'myClasses' => collect(),
                'todaysSchedule' => collect(),
                'activeLessonsCount' => 0,
                'pendingTasksCount' => 0,
                'upcomingLessonPlans' => collect(),
                'assignmentWorkload' => collect(),
                'teachingProgress' => [
                    'lessons_completed' => 0,
                    'lessons_remaining' => 0,
                    'assignments_graded' => 0,
                    'assignments_pending' => 0,
                    'attendance_recorded' => 0,
                    'lesson_progress_pct' => 0,
                    'grading_progress_pct' => 0,
                    'attendance_pct' => 0,
                ],
                'recentActivity' => collect(),
                'greeting' => 'Hello',
            ];
        }

        return Cache::remember('teacher.dashboard.v3.'.$teacher->id, 180, function () use ($teacher, $user) {
        // Get teacher's subjects with sections, grouped by grade level
        $subjectCollection = $teacher->subjects()->with('sections')->get();
        $subjectIds = $subjectCollection->pluck('id');
        $teacherSubjects = $subjectCollection->groupBy('class')->sortKeys();
        // Production schema always has enrollments.status (avoid INFORMATION_SCHEMA on Aiven).
        $hasEnrollmentStatus = true;

        // Get total classes (sections where teacher is adviser)
        $totalClasses = Section::where('adviser_id', $teacher->id)->count();

        // Get total students across all teacher's subjects
        $totalStudents = $subjectIds->isEmpty()
            ? 0
            : Enrollment::whereIn('subject_id', $subjectIds)
                ->when($hasEnrollmentStatus, fn ($q) => $q->where('status', 'active'))
                ->distinct('student_id')
                ->count('student_id');

        // Get total lessons (subjects taught by teacher)
        $totalLessons = $teacherSubjects->count();

        $monthStart = now()->startOfMonth();
        $monthEnd = now()->endOfMonth();
        $eventStats = CalendarEvent::where('teacher_id', $teacher->id)
            ->whereBetween('start_time', [$monthStart, $monthEnd])
            ->selectRaw('COUNT(*) as total, SUM(CASE WHEN start_time <= ? THEN 1 ELSE 0 END) as completed', [now()])
            ->first();
        $totalHours = (int) ($eventStats->total ?? 0);
        $totalEventsThisMonth = $totalHours;
        $completedEventsThisMonth = (int) ($eventStats->completed ?? 0);

        $upcomingEvents = CalendarEvent::with(['subject'])
            ->where('teacher_id', $teacher->id)
            ->where('start_time', '>=', now())
            ->orderBy('start_time', 'asc')
            ->take(10)
            ->get();
        $upcomingLessons = $upcomingEvents->take(5);

        $semesterProgress = $totalEventsThisMonth > 0
            ? round(($completedEventsThisMonth / $totalEventsThisMonth) * 100, 1)
            : 0;

        $teachingHistory = CalendarEvent::with(['subject'])
            ->where('teacher_id', $teacher->id)
            ->where('start_time', '<=', now())
            ->orderBy('start_time', 'desc')
            ->take(10)
            ->get();

        $attendanceStats = $subjectIds->isEmpty()
            ? (object) ['total_records' => 0, 'present_count' => 0, 'absent_count' => 0]
            : Attendance::whereIn('subject_id', $subjectIds)
                ->selectRaw('
                    COUNT(*) as total_records,
                    SUM(CASE WHEN status = "present" THEN 1 ELSE 0 END) as present_count,
                    SUM(CASE WHEN status = "absent" THEN 1 ELSE 0 END) as absent_count
                ')->first();

        $attendancePercentage = ($attendanceStats && $attendanceStats->total_records > 0)
            ? round(($attendanceStats->present_count / $attendanceStats->total_records) * 100, 1)
            : 0;

        $teacherSections = $teacher->sections()->get();

        // ---- UI enrichment (same modules; additional presentation data) ----
        $assignedSectionIds = $teacherSections->pluck('id')->map(fn ($id) => (int) $id)->all();

        $schedules = ClassSchedule::with(['subject', 'section', 'room'])
            ->where('teacher_id', $teacher->id)
            ->where('is_active', true)
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get();

        $options = null;
        if ($schedules->isEmpty()) {
            // Only hit assignment service when there are no schedule rows to build cards from.
            $options = app(TeacherClassAssignmentService::class)->optionsFor($teacher);
            $assignedSectionIds = collect($assignedSectionIds)
                ->merge($options['sections']->pluck('id'))
                ->map(fn ($id) => (int) $id)
                ->unique()
                ->values()
                ->all();
        }

        $sectionIdsForCounts = collect($assignedSectionIds)
            ->merge($schedules->pluck('section_id'))
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        $sectionStudentCounts = empty($sectionIdsForCounts)
            ? collect()
            : DB::table('student_section_assignments as ssa')
                ->join('students as s', 's.id', '=', 'ssa.student_id')
                ->whereNull('s.deleted_at')
                ->whereIn('ssa.section_id', $sectionIdsForCounts)
                ->groupBy('ssa.section_id')
                ->selectRaw('ssa.section_id, COUNT(DISTINCT ssa.student_id) as c')
                ->pluck('c', 'section_id');

        if ($schedules->isEmpty() && $options && ($options['subjects']->isNotEmpty() || $options['sections']->isNotEmpty())) {
            // Fallback class cards from assignments when no schedule rows exist
            $myClasses = collect();
            foreach ($options['subjectsBySection'] as $sectionId => $subjectIdsForSection) {
                $section = $options['sections']->firstWhere('id', (int) $sectionId);
                if (! $section) {
                    continue;
                }
                foreach ($subjectIdsForSection as $sid) {
                    $subject = $options['subjects']->firstWhere('id', (int) $sid);
                    if (! $subject) {
                        continue;
                    }
                    $studentCount = (int) ($sectionStudentCounts[$section->id] ?? 0);
                    $myClasses->push((object) [
                        'grade_level' => $section->grade_level ?? ($subject->class ?? '—'),
                        'subject_name' => $subject->subject_name,
                        'section_name' => $section->name,
                        'section_id' => $section->id,
                        'student_count' => $studentCount,
                        'schedule_label' => 'Schedule not set',
                        'room' => null,
                        'status' => 'Active',
                    ]);
                }
            }
        } else {
            $myClasses = $schedules->map(function (ClassSchedule $row) use ($sectionStudentCounts) {
                $section = $row->section;
                $studentCount = $section
                    ? (int) ($sectionStudentCounts[$section->id] ?? 0)
                    : 0;
                $start = $row->start_time ? Carbon::parse($row->start_time)->format('g:i A') : '';
                $end = $row->end_time ? Carbon::parse($row->end_time)->format('g:i A') : '';

                return (object) [
                    'grade_level' => $section->grade_level ?? ($row->subject->class ?? '—'),
                    'subject_name' => $row->subject->subject_name ?? 'Subject',
                    'section_name' => $section->name ?? '—',
                    'section_id' => $section->id ?? null,
                    'student_count' => $studentCount,
                    'schedule_label' => trim(ucfirst($row->day_of_week).($start ? ' • '.$start.($end ? ' – '.$end : '') : '')),
                    'room' => $row->room->room_name ?? null,
                    'status' => $row->is_active ? 'Active' : 'Inactive',
                ];
            })->unique(fn ($c) => ($c->section_id ?? 0).'-'.($c->subject_name ?? ''))->values();
        }

        if ($totalClasses === 0) {
            $totalClasses = max($myClasses->count(), $teacherSections->count());
        }

        $todayName = strtolower(now()->format('l'));
        $todaysSchedule = $schedules->where('day_of_week', $todayName)->values()->map(function (ClassSchedule $row) use ($sectionStudentCounts) {
            $section = $row->section;
            $studentCount = $section
                ? (int) ($sectionStudentCounts[$section->id] ?? 0)
                : 0;

            return (object) [
                'start_label' => $row->start_time ? Carbon::parse($row->start_time)->format('g:i A') : '—',
                'end_label' => $row->end_time ? Carbon::parse($row->end_time)->format('g:i A') : '',
                'subject_name' => $row->subject->subject_name ?? 'Subject',
                'grade_level' => $section->grade_level ?? '—',
                'section_name' => $section->name ?? '—',
                'room' => $row->room->room_name ?? '—',
                'student_count' => $studentCount,
            ];
        });

        $lessonStats = Lesson::where('teacher_id', $teacher->id)
            ->selectRaw("
                SUM(CASE WHEN status = 'published' OR is_active = 1 THEN 1 ELSE 0 END) as active_count,
                SUM(CASE WHEN status = 'draft' THEN 1 ELSE 0 END) as draft_count,
                SUM(CASE WHEN status = 'published' AND lesson_date IS NOT NULL AND lesson_date < ? THEN 1 ELSE 0 END) as completed_count,
                SUM(CASE WHEN lesson_date IS NOT NULL AND lesson_date >= ? THEN 1
                         WHEN lesson_date IS NULL AND status IN ('draft', 'published') THEN 1
                         ELSE 0 END) as remaining_count
            ", [now()->toDateString(), now()->toDateString()])
            ->first();

        $activeLessonsCount = (int) ($lessonStats->active_count ?? 0);

        $upcomingLessonPlans = Lesson::with(['subject', 'section'])
            ->where('teacher_id', $teacher->id)
            ->where(function ($q) {
                $q->whereDate('lesson_date', '>=', now()->toDateString())
                    ->orWhereNull('lesson_date');
            })
            ->orderByRaw('CASE WHEN lesson_date IS NULL THEN 1 ELSE 0 END')
            ->orderBy('lesson_date')
            ->take(6)
            ->get();

        $assignmentRows = Assignment::with(['subject', 'section'])
            ->where('teacher_id', $teacher->id)
            ->where('is_active', true)
            ->whereIn('status', ['published', 'closed', 'draft'])
            ->orderByRaw('CASE WHEN due_date IS NULL THEN 1 ELSE 0 END')
            ->orderBy('due_date')
            ->take(8)
            ->get();

        $assignmentIds = $assignmentRows->pluck('id')->all();
        $submissionStats = empty($assignmentIds)
            ? collect()
            : AssignmentSubmission::whereIn('assignment_id', $assignmentIds)
                ->whereIn('status', ['submitted', 'late', 'graded'])
                ->selectRaw("
                    assignment_id,
                    COUNT(*) as submitted,
                    SUM(CASE WHEN status = 'graded' THEN 1 ELSE 0 END) as graded
                ")
                ->groupBy('assignment_id')
                ->get()
                ->keyBy('assignment_id');

        $assignmentWorkload = $assignmentRows->map(function (Assignment $assignment) use ($submissionStats) {
            $stats = $submissionStats->get($assignment->id);
            $submitted = (int) ($stats->submitted ?? 0);
            $graded = (int) ($stats->graded ?? 0);
            $pending = max(0, $submitted - $graded);
            $pct = $submitted > 0 ? round(($graded / $submitted) * 100) : 0;

            return (object) [
                'id' => $assignment->id,
                'title' => $assignment->title,
                'subject_name' => $assignment->subject->subject_name ?? '—',
                'class_label' => trim(($assignment->section->grade_level ?? '').' '.($assignment->section->name ?? '')) ?: '—',
                'due_date' => $assignment->due_date,
                'status' => $assignment->status,
                'submitted' => $submitted,
                'graded' => $graded,
                'pending' => $pending,
                'progress_pct' => $pct,
            ];
        });

        $pendingTasksCount = (int) $assignmentWorkload->sum('pending')
            + (int) ($lessonStats->draft_count ?? 0);

        $lessonsCompleted = (int) ($lessonStats->completed_count ?? 0);
        $lessonsRemaining = (int) ($lessonStats->remaining_count ?? 0);

        $assignmentGradeStats = AssignmentSubmission::whereHas('assignment', fn ($q) => $q->where('teacher_id', $teacher->id))
            ->selectRaw("
                SUM(CASE WHEN status = 'graded' THEN 1 ELSE 0 END) as graded_count,
                SUM(CASE WHEN status IN ('submitted', 'late') THEN 1 ELSE 0 END) as pending_count
            ")
            ->first();

        $assignmentsGraded = (int) ($assignmentGradeStats->graded_count ?? 0);
        $assignmentsPendingGrade = (int) ($assignmentGradeStats->pending_count ?? 0);
        $attendanceRecorded = (int) ($attendanceStats->total_records ?? 0);

        $lessonTotal = max(1, $lessonsCompleted + $lessonsRemaining);
        $gradeTotal = max(1, $assignmentsGraded + $assignmentsPendingGrade);

        $teachingProgress = [
            'lessons_completed' => $lessonsCompleted,
            'lessons_remaining' => $lessonsRemaining,
            'assignments_graded' => $assignmentsGraded,
            'assignments_pending' => $assignmentsPendingGrade,
            'attendance_recorded' => $attendanceRecorded,
            'lesson_progress_pct' => round(($lessonsCompleted / $lessonTotal) * 100),
            'grading_progress_pct' => round(($assignmentsGraded / $gradeTotal) * 100),
            'attendance_pct' => $attendancePercentage,
        ];

        $recentActivity = collect();
        foreach ($teachingHistory->take(3) as $history) {
            $recentActivity->push((object) [
                'icon' => 'fa-chalkboard',
                'text' => 'Completed session: '.($history->subject->subject_name ?? $history->title ?? 'Class'),
                'time' => optional($history->start_time)->diffForHumans() ?? '',
            ]);
        }
        // One batched recent-items query instead of 3 sequential latest() hits.
        $recentLessons = Lesson::where('teacher_id', $teacher->id)
            ->latest('id')
            ->take(2)
            ->get(['id', 'title', 'created_at']);
        foreach ($recentLessons as $lesson) {
            $recentActivity->push((object) [
                'icon' => 'fa-book',
                'text' => 'Created lesson "'.$lesson->title.'"',
                'time' => optional($lesson->created_at)->diffForHumans() ?? '',
            ]);
        }
        $recentAssignments = Assignment::where('teacher_id', $teacher->id)
            ->latest('id')
            ->take(2)
            ->get(['id', 'title', 'created_at']);
        foreach ($recentAssignments as $asg) {
            $recentActivity->push((object) [
                'icon' => 'fa-tasks',
                'text' => 'Published assignment "'.$asg->title.'"',
                'time' => optional($asg->created_at)->diffForHumans() ?? '',
            ]);
        }
        if (class_exists(ClassPost::class)) {
            try {
                $post = ClassPost::where('teacher_id', $teacher->id)->latest('id')->first(['id', 'title', 'created_at']);
                if ($post) {
                    $recentActivity->push((object) [
                        'icon' => 'fa-bullhorn',
                        'text' => 'Posted: '.($post->title ?? 'Class update'),
                        'time' => optional($post->created_at)->diffForHumans() ?? '',
                    ]);
                }
            } catch (\Throwable $e) {
                // class_posts table may be missing on older DBs
            }
        }
        $recentActivity = $recentActivity->take(8)->values();

        $hour = (int) now()->format('G');
        $greeting = $hour < 12 ? 'Good morning' : ($hour < 18 ? 'Good afternoon' : 'Good evening');

        // Prefer real class count for overview card
        $classCardCount = $myClasses->count() > 0 ? $myClasses->count() : $totalClasses;

        return compact(
            'teacher',
            'teacherSubjects',
            'teacherSections',
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
        ) + [
            'myClasses' => $myClasses,
            'todaysSchedule' => $todaysSchedule,
            'activeLessonsCount' => $activeLessonsCount,
            'pendingTasksCount' => $pendingTasksCount,
            'upcomingLessonPlans' => $upcomingLessonPlans,
            'assignmentWorkload' => $assignmentWorkload,
            'teachingProgress' => $teachingProgress,
            'recentActivity' => $recentActivity,
            'greeting' => $greeting,
            'classCardCount' => $classCardCount,
            'teacherDisplayName' => $teacher->full_name
                ?? trim(($teacher->first_name ?? '').' '.($teacher->last_name ?? ''))
                ?: ($user->name ?? 'Teacher'),
        ];
        });
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

        return Cache::remember('student.dashboard.v2.'.$student->id, 180, function () use ($student) {
        $student->load([
            'sections:id,name,grade_level,adviser_id',
            'sections.adviser:id,full_name',
            'enrollmentApplication.documents',
        ]);

        $enrollments = $student->enrollments()
            ->with(['subject:id,subject_name,class', 'academicYear:id,name', 'semester:id,name'])
            ->where('status', 'active')
            ->get();

        $catalogSubjects = [];
        $app = $student->enrollmentApplication;
        if ($app && $app->grade_level_applying_for) {
            $catalogSubjects = Cache::remember(
                'catalog.subjects.'.md5((string) $app->grade_level_applying_for),
                600,
                fn () => app(GradeSubjectCatalogService::class)->subjectNamesForGrade($app->grade_level_applying_for)
            );
        }

        return [
            'student' => $student,
            'enrollments' => $enrollments,
            'hasStudent' => true,
            'catalogSubjects' => $catalogSubjects,
        ];
        });
    }

    /**
     * Load parent dashboard data (critical path only; secondary lists are lean + cached).
     */
    private function loadParentData()
    {
        $parentId = auth()->id();
        $childKey = request()->input('child_id', 'first');
        // Do not include date in cache key — date-specific attendance is fetched cheaply inside.
        $cacheKey = 'parent.dashboard.v3.'.$parentId.'.'.$childKey;

        return Cache::remember($cacheKey, 180, function () {
        try {
            $parent = auth()->user();

            if ($parent->role_name !== 'Parent') {
                return null;
            }

            $children = Student::where('parent_email', $parent->email)
                ->with([
                    'sections:id,name,grade_level,adviser_id',
                    'sections.adviser:id,full_name',
                    'enrollmentApplication.documents',
                ])
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
                    'noChildren' => true,
                ];
            }

            $selectedChildId = request()->input('child_id', $children->first()->id);
            $selectedChild = $children->find($selectedChildId) ?: $children->first();

            $currentAcademicYear = Cache::remember('academic.year.latest', 600, fn () => \App\Models\AcademicYear::latest('id')->first());
            $currentSemester = Cache::remember('academic.semester.latest', 600, fn () => \App\Models\Semester::latest('id')->first());

            $hasEnrollmentStatus = true;
            $enrollments = Enrollment::where('student_id', $selectedChild->id)
                ->when($hasEnrollmentStatus, fn ($q) => $q->where('status', 'active'))
                ->with(['subject:id,subject_name,class', 'academicYear:id,name', 'semester:id,name'])
                ->get();
            $enrolledSubjectIds = $enrollments->pluck('subject_id')->filter()->unique()->values();

            $grades = collect();
            if ($currentAcademicYear && $currentSemester) {
                $grades = \App\Models\Grade::with(['subject:id,subject_name'])
                    ->where('student_id', $selectedChild->id)
                    ->where('academic_year_id', $currentAcademicYear->id)
                    ->where('semester_id', $currentSemester->id)
                    ->orderByDesc('created_at')
                    ->limit(50)
                    ->get();
            }

            $date = request()->input('date', now()->format('Y-m-d'));
            $attendance = Attendance::with(['subject:id,subject_name'])
                ->where('student_id', $selectedChild->id)
                ->whereDate('date', $date)
                ->get();

            $attendanceStats = Attendance::where('student_id', $selectedChild->id)
                ->selectRaw('
                    COUNT(*) as total_records,
                    SUM(CASE WHEN status = "present" THEN 1 ELSE 0 END) as present_count,
                    SUM(CASE WHEN status = "absent" THEN 1 ELSE 0 END) as absent_count
                ')->first();

            $lessons = collect();
            $activities = collect();
            $submissions = collect();
            $upcomingEvents = collect();

            if ($currentAcademicYear && $currentSemester && $enrolledSubjectIds->isNotEmpty()) {
                $yearId = $currentAcademicYear->id;
                $semId = $currentSemester->id;
                $subjectIds = $enrolledSubjectIds->all();

                $lessons = Lesson::with(['subject:id,subject_name'])
                    ->whereIn('subject_id', $subjectIds)
                    ->where('academic_year_id', $yearId)
                    ->where('semester_id', $semId)
                    ->orderByDesc('created_at')
                    ->take(5)
                    ->get();

                $activities = \App\Models\Activity::query()
                    ->select('activities.*')
                    ->join('lessons', 'lessons.id', '=', 'activities.lesson_id')
                    ->with(['lesson.subject:id,subject_name'])
                    ->whereIn('lessons.subject_id', $subjectIds)
                    ->where('lessons.academic_year_id', $yearId)
                    ->where('lessons.semester_id', $semId)
                    ->orderBy('activities.due_date')
                    ->take(5)
                    ->get();

                $submissions = \App\Models\ActivitySubmission::query()
                    ->select('activity_submissions.*')
                    ->join('activities', 'activities.id', '=', 'activity_submissions.activity_id')
                    ->join('lessons', 'lessons.id', '=', 'activities.lesson_id')
                    ->with(['activity.lesson.subject:id,subject_name'])
                    ->where('activity_submissions.student_id', $selectedChild->id)
                    ->whereIn('lessons.subject_id', $subjectIds)
                    ->where('lessons.academic_year_id', $yearId)
                    ->where('lessons.semester_id', $semId)
                    ->orderByDesc('activity_submissions.submitted_at')
                    ->take(5)
                    ->get();

                $upcomingEvents = CalendarEvent::whereIn('subject_id', $subjectIds)
                    ->where('start_time', '>=', now())
                    ->orderBy('start_time')
                    ->take(5)
                    ->get(['id', 'title', 'start_time', 'subject_id']);
            }

            $gpa = null;
            if ($currentAcademicYear && $currentSemester) {
                $gpa = \App\Models\StudentGpa::where('student_id', $selectedChild->id)
                    ->where('academic_year_id', $currentAcademicYear->id)
                    ->where('semester_id', $currentSemester->id)
                    ->value('gpa');
            }

            $attendancePercentage = $attendanceStats && $attendanceStats->total_records > 0
                ? round(($attendanceStats->present_count / $attendanceStats->total_records) * 100, 1)
                : 0;

            $performanceInsights = [
                'gpa' => $gpa,
                'attendancePercentage' => $attendancePercentage,
                'academicYear' => $currentAcademicYear->name ?? null,
                'semester' => $currentSemester->name ?? null,
            ];

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
            Log::error('Parent Dashboard Error: '.$e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

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
                'error' => 'An error occurred while loading the dashboard.',
            ];
        }
        });
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
    private function getChildLessons($child, $academicYear, $semester, $enrolledSubjectIds = null)
    {
        if (!$academicYear || !$semester) {
            return collect();
        }

        $enrolledSubjectIds = $enrolledSubjectIds ?? \App\Models\Enrollment::where('student_id', $child->id)
            ->when(SafeSchema::columnExists('enrollments', 'status'), fn ($q) => $q->where('status', 'active'))
            ->pluck('subject_id');

        if ($enrolledSubjectIds->isEmpty()) {
            return collect();
        }

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
    private function getChildActivities($child, $academicYear, $semester, $enrolledSubjectIds = null)
    {
        if (!$academicYear || !$semester) {
            return collect();
        }

        $enrolledSubjectIds = $enrolledSubjectIds ?? \App\Models\Enrollment::where('student_id', $child->id)
            ->when(SafeSchema::columnExists('enrollments', 'status'), fn ($q) => $q->where('status', 'active'))
            ->pluck('subject_id');

        if ($enrolledSubjectIds->isEmpty()) {
            return collect();
        }

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
    private function getChildSubmissions($child, $academicYear, $semester, $enrolledSubjectIds = null)
    {
        if (!$academicYear || !$semester) {
            return collect();
        }

        $enrolledSubjectIds = $enrolledSubjectIds ?? \App\Models\Enrollment::where('student_id', $child->id)
            ->when(SafeSchema::columnExists('enrollments', 'status'), fn ($q) => $q->where('status', 'active'))
            ->pluck('subject_id');

        if ($enrolledSubjectIds->isEmpty()) {
            return collect();
        }

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

        $rules = [
            'email' => 'required|email|max:255|unique:users,email,'.$user->id,
            'avatar' => \App\Support\FormRules::AVATAR,
        ];

        if ($user->role_name === \App\Models\User::ROLE_STUDENT) {
            $rules['name'] = 'nullable|string';
        } else {
            $rules['name'] = \App\Support\FormRules::NAME;
        }

        $request->validate($rules, \App\Support\FormRules::messages());

        if ($user->role_name !== \App\Models\User::ROLE_STUDENT) {
            $user->name = $request->name;
        }
        $user->email = $request->email;

        $stored = \App\Support\AvatarUploader::store($request->file('avatar'), $user->avatar);
        if ($stored) {
            $user->avatar = $stored;
            session(['avatar' => $stored]);
        }

        $user->save();

        activity()
            ->causedBy($user)
            ->performedOn($user)
            ->withProperties(['attributes' => ['email' => $request->email]])
            ->log('updated profile');

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
     * Display activity logs. Admins can view all system activity or their own.
     */
    public function activityLog(Request $request)
    {
        $user = auth()->user();
        $isAdmin = $user->role_name === 'Admin';
        $scope = $request->get('scope', $isAdmin ? 'all' : 'mine');

        if (!$isAdmin) {
            $scope = 'mine';
        }

        if (!in_array($scope, ['all', 'mine'], true)) {
            $scope = $isAdmin ? 'all' : 'mine';
        }

        $query = \Spatie\Activitylog\Models\Activity::query()
            ->with('causer')
            ->orderByDesc('created_at');

        if ($scope === 'mine') {
            $query->where('causer_id', $user->id)
                ->where('causer_type', get_class($user));
        } elseif ($request->filled('user_id')) {
            $query->where('causer_id', $request->user_id)
                ->where('causer_type', User::class);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($builder) use ($search) {
                $builder->where('description', 'like', "%{$search}%")
                    ->orWhere('event', 'like', "%{$search}%")
                    ->orWhere('log_name', 'like', "%{$search}%");
            });
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $activities = $query
            ->paginate($scope === 'all' ? 30 : 20)
            ->withQueryString();

        $users = $isAdmin
            ? User::orderBy('name')->get(['id', 'name', 'role_name'])
            : collect();

        return view('dashboard.activity_log', compact(
            'activities',
            'scope',
            'isAdmin',
            'users'
        ));
    }

    /**
     * @deprecated Redirects to the unified activity log page.
     */
    public function adminActivityLog(Request $request)
    {
        return redirect()->route('activity.log', array_merge(
            $request->query(),
            ['scope' => 'all']
        ));
    }

    /**
     * Load registrar dashboard data
     */
    private function loadRegistrarData()
    {
        return Cache::remember('registrar.dashboard.data', 180, function () {
        $applicationsByStatus = \App\Models\EnrollmentApplication::selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        $pendingApplications = (int) ($applicationsByStatus['pending'] ?? 0);
        $approvedApplications = (int) ($applicationsByStatus['approved'] ?? 0);
        $rejectedApplications = (int) ($applicationsByStatus['rejected'] ?? 0);
        $underReviewApplications = (int) ($applicationsByStatus['under_review'] ?? 0);
        $needsDocumentsApplications = (int) ($applicationsByStatus['needs_documents'] ?? 0);
        $totalApplications = (int) $applicationsByStatus->sum();

        $recentApplications = \App\Models\EnrollmentApplication::orderBy('created_at', 'desc')->take(5)->get();

        $applicationsByGrade = \App\Models\EnrollmentApplication::selectRaw('grade_level_applying_for, COUNT(*) as count')
            ->groupBy('grade_level_applying_for')
            ->orderBy('count', 'desc')
            ->get();

        $thisMonthApplications = \App\Models\EnrollmentApplication::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        $lastMonthApplications = \App\Models\EnrollmentApplication::whereMonth('created_at', now()->subMonth()->month)
            ->whereYear('created_at', now()->subMonth()->year)
            ->count();

        $growthPercentage = $lastMonthApplications > 0
            ? round((($thisMonthApplications - $lastMonthApplications) / $lastMonthApplications) * 100, 1)
            : 0;

        $applicationsByStatus = $applicationsByStatus->map(function ($count, $status) {
            return (object) ['status' => $status, 'count' => $count];
        })->values();

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
        });
    }
}
