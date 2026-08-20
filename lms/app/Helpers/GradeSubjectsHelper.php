<?php

namespace App\Helpers;

class GradeSubjectsHelper
{
    /**
     * Get subjects for a specific grade level
     */
    public static function getSubjectsForGrade($gradeLevel)
    {
        $subjects = config('grade_subjects');
        return $subjects[$gradeLevel] ?? [];
    }
    
    /**
     * Get all grade levels with their subjects
     */
    public static function getAllGradeSubjects()
    {
        return config('grade_subjects');
    }
    
    /**
     * Check if a grade level exists
     */
    public static function gradeLevelExists($gradeLevel)
    {
        $subjects = config('grade_subjects');
        return isset($subjects[$gradeLevel]);
    }
    
    /**
     * Get subject count for a grade level
     */
    public static function getSubjectCount($gradeLevel)
    {
        $subjects = self::getSubjectsForGrade($gradeLevel);
        return count($subjects);
    }
}
