@extends('layouts.enrollment-registrar')

@section('title', $application->application_number)

@section('topbar-actions')
<a href="{{ route('enrollment.registrar.index') }}" class="ep-btn ep-btn-outline ep-btn-sm"><i class="fas fa-arrow-left"></i> Back</a>
<form action="{{ route('enrollment.registrar.destroy', $application->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this application permanently?')">
    @csrf @method('DELETE')
    <button type="submit" class="ep-btn ep-btn-sm" style="background:#EF4444;color:#fff;"><i class="fas fa-trash"></i></button>
</form>
@endsection

@section('content')
@php
    $chips = ['pending'=>'ep-chip-pending','under_review'=>'ep-chip-review','approved'=>'ep-chip-approved','rejected'=>'ep-chip-rejected','needs_documents'=>'ep-chip-docs'];
@endphp

<div class="ep-card mb-4">
    <div class="ep-card-body">
        <div class="row align-items-center g-3">
            <div class="col-auto">
                <div class="ep-user-avatar" style="width:64px;height:64px;border-radius:16px;font-size:1.5rem;">
                    {{ strtoupper(substr($application->first_name,0,1)) }}{{ strtoupper(substr($application->last_name,0,1)) }}
                </div>
            </div>
            <div class="col">
                <h1 class="ep-page-title mb-1" style="font-size:1.5rem;">{{ $application->full_name }}</h1>
                <p class="ep-page-subtitle mb-2">{{ $application->application_number }} · {{ $application->grade_level_applying_for }} · {{ $application->email }}</p>
                <span class="ep-chip {{ $chips[$application->status] ?? 'ep-chip-docs' }}">{{ ucfirst(str_replace('_',' ',$application->status)) }}</span>
            </div>
            <div class="col-md-auto text-md-end">
                <small class="text-muted d-block">Submitted</small><strong>{{ $application->created_at->format('M d, Y g:i A') }}</strong>
                @if($application->reviewed_at)
                    <small class="text-muted d-block mt-2">Reviewed</small><strong>{{ $application->reviewed_at->format('M d, Y') }}</strong>
                @endif
            </div>
        </div>
    </div>
