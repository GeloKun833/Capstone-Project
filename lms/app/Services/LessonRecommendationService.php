<?php

namespace App\Services;

use App\Models\Student;
use App\Models\Lesson;
use App\Models\Activity;
use App\Models\ActivitySubmission;
use App\Models\Grade;
use App\Models\Subject;
use App\Models\CurriculumObjective;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class LessonRecommendationService
{
    /**
     * Analyze student performance and identify weak areas
     */
    public function analyzeStudentPerformance($studentId, $academicYearId = null, $semesterId = null)
    {
        $student = Student::findOrFail($studentId);
        
        // Get student's grades
        $gradesQuery = Grade::where('student_id', $studentId);
        
        if ($academicYearId) {
            $gradesQuery->where('academic_year_id', $academicYearId);
        }
        
        if ($semesterId) {
            $gradesQuery->where('semester_id', $semesterId);
        }
        
        $grades = $gradesQuery->with(['subject', 'component'])->get();
        
        // Get activity submissions
        $submissionsQuery = ActivitySubmission::whereHas('activity.lesson', function($query) use ($student) {
            $query->whereHas('section.students', function($q) use ($student) {
                $q->where('student_id', $student->id);
            });
        });
        
        if ($academicYearId) {
            $submissionsQuery->whereHas('activity.lesson', function($query) use ($academicYearId) {
                $query->where('academic_year_id', $academicYearId);
            });
        }
        
        if ($semesterId) {
            $submissionsQuery->whereHas('activity.lesson', function($query) use ($semesterId) {
                $query->where('semester_id', $semesterId);
            });
        }
        
        $submissions = $submissionsQuery->with(['activity.lesson.subject'])->get();
        
        // Analyze performance by subject
        $subjectPerformance = $this->analyzeSubjectPerformance($grades, $submissions);
        
        // Identify weak areas (subjects with low performance)
        $weakAreas = $this->identifyWeakAreas($subjectPerformance);
        
        // Get performance trends
        $trends = $this->getPerformanceTrends($studentId, $academicYearId);
        
        return [
            'student' => $student,
            'subject_performance' => $subjectPerformance,
            'weak_areas' => $weakAreas,
            'trends' => $trends,
            'overall_average' => $this->calculateOverallAverage($subjectPerformance),
            'total_subjects' => count($subjectPerformance),
            'improvement_needed' => $this->calculateImprovementNeeded($subjectPerformance)
        ];
    }
    
    /**
     * Get recommended lessons based on performance gaps
     */
    public function getRecommendedLessons($studentId, $limit = 5)
    {
        $analysis = $this->analyzeStudentPerformance($studentId);
        $weakAreas = $analysis['weak_areas'];
        
        $recommendedLessons = collect();
        
        foreach ($weakAreas as $weakArea) {
            $lessons = $this->findRelevantLessons($weakArea['subject_id'], $weakArea['topics'], $limit);
            $recommendedLessons = $recommendedLessons->merge($lessons);
        }
        
        // Remove duplicates and sort by relevance
        $recommendedLessons = $recommendedLessons->unique('id')
            ->sortByDesc('relevance_score')
            ->take($limit);
            
        return $recommendedLessons;
    }
    
    /**
     * Find lessons relevant to weak areas
     */
    private function findRelevantLessons($subjectId, $topics, $limit)
    {
        $lessons = Lesson::where('subject_id', $subjectId)
            ->where('status', 'published')
            ->where('is_active', true)
            ->with(['curriculumObjective', 'activities'])
            ->get();
            
        $relevantLessons = collect();
        
        foreach ($lessons as $lesson) {
            $relevanceScore = $this->calculateRelevanceScore($lesson, $topics);
            
            if ($relevanceScore > 0) {
                $lesson->relevance_score = $relevanceScore;
                $lesson->relevance_reason = $this->getRelevanceReason($lesson, $topics);
                $relevantLessons->push($lesson);
            }
        }
        
        return $relevantLessons->sortByDesc('relevance_score')->take($limit);
    }
    
    /**
     * Calculate relevance score for a lesson
     */
    private function calculateRelevanceScore($lesson, $topics)
    {
        $score = 0;
        
        // Check if lesson's curriculum objective matches weak topics
        if ($lesson->curriculumObjective) {
            $objectiveTitle = strtolower($lesson->curriculumObjective->title);
            $objectiveDescription = strtolower($lesson->curriculumObjective->description);
            
            foreach ($topics as $topic) {
                $topicLower = strtolower($topic);
                
                if (str_contains($objectiveTitle, $topicLower) || 
                    str_contains($objectiveDescription, $topicLower)) {
                    $score += 10;
                }
            }
        }
        
        // Check lesson title and description
        $lessonTitle = strtolower($lesson->title);
        $lessonDescription = strtolower($lesson->description);
        
        foreach ($topics as $topic) {
            $topicLower = strtolower($topic);
            
            if (str_contains($lessonTitle, $topicLower)) {
                $score += 8;
            }
            
            if (str_contains($lessonDescription, $topicLower)) {
                $score += 5;
            }
        }
        
        // Bonus for recent lessons
        if ($lesson->lesson_date->isAfter(Carbon::now()->subMonths(3))) {
            $score += 2;
        }
        
        return $score;
    }
    
    /**
     * Get relevance reason for a lesson
     */
    private function getRelevanceReason($lesson, $topics)
    {
        $reasons = [];
        
        if ($lesson->curriculumObjective) {
            $objectiveTitle = strtolower($lesson->curriculumObjective->title);
            
            foreach ($topics as $topic) {
                $topicLower = strtolower($topic);
                if (str_contains($objectiveTitle, $topicLower)) {
                    $reasons[] = "Covers {$topic} in curriculum objective";
                }
            }
        }
        
        $lessonTitle = strtolower($lesson->title);
        foreach ($topics as $topic) {
            $topicLower = strtolower($topic);
            if (str_contains($lessonTitle, $topicLower)) {
                $reasons[] = "Directly addresses {$topic}";
            }
        }
        
        return implode(', ', $reasons);
    }
    
    /**
     * Analyze performance by subject
     */
    private function analyzeSubjectPerformance($grades, $submissions)
    {
        $subjectPerformance = [];
        
        // Analyze grades
        foreach ($grades as $grade) {
            $subjectId = $grade->subject_id;
            $subjectName = $grade->subject->subject_name;
            
            if (!isset($subjectPerformance[$subjectId])) {
                $subjectPerformance[$subjectId] = [
                    'subject_id' => $subjectId,
                    'subject_name' => $subjectName,
                    'grades' => [],
                    'submissions' => [],
                    'average_score' => 0,
                    'total_assignments' => 0,
                    'weak_topics' => []
                ];
            }
            
            $subjectPerformance[$subjectId]['grades'][] = $grade;
        }
        
        // Analyze submissions
        foreach ($submissions as $submission) {
            $subjectId = $submission->activity->lesson->subject_id;
            $subjectName = $submission->activity->lesson->subject->subject_name;
            
            if (!isset($subjectPerformance[$subjectId])) {
                $subjectPerformance[$subjectId] = [
                    'subject_id' => $subjectId,
                    'subject_name' => $subjectName,
                    'grades' => [],
                    'submissions' => [],
                    'average_score' => 0,
                    'total_assignments' => 0,
                    'weak_topics' => []
                ];
            }
            
            $subjectPerformance[$subjectId]['submissions'][] = $submission;
        }
        
        // Calculate averages and identify weak topics
        foreach ($subjectPerformance as &$subject) {
            $totalScore = 0;
            $totalPossible = 0;
            
            // Calculate from grades
            foreach ($subject['grades'] as $grade) {
                $totalScore += $grade->percentage;
                $totalPossible += 100;
            }
            
            // Calculate from submissions
            foreach ($subject['submissions'] as $submission) {
                if ($submission->status === 'graded') {
                    $totalScore += $submission->percentage;
                    $totalPossible += 100;
                }
            }
            
            $subject['total_assignments'] = count($subject['grades']) + count($subject['submissions']);
            $subject['average_score'] = $totalPossible > 0 ? ($totalScore / $totalPossible) * 100 : 0;
            
            // Identify weak topics based on low grades
            $subject['weak_topics'] = $this->identifyWeakTopics($subject);
        }
        
        return $subjectPerformance;
    }
    
    /**
     * Identify weak areas (subjects with low performance)
     */
    private function identifyWeakAreas($subjectPerformance)
    {
        $weakAreas = [];
        $threshold = 75; // Consider below 75% as weak
        
        foreach ($subjectPerformance as $subject) {
            if ($subject['average_score'] < $threshold && $subject['total_assignments'] > 0) {
                $weakAreas[] = [
                    'subject_id' => $subject['subject_id'],
                    'subject_name' => $subject['subject_name'],
                    'average_score' => $subject['average_score'],
                    'topics' => $subject['weak_topics']
                ];
            }
        }
        
        // Sort by lowest performance first
        usort($weakAreas, function($a, $b) {
            return $a['average_score'] <=> $b['average_score'];
        });
        
        return $weakAreas;
    }
    
    /**
     * Identify weak topics within a subject
     */
    private function identifyWeakTopics($subject)
    {
        $weakTopics = [];
        $topicScores = [];
        
        // Analyze grades by component/topic
        foreach ($subject['grades'] as $grade) {
            $topic = $grade->component->name ?? 'General';
            
            if (!isset($topicScores[$topic])) {
                $topicScores[$topic] = ['total' => 0, 'count' => 0];
            }
            
            $topicScores[$topic]['total'] += $grade->percentage;
            $topicScores[$topic]['count']++;
        }
        
        // Calculate averages and identify weak topics
        foreach ($topicScores as $topic => $scores) {
            $average = $scores['total'] / $scores['count'];
            if ($average < 75) {
                $weakTopics[] = $topic;
            }
        }
        
        return $weakTopics;
    }
    
    /**
     * Get performance trends over time
     */
    private function getPerformanceTrends($studentId, $academicYearId = null)
    {
        $trends = [];
        
        // Get monthly performance data
        $monthlyData = Grade::where('student_id', $studentId)
            ->when($academicYearId, function($query) use ($academicYearId) {
                return $query->where('academic_year_id', $academicYearId);
            })
            ->selectRaw('MONTH(created_at) as month, AVG(percentage) as average_score, COUNT(*) as assignments')
            ->groupBy('month')
            ->orderBy('month')
            ->get();
            
        foreach ($monthlyData as $data) {
            $trends[] = [
                'month' => $data->month,
                'average_score' => round($data->average_score, 2),
                'assignments' => $data->assignments
            ];
        }
        
        return $trends;
    }
    
    /**
     * Calculate overall average
     */
    private function calculateOverallAverage($subjectPerformance)
    {
        $totalScore = 0;
        $totalSubjects = 0;
        
        foreach ($subjectPerformance as $subject) {
            if ($subject['total_assignments'] > 0) {
                $totalScore += $subject['average_score'];
                $totalSubjects++;
            }
        }
        
        return $totalSubjects > 0 ? round($totalScore / $totalSubjects, 2) : 0;
    }
    
    /**
     * Calculate improvement needed
     */
    private function calculateImprovementNeeded($subjectPerformance)
    {
        $targetScore = 85; // Target average score
        $currentAverage = $this->calculateOverallAverage($subjectPerformance);
        
        return max(0, $targetScore - $currentAverage);
    }
    
    /**
     * Get performance comparison with class average
     */
    public function getClassComparison($studentId, $subjectId, $academicYearId = null, $semesterId = null)
    {
        // Get student's average
        $studentQuery = Grade::where('student_id', $studentId)->where('subject_id', $subjectId);
        if ($academicYearId) $studentQuery->where('academic_year_id', $academicYearId);
        if ($semesterId) $studentQuery->where('semester_id', $semesterId);
        
        $studentAverage = $studentQuery->avg('percentage') ?? 0;
        
        // Get class average
        $classQuery = Grade::where('subject_id', $subjectId);
        if ($academicYearId) $classQuery->where('academic_year_id', $academicYearId);
        if ($semesterId) $classQuery->where('semester_id', $semesterId);
        
        $classAverage = $classQuery->avg('percentage') ?? 0;
        
        return [
            'student_average' => round($studentAverage, 2),
            'class_average' => round($classAverage, 2),
            'difference' => round($studentAverage - $classAverage, 2),
            'percentile' => $this->calculatePercentile($studentId, $subjectId, $academicYearId, $semesterId)
        ];
    }
    
    /**
     * Calculate student's percentile in class
     */
    private function calculatePercentile($studentId, $subjectId, $academicYearId = null, $semesterId = null)
    {
        $query = Grade::where('subject_id', $subjectId);
        if ($academicYearId) $query->where('academic_year_id', $academicYearId);
        if ($semesterId) $query->where('semester_id', $semesterId);
        
        $allScores = $query->pluck('percentage')->sort()->values();
        
        if ($allScores->isEmpty()) return 50;
        
        $studentQuery = Grade::where('student_id', $studentId)->where('subject_id', $subjectId);
        if ($academicYearId) $studentQuery->where('academic_year_id', $academicYearId);
        if ($semesterId) $studentQuery->where('semester_id', $semesterId);
        
        $studentAverage = $studentQuery->avg('percentage') ?? 0;
        
        $belowCount = $allScores->filter(function($score) use ($studentAverage) {
            return $score < $studentAverage;
        })->count();
        
        return round(($belowCount / $allScores->count()) * 100, 1);
    }
} 