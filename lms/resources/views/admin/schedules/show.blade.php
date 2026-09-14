@extends('layouts.master')
@section('content')

<div class="page-wrapper">
    <div class="content container-fluid dir-page">
        <div class="page-header">
            <div class="row align-items-start">
                <div class="col">
                    <h3 class="page-title mb-1">Schedule Details</h3>
                    <p class="dir-subtitle">Weekly class time and assignment.</p>
                </div>
                <div class="col-auto text-end">
                    <ul class="breadcrumb justify-content-end mb-2">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.schedules.index') }}">Schedules</a></li>
                        <li class="breadcrumb-item active">View</li>
                    </ul>
                    <a href="{{ route('admin.schedules.edit', $schedule) }}" class="btn btn-primary dir-btn">
                        <i class="fas fa-pen me-1"></i> Edit
                    </a>
                </div>
            </div>
        </div>

        <div class="dir-card">
            <div class="dir-toolbar">
                <div>
                    <h5 class="dir-toolbar-title">Class information</h5>
                    <span class="dir-count mt-1">{{ ucfirst($schedule->day_of_week) }}</span>
                </div>
            </div>
            <div class="p-4">
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <div class="dir-person-meta">Teacher</div>
                        <div class="dir-person-name">{{ $schedule->teacher->full_name ?? 'N/A' }}</div>
                    </div>
                    <div class="col-md-4">
                        <div class="dir-person-meta">Section</div>
                        <div class="dir-person-name">{{ $schedule->section->name ?? 'N/A' }}</div>
                        @if($schedule->section)
                            <span class="dir-person-meta">{{ $schedule->section->grade_level }}</span>
                        @endif
                    </div>
                    <div class="col-md-4">
                        <div class="dir-person-meta">Subject</div>
                        <div class="d-flex align-items-center gap-2">
                            <span class="dir-dot" style="background: {{ $schedule->color ?: '#3d5ee1' }};"></span>
                            <span class="dir-person-name">{{ $schedule->subject->subject_name ?? 'N/A' }}</span>
                        </div>
                        @if($schedule->subject?->class)
                            <span class="dir-person-meta">{{ $schedule->subject->class }}</span>
                        @endif
                    </div>
                    <div class="col-md-4">
                        <div class="dir-person-meta">Room</div>
                        <div>{{ $schedule->room->room_name ?? '—' }}</div>
                    </div>
                </div>

                <h5 class="dir-toolbar-title mb-3">Schedule details</h5>
                <div class="row g-3">
                    <div class="col-md-3">
                        <div class="dir-person-meta">Day</div>
                        <span class="dir-day">{{ ucfirst($schedule->day_of_week) }}</span>
                    </div>
                    <div class="col-md-3">
                        <div class="dir-person-meta">Time</div>
                        <div class="dir-time">
                            {{ \Carbon\Carbon::parse($schedule->start_time)->format('h:i A') }}
                            –
                            {{ \Carbon\Carbon::parse($schedule->end_time)->format('h:i A') }}
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="dir-person-meta">Type</div>
                        <span class="dir-chip">{{ ucfirst($schedule->class_type) }}</span>
                    </div>
                    <div class="col-md-3">
                        <div class="dir-person-meta">Status</div>
                        @if($schedule->is_active)
                            <span class="dir-badge dir-badge--active">Active</span>
                        @else
                            <span class="dir-badge dir-badge--disabled">Inactive</span>
                        @endif
                    </div>
                    <div class="col-12">
                        <div class="dir-person-meta">Notes</div>
                        <div>{{ $schedule->notes ?: '—' }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/directory-modern.css') }}?v=20260914c">
@endpush
