@extends('layouts.enrollment-portal')

@section('title', 'Check Application Status')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="text-center mb-4">
            <h1 class="ep-page-title">Check Application Status</h1>
            <p class="ep-page-subtitle">Enter your application number and email to view your enrollment status.</p>
        </div>

        <div class="ep-card mb-4">
            <div class="ep-card-header">
                <h3><i class="fas fa-magnifying-glass me-2 text-primary"></i>Status Inquiry</h3>
            </div>
            <div class="ep-card-body">
                <form action="{{ route('enrollment.portal.check-status') }}" method="POST">
                    @csrf
                    <div class="ep-form-group">
                        <label class="ep-label" for="application_number">Application Number <span class="required">*</span></label>
                        <input type="text" class="form-control @error('application_number') is-invalid @enderror"
                               id="application_number" name="application_number"
                               value="{{ old('application_number') }}"
                               placeholder="e.g., APP-2025-000001" required>
                        @error('application_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        <div class="ep-hint">You received this when you submitted your application.</div>
                    </div>
                    <div class="ep-form-group">
                        <label class="ep-label" for="email">Email Address <span class="required">*</span></label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror"
                               id="email" name="email" value="{{ old('email') }}"
                               placeholder="Email used in your application" required>
                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <button type="submit" class="ep-btn ep-btn-primary ep-btn-lg ep-btn-block">
                        <i class="fas fa-search"></i> Check Status
                    </button>
                </form>
            </div>
        </div>

        <div class="ep-card mb-4">
            <div class="ep-card-header"><h3>Status Guide</h3></div>
            <div class="ep-card-body">
                <div class="row g-3">
                    @php
                    $statuses = [
                        ['pending', 'Pending', 'Application received, awaiting review.', 'ep-chip-pending', 'clock'],
                        ['under_review', 'Under Review', 'Being evaluated by the registrar.', 'ep-chip-review', 'eye'],
                        ['approved', 'Approved', 'Congratulations! Application accepted.', 'ep-chip-approved', 'check'],
                        ['rejected', 'Rejected', 'Application was not accepted.', 'ep-chip-rejected', 'times'],
                        ['needs_documents', 'Needs Documents', 'Additional documents required.', 'ep-chip-docs', 'file'],
                    ];
                    @endphp
                    @foreach($statuses as $s)
                    <div class="col-md-6">
                        <div class="d-flex align-items-start gap-2">
                            <span class="ep-chip {{ $s[3] }}"><i class="fas fa-{{ $s[4] }}"></i> {{ $s[1] }}</span>
                        </div>
                        <small class="text-muted d-block mt-1 ms-1">{{ $s[2] }}</small>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="ep-card">
            <div class="ep-card-header"><h3><i class="fas fa-headset me-2"></i>Need Help?</h3></div>
            <div class="ep-card-body">
                <div class="ep-info-grid">
                    <div class="ep-credential"><i class="fas fa-phone text-primary"></i><span>(049) 123-4567</span></div>
                    <div class="ep-credential"><i class="fas fa-envelope text-primary"></i><span>enrollment@panoramamontessori.edu.ph</span></div>
                    <div class="ep-credential"><i class="fas fa-clock text-primary"></i><span>Office Hours: 8:00 AM – 5:00 PM</span></div>
                </div>
            </div>
        </div>

        <div class="text-center mt-4">
            <a href="{{ route('enrollment.portal.index') }}" class="ep-btn ep-btn-outline">
                <i class="fas fa-arrow-left"></i> Back to Home
            </a>
        </div>
    </div>
</div>
@endsection
