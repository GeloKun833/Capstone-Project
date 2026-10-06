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
        'academic_year_id',
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

    /**
     * Sections for a year: rows tagged with that year, or older rows that
     * already have a schedule or student placement in that year.
     * Untagged rows are not copied and are not shown for a different year.
     */
    public function scopeForAcademicYear($query, int $yearId)
    {
        return $query->where(function ($outer) use ($yearId) {
            $outer->where('academic_year_id', $yearId)
                ->orWhere(function ($legacy) use ($yearId) {
                    $legacy->whereNull('academic_year_id')
                        ->where(function ($linked) use ($yearId) {
                            $linked->whereHas('classSchedules', function ($schedules) use ($yearId) {
                                $schedules->where('academic_year_id', $yearId);
                            })->orWhereExists(function ($assignments) use ($yearId) {
                                $assignments->selectRaw('1')
                                    ->from('student_section_assignments')
                                    ->whereColumn('student_section_assignments.section_id', 'sections.id')
                                    ->where('student_section_assignments.academic_year_id', $yearId);
                            });
                        });
                });
        });
    }
}
