@extends('layouts.enrollment-portal')

@section('title', 'Application Details')

@section('content')
<div class="row">
    <div class="col-lg-11 mx-auto">
        {{-- Profile Header --}}
        <div class="ep-card mb-4">
            <div class="ep-card-body">
                <div class="row align-items-center g-3">
                    <div class="col-auto">
                        <div class="ep-user-avatar" style="width:72px;height:72px;border-radius:18px;font-size:1.75rem;">
                            {{ strtoupper(substr($application->first_name, 0, 1)) }}{{ strtoupper(substr($application->last_name, 0, 1)) }}
                        </div>
                    </div>
                    <div class="col">
                        <h1 class="ep-page-title mb-1">{{ $application->full_name }}</h1>
                        <p class="ep-page-subtitle mb-2">{{ $application->application_number }} · {{ $application->grade_level_applying_for }}</p>
                        @php
                            $chipMap = ['pending'=>'ep-chip-pending','under_review'=>'ep-chip-review','approved'=>'ep-chip-approved','rejected'=>'ep-chip-rejected','needs_documents'=>'ep-chip-docs'];
                            $chip = $chipMap[$application->status] ?? 'ep-chip-docs';
                        @endphp
                        <span class="ep-chip {{ $chip }}">
                            <i class="fas fa-circle" style="font-size:.5rem;"></i>
                            {{ ucfirst(str_replace('_', ' ', $application->status)) }}
                        </span>
                    </div>
                    <div class="col-md-auto text-md-end">
                        <small class="text-muted d-block">Submitted</small>
                        <strong>{{ $application->created_at->format('M d, Y') }}</strong>
                        @if($application->reviewed_at)
                            <small class="text-muted d-block mt-2">Last Updated</small>
                            <strong>{{ $application->reviewed_at->format('M d, Y') }}</strong>
                        @endif
                    </div>
                </div>
                @if($application->notes)
                    <div class="ep-alert ep-alert-info mt-3 mb-0"><strong>Notes:</strong> {{ $application->notes }}</div>
                @endif
                @if($application->rejection_reason && $application->status === 'rejected')
                    <div class="ep-alert ep-alert-danger mt-3 mb-0"><strong>Rejection Reason:</strong> {{ $application->rejection_reason }}</div>
                @endif
            </div>
        </div>
        
        <div class="ep-card mb-4">
            <div class="ep-card-header">
                <h3><i class="fas fa-user me-2 text-primary"></i>Student Information</h3>
            </div>
            <div class="ep-card-body">
                <h6 class="fw-bold mb-3 text-primary">Basic Information</h6>
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td style="width: 40%;"><strong>Level Applied for:</strong></td>
                                <td>{{ $application->grade_level_applying_for }}</td>
                            </tr>
                            <tr>
                                <td><strong>Full Name:</strong></td>
                                <td>{{ $application->full_name }}</td>
                            </tr>
                            <tr>
                                <td><strong>LRN:</strong></td>
                                <td>{{ $application->lrn ?: 'Not provided' }}</td>
                            </tr>
                            <tr>
                                <td><strong>ESC NO.:</strong></td>
                                <td>{{ $application->esc_no ?: 'Not provided' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Email:</strong></td>
                                <td>{{ $application->email ?: 'Not provided' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Gender:</strong></td>
                                <td>{{ $application->gender }}</td>
                            </tr>
                            <tr>
                                <td><strong>Date Enrolled:</strong></td>
                                <td>{{ $application->date_enrolled ? $application->date_enrolled->format('M d, Y') : 'Not set' }}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td style="width: 40%;"><strong>Date of Birth:</strong></td>
                                <td>{{ $application->date_of_birth->format('M d, Y') }}</td>
                            </tr>
                            <tr>
                                <td><strong>Age:</strong></td>
                                <td>
                                    @if($application->age_years !== null)
                                        {{ $application->age_years }} years, {{ $application->age_months ?? 0 }} months
                                    @else
                                        Not calculated
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Birthplace:</strong></td>
                                <td>{{ $application->birthplace ?: 'Not provided' }}</td>
                            </tr>
                            <tr>
                                <td><strong>PSA Birth Cert. No.:</strong></td>
                                <td>{{ $application->psa_birth_cert_no ?: 'Not provided' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Religion:</strong></td>
                                <td>{{ $application->religion ?: 'Not provided' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Citizenship:</strong></td>
                                <td>{{ $application->citizenship ?: 'Not provided' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Phone:</strong></td>
                                <td>{{ $application->phone_number ?: 'Not provided' }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
                
                <h6 class="fw-bold mb-3 mt-4 text-primary">Address</h6>
                <div class="row">
                    <div class="col-md-12">
                        <table class="table table-borderless">
                            <tr>
                                <td style="width: 20%;"><strong>Lot # Block # Village / Subd:</strong></td>
                                <td>{{ $application->address_lot_block_village ?: 'Not provided' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Barangay / District:</strong></td>
                                <td>{{ $application->address_barangay_district ?: 'Not provided' }}</td>
                            </tr>
                            <tr>
                                <td><strong>City / Municipality:</strong></td>
                                <td>{{ $application->address_city_municipality ?: 'Not provided' }}</td>
                            </tr>
                            @if($application->address)
                            <tr>
                                <td><strong>Complete Address:</strong></td>
                                <td>{{ $application->address }}</td>
                            </tr>
                            @endif
                        </table>
                    </div>
                </div>
                
                <h6 class="fw-bold mb-3 mt-4 text-primary">COVID-19 Vaccination</h6>
                <div class="row">
                    <div class="col-md-12">
                        <table class="table table-borderless">
                            <tr>
                                <td style="width: 30%;"><strong>Vaccinated against COVID-19?:</strong></td>
                                <td>{{ $application->covid_vaccinated ?: 'Not specified' }}</td>
                            </tr>
                            @if($application->covid_vaccinated === 'Yes')
                            <tr>
                                <td><strong>Date of 1st Shot:</strong></td>
                                <td>{{ $application->covid_first_shot_date ? $application->covid_first_shot_date->format('M d, Y') : 'Not provided' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Full Vaccination Date:</strong></td>
                                <td>{{ $application->covid_full_vaccination_date ? $application->covid_full_vaccination_date->format('M d, Y') : 'Not provided' }}</td>
                            </tr>
                            @endif
                        </table>
                    </div>
                </div>
                
                <h6 class="fw-bold mb-3 mt-4 text-primary">Last School Attended</h6>
                <div class="row">
                    <div class="col-md-12">
                        <table class="table table-borderless">
                            <tr>
                                <td style="width: 25%;"><strong>School Name:</strong></td>
                                <td>{{ $application->previous_school ?: 'Not provided' }}</td>
                            </tr>
                            <tr>
                                <td><strong>School ID:</strong></td>
                                <td>{{ $application->previous_school_id ?: 'Not provided' }}</td>
                            </tr>
                            <tr>
                                <td><strong>School's Location:</strong></td>
                                <td>{{ $application->previous_school_location ?: 'Not provided' }}</td>
                            </tr>
                            <tr>
                                <td><strong>School Type:</strong></td>
                                <td>{{ $application->previous_school_type ?: 'Not provided' }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Parent/Guardian Information -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0"><i class="fas fa-users me-2"></i>OTHER INFORMATION (for parents)</h5>
            </div>
            <div class="card-body p-4">
                <!-- Father's Information -->
                <h6 class="fw-bold mb-3 text-primary">Father's Information</h6>
                <div class="row mb-4">
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td style="width: 40%;"><strong>Name:</strong></td>
                                <td>
                                    @if($application->father_last_name || $application->father_first_name)
                                        {{ trim(($application->father_last_name ?? '') . ', ' . ($application->father_first_name ?? '') . ' ' . ($application->father_middle_name ?? '')) }}
                                    @else
                                        Not provided
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Education:</strong></td>
                                <td>{{ $application->father_education ?: 'Not provided' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Employment:</strong></td>
                                <td>{{ $application->father_employment ?: 'Not provided' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Company:</strong></td>
                                <td>{{ $application->father_company_name ?: 'Not provided' }}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td style="width: 40%;"><strong>Work Address:</strong></td>
                                <td>{{ $application->father_work_address ?: 'Not provided' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Contact No.:</strong></td>
                                <td>{{ $application->father_contact_no ?: 'Not provided' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Email:</strong></td>
                                <td>{{ $application->father_email ?: 'Not provided' }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
                
                <!-- Mother's Information -->
                <h6 class="fw-bold mb-3 mt-4 text-primary">Mother's Information</h6>
                <div class="row mb-4">
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td style="width: 40%;"><strong>Name (Maiden):</strong></td>
                                <td>
                                    @if($application->mother_last_name || $application->mother_first_name)
                                        {{ trim(($application->mother_last_name ?? '') . ', ' . ($application->mother_first_name ?? '') . ' ' . ($application->mother_middle_name ?? '')) }}
                                    @else
                                        Not provided
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Education:</strong></td>
                                <td>{{ $application->mother_education ?: 'Not provided' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Employment:</strong></td>
                                <td>{{ $application->mother_employment ?: 'Not provided' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Company:</strong></td>
                                <td>{{ $application->mother_company_name ?: 'Not provided' }}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td style="width: 40%;"><strong>Work Address:</strong></td>
                                <td>{{ $application->mother_work_address ?: 'Not provided' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Contact No.:</strong></td>
                                <td>{{ $application->mother_contact_no ?: 'Not provided' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Email:</strong></td>
                                <td>{{ $application->mother_email ?: 'Not provided' }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
                
                <!-- Family Income Information -->
                <h6 class="fw-bold mb-3 mt-4 text-primary">FAMILY'S MONTHLY INCOME BRACKET</h6>
                <div class="row mb-4">
                    <div class="col-md-12">
                        <table class="table table-borderless">
                            <tr>
                                <td style="width: 30%;"><strong>Income Bracket:</strong></td>
                                <td>{{ $application->family_income_bracket ?: 'Not provided' }}</td>
                            </tr>
                            <tr>
                                <td><strong>No. of Siblings:</strong></td>
                                <td>{{ $application->no_of_siblings ?? 'Not provided' }}</td>
                            </tr>
                            <tr>
                                <td><strong>No. of Siblings Studying:</strong></td>
                                <td>{{ $application->no_of_siblings_studying ?? 'Not provided' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Schools they are attending:</strong></td>
                                <td>{{ $application->siblings_schools ?: 'Not provided' }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
                
                <!-- Guardian/Authorized Fetcher Information -->
                <h6 class="fw-bold mb-3 mt-4 text-primary">Guardian/Authorized Fetcher Information</h6>
                <div class="row mb-4">
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td style="width: 40%;"><strong>Guardian's Name:</strong></td>
                                <td>{{ $application->guardian_name ?: 'Not provided' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Relation:</strong></td>
                                <td>{{ $application->guardian_relation ?: 'Not provided' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Contact Nos.:</strong></td>
                                <td>{{ $application->guardian_contact_no ?: 'Not provided' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Email:</strong></td>
                                <td>{{ $application->guardian_email ?: 'Not provided' }}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td style="width: 40%;"><strong>Authorized Fetcher:</strong></td>
                                <td>{{ $application->authorized_fetcher ?: 'Not provided' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Relation:</strong></td>
                                <td>{{ $application->authorized_fetcher_relation ?: 'Not provided' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Emergency Contact:</strong></td>
                                <td>{{ $application->emergency_contact_name ?: 'Not provided' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Emergency Phone:</strong></td>
                                <td>{{ $application->emergency_contact_phone ?: 'Not provided' }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
                
                <!-- Legacy Parent Information (for backward compatibility) -->
                @if($application->parent_name)
                <h6 class="fw-bold mb-3 mt-4 text-primary">Primary Contact</h6>
                <div class="row">
                    <div class="col-md-12">
                        <table class="table table-borderless">
                            <tr>
                                <td style="width: 20%;"><strong>Parent/Guardian Name:</strong></td>
                                <td>{{ $application->parent_name }}</td>
                            </tr>
                            <tr>
                                <td><strong>Relationship:</strong></td>
                                <td>{{ $application->parent_relationship ?: 'Not provided' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Phone:</strong></td>
                                <td>{{ $application->parent_phone ?: 'Not provided' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Email:</strong></td>
                                <td>{{ $application->parent_email ?: 'Not provided' }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
                @endif
            </div>
        </div>
        
        <!-- Enrollment Guidelines Information -->
        @if($application->parent_signature_name || $application->date_of_first_attendance)
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-secondary text-white">
                <h5 class="mb-0"><i class="fas fa-file-contract me-2"></i>Enrollment Guidelines Information</h5>
            </div>
            <div class="card-body p-4">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td style="width: 40%;"><strong>Parent Signature Name:</strong></td>
                                <td>{{ $application->parent_signature_name ?: 'Not provided' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Date of First Attendance:</strong></td>
                                <td>{{ $application->date_of_first_attendance ? $application->date_of_first_attendance->format('M d, Y') : 'Not set' }}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h6 class="fw-bold mb-3">Documents Submitted Checklist</h6>
                        <table class="table table-sm table-borderless">
                            <tr>
                                <td>
                                    <i class="fas {{ $application->doc_submitted_form138 ? 'fa-check text-success' : 'fa-times text-danger' }} me-2"></i>
                                    Form138/SF9/Report Card
                                </td>
                                <td>
                                    <i class="fas {{ $application->doc_submitted_psa_birth ? 'fa-check text-success' : 'fa-times text-danger' }} me-2"></i>
                                    PSA Birth Cert (Orig)
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <i class="fas {{ $application->doc_submitted_form137 ? 'fa-check text-success' : 'fa-times text-danger' }} me-2"></i>
                                    Form137/SF10
                                </td>
                                <td>
                                    <i class="fas {{ $application->doc_submitted_baptismal ? 'fa-check text-success' : 'fa-times text-danger' }} me-2"></i>
                                    Baptismal Cert (Gr.3 only)
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <i class="fas {{ $application->doc_submitted_pic_1x1 ? 'fa-check text-success' : 'fa-times text-danger' }} me-2"></i>
                                    Recent Pic (1x1) 3 copies
                                </td>
                                <td>
                                    <i class="fas {{ $application->doc_submitted_pic_2x2 ? 'fa-check text-success' : 'fa-times text-danger' }} me-2"></i>
                                    Recent Pic (2x2) 2 copies (Grade 7)
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <i class="fas {{ $application->doc_submitted_itr ? 'fa-check text-success' : 'fa-times text-danger' }} me-2"></i>
                                    ITR of Parents
                                </td>
                                <td>
                                    <i class="fas {{ $application->doc_submitted_unemployment ? 'fa-check text-success' : 'fa-times text-danger' }} me-2"></i>
                                    Certification of Unemployment
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        @endif
        
        <!-- Documents Status -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-warning text-dark">
                <h5 class="mb-0">
                    <i class="fas fa-file-upload me-2"></i>Documents Status
                    <span class="badge bg-info ms-2">{{ $application->documents->count() }}/6 uploaded</span>
                </h5>
            </div>
            <div class="card-body p-4">
                @php
                    $requiredDocuments = [
                        'birth_certificate' => 'Birth Certificate',
                        'sf9' => 'SF9 (Learner\'s Permanent Record)',
                        'sf10' => 'SF10 (Report Card)',
                        'good_moral' => 'Certificate of Good Moral Character',
                        'id_photo' => 'ID Photo (2x2)',
                        'parent_guardian_id' => 'Parent/Guardian ID'
                    ];
                    
                    $uploadedTypes = $application->documents->pluck('document_type')->toArray();
                    $missingDocuments = array_diff(array_keys($requiredDocuments), $uploadedTypes);
                @endphp
                
                <!-- Document Status Overview -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle me-2"></i>
                            <strong>Uploaded Documents:</strong> {{ $application->documents->count() }}
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>Missing Documents:</strong> {{ count($missingDocuments) }}
                        </div>
                    </div>
                </div>
                
                <!-- Missing Documents Alert -->
                @if(count($missingDocuments) > 0)
                    <div class="alert alert-danger mb-4">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        <strong>Missing Required Documents:</strong>
                        <ul class="mb-0 mt-2">
                            @foreach($missingDocuments as $missingType)
                                <li>{{ $requiredDocuments[$missingType] }}</li>
                            @endforeach
                        </ul>
                        <small class="mt-2 d-block">
                            <i class="fas fa-info-circle me-1"></i>
                            Please upload these documents to complete your enrollment application. You can contact the registrar's office for assistance.
                        </small>
                    </div>
                @else
                    <div class="alert alert-success mb-4">
                        <i class="fas fa-check-circle me-2"></i>
                        <strong>All Required Documents Uploaded!</strong>
                        <p class="mb-0 mt-1">Your application has all the necessary documents for review.</p>
                    </div>
                @endif
                
                @if($application->documents->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Document Type</th>
                                    <th>File Name</th>
                                    <th>File Size</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($application->documents as $document)
                                    <tr>
                                        <td>
                                            <strong>{{ \App\Models\EnrollmentDocument::DOCUMENT_TYPES[$document->document_type] ?? $document->document_type }}</strong>
                                        </td>
                                        <td>{{ $document->file_name }}</td>
                                        <td>{{ $document->file_size_formatted }}</td>
                                        <td>
                                            <span class="badge badge-{{ $document->status_badge }}">
                                                {{ ucfirst($document->status) }}
                                            </span>
                                        </td>
                                        <td>
                                            <a href="{{ route('enrollment.portal.download-document', $document->id) }}" 
                                               class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-download me-1"></i>Download
                                            </a>
                                        </td>
                                    </tr>
                                    @if($document->verification_notes)
                                        <tr>
                                            <td colspan="5">
                                                <small class="text-muted">
                                                    <strong>Verification Notes:</strong> {{ $document->verification_notes }}
                                                </small>
                                            </td>
                                        </tr>
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="fas fa-file-alt fa-3x text-muted mb-3"></i>
                        <p class="text-muted">No documents uploaded yet.</p>
                        <p class="text-muted">All 6 required documents are missing.</p>
                    </div>
                @endif
            </div>
        </div>
        
        <!-- Application Timeline -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-secondary text-white">
                <h5 class="mb-0"><i class="fas fa-clock me-2"></i>Application Timeline</h5>
            </div>
            <div class="card-body p-4">
                <div class="timeline">
                    <div class="timeline-item">
                        <div class="timeline-marker bg-success"></div>
                        <div class="timeline-content">
                            <h6 class="timeline-title">Application Submitted</h6>
                            <p class="timeline-text mb-1">Your application was successfully submitted.</p>
                            <small class="text-muted">{{ $application->created_at->format('M d, Y g:i A') }}</small>
                        </div>
                    </div>
                    
                    @if($application->reviewed_at)
                        <div class="timeline-item">
                            <div class="timeline-marker bg-primary"></div>
                            <div class="timeline-content">
                                <h6 class="timeline-title">Application Reviewed</h6>
                                <p class="timeline-text mb-1">
                                    Status changed to: <span class="badge badge-{{ $application->status_badge }}">
                                        {{ ucfirst(str_replace('_', ' ', $application->status)) }}
                                    </span>
                                </p>
                                <small class="text-muted">{{ $application->reviewed_at->format('M d, Y g:i A') }}</small>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        
        <div class="text-center mt-4 d-flex flex-wrap justify-content-center gap-2">
            <a href="{{ route('enrollment.portal.status') }}" class="ep-btn ep-btn-outline">
                <i class="fas fa-search"></i> Check Another Application
            </a>
            <a href="{{ route('enrollment.portal.index') }}" class="ep-btn ep-btn-primary">
                <i class="fas fa-home"></i> Back to Home
            </a>
        </div>
    </div>
</div>

@endsection

@section('styles')
<style>
.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 15px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #dee2e6;
}

.timeline-item {
    position: relative;
    margin-bottom: 20px;
}

.timeline-marker {
    position: absolute;
    left: -22px;
    top: 5px;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    border: 3px solid #fff;
    box-shadow: 0 0 0 3px #dee2e6;
}

.timeline-content {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 8px;
    border-left: 3px solid #007bff;
}

.timeline-title {
    margin-bottom: 5px;
    color: #495057;
}

.timeline-text {
    color: #6c757d;
    margin-bottom: 5px;
}
</style>
@endsection
