<?php

namespace App\Helpers;

class GradeNarrativeHelper
{
    public static function subjectNarrative(float $average, string $subjectName): string
    {
        if ($average >= 90) {
            return "Excellent in {$subjectName}";
        }
        if ($average >= 85) {
            return "Very good performance in {$subjectName}";
        }
        if ($average >= 80) {
            return "Satisfactory in {$subjectName}";
        }
        if ($average >= 75) {
            return "Fair performance in {$subjectName}";
        }

        return "Needs improvement in {$subjectName}";
    }

    public static function overallNarrative(float $gpa): string
    {
        if ($gpa >= 3.5) {
            return 'Outstanding overall academic performance. Keep up the excellent work!';
        }
        if ($gpa >= 3.0) {
            return 'Good overall academic standing with room for continued growth.';
        }
        if ($gpa >= 2.0) {
            return 'Satisfactory performance. Focus on weaker subjects for improvement.';
        }

        return 'Academic support is recommended. Please meet with teachers and advisers.';
    }

    public static function attendanceNarrative(float $percentage): string
    {
        if ($percentage >= 95) {
            return 'Excellent attendance record.';
        }
        if ($percentage >= 85) {
            return 'Good attendance with minor absences.';
        }
        if ($percentage >= 75) {
            return 'Attendance needs monitoring due to frequent absences.';
        }

        return 'Poor attendance — immediate intervention recommended.';
    }
}
