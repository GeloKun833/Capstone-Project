<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EnrollmentDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'enrollment_application_id',
        'document_type',
        'file_name',
        'file_path',
        'file_size',
        'mime_type',
        'status',
        'verification_notes',
        'verified_by',
        'verified_at',
    ];

    protected $casts = [
        'verified_at' => 'datetime',
    ];

    // Relationships
    public function enrollmentApplication()
    {
        return $this->belongsTo(EnrollmentApplication::class);
    }

    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeVerified($query)
    {
        return $query->where('status', 'verified');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    // Accessors
    public function getStatusBadgeAttribute()
    {
        $badges = [
            'pending' => 'warning',
            'verified' => 'success',
            'rejected' => 'danger',
        ];

        return $badges[$this->status] ?? 'secondary';
    }

    public function getFileSizeFormattedAttribute()
    {
        $bytes = (int) $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    // Methods
    public function verify($verifierId = null, $notes = null)
    {
        $this->update([
            'status' => 'verified',
            'verified_by' => $verifierId ?? auth()->id(),
            'verified_at' => now(),
            'verification_notes' => $notes,
        ]);
    }

    public function reject($notes = null, $verifierId = null)
    {
        $this->update([
            'status' => 'rejected',
            'verified_by' => $verifierId ?? auth()->id(),
            'verified_at' => now(),
            'verification_notes' => $notes,
        ]);
    }

    // Constants for document types
    const DOCUMENT_TYPES = [
        'birth_certificate' => 'Birth Certificate',
        'report_card' => 'Report Card/Transcript',
        'sf9' => 'SF9 (Learner\'s Permanent Record)',
        'sf10' => 'SF10 (Report Card)',
        'good_moral' => 'Certificate of Good Moral Character',
        'medical_certificate' => 'Medical Certificate',
        'id_photo' => 'ID Photo (2x2)',
        'parent_guardian_id' => 'Parent/Guardian ID',
        'proof_of_residence' => 'Proof of Residence',
        'other' => 'Other Document',
    ];
}
