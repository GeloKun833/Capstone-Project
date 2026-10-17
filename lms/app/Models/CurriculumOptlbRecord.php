<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CurriculumOptlbRecord extends Model
{
    protected $fillable = [
        'demo_key',
        'course_outline_id',
        'academic_year_id',
        'subject_id',
        'grade_level',
        'title',
        'learning_objectives',
        'topic_sequence',
        'suggested_activities',
        'assessment',
        'references',
        'status',
        'demo_label',
    ];

    public function courseOutline()
    {
        return $this->belongsTo(CurriculumCourseOutline::class, 'course_outline_id');
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function materials()
    {
        return $this->hasMany(CurriculumDemoMaterial::class, 'optlb_record_id');
    }
}
