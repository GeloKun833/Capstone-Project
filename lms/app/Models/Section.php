<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Section extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'grade_level',
        'adviser_id',
        'capacity',
        'description',
    ];

    public function adviser()
    {
        return $this->belongsTo(Teacher::class, 'adviser_id');
    }

    public function teachers()
    {
        return $this->belongsToMany(Teacher::class, 'section_teacher', 'section_id', 'teacher_id')
            ->withTimestamps();
    }

    public function subjects()
    {
        return $this->belongsToMany(Subject::class, 'section_subject', 'section_id', 'subject_id')
            ->withTimestamps();
    }

    public function students()
    {
        return $this->belongsToMany(Student::class, 'section_student', 'section_id', 'student_id');
    }

    /**
     * Students assigned via modern enrollment section placement.
     */
    public function assignedStudents()
    {
        return $this->belongsToMany(Student::class, 'student_section_assignments', 'section_id', 'student_id')
            ->withPivot('academic_year_id', 'semester_id', 'assigned_date')
            ->withTimestamps();
    }

    /**
     * Unique student IDs across modern assignments, legacy pivot, and students.section name.
     *
     * @return list<int>
     */
    public function enrolledStudentIds(): array
    {
        $ids = $this->assignedStudents()->pluck('students.id');

        try {
            $ids = $ids->merge($this->students()->pluck('students.id'));
        } catch (\Throwable $e) {
            // Legacy pivot may be missing.
        }

        $columnQuery = Student::query()->where('section', $this->name);
        $labels = \App\Services\GradeSubjectCatalogService::gradeAliases($this->grade_level);
        if (! empty($labels)) {
            $columnQuery->where(function ($q) use ($labels) {
                $q->whereIn('year_level', $labels)
                    ->orWhereIn('class', $labels)
                    ->orWhere(function ($empty) {
                        $empty->where(function ($inner) {
                            $inner->whereNull('year_level')->orWhere('year_level', '');
                        })->where(function ($inner) {
                            $inner->whereNull('class')->orWhere('class', '');
                        });
                    });
            });
        }
        $ids = $ids->merge($columnQuery->pluck('id'));

        return $ids->unique()->filter()->map(fn ($id) => (int) $id)->values()->all();
    }

    /**
     * Unique student count across legacy + modern section links.
     */
    public function enrolledStudentsCount(): int
    {
        return count($this->enrolledStudentIds());
    }

    /**
     * Get the class schedules for this section
     */
    public function classSchedules()
    {
        return $this->hasMany(ClassSchedule::class);
    }
}
