<?php

namespace App\Services;

use App\Models\Student;
use App\Models\Teacher;
use App\Models\Grade;
use App\Models\Attendance;
use App\Models\Subject;
use App\Models\Section;
use App\Models\AcademicYear;
use App\Models\Semester;
use App\Models\ActivitySubmission;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class AcademicAnalyticsService
{
    /**
     * Get student analytics data
     */
    public function getStudentAnalytics($studentId, $academicYearId = null, $semesterId = null)
    {
        $student = Student::findOrFail($studentId);
        
        return [
            'student' => $student,
            'grade_trends' => $this->getStudentGradeTrends($studentId, $academicYearId, $semesterId),
            'attendance_summary' => $this->getStudentAttendanceSummary($studentId, $academicYearId, $semesterId),
            'performance_indicators' => $this->getStudentPerformanceIndicators($studentId, $academicYearId, $semesterId),
            'subject_performance' => $this->getStudentSubjectPerformance($studentId, $academicYearId, $semesterId),
            'recent_activities' => $this->getStudentRecentActivities($studentId),
            'gpa_trend' => $this->getStudentGpaTrend($studentId, $academicYearId, $semesterId)
        ];
    }

    /**
     * Get teacher analytics data
     */
    public function getTeacherAnalytics($teacherId, $academicYearId = null, $semesterId = null)
    {
        $teacher = Teacher::findOrFail($teacherId);
        
        return [
            'teacher' => $teacher,
            'class_averages' => $this->getTeacherClassAverages($teacherId, $academicYearId, $semesterId),
            'top_students' => $this->getTeacherTopStudents($teacherId, $academicYearId, $semesterId),
            'attendance_overview' => $this->getTeacherAttendanceOverview($teacherId, $academicYearId, $semesterId),
            'assessment_breakdown' => $this->getTeacherAssessmentBreakdown($teacherId, $academicYearId, $semesterId),
            'recent_grades' => $this->getTeacherRecentGrades($teacherId),
            'class_performance' => $this->getTeacherClassPerformance($teacherId, $academicYearId, $semesterId)
        ];
    }

    /**
     * Get admin analytics data
     */
    public function getAdminAnalytics($academicYearId = null, $semesterId = null)
    {
        return [
            'school_overview' => $this->getSchoolOverview($academicYearId, $semesterId),
            'enrollment_overview' => $this->getEnrollmentOverview(),
            'students_by_grade' => $this->getStudentsByGrade(),
            'gpa_comparison' => $this->getGpaComparison($academicYearId, $semesterId),
            'pass_fail_rates' => $this->getPassFailRates($academicYearId, $semesterId),
            'attendance_summary' => $this->getSchoolAttendanceSummary($academicYearId, $semesterId),
            'subject_performance' => $this->getSchoolSubjectPerformance($academicYearId, $semesterId),
            'section_comparison' => $this->getSectionComparison($academicYearId, $semesterId),
        ];
    }

    /**
     * Get student grade trends
     */
    private function getStudentGradeTrends($studentId, $academicYearId = null, $semesterId = null)
    {
        $query = Grade::where('student_id', $studentId)
            ->with(['subject', 'academicYear', 'semester']);

        if ($academicYearId) $query->where('academic_year_id', $academicYearId);
        if ($semesterId) $query->where('semester_id', $semesterId);

        $grades = $query->orderBy('created_at')->get();

        $trends = [];
        foreach ($grades as $grade) {
            $year = optional($grade->academicYear)->name ?? 'Academic Year';
            $sem = optional($grade->semester)->name ?? 'Period';
            $trends[] = [
                'period' => $year.' - '.$sem,
                'subject' => optional($grade->subject)->subject_name ?? 'Subject',
                'score' => $grade->percentage,
                'date' => optional($grade->created_at)->format('Y-m-d'),
            ];
        }

        return $trends;
    }

    /**
     * Get student attendance summary
     */
    private function getStudentAttendanceSummary($studentId, $academicYearId = null, $semesterId = null)
    {
        $query = Attendance::where('student_id', $studentId);

        if ($academicYearId && Schema::hasColumn('attendances', 'academic_year_id')) {
            $query->where('academic_year_id', $academicYearId);
        }
        if ($semesterId && Schema::hasColumn('attendances', 'semester_id')) {
            $query->where('semester_id', $semesterId);
        }

        $attendance = $query->get();

        $monthlyData = [];
        foreach ($attendance as $record) {
            // Ensure date is a Carbon instance
            $date = $record->date instanceof Carbon ? $record->date : Carbon::parse($record->date);
            $month = $date->format('Y-m');
            if (!isset($monthlyData[$month])) {
                $monthlyData[$month] = ['present' => 0, 'total' => 0];
            }
            $monthlyData[$month]['total']++;
            if ($record->status === 'present') {
                $monthlyData[$month]['present']++;
            }
        }

        $summary = [];
        foreach ($monthlyData as $month => $data) {
            $summary[] = [
                'month' => Carbon::createFromFormat('Y-m', $month)->format('M Y'),
                'percentage' => $data['total'] > 0 ? round(($data['present'] / $data['total']) * 100, 2) : 0,
                'present' => $data['present'],
                'total' => $data['total'],
            ];
        }

        return $summary;
    }

    /**
     * Get student performance indicators
     */
    private function getStudentPerformanceIndicators($studentId, $academicYearId = null, $semesterId = null)
    {
        $query = Grade::where('student_id', $studentId);

        if ($academicYearId) $query->where('academic_year_id', $academicYearId);
        if ($semesterId) $query->where('semester_id', $semesterId);

        $grades = $query->get();

        $totalGrades = $grades->count();
        $averageScore = $grades->avg('percentage') ?? 0;
        $lowGrades = $grades->where('percentage', '<', 75)->count();
        $excellentGrades = $grades->where('percentage', '>=', 90)->count();

        return [
            'total_assignments' => $totalGrades,
            'average_score' => round($averageScore, 2),
            'low_grades_count' => $lowGrades,
            'excellent_grades_count' => $excellentGrades,
            'performance_level' => $this->getPerformanceLevel($averageScore),
            'improvement_needed' => $lowGrades > 0
        ];
    }

    /**
     * Get student subject performance
     */
    private function getStudentSubjectPerformance($studentId, $academicYearId = null, $semesterId = null)
    {
        $query = Grade::where('student_id', $studentId)
            ->with('subject');

        if ($academicYearId) $query->where('academic_year_id', $academicYearId);
        if ($semesterId) $query->where('semester_id', $semesterId);

        $grades = $query->get();

        $subjectPerformance = [];
        foreach ($grades->groupBy('subject_id') as $subjectId => $subjectGrades) {
            $subject = $subjectGrades->first()->subject;
            $subjectPerformance[] = [
                'subject' => $subject->subject_name,
                'average_score' => round($subjectGrades->avg('percentage'), 2),
                'assignments_count' => $subjectGrades->count(),
                'highest_score' => $subjectGrades->max('percentage'),
                'lowest_score' => $subjectGrades->min('percentage')
            ];
        }

        return $subjectPerformance;
    }

    /**
     * Get student recent activities
     */
    private function getStudentRecentActivities($studentId)
    {
        return ActivitySubmission::where('student_id', $studentId)
            ->with(['activity.lesson.subject'])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get()
            ->map(function($submission) {
                return [
                    'activity' => $submission->activity->title,
                    'subject' => $submission->activity->lesson->subject->subject_name,
                    'submitted_at' => $submission->created_at->format('M d, Y'),
                    'status' => $submission->status,
                    'score' => $submission->total_score ?? '-'
                ];
            });
    }

    /**
     * Get student GPA trend
     */
    private function getStudentGpaTrend($studentId, $academicYearId = null, $semesterId = null)
    {
        $query = DB::table('student_gpa')
            ->leftJoin('academic_years', 'student_gpa.academic_year_id', '=', 'academic_years.id')
            ->leftJoin('semesters', 'student_gpa.semester_id', '=', 'semesters.id')
            ->where('student_gpa.student_id', $studentId)
            ->select(
                'student_gpa.gpa',
                'student_gpa.created_at',
                'academic_years.name as academic_year_name',
                'semesters.name as semester_name'
            );

        if ($academicYearId) {
            $query->where('student_gpa.academic_year_id', $academicYearId);
        }
        if ($semesterId) {
            $query->where('student_gpa.semester_id', $semesterId);
        }

        return $query->orderBy('student_gpa.created_at')
            ->get()
            ->map(function ($gpa) {
                $year = $gpa->academic_year_name ?: 'Academic Year';
                $sem = $gpa->semester_name ?: 'Period';
                $value = $gpa->gpa !== null ? (float) $gpa->gpa : null;

                return [
                    'period' => $year.' - '.$sem,
                    'gpa' => $value,
                    'letter_grade' => $this->letterGradeFromGpa($value),
                ];
            })
            ->values();
    }

    private function letterGradeFromGpa(?float $gpa): string
    {
        if ($gpa === null) {
            return '—';
        }
        if ($gpa >= 3.5) {
            return 'A';
        }
        if ($gpa >= 3.0) {
            return 'B';
        }
        if ($gpa >= 2.5) {
            return 'C';
        }
        if ($gpa >= 2.0) {
            return 'D';
        }

        return 'F';
    }

    /**
     * Get teacher class averages
     */
    private function getTeacherClassAverages($teacherId, $academicYearId = null, $semesterId = null)
    {
        $query = Grade::whereHas('subject.teachers', function($q) use ($teacherId) {
            $q->where('teacher_id', $teacherId);
        })->with(['subject', 'academicYear', 'semester']);

        if ($academicYearId) $query->where('academic_year_id', $academicYearId);
        if ($semesterId) $query->where('semester_id', $semesterId);

        $grades = $query->get();

        $classAverages = [];
        foreach ($grades->groupBy('subject_id') as $subjectId => $subjectGrades) {
            $subject = $subjectGrades->first()->subject;
            $classAverages[] = [
                'subject' => $subject->subject_name,
                'average_score' => round($subjectGrades->avg('percentage'), 2),
                'total_students' => $subjectGrades->groupBy('student_id')->count(),
                'assignments_count' => $subjectGrades->count()
            ];
        }

        return $classAverages;
    }

    /**
     * Get teacher top students
     */
    private function getTeacherTopStudents($teacherId, $academicYearId = null, $semesterId = null)
    {
        $query = Grade::whereHas('subject.teachers', function($q) use ($teacherId) {
            $q->where('teacher_id', $teacherId);
        })->with(['student', 'subject']);

        if ($academicYearId) $query->where('academic_year_id', $academicYearId);
        if ($semesterId) $query->where('semester_id', $semesterId);

        $grades = $query->get();

        $studentAverages = [];
        foreach ($grades->groupBy('student_id') as $studentId => $studentGrades) {
            $student = $studentGrades->first()->student;
            $studentAverages[] = [
                'student_name' => $student->first_name . ' ' . $student->last_name,
                'average_score' => round($studentGrades->avg('percentage'), 2),
                'assignments_count' => $studentGrades->count(),
                'subjects_count' => $studentGrades->groupBy('subject_id')->count()
            ];
        }

        // Sort by average score descending
        usort($studentAverages, function($a, $b) {
            return $b['average_score'] <=> $a['average_score'];
        });

        return [
            'top_students' => array_slice($studentAverages, 0, 5),
            'lowest_students' => array_slice($studentAverages, -5)
        ];
    }

    /**
     * Get teacher attendance overview
     */
    private function getTeacherAttendanceOverview($teacherId, $academicYearId = null, $semesterId = null)
    {
        // Get sections taught by this teacher
        $sections = Section::whereHas('subjects.teachers', function($q) use ($teacherId) {
            $q->where('teacher_id', $teacherId);
        })->pluck('id');

        $query = Attendance::whereIn('section_id', $sections);

        if ($academicYearId) $query->where('academic_year_id', $academicYearId);
        if ($semesterId) $query->where('semester_id', $semesterId);

        $attendance = $query->get();

        $overview = [];
        foreach ($attendance->groupBy('section_id') as $sectionId => $sectionAttendance) {
            $section = Section::find($sectionId);
            $totalRecords = $sectionAttendance->count();
            $presentRecords = $sectionAttendance->where('status', 'present')->count();
            
            $overview[] = [
                'section' => $section->name,
                'total_records' => $totalRecords,
                'present_records' => $presentRecords,
                'attendance_rate' => $totalRecords > 0 ? round(($presentRecords / $totalRecords) * 100, 2) : 0
            ];
        }

        return $overview;
    }

    /**
     * Get teacher assessment breakdown
     */
    private function getTeacherAssessmentBreakdown($teacherId, $academicYearId = null, $semesterId = null)
    {
        $query = Grade::whereHas('subject.teachers', function($q) use ($teacherId) {
            $q->where('teacher_id', $teacherId);
        })->with('component');

        if ($academicYearId) $query->where('academic_year_id', $academicYearId);
        if ($semesterId) $query->where('semester_id', $semesterId);

        $grades = $query->get();

        $breakdown = [];
        foreach ($grades->groupBy('component_id') as $componentId => $componentGrades) {
            $component = $componentGrades->first()->component;
            $breakdown[] = [
                'assessment_type' => $component->name,
                'count' => $componentGrades->count(),
                'average_score' => round($componentGrades->avg('percentage'), 2),
                'weight' => $component->weight ?? 0
            ];
        }

        return $breakdown;
    }

    /**
     * Get teacher recent grades
     */
    private function getTeacherRecentGrades($teacherId)
    {
        return Grade::whereHas('subject.teachers', function($q) use ($teacherId) {
            $q->where('teacher_id', $teacherId);
        })
        ->with(['student', 'subject'])
        ->orderBy('created_at', 'desc')
        ->take(10)
        ->get()
        ->map(function($grade) {
            return [
                'student_name' => $grade->student->first_name . ' ' . $grade->student->last_name,
                'subject' => $grade->subject->subject_name,
                'score' => $grade->percentage,
                'date' => $grade->created_at->format('M d, Y')
            ];
        });
    }

    /**
     * Get teacher class performance
     */
    private function getTeacherClassPerformance($teacherId, $academicYearId = null, $semesterId = null)
    {
        $query = Grade::whereHas('subject.teachers', function($q) use ($teacherId) {
            $q->where('teacher_id', $teacherId);
        })->with(['student.sections', 'subject']);

        if ($academicYearId) $query->where('academic_year_id', $academicYearId);
        if ($semesterId) $query->where('semester_id', $semesterId);

        $grades = $query->get();

        $classPerformance = [];
        foreach ($grades->groupBy('student.sections.first.id') as $sectionId => $sectionGrades) {
            $section = Section::find($sectionId);
            if (!$section) continue;

            $classPerformance[] = [
                'section' => $section->name,
                'average_score' => round($sectionGrades->avg('percentage'), 2),
                'students_count' => $sectionGrades->groupBy('student_id')->count(),
                'assignments_count' => $sectionGrades->count()
            ];
        }

        return $classPerformance;
    }

    /**
     * Get school overview KPIs
     */
    private function getSchoolOverview($academicYearId = null, $semesterId = null)
    {
        $gradeQuery = Grade::query();
        if ($academicYearId) {
            $gradeQuery->where('academic_year_id', $academicYearId);
        }
        if ($semesterId) {
            $gradeQuery->where('semester_id', $semesterId);
        }

        $gradeStats = $gradeQuery
            ->selectRaw('COUNT(*) as total_grades')
            ->selectRaw('AVG(percentage) as average_score')
            ->selectRaw('SUM(CASE WHEN percentage >= 60 THEN 1 ELSE 0 END) as passing_grades')
            ->first();

        $totalGrades = (int) ($gradeStats->total_grades ?? 0);
        $passing = (int) ($gradeStats->passing_grades ?? 0);

        $attendanceQuery = Attendance::query();
        if ($academicYearId) {
            $attendanceQuery->where('academic_year_id', $academicYearId);
        }
        if ($semesterId) {
            $attendanceQuery->where('semester_id', $semesterId);
        }
        $attendanceStats = $attendanceQuery
            ->selectRaw('COUNT(*) as total')
            ->selectRaw("SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present")
            ->first();
        $attTotal = (int) ($attendanceStats->total ?? 0);
        $attPresent = (int) ($attendanceStats->present ?? 0);

        $parentsQuery = DB::table('users')->where('role_name', 'Parent');
        if (Schema::hasColumn('users', 'deleted_at')) {
            $parentsQuery->whereNull('deleted_at');
        }

        return [
            'total_students' => Student::count(),
            'total_teachers' => Teacher::count(),
            'total_parents' => $parentsQuery->count(),
            'total_subjects' => Subject::count(),
            'total_sections' => Section::count(),
            'average_score' => round((float) ($gradeStats->average_score ?? 0), 2),
            'total_assignments' => $totalGrades,
            'pass_rate' => $totalGrades > 0 ? round(($passing / $totalGrades) * 100, 2) : 0,
            'attendance_rate' => $attTotal > 0 ? round(($attPresent / $attTotal) * 100, 2) : 0,
        ];
    }

    /**
     * Enrollment application pipeline (live registrar queue).
     */
    private function getEnrollmentOverview(): array
    {
        if (!class_exists(\App\Models\EnrollmentApplication::class) || !Schema::hasTable('enrollment_applications')) {
            return [
                'total' => 0,
                'pending' => 0,
                'under_review' => 0,
                'approved' => 0,
                'rejected' => 0,
                'needs_documents' => 0,
                'by_grade' => [],
            ];
        }

        $rows = \App\Models\EnrollmentApplication::query()
            ->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        $byGrade = \App\Models\EnrollmentApplication::query()
            ->select('grade_level_applying_for', DB::raw('COUNT(*) as total'))
            ->whereNotNull('grade_level_applying_for')
            ->groupBy('grade_level_applying_for')
            ->orderBy('grade_level_applying_for')
            ->get()
            ->map(fn ($r) => [
                'grade' => $r->grade_level_applying_for,
                'count' => (int) $r->total,
            ])
            ->values()
            ->all();

        return [
            'total' => (int) $rows->sum(),
            'pending' => (int) ($rows['pending'] ?? 0),
            'under_review' => (int) ($rows['under_review'] ?? 0),
            'approved' => (int) ($rows['approved'] ?? 0),
            'rejected' => (int) ($rows['rejected'] ?? 0),
            'needs_documents' => (int) ($rows['needs_documents'] ?? 0),
            'by_grade' => $byGrade,
        ];
    }

    /**
     * Active student headcount by class/grade label.
     */
    private function getStudentsByGrade(): array
    {
        return Student::query()
            ->select(DB::raw("COALESCE(NULLIF(TRIM(class), ''), NULLIF(TRIM(year_level), ''), 'Unassigned') as grade_label"))
            ->selectRaw('COUNT(*) as total')
            ->groupBy('grade_label')
            ->orderBy('grade_label')
            ->get()
            ->map(fn ($r) => [
                'grade' => $r->grade_label,
                'count' => (int) $r->total,
            ])
            ->values()
            ->all();
    }

    /**
     * Get GPA comparison
     */
    private function getGpaComparison($academicYearId = null, $semesterId = null)
    {
        $query = DB::table('student_gpa')
            ->leftJoin('students', 'student_gpa.student_id', '=', 'students.id')
            ->select(
                'student_gpa.gpa',
                DB::raw("COALESCE(students.year_level, students.class, 'N/A') as grade_level")
            );

        if ($academicYearId) {
            $query->where('student_gpa.academic_year_id', $academicYearId);
        }
        if ($semesterId) {
            $query->where('student_gpa.semester_id', $semesterId);
        }

        $gpaData = $query->get();

        $comparison = [];
        foreach ($gpaData->groupBy('grade_level') as $gradeLevel => $gradeGpas) {
            $comparison[] = [
                'grade_level' => (string) $gradeLevel,
                'average_gpa' => round((float) $gradeGpas->avg('gpa'), 2),
                'students_count' => $gradeGpas->count(),
                'highest_gpa' => $gradeGpas->max('gpa'),
                'lowest_gpa' => $gradeGpas->min('gpa'),
            ];
        }

        return $comparison;
    }

    /**
     * Get pass/fail rates
     */
    private function getPassFailRates($academicYearId = null, $semesterId = null)
    {
        $query = Grade::query();

        if ($academicYearId) $query->where('academic_year_id', $academicYearId);
        if ($semesterId) $query->where('semester_id', $semesterId);

        $grades = $query->with('subject')->get();

        $rates = [];
        foreach ($grades->groupBy('subject_id') as $subjectId => $subjectGrades) {
            $subject = $subjectGrades->first()->subject;
            $totalGrades = $subjectGrades->count();
            $passingGrades = $subjectGrades->where('percentage', '>=', 60)->count();

            $rates[] = [
                'subject' => $subject->subject_name,
                'pass_rate' => $totalGrades > 0 ? round(($passingGrades / $totalGrades) * 100, 2) : 0,
                'fail_rate' => $totalGrades > 0 ? round((($totalGrades - $passingGrades) / $totalGrades) * 100, 2) : 0,
                'total_students' => $totalGrades
            ];
        }

        return $rates;
    }

    /**
     * Get school attendance summary
     */
    private function getSchoolAttendanceSummary($academicYearId = null, $semesterId = null)
    {
        $query = Attendance::query();

        if ($academicYearId) $query->where('academic_year_id', $academicYearId);
        if ($semesterId) $query->where('semester_id', $semesterId);

        $attendance = $query->get();

        $monthlyData = [];
        foreach ($attendance as $record) {
            // Ensure date is a Carbon instance
            $date = $record->date instanceof Carbon ? $record->date : Carbon::parse($record->date);
            $month = $date->format('Y-m');
            if (!isset($monthlyData[$month])) {
                $monthlyData[$month] = ['present' => 0, 'total' => 0];
            }
            $monthlyData[$month]['total']++;
            if ($record->status === 'present') {
                $monthlyData[$month]['present']++;
            }
        }

        $summary = [];
        foreach ($monthlyData as $month => $data) {
            $summary[] = [
                'month' => Carbon::createFromFormat('Y-m', $month)->format('M Y'),
                'attendance_rate' => round(($data['present'] / $data['total']) * 100, 2),
                'present' => $data['present'],
                'total' => $data['total']
            ];
        }

        return $summary;
    }

    /**
     * Get school subject performance
     */
    private function getSchoolSubjectPerformance($academicYearId = null, $semesterId = null)
    {
        $query = Grade::query();

        if ($academicYearId) $query->where('academic_year_id', $academicYearId);
        if ($semesterId) $query->where('semester_id', $semesterId);

        $grades = $query->with('subject')->get();

        $performance = [];
        foreach ($grades->groupBy('subject_id') as $subjectId => $subjectGrades) {
            $subject = $subjectGrades->first()->subject;
            $performance[] = [
                'subject' => $subject->subject_name,
                'average_score' => round($subjectGrades->avg('percentage'), 2),
                'students_count' => $subjectGrades->groupBy('student_id')->count(),
                'assignments_count' => $subjectGrades->count()
            ];
        }

        return $performance;
    }

    /**
     * Section comparison via student_section_assignments (real section links).
     */
    private function getSectionComparison($academicYearId = null, $semesterId = null)
    {
        if (!Schema::hasTable('student_section_assignments')) {
            return [];
        }

        $gradesQuery = Grade::query();
        if ($academicYearId) {
            $gradesQuery->where('academic_year_id', $academicYearId);
        }
        if ($semesterId) {
            $gradesQuery->where('semester_id', $semesterId);
        }

        $gradeRows = $gradesQuery
            ->select('student_id', DB::raw('AVG(percentage) as avg_score'), DB::raw('COUNT(*) as grade_count'))
            ->groupBy('student_id')
            ->get()
            ->keyBy('student_id');

        if ($gradeRows->isEmpty()) {
            return [];
        }

        $assignQuery = DB::table('student_section_assignments')->select('section_id', 'student_id');
        if ($academicYearId && Schema::hasColumn('student_section_assignments', 'academic_year_id')) {
            $assignQuery->where('academic_year_id', $academicYearId);
        }
        if ($semesterId && Schema::hasColumn('student_section_assignments', 'semester_id')) {
            $assignQuery->where('semester_id', $semesterId);
        }

        $assignments = $assignQuery->get()->groupBy('section_id');
        $sections = Section::whereIn('id', $assignments->keys())->get()->keyBy('id');

        $comparison = [];
        foreach ($assignments as $sectionId => $rows) {
            $section = $sections->get($sectionId);
            if (!$section) {
                continue;
            }

            $scores = [];
            $gradeCount = 0;
            foreach ($rows as $row) {
                $g = $gradeRows->get($row->student_id);
                if (!$g) {
                    continue;
                }
                $scores[] = (float) $g->avg_score;
                $gradeCount += (int) $g->grade_count;
            }

            if (empty($scores)) {
                continue;
            }

            $comparison[] = [
                'section' => $section->name . ($section->grade_level ? ' (' . $section->grade_level . ')' : ''),
                'average_score' => round(array_sum($scores) / count($scores), 2),
                'students_count' => count($scores),
                'assignments_count' => $gradeCount,
            ];
        }

        usort($comparison, fn ($a, $b) => $b['average_score'] <=> $a['average_score']);

        return $comparison;
    }

    /**
     * Get performance level
     */
    private function getPerformanceLevel($averageScore)
    {
        if ($averageScore >= 90) return 'Excellent';
        if ($averageScore >= 80) return 'Good';
        if ($averageScore >= 70) return 'Average';
        if ($averageScore >= 60) return 'Below Average';
        return 'Needs Improvement';
    }
} 