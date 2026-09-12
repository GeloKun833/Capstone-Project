@extends('layouts.master')
@section('content')

<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">Schedule Details</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.schedules.index') }}">Schedules</a></li>
                        <li class="breadcrumb-item active">View</li>
                    </ul>
                </div>
                <div class="col-auto">
                    <a href="{{ route('admin.schedules.edit', $schedule) }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-edit"></i> Edit
                    </a>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <h5 class="mb-3">Class Information</h5>
                <div class="row mb-4">
                    <div class="col-md-4 mb-2"><strong>Teacher:</strong> {{ $schedule->teacher->full_name ?? 'N/A' }}</div>
                    <div class="col-md-4 mb-2"><strong>Section:</strong> {{ $schedule->section->name ?? 'N/A' }}
                        @if($schedule->section)
                            <span class="text-muted">({{ $schedule->section->grade_level }})</span>
                        @endif
                    </div>
                    <div class="col-md-4 mb-2"><strong>Subject:</strong> {{ $schedule->subject->subject_name ?? 'N/A' }}
                        @if($schedule->subject?->class)
                            <span class="text-muted">({{ $schedule->subject->class }})</span>
                        @endif
                    </div>
                    <div class="col-md-4 mb-2"><strong>Room:</strong> {{ $schedule->room->room_name ?? '—' }}</div>
                </div>

                <h5 class="mb-3">Schedule Details</h5>
                <div class="row">
                    <div class="col-md-3 mb-2"><strong>Day:</strong> {{ ucfirst($schedule->day_of_week) }}</div>
                    <div class="col-md-3 mb-2"><strong>Time:</strong>
                        {{ \Carbon\Carbon::parse($schedule->start_time)->format('h:i A') }}
                        –
                        {{ \Carbon\Carbon::parse($schedule->end_time)->format('h:i A') }}
                    </div>
                    <div class="col-md-3 mb-2"><strong>Type:</strong> {{ ucfirst($schedule->class_type) }}</div>
                    <div class="col-md-3 mb-2"><strong>Status:</strong>
                        @if($schedule->is_active)
                            <span class="badge bg-success">Active</span>
                        @else
                            <span class="badge bg-secondary">Inactive</span>
                        @endif
                    </div>
                    <div class="col-12 mt-2">
                        <strong>Notes:</strong>
                        <div class="text-muted">{{ $schedule->notes ?: '—' }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
