<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Student extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'user_id',
        'first_name',
        'last_name',
        'middle_name',
        'gender',
        'date_of_birth',
        'roll',
        'blood_group',
        'religion',
        'email',
        'parent_email',
        'parent_name',
        'parent_phone',
        'parent_relationship',
        'emergency_contact_name',
        'emergency_contact_phone',
        'address',
        'previous_school',
        'enrollment_status',
        'enrollment_application_id',
        'class',
        'year_level',
        'section',
        'admission_id',
        'phone_number',
        'upload',
    ];

    public function enrollments() { return $this->hasMany(Enrollment::class); }
    public function subjects() { return $this->belongsToMany(Subject::class, 'enrollments'); }
    public function sections()
    {
        return $this->belongsToMany(Section::class, 'student_section_assignments', 'student_id', 'section_id')
            ->withPivot('academic_year_id', 'semester_id', 'assigned_date')
            ->withTimestamps();
    }
    
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function enrollmentApplication()
    {
        return $this->belongsTo(EnrollmentApplication::class);
    }

    public function attendances() {
        return $this->hasMany(Attendance::class);
    }

    public function grades()
    {
        return $this->hasMany(Grade::class);
    }

    public function gpaRecords()
    {
        return $this->hasMany(StudentGpa::class);
    }

    public function gradeAlerts()
    {
        return $this->hasMany(GradeAlert::class);
    }

    public function promotions()
    {
        return $this->hasMany(StudentPromotion::class);
    }

    public function quarterlyGrades()
    {
        return $this->hasMany(QuarterlyGrade::class);
    }

    // Get current GPA for a specific academic period
    public function getCurrentGpa($academicYearId = null, $semesterId = null)
    {
        $query = $this->gpaRecords();
        
        if ($academicYearId) {
            $query->where('academic_year_id', $academicYearId);
        }
        
        if ($semesterId) {
            $query->where('semester_id', $semesterId);
        }
        
        return $query->latest()->first();
    }

    // Get active alerts
    public function getActiveAlerts()
    {
        return $this->gradeAlerts()->where('is_resolved', false)->get();
    }

    /**
     * Get the student's full name
     */
    public function getFullNameAttribute()
    {
        return $this->first_name . ' ' . $this->last_name;
    }
}
