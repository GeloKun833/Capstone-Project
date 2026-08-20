@extends('layouts.enrollment-portal')

@section('title', 'Welcome')

@section('content')
{{-- Hero --}}
<div class="mb-5">
    <div class="ep-card mb-4" style="background:linear-gradient(135deg,#2563EB 0%,#3B82F6 50%,#60A5FA 100%);border:none;color:#fff;">
        <div class="ep-card-body py-5">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <span class="ep-chip" style="background:rgba(255,255,255,.2);color:#fff;margin-bottom:1rem;">SY 2025–2026 Enrollment Open</span>
                    <h1 class="ep-page-title" style="color:#fff!important;font-size:2.25rem;">Welcome to Our Enrollment Portal</h1>
                    <p style="color:rgba(255,255,255,.9)!important;font-size:1.1rem;max-width:560px;">
                        Begin your educational journey with Panorama Montessori School. Select your enrollment type to get started — it only takes a few minutes.
                    </p>
                    <div class="d-flex flex-wrap gap-2 mt-4">
                        <a href="{{ route('enrollment.portal.create', ['type' => 'new']) }}" class="ep-btn ep-btn-lg" style="background:#fff;color:#2563EB;">
                            <i class="fas fa-rocket"></i> Start Application
                        </a>
                        <a href="{{ route('enrollment.portal.status') }}" class="ep-btn ep-btn-lg ep-btn-outline" style="border-color:rgba(255,255,255,.5);color:#fff;">
                            <i class="fas fa-search"></i> Track Application
                        </a>
                    </div>
                </div>
                <div class="col-lg-4 d-none d-lg-block text-center">
                    <div style="font-size:6rem;opacity:.25;"><i class="fas fa-graduation-cap"></i></div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Quick Stats --}}
<div class="ep-stat-grid mb-5">
    <div class="ep-stat-card">
        <div class="ep-stat-icon blue"><i class="fas fa-user-plus"></i></div>
        <div class="ep-stat-value">3</div>
        <div class="ep-stat-label">Enrollment Types</div>
    </div>
    <div class="ep-stat-card">
        <div class="ep-stat-icon green"><i class="fas fa-clock"></i></div>
        <div class="ep-stat-value">3–5</div>
        <div class="ep-stat-label">Days Processing Time</div>
    </div>
    <div class="ep-stat-card">
        <div class="ep-stat-icon amber"><i class="fas fa-file-lines"></i></div>
        <div class="ep-stat-value">100%</div>
        <div class="ep-stat-label">Digital Application</div>
    </div>
    <div class="ep-stat-card">
        <div class="ep-stat-icon red"><i class="fas fa-headset"></i></div>
        <div class="ep-stat-value">24/7</div>
        <div class="ep-stat-label">Status Tracking</div>
    </div>
</div>

{{-- Enrollment Types --}}
<div class="mb-2"><h2 class="ep-page-title" style="font-size:1.35rem;">Choose Your Enrollment Type</h2></div>
<p class="ep-page-subtitle mb-4">Select the option that best describes your situation</p>