</div>

        <div class="row">
            <!-- Application Details -->
            <div class="col-lg-8">
                <!-- Application Status -->
                <div class="ep-card mb-4">
                    <div class="ep-card-header">
                        <h5 class="card-title mb-0">Application Status</h5>
                    </div>
                    <div class="ep-card-body">
                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <h4 class="mb-2">
                                    @switch($application->status)
                                        @case('pending')
                                            <span class="badge bg-warning">Pending</span>
                                            @break
                                        @case('approved')
                                            <span class="badge bg-success">Approved</span>
                                            @break
                                        @case('rejected')
                                            <span class="badge bg-danger">Rejected</span>
                                            @break
                                        @case('under_review')
                                            <span class="badge bg-info">Under Review</span>
                                            @break
                                        @case('needs_documents')
                                            <span class="badge bg-secondary">Needs Documents</span>
                                            @break
                                        @default
                                            <span class="badge bg-light text-dark">{{ ucfirst(str_replace('_', ' ', $application->status)) }}</span>
                                    @endswitch
                                </h4>
                                @if($application->reviewed_at)
                                    <small class="text-muted">
                                        Last updated: {{ $application->reviewed_at->format('M d, Y g:i A') }}
                                        @if($application->reviewer)
                                            by {{ $application->reviewer->name }}
                                        @endif
                                    </small>
                                @endif
                            </div>
                            <div class="col-md-6 text-end">
                                <small class="text-muted">Submitted: {{ $application->created_at->format('M d, Y g:i A') }}</small>
                            </div>
                        </div>
                        
                        @if($application->notes)
                            <div class="mt-3">
                                <h6 class="text-info">Notes:</h6>
                                <p class="mb-0">{{ $application->notes }}</p>
                            </div>
                        @endif
                        
                        @if($application->rejection_reason && $application->status === 'rejected')
                            <div class="mt-3">
                                <h6 class="text-danger">Rejection Reason:</h6>
                                <p class="mb-0 text-danger">{{ $application->rejection_reason }}</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Personal Information -->
                <div class="ep-card mb-4">
                    <div class="ep-card-header">
                        <h5 class="card-title mb-0">STUDENT'S INFORMATION</h5>
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
                <div class="ep-card mb-4">
                    <div class="ep-card-header">
                        <h5 class="card-title mb-0">OTHER INFORMATION (for parents)</h5>
                    </div>
                    <div class="ep-card-body">
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
                <div class="ep-card mb-4">
                    <div class="ep-card-header">
                        <h5 class="card-title mb-0">Enrollment Guidelines Information</h5>
                    </div>
                    <div class="ep-card-body">
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
                <div class="ep-card mb-4">
                    <div class="ep-card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-file-alt me-2"></i>Documents Status
                            <span class="badge bg-info ms-2">{{ $application->documents->count() }}/6 uploaded</span>
                        </h5>
                    </div>
                    <div class="ep-card-body">
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
                                                    <span class="badge bg-{{ $document->status_badge }}">
                                                        {{ ucfirst($document->status) }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="btn-group">
                                                        <a href="{{ route('enrollment.portal.download-document', $document->id) }}" 
                                                           class="btn btn-sm btn-outline-primary">
                                                            <i class="fas fa-download"></i>
                                                        </a>
                                                        @if($document->status === 'pending')
                                                            <button type="button" class="btn btn-sm btn-success" 
                                                                    onclick="openVerifyModal({{ $document->id }})"
                                                                    title="Verify Document">
                                                                <i class="fas fa-check"></i>
                                                            </button>
                                                            <button type="button" class="btn btn-sm btn-danger" 
                                                                    onclick="openRejectModal({{ $document->id }})"
                                                                    title="Reject Document">
                                                                <i class="fas fa-times"></i>
                                                            </button>
                                                            
                                                            <!-- Fallback: Direct action buttons if modal fails -->
                                                            <div class="btn-group d-none" id="fallback-{{ $document->id }}">
                                                                <form action="{{ route('enrollment.registrar.verify-document', $document->id) }}" 
                                                                      method="POST" class="d-inline">
                                                                    @csrf
                                                                    <button type="submit" class="btn btn-sm btn-success" 
                                                                            onclick="return confirm('Are you sure you want to verify this document?')">
                                                                        <i class="fas fa-check"></i> Verify
                                                                    </button>
                                                                </form>
                                                                <form action="{{ route('enrollment.registrar.reject-document', $document->id) }}" 
                                                                      method="POST" class="d-inline">
                                                                    @csrf
                                                                    <textarea name="verification_notes" class="form-control form-control-sm mb-2" 
                                                                              placeholder="Rejection reason (required)" required></textarea>
                                                                    <button type="submit" class="btn btn-sm btn-danger" 
                                                                            onclick="return confirm('Are you sure you want to reject this document?')">
                                                                        <i class="fas fa-times"></i> Reject
                                                                    </button>
                                                                </form>
                                                            </div>
                                                        @endif
                                                    </div>
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
            </div>

            <!-- Action Panel -->
            <div class="col-lg-4 ep-action-panel">
                <!-- Review Actions -->
                <div class="ep-card mb-4">
                    <div class="ep-card-header">
                        <h5 class="card-title mb-0">Review Actions</h5>
                    </div>
                    <div class="ep-card-body">
                        @if($application->status === 'pending')
                            <form action="{{ route('enrollment.registrar.mark-under-review', $application->id) }}" method="POST" class="mb-3">
                                @csrf
                                <button type="submit" class="btn btn-info w-100">
                                    <i class="fas fa-search me-2"></i>Mark Under Review
                                </button>
                            </form>
                        @endif

                        @if(in_array($application->status, ['pending', 'under_review', 'needs_documents']))
                            <!-- Approve Form -->
                            <form action="{{ route('enrollment.registrar.approve', $application->id) }}" method="POST" class="mb-3">
                                @csrf
                                <div class="form-group mb-3">
                                    <label>Approval Notes (Optional)</label>
                                    <textarea class="form-control" name="notes" rows="3" 
                                              placeholder="Add any notes about the approval..."></textarea>
                                </div>
                                <button type="submit" class="btn btn-success w-100">
                                    <i class="fas fa-check me-2"></i>Approve Application
                                </button>
                            </form>

                            <!-- Reject Form -->
                            <form action="{{ route('enrollment.registrar.reject', $application->id) }}" method="POST" class="mb-3">
                                @csrf
                                <div class="form-group mb-3">
                                    <label>Rejection Reason <span class="text-danger">*</span></label>
                                    <textarea class="form-control" name="rejection_reason" rows="3" 
                                              placeholder="Please provide a reason for rejection..." required></textarea>
                                </div>
                                <button type="submit" class="btn btn-danger w-100">
                                    <i class="fas fa-times me-2"></i>Reject Application
                                </button>
                            </form>

                            <!-- Needs Documents Form -->
                            <form action="{{ route('enrollment.registrar.needs-documents', $application->id) }}" method="POST" class="mb-3">
                                @csrf
                                <div class="form-group mb-3">
                                    <label>Document Requirements <span class="text-danger">*</span></label>
                                    <textarea class="form-control" name="notes" rows="3" 
                                              placeholder="Please specify what additional documents are needed..." required></textarea>
                                </div>
                                <button type="submit" class="btn btn-warning w-100">
                                    <i class="fas fa-file-plus me-2"></i>Request More Documents
                                </button>
                            </form>
                        @endif

                        <!-- Manual Student Account Creation (Available for all statuses except rejected) -->
                        @if($application->status !== 'rejected')
                            <hr>
                            <div class="alert alert-info py-2 px-3 mb-2">
                                <small><i class="fas fa-info-circle me-1"></i> You can manually create the student account even if documents are incomplete.</small>
                            </div>
                            <button type="button" class="btn btn-primary w-100" onclick="openCreateAccountModal()">
                                <i class="fas fa-user-plus me-2"></i>Create Student Account
                            </button>
                        @endif
                    </div>
                </div>

                <!-- Application Timeline -->
                <div class="ep-card mb-4">
                    <div class="ep-card-header">
                        <h5 class="card-title mb-0">Application Timeline</h5>
                    </div>
                    <div class="ep-card-body">
                        <div class="list-group">
                            <div class="list-group-item">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1">Application Submitted</h6>
                                    <small class="text-muted">{{ $application->created_at->format('M d, Y g:i A') }}</small>
                                </div>
                            </div>
                            
                            @if($application->reviewed_at)
                                <div class="list-group-item">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1">Application Reviewed</h6>
                                        <small class="text-muted">{{ $application->reviewed_at->format('M d, Y g:i A') }}</small>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

<!-- Document Verification Modal -->
<div class="modal fade" id="verifyDocumentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Verify Document</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="verifyDocumentForm" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label>Verification Notes (Optional)</label>
                        <textarea class="form-control" name="verification_notes" rows="3" 
                                  placeholder="Add any notes about the verification..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Verify Document</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Document Rejection Modal -->
<div class="modal fade" id="rejectDocumentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Reject Document</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="rejectDocumentForm" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label>Rejection Reason <span class="text-danger">*</span></label>
                        <textarea class="form-control" name="verification_notes" rows="3" 
                                  placeholder="Please provide a reason for rejection..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Reject Document</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
// Simple, direct functions for opening modals
function openVerifyModal(documentId) {
    console.log('Opening verify modal for document:', documentId);
    
    // Set the form action
    const form = document.getElementById('verifyDocumentForm');
    if (form) {
        form.action = `{{ route('enrollment.registrar.verify-document', '') }}/${documentId}`;
        console.log('Verify form action set to:', form.action);
        
        // Clear any previous notes
        const notesField = form.querySelector('textarea[name="verification_notes"]');
        if (notesField) {
            notesField.value = '';
        }
        
        // Show the modal using jQuery (if available) or vanilla JS
        const modal = document.getElementById('verifyDocumentModal');
        if (modal) {
            // Try jQuery first
            if (typeof $ !== 'undefined') {
                $(modal).modal('show');
                console.log('Modal shown using jQuery');
            } else {
                // Fallback to Bootstrap 5
                const bsModal = new bootstrap.Modal(modal);
                bsModal.show();
                console.log('Modal shown using Bootstrap 5');
            }
        } else {
            console.error('Verify modal element not found');
            alert('Modal not found. Please refresh the page.');
        }
    } else {
        console.error('Verify form element not found');
        alert('Form not found. Please refresh the page.');
    }
}

function openRejectModal(documentId) {
    console.log('Opening reject modal for document:', documentId);
    
    // Set the form action
    const form = document.getElementById('rejectDocumentForm');
    if (form) {
        form.action = `{{ route('enrollment.registrar.reject-document', '') }}/${documentId}`;
        console.log('Reject form action set to:', form.action);
        
        // Clear any previous notes
        const notesField = form.querySelector('textarea[name="verification_notes"]');
        if (notesField) {
            notesField.value = '';
        }
        
        // Show the modal using jQuery (if available) or vanilla JS
        const modal = document.getElementById('rejectDocumentModal');
        if (modal) {
            // Try jQuery first
            if (typeof $ !== 'undefined') {
                $(modal).modal('show');
                console.log('Modal shown using jQuery');
            } else {
                // Fallback to Bootstrap 5
                const bsModal = new bootstrap.Modal(modal);
                bsModal.show();
                console.log('Modal shown using Bootstrap 5');
            }
        } else {
            console.error('Reject modal element not found');
            alert('Modal not found. Please refresh the page.');
        }
    } else {
        console.error('Reject form element not found');
        alert('Form not found. Please refresh the page.');
    }
}

// Fallback function to show direct action buttons
function showFallbackButtons(documentId) {
    console.log('Showing fallback buttons for document:', documentId);
    const fallback = document.getElementById('fallback-' + documentId);
    if (fallback) {
        fallback.classList.remove('d-none');
        fallback.classList.add('d-block');
    }
}

// Debug function to check if everything is loaded
document.addEventListener('DOMContentLoaded', function() {
    console.log('Document loaded');
    console.log('jQuery available:', typeof $ !== 'undefined');
    console.log('Bootstrap available:', typeof bootstrap !== 'undefined');
    console.log('Verify form found:', document.getElementById('verifyDocumentForm') !== null);
    console.log('Reject form found:', document.getElementById('rejectDocumentForm') !== null);
    console.log('Verify modal found:', document.getElementById('verifyDocumentModal') !== null);
    console.log('Reject modal found:', document.getElementById('rejectDocumentModal') !== null);
    
    // Add a button to toggle fallback mode if modals don't work
    const debugPanel = document.createElement('div');
    debugPanel.innerHTML = `
        <div class="alert alert-info mt-3">
            <h6>Debug Panel</h6>
            <button type="button" class="btn btn-sm btn-warning" onclick="toggleFallbackMode()">
                Show Fallback Buttons (if modals don't work)
            </button>
            <button type="button" class="btn btn-sm btn-info" onclick="testModal()">
                Test Modal Functionality
            </button>
        </div>
    `;
    document.querySelector('.card-body').appendChild(debugPanel);
});

function toggleFallbackMode() {
    console.log('Toggling fallback mode');
    document.querySelectorAll('[id^="fallback-"]').forEach(function(fallback) {
        if (fallback.classList.contains('d-none')) {
            fallback.classList.remove('d-none');
            fallback.classList.add('d-block');
        } else {
            fallback.classList.add('d-none');
            fallback.classList.remove('d-block');
        }
    });
}

function testModal() {
    console.log('Testing modal functionality');
    const modal = document.getElementById('verifyDocumentModal');
    if (modal) {
        try {
            if (typeof $ !== 'undefined') {
                $(modal).modal('show');
                console.log('Modal test successful with jQuery');
            } else if (typeof bootstrap !== 'undefined') {
                const bsModal = new bootstrap.Modal(modal);
                bsModal.show();
                console.log('Modal test successful with Bootstrap 5');
            } else {
                console.error('Neither jQuery nor Bootstrap available');
                alert('Modal system not available. Use fallback buttons.');
            }
        } catch (e) {
            console.error('Modal test failed:', e);
            alert('Modal test failed: ' + e.message);
        }
    } else {
        console.error('Modal element not found');
        alert('Modal element not found');
    }
}
</script>

<!-- Create Student Account Modal -->
<div class="modal fade" id="createAccountModal" tabindex="-1" aria-labelledby="createAccountModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ route('enrollment.registrar.create-manual-account', $application->id) }}" method="POST">
                @csrf
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="createAccountModalLabel">
                        <i class="fas fa-user-plus me-2"></i>Create Student Account
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Student Information -->
                    <div class="alert alert-info">
                        <h6 class="fw-bold mb-2"><i class="fas fa-user me-2"></i>Student Information</h6>
                        <table class="table table-sm table-borderless mb-0">
                            <tr>
                                <td class="fw-bold" style="width: 40%;">Full Name:</td>
                                <td>{{ $application->full_name }}</td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Email:</td>
                                <td>{{ $application->email }}</td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Grade Level:</td>
                                <td>{{ $application->grade_level_applying_for }}</td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Documents:</td>
                                <td>
                                    @if($application->documents->count() > 0)
                                        <span class="badge bg-success">{{ $application->documents->count() }} uploaded</span>
                                    @else
                                        <span class="badge bg-warning">No documents</span>
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </div>

                    <!-- Account Credentials -->
                    <div class="mb-3">
                        <label for="username" class="form-label fw-bold">
                            <i class="fas fa-user me-1"></i>Username <span class="text-danger">*</span>
                        </label>
                        <input type="text" 
                               class="form-control" 
                               id="username" 
                               name="username" 
                               value="{{ $application->email }}" 
                               required>
                        <small class="text-muted">This will be used as the login email address.</small>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label fw-bold">
                            <i class="fas fa-lock me-1"></i>Password <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <input type="password" 
                                   class="form-control" 
                                   id="password" 
                                   name="password" 
                                   value="password123" 
                                   required>
                            <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <small class="text-muted">Default password is "password123". Student can change it after login.</small>
                    </div>

                    <div class="mb-3">
                        <label for="student_id_number" class="form-label fw-bold">
                            <i class="fas fa-id-card me-1"></i>Student ID Number <span class="text-muted">(Optional)</span>
                        </label>
                        <input type="text" 
                               class="form-control" 
                               id="student_id_number" 
                               name="student_id_number" 
                               placeholder="Auto-generated if left empty">
                        <small class="text-muted">Leave empty to auto-generate student ID.</small>
                    </div>

                    <!-- Additional Options -->
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" id="send_email" name="send_email" checked>
                        <label class="form-check-label" for="send_email">
                            <i class="fas fa-envelope me-1"></i>Send welcome email with login credentials
                        </label>
                    </div>

                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="require_password_change" name="require_password_change" checked>
                        <label class="form-check-label" for="require_password_change">
                            <i class="fas fa-shield-alt me-1"></i>Require password change on first login
                        </label>
                    </div>

                    <!-- What Will Happen -->
                    <div class="alert alert-success">
                        <h6 class="fw-bold mb-2"><i class="fas fa-check-circle me-2"></i>What Will Happen:</h6>
                        <ul class="mb-0 small">
                            <li>✅ User account created with the specified credentials</li>
                            <li>✅ Student profile created and linked</li>
                            <li>✅ Automatically assigned to appropriate section</li>
                            <li>✅ Enrolled in all subjects for {{ $application->grade_level_applying_for }}</li>
                            <li>✅ Application marked as "approved"</li>
                            <li>✅ Student can login immediately</li>
                        </ul>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>Cancel
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-user-plus me-2"></i>Create Account
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Open Create Account Modal with fallback support
function openCreateAccountModal() {
    console.log('🔵 Opening Create Account Modal...');
    const modal = document.getElementById('createAccountModal');
    
    if (!modal) {
        console.error('❌ Modal element not found');
        alert('Error: Modal not found. Please refresh the page.');
        return;
    }
    
    try {
        // Try Bootstrap 5 first
        if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            console.log('✅ Using Bootstrap 5');
            const bsModal = new bootstrap.Modal(modal);
            bsModal.show();
        }
        // Try jQuery/Bootstrap 4 fallback
        else if (typeof $ !== 'undefined' && $.fn.modal) {
            console.log('✅ Using jQuery Modal');
            $(modal).modal('show');
        }
        // Manual fallback
        else {
            console.log('⚠️ Using manual modal display');
            modal.classList.add('show');
            modal.style.display = 'block';
            modal.setAttribute('aria-modal', 'true');
            modal.setAttribute('role', 'dialog');
            
            // Add backdrop
            const backdrop = document.createElement('div');
            backdrop.className = 'modal-backdrop fade show';
            backdrop.id = 'createAccountModalBackdrop';
            document.body.appendChild(backdrop);
            document.body.classList.add('modal-open');
            
            // Close on backdrop click
            backdrop.addEventListener('click', function() {
                closeCreateAccountModal();
            });
        }
        console.log('✅ Modal opened successfully');
    } catch (e) {
        console.error('❌ Error opening modal:', e);
        alert('Error opening modal: ' + e.message);
    }
}

// Close Create Account Modal
function closeCreateAccountModal() {
    console.log('🔴 Closing Create Account Modal...');
    const modal = document.getElementById('createAccountModal');
    
    if (modal) {
        modal.classList.remove('show');
        modal.style.display = 'none';
        modal.removeAttribute('aria-modal');
        modal.removeAttribute('role');
        
        // Remove backdrop
        const backdrop = document.getElementById('createAccountModalBackdrop');
        if (backdrop) {
            backdrop.remove();
        }
        document.body.classList.remove('modal-open');
    }
}

// Toggle password visibility
document.addEventListener('DOMContentLoaded', function() {
    const toggleBtn = document.getElementById('togglePassword');
    if (toggleBtn) {
        toggleBtn.addEventListener('click', function() {
            const passwordInput = document.getElementById('password');
            const icon = this.querySelector('i');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
    }
    
    // Attach close handlers to modal close buttons
    const closeButtons = document.querySelectorAll('[data-bs-dismiss="modal"]');
    closeButtons.forEach(btn => {
        btn.addEventListener('click', closeCreateAccountModal);
    });
});

console.log('✅ Create Account Modal script loaded');
</script>

@endsection
