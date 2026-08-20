<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Enrollment extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id', 
        'subject_id', 
        'academic_year_id', 
        'semester_id',
        'enrollment_date',
        'status'
    ];

    protected $casts = [
        'enrollment_date' => 'datetime',
    ];

    public function student() { return $this->belongsTo(Student::class); }
    public function subject() { return $this->belongsTo(Subject::class); }
    public function academicYear() { return $this->belongsTo(AcademicYear::class); }
    public function semester() { return $this->belongsTo(Semester::class); }
}