<div class="ep-type-grid mb-5">
    <div class="ep-type-card">
        <div class="ep-type-card-header new">
            <div class="ep-type-icon"><i class="fas fa-user-plus"></i></div>
            <h3 style="color:#fff!important;margin:0;font-weight:800;">New Student</h3>
        </div>
        <div class="ep-type-card-body">
            <p class="text-muted mb-3">First time enrolling at Panorama Montessori School.</p>
            <ul class="ep-type-features">
                <li><i class="fas fa-check-circle"></i> Complete application form</li>
                <li><i class="fas fa-check-circle"></i> Upload required documents</li>
                <li><i class="fas fa-check-circle"></i> No SF9 required</li>
                <li><i class="fas fa-check-circle"></i> Auto section assignment</li>
            </ul>
            <a href="{{ route('enrollment.portal.create', ['type' => 'new']) }}" class="ep-btn ep-btn-primary ep-btn-block ep-btn-lg">
                Start New Student Enrollment <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>

    <div class="ep-type-card">
        <div class="ep-type-card-header returning">
            <div class="ep-type-icon"><i class="fas fa-user-check"></i></div>
            <h3 style="color:#fff!important;margin:0;font-weight:800;">Old Student</h3>
        </div>
        <div class="ep-type-card-body">
            <p class="text-muted mb-3">Returning student re-enrolling for the new school year.</p>
            <ul class="ep-type-features">
                <li><i class="fas fa-check-circle"></i> View subjects & schedule</li>
                <li><i class="fas fa-check-circle"></i> Choose block section</li>
                <li><i class="fas fa-check-circle"></i> Generate enrollment form</li>
                <li><i class="fas fa-check-circle"></i> Fast-track process</li>
            </ul>
            <a href="{{ route('enrollment.old-student.login') }}" class="ep-btn ep-btn-success ep-btn-block ep-btn-lg">
                Login for Old Students <i class="fas fa-sign-in-alt"></i>
            </a>
        </div>
    </div>

    <div class="ep-type-card">
        <div class="ep-type-card-header transfer">
            <div class="ep-type-icon"><i class="fas fa-right-left"></i></div>
            <h3 style="color:#fff!important;margin:0;font-weight:800;">Transferee</h3>
        </div>
        <div class="ep-type-card-body">
            <p class="text-muted mb-3">Transferring from another school.</p>
            <ul class="ep-type-features">
                <li><i class="fas fa-check-circle"></i> Complete application form</li>
                <li><i class="fas fa-check-circle"></i> SF9 from previous school <strong class="text-danger">required</strong></li>
                <li><i class="fas fa-check-circle"></i> Document verification</li>
                <li><i class="fas fa-check-circle"></i> Section assignment</li>
            </ul>
            <a href="{{ route('enrollment.portal.create', ['type' => 'transferee']) }}" class="ep-btn ep-btn-warning ep-btn-block ep-btn-lg">
                Start Transferee Enrollment <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>
</div>

{{-- Bottom Row --}}
<div class="row g-4 mb-5">
    <div class="col-lg-6">
        <div class="ep-card h-100">
            <div class="ep-card-header">
                <h3><i class="fas fa-search me-2 text-primary"></i>Check Application Status</h3>
            </div>
            <div class="ep-card-body">
                <p class="text-muted">Already applied? Track your application using your application number and email.</p>
                <div class="d-flex flex-wrap gap-2 mb-4">
                    <span class="ep-chip ep-chip-pending"><i class="fas fa-clock"></i> Pending</span>
                    <span class="ep-chip ep-chip-review"><i class="fas fa-eye"></i> Under Review</span>
                    <span class="ep-chip ep-chip-approved"><i class="fas fa-check"></i> Approved</span>
                    <span class="ep-chip ep-chip-rejected"><i class="fas fa-times"></i> Rejected</span>
                    <span class="ep-chip ep-chip-docs"><i class="fas fa-file"></i> Needs Documents</span>
                </div>
                <a href="{{ route('enrollment.portal.status') }}" class="ep-btn ep-btn-primary ep-btn-block">
                    <i class="fas fa-magnifying-glass"></i> Check Status Now
                </a>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="ep-card h-100">
            <div class="ep-card-header">
                <h3><i class="fas fa-list-check me-2 text-primary"></i>Enrollment Process</h3>
            </div>
            <div class="ep-card-body">
                <div class="ep-process-list">
                    @foreach(['Select your enrollment type','Complete the application form','Upload required documents','Wait for registrar approval','Receive your login credentials'] as $i => $step)
                    <div class="ep-process-item">
                        <div class="ep-process-num">{{ $i + 1 }}</div>
                        <span>{{ $step }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Requirements & Contact --}}
<div class="ep-card">
    <div class="ep-card-header">
        <h3><i class="fas fa-circle-info me-2 text-primary"></i>Important Information</h3>
    </div>
    <div class="ep-card-body">
        <div class="row g-4">
            <div class="col-md-6">
                <h5 class="fw-bold mb-3">Required Documents</h5>
                <ul class="list-unstyled">
                    @foreach(['Birth Certificate','SF10 (Report Card)','Certificate of Good Moral Character','ID Photo (2×2)','Parent/Guardian ID','SF9 (Transferees only)'] as $doc)
                    <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i>{{ $doc }}</li>
                    @endforeach
                </ul>
            </div>
            <div class="col-md-6">
                <h5 class="fw-bold mb-3">Contact Us</h5>
                <div class="ep-info-grid" style="grid-template-columns:1fr;">
                    <div class="ep-credential"><i class="fas fa-phone text-primary"></i><span>(049) 123-4567</span></div>
                    <div class="ep-credential"><i class="fas fa-envelope text-primary"></i><span>enrollment@panoramamontessori.edu.ph</span></div>
                    <div class="ep-credential"><i class="fas fa-location-dot text-primary"></i><span>Panorama Ville, Brgy. Dita, City of Santa Rosa, Laguna</span></div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
