<?php

namespace App\Services;

use App\Models\Grade;
use App\Models\QuarterlyGrade;
use App\Models\Semester;
use App\Models\SubjectComponent;

class QuarterlyGradeSyncService
{
    public function sync(QuarterlyGrade $quarterlyGrade, ?int $semesterId = null): ?Grade
    {
        if ($quarterlyGrade->final_grade === null) {
            return null;
        }

        $semesterId = $semesterId
            ?? Semester::where('academic_year_id', $quarterlyGrade->academic_year_id)->orderBy('id')->value('id')
            ?? Semester::latest()->value('id');

        if (!$semesterId) {
            return null;
        }

        $component = SubjectComponent::firstOrCreate(
            [
                'subject_id' => $quarterlyGrade->subject_id,
                'name' => 'Final Grade',
            ],
            [
                'weight' => 100,
                'is_active' => true,
            ]
        );

        return Grade::updateOrCreate(
            [
                'student_id' => $quarterlyGrade->student_id,
                'subject_id' => $quarterlyGrade->subject_id,
                'component_id' => $component->id,
                'academic_year_id' => $quarterlyGrade->academic_year_id,
                'semester_id' => $semesterId,
            ],
            [
                'teacher_id' => $quarterlyGrade->teacher_id,
                'score' => $quarterlyGrade->final_grade,
                'max_score' => 100,
                'remarks' => $quarterlyGrade->remarks,
                'grading_period' => 'quarterly',
            ]
        );
    }

    public function syncBatch(iterable $quarterlyGrades, ?int $semesterId = null): array
    {
        $studentIds = [];

        foreach ($quarterlyGrades as $quarterlyGrade) {
            $grade = $this->sync($quarterlyGrade, $semesterId);
            if ($grade) {
                $studentIds[] = $quarterlyGrade->student_id;
            }
        }

        return array_unique($studentIds);
    }
}
