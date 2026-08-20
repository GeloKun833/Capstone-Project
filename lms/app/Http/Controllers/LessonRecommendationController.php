<?php

namespace App\Http\Controllers;

use App\Services\LessonRecommendationService;
use App\Models\Student;
use App\Models\Subject;
use App\Models\AcademicYear;
use App\Models\Semester;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LessonRecommendationController extends Controller
{
    protected $recommendationService;

    public function __construct(LessonRecommendationService $recommendationService)
    {
        $this->recommendationService = $recommendationService;
    }

    /**
     * Show performance analysis and recommendations for a student
     */
    public function studentAnalysis(Request $request)
    {
        $studentId = $request->get('student_id');
        $academicYearId = $request->get('academic_year_id');
        $semesterId = $request->get('semester_id');

        $students = Student::all();
        $subjects = Subject::all();
        $academicYears = AcademicYear::all();
        $semesters = Semester::all();

        $analysis = null;
        $recommendations = null;
        $classComparison = null;

        if ($studentId) {
            $analysis = $this->recommendationService->analyzeStudentPerformance($studentId, $academicYearId, $semesterId);
            $recommendations = $this->recommendationService->getRecommendedLessons($studentId, 10);
            
            if ($request->get('subject_id')) {
                $classComparison = $this->recommendationService->getClassComparison(
                    $studentId, 
                    $request->get('subject_id'), 
                    $academicYearId, 
                    $semesterId
                );
            }
        }

        return view('lessons.recommendations.student-analysis', compact(
            'students',
            'subjects',
            'academicYears',
            'semesters',
            'analysis',
            'recommendations',
            'classComparison',
            'studentId',
            'academicYearId',
            'semesterId'
        ));
    }

    /**
     * Show recommendations for current student (student dashboard)
     */
    public function myRecommendations(Request $request)
    {
        $student = Auth::user()->student;
        if (!$student) {
            return redirect()->back()->with('error', 'Student profile not found.');
        }

        $academicYearId = $request->get('academic_year_id');
        $semesterId = $request->get('semester_id');

        $analysis = $this->recommendationService->analyzeStudentPerformance(
            $student->id, 
            $academicYearId, 
            $semesterId
        );
        
        $recommendations = $this->recommendationService->getRecommendedLessons($student->id, 5);

        return view('lessons.recommendations.my-recommendations', compact(
            'analysis',
            'recommendations',
            'academicYearId',
            'semesterId'
        ));
    }

    /**
     * Show class-wide performance analysis (for teachers)
     */
    public function classAnalysis(Request $request)
    {
        // Check if user is a teacher
        if (Auth::user()->role_name !== 'Teacher') {
            abort(403, 'Unauthorized action.');
        }

        $teacher = Auth::user()->teacher;
        if (!$teacher) {
            return redirect()->back()->with('error', 'Teacher profile not found.');
        }

        $subjectId = $request->get('subject_id');
        $sectionId = $request->get('section_id');
        $academicYearId = $request->get('academic_year_id');
        $semesterId = $request->get('semester_id');

        $subjects = Subject::all();
        $academicYears = AcademicYear::all();
        $semesters = Semester::all();

        $classAnalysis = null;
        $weakStudents = null;
        $recommendations = null;

        if ($subjectId && $sectionId) {
            $classAnalysis = $this->getClassAnalysis($subjectId, $sectionId, $academicYearId, $semesterId);
            $weakStudents = $this->getWeakStudents($subjectId, $sectionId, $academicYearId, $semesterId);
            $recommendations = $this->getClassRecommendations($subjectId, $sectionId);
        }

        return view('lessons.recommendations.class-analysis', compact(
            'subjects',
            'academicYears',
            'semesters',
            'classAnalysis',
            'weakStudents',
            'recommendations',
            'subjectId',
            'sectionId',
            'academicYearId',
            'semesterId'
        ));
    }

    /**
     * Get class performance analysis
     */
    private function getClassAnalysis($subjectId, $sectionId, $academicYearId = null, $semesterId = null)
    {
        $query = \App\Models\Grade::where('subject_id', $subjectId)
            ->whereHas('student.sections', function($q) use ($sectionId) {
                $q->where('section_id', $sectionId);
            });

        if ($academicYearId) $query->where('academic_year_id', $academicYearId);
        if ($semesterId) $query->where('semester_id', $semesterId);

        $grades = $query->get();

        if ($grades->isEmpty()) {
            return null;
        }

        $totalStudents = $grades->groupBy('student_id')->count();
        $averageScore = $grades->avg('percentage');
        $highestScore = $grades->max('percentage');
        $lowestScore = $grades->min('percentage');

        // Grade distribution
        $gradeDistribution = [
            'A' => $grades->where('percentage', '>=', 90)->count(),
            'B' => $grades->whereBetween('percentage', [80, 89])->count(),
            'C' => $grades->whereBetween('percentage', [70, 79])->count(),
            'D' => $grades->whereBetween('percentage', [60, 69])->count(),
            'F' => $grades->where('percentage', '<', 60)->count(),
        ];

        return [
            'total_students' => $totalStudents,
            'average_score' => round($averageScore, 2),
            'highest_score' => round($highestScore, 2),
            'lowest_score' => round($lowestScore, 2),
            'grade_distribution' => $gradeDistribution,
            'total_assignments' => $grades->count()
        ];
    }

    /**
     * Get students with weak performance
     */
    private function getWeakStudents($subjectId, $sectionId, $academicYearId = null, $semesterId = null)
    {
        $query = \App\Models\Grade::where('subject_id', $subjectId)
            ->whereHas('student.sections', function($q) use ($sectionId) {
                $q->where('section_id', $sectionId);
            });

        if ($academicYearId) $query->where('academic_year_id', $academicYearId);
        if ($semesterId) $query->where('semester_id', $semesterId);

        $weakStudents = $query->with(['student'])
            ->get()
            ->groupBy('student_id')
            ->map(function($grades) {
                return [
                    'student' => $grades->first()->student,
                    'average_score' => round($grades->avg('percentage'), 2),
                    'assignments_count' => $grades->count()
                ];
            })
            ->filter(function($data) {
                return $data['average_score'] < 75; // Below 75% is considered weak
            })
            ->sortBy('average_score')
            ->values();

        return $weakStudents;
    }

    /**
     * Get recommendations for class improvement
     */
    private function getClassRecommendations($subjectId, $sectionId)
    {
        // Get lessons that could help improve class performance
        $lessons = \App\Models\Lesson::where('subject_id', $subjectId)
            ->where('status', 'published')
            ->where('is_active', true)
            ->with(['curriculumObjective', 'activities'])
            ->get();

        $recommendations = collect();

        foreach ($lessons as $lesson) {
            $relevanceScore = $this->calculateClassRelevanceScore($lesson, $subjectId);
            
            if ($relevanceScore > 0) {
                $lesson->relevance_score = $relevanceScore;
                $lesson->relevance_reason = "Could help improve overall class performance in this subject";
                $recommendations->push($lesson);
            }
        }

        return $recommendations->sortByDesc('relevance_score')->take(5);
    }

    /**
     * Calculate relevance score for class recommendations
     */
    private function calculateClassRelevanceScore($lesson, $subjectId)
    {
        $score = 0;

        // Check if lesson has activities
        if ($lesson->activities->count() > 0) {
            $score += 5;
        }

        // Check if lesson is recent
        if ($lesson->lesson_date->isAfter(\Carbon\Carbon::now()->subMonths(6))) {
            $score += 3;
        }

        // Check curriculum objective relevance
        if ($lesson->curriculumObjective) {
            $score += 2;
        }

        return $score;
    }

    /**
     * Export performance analysis report
     */
    public function exportAnalysis(Request $request)
    {
        $studentId = $request->get('student_id');
        $academicYearId = $request->get('academic_year_id');
        $semesterId = $request->get('semester_id');

        if (!$studentId) {
            return redirect()->back()->with('error', 'Student ID is required.');
        }

        $analysis = $this->recommendationService->analyzeStudentPerformance($studentId, $academicYearId, $semesterId);
        $recommendations = $this->recommendationService->getRecommendedLessons($studentId, 10);

        $filename = 'performance_analysis_' . $studentId . '_' . date('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($analysis, $recommendations) {
            $file = fopen('php://output', 'w');
            
            // Performance Summary
            fputcsv($file, ['Performance Analysis Report']);
            fputcsv($file, ['Student', $analysis['student']->first_name . ' ' . $analysis['student']->last_name]);
            fputcsv($file, ['Overall Average', $analysis['overall_average'] . '%']);
            fputcsv($file, ['Total Subjects', $analysis['total_subjects']]);
            fputcsv($file, ['Improvement Needed', $analysis['improvement_needed'] . '%']);
            fputcsv($file, []);
            
            // Subject Performance
            fputcsv($file, ['Subject Performance']);
            fputcsv($file, ['Subject', 'Average Score', 'Total Assignments', 'Weak Topics']);
            
            foreach ($analysis['subject_performance'] as $subject) {
                fputcsv($file, [
                    $subject['subject_name'],
                    $subject['average_score'] . '%',
                    $subject['total_assignments'],
                    implode(', ', $subject['weak_topics'])
                ]);
            }
            
            fputcsv($file, []);
            
            // Recommended Lessons
            fputcsv($file, ['Recommended Lessons']);
            fputcsv($file, ['Lesson Title', 'Subject', 'Relevance Score', 'Relevance Reason']);
            
            foreach ($recommendations as $lesson) {
                fputcsv($file, [
                    $lesson->title,
                    $lesson->subject->subject_name,
                    $lesson->relevance_score,
                    $lesson->relevance_reason
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
} 