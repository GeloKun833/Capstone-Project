<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EnrollmentApplication extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'application_number',
        'enrollment_type',
        'student_category', // New: new_student, old_student, transferee
        'existing_student_id', // For old students re-enrolling
        'first_name',
        'last_name',
        'middle_name',
        'date_of_birth',
        'gender',
        'email',
        'phone_number',
        'address',
        'address_lot_block_village',
        'address_barangay_district',
        'address_city_municipality',
        'age_years',
        'age_months',
        'date_enrolled',
        'lrn',
        'esc_no',
        'covid_vaccinated',
        'covid_first_shot_date',
        'covid_full_vaccination_date',
        'religion',
        'citizenship',
        'birthplace',
        'previous_school',
        'previous_school_id',
        'previous_school_location',
        'previous_school_type',
        'psa_birth_cert_no',
        'parent_name',
        'parent_phone',
        'parent_email',
        'parent_relationship',
        'father_last_name',
        'father_first_name',
        'father_middle_name',
        'father_education',
        'father_employment',
        'father_company_name',
        'father_work_address',
        'father_contact_no',
        'father_email',
        'mother_last_name',
        'mother_first_name',
        'mother_middle_name',
        'mother_education',
        'mother_employment',
        'mother_company_name',
        'mother_work_address',
        'mother_contact_no',
        'mother_email',
        'family_income_bracket',
        'no_of_siblings',
        'no_of_siblings_studying',
        'siblings_schools',
        'emergency_contact_name',
        'emergency_contact_phone',
        'guardian_name',
        'guardian_relation',
        'guardian_contact_no',
        'guardian_email',
        'authorized_fetcher',
        'authorized_fetcher_relation',
        'parent_signature_name',
        'date_of_first_attendance',
        'doc_submitted_form138',
        'doc_submitted_psa_birth',
        'doc_submitted_form137',
        'doc_submitted_baptismal',
        'doc_submitted_pic_1x1',
        'doc_submitted_pic_2x2',
        'doc_submitted_itr',
        'doc_submitted_unemployment',
        'grade_level_applying_for',
        'status',
        'notes',
        'rejection_reason',
        'reviewed_by',
        'reviewed_at',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'date_enrolled' => 'date',
        'covid_first_shot_date' => 'date',
        'covid_full_vaccination_date' => 'date',
        'date_of_first_attendance' => 'date',
        'reviewed_at' => 'datetime',
        'doc_submitted_form138' => 'boolean',
        'doc_submitted_psa_birth' => 'boolean',
        'doc_submitted_form137' => 'boolean',
        'doc_submitted_baptismal' => 'boolean',
        'doc_submitted_pic_1x1' => 'boolean',
        'doc_submitted_pic_2x2' => 'boolean',
        'doc_submitted_itr' => 'boolean',
        'doc_submitted_unemployment' => 'boolean',
    ];

    // Relationships
    public function documents()
    {
        return $this->hasMany(EnrollmentDocument::class);
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function student()
    {
        return $this->hasOne(Student::class);
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeUnderReview($query)
    {
        return $query->where('status', 'under_review');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    public function scopeNeedsDocuments($query)
    {
        return $query->where('status', 'needs_documents');
    }

    // Accessors
    public function getFullNameAttribute()
    {
        return $this->first_name . ' ' . ($this->middle_name ? $this->middle_name . ' ' : '') . $this->last_name;
    }

    public function getStatusBadgeAttribute()
    {
        $badges = [
            'pending' => 'warning',
            'under_review' => 'info',
            'approved' => 'success',
            'rejected' => 'danger',
            'needs_documents' => 'secondary',
        ];

        return $badges[$this->status] ?? 'secondary';
    }

    // Methods
    public function markAsUnderReview($reviewerId = null)
    {
        $this->update([
            'status' => 'under_review',
            'reviewed_by' => $reviewerId ?? auth()->id(),
            'reviewed_at' => now(),
        ]);
    }

    public function approve($reviewerId = null)
    {
        $this->update([
            'status' => 'approved',
            'reviewed_by' => $reviewerId ?? auth()->id(),
            'reviewed_at' => now(),
        ]);
    }

    public function reject($reason = null, $reviewerId = null)
    {
        $this->update([
            'status' => 'rejected',
            'rejection_reason' => $reason,
            'reviewed_by' => $reviewerId ?? auth()->id(),
            'reviewed_at' => now(),
        ]);
    }

    public function needsDocuments($notes = null, $reviewerId = null)
    {
        $this->update([
            'status' => 'needs_documents',
            'notes' => $notes,
            'reviewed_by' => $reviewerId ?? auth()->id(),
            'reviewed_at' => now(),
        ]);
    }

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            if (empty($model->application_number)) {
                $model->application_number = 'APP-' . date('Y') . '-' . str_pad(EnrollmentApplication::count() + 1, 6, '0', STR_PAD_LEFT);
            }
        });
    }
}
