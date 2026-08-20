<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GradeAlert extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'subject_id',
        'alert_type',
        'message',
        'threshold_value',
        'current_value',
        'is_resolved',
        'resolved_at',
        'resolved_by',
        'academic_year_id',
        'semester_id',
    ];

    protected $casts = [
        'threshold_value' => 'decimal:2',
        'current_value' => 'decimal:2',
        'is_resolved' => 'boolean',
        'resolved_at' => 'datetime',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function resolvedBy()
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function semester()
    {
        return $this->belongsTo(Semester::class);
    }

    // Alert type constants
    const TYPE_LOW_GRADE = 'low_grade';
    const TYPE_PERFORMANCE_DROP = 'performance_drop';
    const TYPE_AT_RISK = 'at_risk';

    // Get alert severity level
    public function getSeverityLevelAttribute()
    {
        switch ($this->alert_type) {
            case self::TYPE_LOW_GRADE:
                return 'warning';
            case self::TYPE_PERFORMANCE_DROP:
                return 'danger';
            case self::TYPE_AT_RISK:
                return 'critical';
            default:
                return 'info';
        }
    }

    // Get alert icon
    public function getAlertIconAttribute()
    {
        switch ($this->alert_type) {
            case self::TYPE_LOW_GRADE:
                return 'exclamation-triangle';
            case self::TYPE_PERFORMANCE_DROP:
                return 'arrow-down';
            case self::TYPE_AT_RISK:
                return 'exclamation-circle';
            default:
                return 'info-circle';
        }
    }
} 