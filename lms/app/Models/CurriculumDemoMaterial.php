<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CurriculumDemoMaterial extends Model
{
    protected $fillable = [
        'demo_key',
        'course_outline_id',
        'optlb_record_id',
        'academic_year_id',
        'subject_id',
        'grade_level',
        'title',
        'file_path',
        'file_name',
        'demo_label',
    ];

    public function courseOutline()
    {
        return $this->belongsTo(CurriculumCourseOutline::class, 'course_outline_id');
    }

    public function optlbRecord()
    {
        return $this->belongsTo(CurriculumOptlbRecord::class, 'optlb_record_id');
    }
}
