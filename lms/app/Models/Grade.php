<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Grade extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'subject_id',
        'teacher_id',
        'component_id',
        'score',
        'max_score',
        'percentage',
        'remarks',
        'grading_period',
        'academic_year_id',
        'semester_id',
    ];

    protected $casts = [
        'score' => 'decimal:2',
        'max_score' => 'decimal:2',
        'percentage' => 'decimal:2',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function component()
    {
        return $this->belongsTo(SubjectComponent::class, 'component_id');
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function semester()
    {
        return $this->belongsTo(Semester::class);
    }

    // Calculate percentage automatically
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($grade) {
            if ($grade->score && $grade->max_score && $grade->max_score > 0) {
                $grade->percentage = ($grade->score / $grade->max_score) * 100;
            }
        });
    }
} 