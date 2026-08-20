<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentPromotion extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'promoted_by',
        'from_year_level',
        'to_year_level',
        'from_academic_year_id',
        'to_academic_year_id',
        'promotion_status',
        'remarks',
        'final_gpa',
        'promotion_date',
    ];

    protected $casts = [
        'promotion_date' => 'date',
        'final_gpa' => 'decimal:2',
    ];

    /**
     * Get the student that was promoted
     */
    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Get the user who promoted the student
     */
    public function promoter()
    {
        return $this->belongsTo(User::class, 'promoted_by');
    }

    /**
     * Get the academic year the student was promoted from
     */
    public function fromAcademicYear()
    {
        return $this->belongsTo(AcademicYear::class, 'from_academic_year_id');
    }

    /**
     * Get the academic year the student was promoted to
     */
    public function toAcademicYear()
    {
        return $this->belongsTo(AcademicYear::class, 'to_academic_year_id');
    }

    /**
     * Scope to get only promoted students
     */
    public function scopePromoted($query)
    {
        return $query->where('promotion_status', 'promoted');
    }

    /**
     * Scope to get only retained students
     */
    public function scopeRetained($query)
    {
        return $query->where('promotion_status', 'retained');
    }

    /**
     * Scope to get only graduated students
     */
    public function scopeGraduated($query)
    {
        return $query->where('promotion_status', 'graduated');
    }

    /**
     * Get badge color based on promotion status
     */
    public function getStatusBadgeAttribute()
    {
        return [
            'promoted' => 'success',
            'retained' => 'warning',
            'graduated' => 'primary',
        ][$this->promotion_status] ?? 'secondary';
    }
}
