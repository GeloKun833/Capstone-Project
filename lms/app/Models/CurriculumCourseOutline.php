<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CurriculumCourseOutline extends Model
{
    protected $fillable = [
        'demo_key',
        'curriculum_id',
        'academic_year_id',
        'subject_id',
        'grade_level',
        'title',
        'description',
        'learning_objectives',
        'topics',
        'assessment_methods',
        'sequence_note',
        'status',
        'document_type',
    ];

    protected $casts = [
        'topics' => 'array',
    ];

    public function curriculum()
    {
        return $this->belongsTo(Curriculum::class);
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function optlbRecord()
    {
        return $this->hasOne(CurriculumOptlbRecord::class, 'course_outline_id');
    }

    public function materials()
    {
        return $this->hasMany(CurriculumDemoMaterial::class, 'course_outline_id');
    }

    public function visibleToTeacher(?Teacher $teacher): bool
    {
        if (! $teacher || $this->status !== 'published') {
            return false;
        }

        $teachesSubject = DB::table('subject_teacher')
            ->where('teacher_id', $teacher->id)
            ->where('subject_id', $this->subject_id)
            ->where('academic_year_id', $this->academic_year_id)
            ->exists();

        if ($teachesSubject) {
            return true;
        }

        return DB::table('class_schedules')
            ->where('teacher_id', $teacher->id)
            ->where('subject_id', $this->subject_id)
            ->where('academic_year_id', $this->academic_year_id)
            ->where('is_active', true)
            ->exists();
    }
}
