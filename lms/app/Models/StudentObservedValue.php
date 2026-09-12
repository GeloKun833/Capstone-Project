<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentObservedValue extends Model
{
    use HasFactory;

    public const RATINGS = ['AO', 'SO', 'RO'];

    protected $fillable = [
        'student_id',
        'academic_year_id',
        'indicator_id',
        'teacher_id',
        'quarter_1',
        'quarter_2',
        'quarter_3',
        'quarter_4',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function indicator()
    {
        return $this->belongsTo(ObservedValueIndicator::class, 'indicator_id');
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }
}
