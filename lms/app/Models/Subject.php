<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    use HasFactory;
    protected $fillable = [
        'subject_id',
        'subject_name',
        'class',
    ];

    /** auto genarate id */
    protected static function boot()
    {
        parent::boot();
        self::creating(function ($model) {
            $getUser = self::orderBy('subject_id', 'desc')->first();

            if ($getUser) {
                $latestID = intval(substr($getUser->subject_id, 5));
                $nextID = $latestID + 1;
            } else {
                $nextID = 1;
            }
            $model->subject_id = 'PRE' . sprintf("%03s", $nextID);
            while (self::where('subject_id', $model->subject_id)->exists()) {
                $nextID++;
                $model->subject_id = 'PRE' . sprintf("%03s", $nextID);
            }
        });
    }

    public function enrollments() { return $this->hasMany(Enrollment::class); }
    public function students() { return $this->belongsToMany(Student::class, 'enrollments'); }
    
    /**
     * Get the teachers assigned to this subject
     */
    public function teachers()
    {
        return $this->belongsToMany(Teacher::class, 'subject_teacher', 'subject_id', 'teacher_id');
    }

    /**
     * Get the sections for this subject
     */
    public function sections()
    {
        return $this->belongsToMany(Section::class, 'section_subject', 'subject_id', 'section_id');
    }

    public function curricula()
    {
        return $this->belongsToMany(Curriculum::class, 'curriculum_subject', 'subject_id', 'curriculum_id');
    }

    public function attendances() {
        return $this->hasMany(Attendance::class);
    }

    public function grades()
    {
        return $this->hasMany(Grade::class);
    }

    public function weightSettings()
    {
        return $this->hasMany(WeightSetting::class);
    }

    public function gradeAlerts()
    {
        return $this->hasMany(GradeAlert::class);
    }

    /**
     * Get the class schedules for this subject
     */
    public function classSchedules()
    {
        return $this->hasMany(ClassSchedule::class);
    }
}
