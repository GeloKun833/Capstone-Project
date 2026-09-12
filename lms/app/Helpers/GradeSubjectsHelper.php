<?php

namespace App\Helpers;

use App\Services\GradeSubjectCatalogService;

class GradeSubjectsHelper
{
    /**
     * Get subject names for a grade from the admin subjects catalog (DB).
     */
    public static function getSubjectsForGrade($gradeLevel)
    {
        return app(GradeSubjectCatalogService::class)->subjectNamesForGrade($gradeLevel);
    }

    /**
     * Get all grade levels with their subject names.
     */
    public static function getAllGradeSubjects()
    {
        $service = app(GradeSubjectCatalogService::class);
        $all = [];
        foreach (GradeSubjectCatalogService::gradeLevels() as $grade) {
            $names = $service->subjectNamesForGrade($grade);
            if (!empty($names)) {
                $all[$grade] = $names;
            }
        }

        return $all;
    }

    public static function gradeLevelExists($gradeLevel)
    {
        return in_array($gradeLevel, GradeSubjectCatalogService::gradeLevels(), true);
    }

    public static function getSubjectCount($gradeLevel)
    {
        return count(self::getSubjectsForGrade($gradeLevel));
    }
}
