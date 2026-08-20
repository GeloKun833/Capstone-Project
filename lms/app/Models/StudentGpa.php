<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentGpa extends Model
{
    use HasFactory;

    protected $table = 'student_gpa';

    protected $fillable = [
        'student_id',
        'academic_year_id',
        'semester_id',
        'gpa',
        'total_units',
        'total_grade_points',
        'rank',
        'remarks',
    ];

    protected $casts = [
        'gpa' => 'decimal:2',
        'total_units' => 'integer',
        'total_grade_points' => 'integer',
        'rank' => 'integer',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function semester()
    {
        return $this->belongsTo(Semester::class);
    }

    // Get letter grade based on GPA
    public function getLetterGradeAttribute()
    {
        if ($this->gpa >= 3.5) return 'A';
        if ($this->gpa >= 3.0) return 'B';
        if ($this->gpa >= 2.5) return 'C';
        if ($this->gpa >= 2.0) return 'D';
        return 'F';
    }

    // Get grade description
    public function getGradeDescriptionAttribute()
    {
        if ($this->gpa >= 3.5) return 'Excellent';
        if ($this->gpa >= 3.0) return 'Good';
        if ($this->gpa >= 2.5) return 'Satisfactory';
        if ($this->gpa >= 2.0) return 'Needs Improvement';
        return 'Failing';
    }
} 