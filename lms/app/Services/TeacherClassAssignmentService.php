<?php

namespace App\Services;

use App\Models\ClassSchedule;
use App\Models\Section;
use App\Models\Teacher;
use Illuminate\Support\Facades\DB;

class TeacherClassAssignmentService
{
    /**
     * Subjects/sections assigned to the teacher by Admin (+ schedule pairs).
     *
     * @return array{
     *   subjects:\Illuminate\Support\Collection,
     *   sections:\Illuminate\Support\Collection,
     *   assignmentMap:array<int,array<int>>,
     *   subjectsBySection:array<int,array<int>>
     * }
     */
    public function optionsFor(Teacher $teacher): array
    {
        $teacher->load(['subjects', 'sections', 'gradeLevels']);

        $subjects = $teacher->subjects->sortBy(['class', 'subject_name'])->values();
        $sections = $teacher->sections->sortBy(['grade_level', 'name'])->values();

        if ($sections->isEmpty() && $teacher->gradeLevels->isNotEmpty()) {
            $expanded = collect();
            foreach ($teacher->gradeLevels->pluck('grade_level')->filter()->unique() as $grade) {
                foreach (GradeSubjectCatalogService::gradeAliases($grade) as $alias) {
                    $expanded->push($alias);
                }
            }
            $sections = Section::query()
                ->whereIn('grade_level', $expanded->unique()->all())
                ->orderBy('grade_level')
                ->orderBy('name')
                ->get();
        }

        $map = [];
        $subjectsBySection = [];

        $addPair = function (int $subjectId, int $sectionId) use (&$map, &$subjectsBySection) {
            if (! isset($map[$subjectId])) {
                $map[$subjectId] = [];
            }
            if (! in_array($sectionId, $map[$subjectId], true)) {
                $map[$subjectId][] = $sectionId;
            }

            if (! isset($subjectsBySection[$sectionId])) {
                $subjectsBySection[$sectionId] = [];
            }
            if (! in_array($subjectId, $subjectsBySection[$sectionId], true)) {
                $subjectsBySection[$sectionId][] = $subjectId;
            }
        };

        ClassSchedule::query()
            ->where('teacher_id', $teacher->id)
            ->where('is_active', true)
            ->get(['subject_id', 'section_id'])
            ->each(function ($row) use ($addPair) {
                if ($row->subject_id && $row->section_id) {
                    $addPair((int) $row->subject_id, (int) $row->section_id);
                }
            });

        $subjectIds = $subjects->pluck('id');
        $sectionIds = $sections->pluck('id');

        if ($subjectIds->isNotEmpty() && $sectionIds->isNotEmpty()) {
            DB::table('section_subject')
                ->whereIn('subject_id', $subjectIds)
                ->whereIn('section_id', $sectionIds)
                ->get()
                ->each(function ($row) use ($addPair) {
                    $addPair((int) $row->subject_id, (int) $row->section_id);
                });

            foreach ($subjects as $subject) {
                $subjectGrade = trim((string) $subject->class);
                if ($subjectGrade === '') {
                    continue;
                }
                $subjectAliases = GradeSubjectCatalogService::gradeAliases($subjectGrade);

                foreach ($sections as $section) {
                    $sectionGrade = trim((string) $section->grade_level);
                    if ($sectionGrade === '') {
                        continue;
                    }
                    $sectionAliases = GradeSubjectCatalogService::gradeAliases($sectionGrade);

                    $gradesMatch = count(array_intersect($subjectAliases, $sectionAliases)) > 0
                        || strcasecmp($subjectGrade, $sectionGrade) === 0;

                    if ($gradesMatch) {
                        $addPair((int) $subject->id, (int) $section->id);
                    }
                }
            }
        }

        return [
            'subjects' => $subjects,
            'sections' => $sections,
            'assignmentMap' => $map,
            'subjectsBySection' => $subjectsBySection,
        ];
    }
}
