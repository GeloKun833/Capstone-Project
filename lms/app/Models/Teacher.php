<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Teacher extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'full_name',
        'gender',
        'date_of_birth',
        'qualification',
        'experience',
        'phone_number',
        'address',
        'city',
        'state',
        'zip_code',
        'country',
        'avatar',
    ];

    /**
     * Get the subjects assigned to this teacher
     */
    public function subjects()
    {
        return $this->belongsToMany(Subject::class, 'subject_teacher', 'teacher_id', 'subject_id');
    }

    /**
     * Get the sections assigned to this teacher
     */
    public function sections()
    {
        return $this->belongsToMany(Section::class, 'section_teacher', 'teacher_id', 'section_id');
    }

    /**
     * Get the grade levels assigned to this teacher
     */
    public function gradeLevels()
    {
        return $this->hasMany(\App\Models\TeacherGradeLevel::class, 'teacher_id');
    }

    /**
     * Get the user associated with this teacher
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    /**
     * Get the teacher's full name
     */
    public function getFullNameAttribute()
    {
        return $this->attributes['full_name'] ?? '';
    }
}
