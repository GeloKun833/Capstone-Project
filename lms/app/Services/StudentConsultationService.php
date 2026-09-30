<?php

namespace App\Services;

use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Support\Collection;

class StudentConsultationService
{
    /**
     * Return each active subject enrollment with teachers assigned to one of the
     * student's current sections for that subject.
     */
    public function optionsFor(Student $student): Collection
    {
        $subjectIds = $student->enrollments()
            ->where('status', 'active')
            ->pluck('subject_id')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();
        $sectionIds = $student->resolvedSectionIds();

        if ($subjectIds->isEmpty() || $sectionIds === []) {
            return collect();
        }

        $teacherService = app(TeacherClassAssignmentService::class);
        $teacherIdsBySubject = [];

        foreach (Teacher::query()->orderBy('full_name')->get() as $teacher) {
            $teacherOptions = $teacherService->optionsFor($teacher);
            foreach ($sectionIds as $sectionId) {
                foreach ($teacherOptions['subjectsBySection'][$sectionId] ?? [] as $subjectId) {
                    $subjectId = (int) $subjectId;
                    if ($subjectIds->contains($subjectId)) {
                        $teacherIdsBySubject[$subjectId][] = (int) $teacher->id;
                    }
                }
            }
        }

        $teacherIds = collect($teacherIdsBySubject)->flatten()->unique()->values();
        $teachers = Teacher::query()
            ->with('user')
            ->whereIn('id', $teacherIds)
            ->orderBy('full_name')
            ->get()
            ->keyBy('id');

        return Subject::query()
            ->whereIn('id', array_keys($teacherIdsBySubject))
            ->orderBy('subject_name')
            ->get(['id', 'subject_name', 'class'])
            ->map(function (Subject $subject) use ($teacherIdsBySubject, $teachers) {
                $subject->setRelation(
                    'consultationTeachers',
                    collect($teacherIdsBySubject[$subject->id] ?? [])
                        ->unique()
                        ->map(fn ($teacherId) => $teachers->get($teacherId))
                        ->filter()
                        ->values()
                );

                return $subject;
            });
    }

    public function teacherIsAssigned(Student $student, int $subjectId, int $teacherId): bool
    {
        return $this->optionsFor($student)->contains(function (Subject $subject) use ($subjectId, $teacherId) {
            return (int) $subject->id === $subjectId
                && $subject->consultationTeachers->contains(fn (Teacher $teacher) => (int) $teacher->id === $teacherId);
        });
    }
}