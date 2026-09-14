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
     * Unique student count across legacy + modern section links.
     */
    public function enrolledStudentsCount(): int
    {
        $legacy = $this->students()->pluck('students.id');
        $modern = $this->assignedStudents()->pluck('students.id');

        return $legacy->merge($modern)->unique()->count();
    }

    /**
     * Get the class schedules for this section
     */
    public function classSchedules()
    {
        return $this->hasMany(ClassSchedule::class);
    }
}
