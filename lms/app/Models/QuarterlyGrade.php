<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuarterlyGrade extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'subject_id',
        'teacher_id',
        'academic_year_id',
        'quarter_1',
        'quarter_2',
        'quarter_3',
        'quarter_4',
        'final_grade',
        'remarks',
    ];

    protected $casts = [
        'quarter_1' => 'decimal:2',
        'quarter_2' => 'decimal:2',
        'quarter_3' => 'decimal:2',
        'quarter_4' => 'decimal:2',
        'final_grade' => 'decimal:2',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    /**
     * Calculate final grade as average of all quarters
     */
    public function calculateFinalGrade()
    {
        $quarters = array_filter([
            $this->quarter_1,
            $this->quarter_2,
            $this->quarter_3,
            $this->quarter_4
        ], function($value) {
            return $value !== null && $value !== '';
        });

        if (empty($quarters)) {
            return null;
        }

        return round(array_sum($quarters) / count($quarters), 2);
    }

    /**
     * Get remarks based on DepEd grading scale
     */
    public function getRemarks()
    {
        // Recalculate final grade if needed
        if ($this->final_grade === null) {
            $this->final_grade = $this->calculateFinalGrade();
        }
        
        $finalGrade = $this->final_grade;

        if ($finalGrade === null) {
            return null;
        }

        // DepEd Grading Scale
        if ($finalGrade >= 90 && $finalGrade <= 100) {
            return 'Outstanding';
        } elseif ($finalGrade >= 85 && $finalGrade <= 89) {
            return 'Very Satisfactory';
        } elseif ($finalGrade >= 80 && $finalGrade <= 84) {
            return 'Satisfactory';
        } elseif ($finalGrade >= 75 && $finalGrade <= 79) {
            return 'Fairly Satisfactory';
        } elseif ($finalGrade < 75) {
            return 'Did Not Meet Expectations';
        }

        return null;
    }
    
    /**
     * Get remarks class for styling
     */
    public function getRemarksClassAttribute()
    {
        $finalGrade = $this->final_grade ?? $this->calculateFinalGrade();
        
        if ($finalGrade === null) {
            return 'bg-secondary';
        }
        
        if ($finalGrade >= 90 && $finalGrade <= 100) {
            return 'remarks-outstanding';
        } elseif ($finalGrade >= 85 && $finalGrade <= 89) {
            return 'remarks-very-satisfactory';
        } elseif ($finalGrade >= 80 && $finalGrade <= 84) {
            return 'remarks-satisfactory';
        } elseif ($finalGrade >= 75 && $finalGrade <= 79) {
            return 'remarks-fairly-satisfactory';
        } elseif ($finalGrade < 75) {
            return 'remarks-did-not-meet';
        }
        
        return 'bg-secondary';
    }

    /**
     * Auto-calculate final grade when saving
     * Remarks are set manually by teachers, but we can suggest based on final grade
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($quarterlyGrade) {
            // Calculate final grade
            $quarterlyGrade->final_grade = $quarterlyGrade->calculateFinalGrade();
            
            // Don't auto-set remarks - teachers will input them manually
            // Only set if explicitly empty/null and teacher hasn't provided one
            // This preserves teacher's manual input
        });
    }
    
    /**
     * Get suggested remarks based on final grade (for auto-fill)
     */
    public function getSuggestedRemarks()
    {
        return $this->getRemarks();
    }
}
