<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WeightSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'subject_id',
        'component_id',
        'weight',
        'is_active',
        'academic_year_id',
        'semester_id',
    ];

    protected $casts = [
        'weight' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function subject()
    {
        return $this->belongsTo(Subject::class);
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
} 