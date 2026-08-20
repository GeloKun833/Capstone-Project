<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeacherGradeLevel extends Model
{
    use HasFactory;

    protected $table = 'teacher_grade_level';

    protected $fillable = [
        'teacher_id',
        'grade_level',
    ];

    public function teacher()
    {
        return $this->belongsTo(Teacher::class, 'teacher_id');
    }
}
