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
            'gpa_comparison' => $this->getGpaComparison($academicYearId, $semesterId),
            'pass_fail_rates' => $this->getPassFailRates($academicYearId, $semesterId),
            'attendance_summary' => $this->getSchoolAttendanceSummary($academicYearId, $semesterId),
            'subject_performance' => $this->getSchoolSubjectPerformance($academicYearId, $semesterId),
            'section_comparison' => $this->getSectionComparison($academicYearId, $semesterId)
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
            $period = $grade->academicYear->name . ' - ' . $grade->semester->name;
            $trends[] = [
                'period' => $period,
                'subject' => $grade->subject->subject_name,
                'score' => $grade->percentage,
                'date' => $grade->created_at->format('Y-m-d')
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
                'percentage' => round(($data['present'] / $data['total']) * 100, 2),
                'present' => $data['present'],
                'total' => $data['total']
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
        $query = DB::table('student_gpa')->where('student_id', $studentId);

        if ($academicYearId) $query->where('academic_year_id', $academicYearId);
        if ($semesterId) $query->where('semester_id', $semesterId);

        return $query->orderBy('created_at')
            ->get()
            ->map(function($gpa) {
                return [
                    'period' => $gpa->academic_year_name . ' - ' . $gpa->semester_name,
                    'gpa' => $gpa->gpa,
                    'letter_grade' => $gpa->letter_grade
                ];
            });
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
     * Get school overview
     */
    private function getSchoolOverview($academicYearId = null, $semesterId = null)
    {
        $query = Grade::query();

        if ($academicYearId) $query->where('academic_year_id', $academicYearId);
        if ($semesterId) $query->where('semester_id', $semesterId);

        $grades = $query->get();

        $totalStudents = Student::count();
        $totalTeachers = Teacher::count();
        $totalSubjects = Subject::count();

        return [
            'total_students' => $totalStudents,
            'total_teachers' => $totalTeachers,
            'total_subjects' => $totalSubjects,
            'average_score' => round($grades->avg('percentage'), 2),
            'total_assignments' => $grades->count(),
            'pass_rate' => $grades->count() > 0 ? round(($grades->where('percentage', '>=', 60)->count() / $grades->count()) * 100, 2) : 0
        ];
    }

    /**
     * Get GPA comparison
     */
    private function getGpaComparison($academicYearId = null, $semesterId = null)
    {
        $query = DB::table('student_gpa');

        if ($academicYearId) $query->where('academic_year_id', $academicYearId);
        if ($semesterId) $query->where('semester_id', $semesterId);

        $gpaData = $query->get();

        $comparison = [];
        foreach ($gpaData->groupBy('grade_level') as $gradeLevel => $gradeGpas) {
            $comparison[] = [
                'grade_level' => 'Grade ' . $gradeLevel,
                'average_gpa' => round($gradeGpas->avg('gpa'), 2),
                'students_count' => $gradeGpas->count(),
                'highest_gpa' => $gradeGpas->max('gpa'),
                'lowest_gpa' => $gradeGpas->min('gpa')
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
     * Get section comparison
     */
    private function getSectionComparison($academicYearId = null, $semesterId = null)
    {
        $query = Grade::query();

        if ($academicYearId) $query->where('academic_year_id', $academicYearId);
        if ($semesterId) $query->where('semester_id', $semesterId);

        $grades = $query->with(['student.sections'])->get();

        $comparison = [];
        foreach ($grades->groupBy('student.sections.first.id') as $sectionId => $sectionGrades) {
            $section = Section::find($sectionId);
            if (!$section) continue;

            $comparison[] = [
                'section' => $section->name,
                'average_score' => round($sectionGrades->avg('percentage'), 2),
                'students_count' => $sectionGrades->groupBy('student_id')->count(),
                'assignments_count' => $sectionGrades->count()
            ];
        }

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