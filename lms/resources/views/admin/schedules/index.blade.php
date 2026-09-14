@extends('layouts.master')
@section('content')

<div class="page-wrapper">
    <div class="content container-fluid dir-page">
        <div class="page-header">
            <div class="row align-items-start">
                <div class="col">
                    <h3 class="page-title mb-1">Class Schedules</h3>
                    <p class="dir-subtitle">Assign class times by teacher, section, and subject.</p>
                </div>
                <div class="col-auto text-end">
                    <ul class="breadcrumb justify-content-end mb-2">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Class Schedules</li>
                    </ul>
                    <a href="{{ route('admin.schedules.create') }}" class="btn btn-primary dir-btn">
                        <i class="fas fa-plus me-1"></i> Create Schedule
                    </a>
                </div>
            </div>
        </div>

        <div class="dir-card dir-filters">
            <form method="GET" action="{{ route('admin.schedules.index') }}">
                <div class="row g-2 align-items-end">
                    <div class="col-lg-3 col-md-6">
                        <label class="form-label">Section</label>
                        <select name="section_id" class="form-control">
                            <option value="">All Sections</option>
                            @foreach($sections as $section)
                                <option value="{{ $section->id }}" {{ request('section_id') == $section->id ? 'selected' : '' }}>
                                    {{ $section->name }}@if($section->grade_level) ({{ $section->grade_level }})@endif
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <label class="form-label">Teacher</label>
                        <select name="teacher_id" class="form-control">
                            <option value="">All Teachers</option>
                            @foreach($teachers as $teacher)
                                <option value="{{ $teacher->id }}" {{ request('teacher_id') == $teacher->id ? 'selected' : '' }}>
                                    {{ $teacher->full_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <label class="form-label">Day of Week</label>
                        <select name="day_of_week" class="form-control">
                            <option value="">All Days</option>
                            @foreach($days as $day)
                                <option value="{{ $day }}" {{ request('day_of_week') == $day ? 'selected' : '' }}>
                                    {{ ucfirst($day) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-3 col-md-6 pb-3">
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary dir-btn flex-fill">
                                <i class="fas fa-filter me-1"></i> Filter
                            </button>
                            <a href="{{ route('admin.schedules.index') }}" class="btn btn-outline-secondary dir-btn">Clear</a>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <div class="dir-card">
            <div class="dir-toolbar">
                <div>
                    <h5 class="dir-toolbar-title">Weekly class times</h5>
                    <span class="dir-count mt-1">{{ $schedules->total() }} schedule{{ $schedules->total() === 1 ? '' : 's' }}</span>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table dir-table mb-0">
                    <thead>
                        <tr>
                            <th>Day</th>
                            <th>Time</th>
                            <th>Subject</th>
                            <th>Section</th>
                            <th>Teacher</th>
                            <th>Room</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($schedules as $schedule)
                            <tr>
                                <td>
                                    <span class="dir-day">{{ ucfirst($schedule->day_of_week) }}</span>
                                </td>
                                <td>
                                    <div class="dir-time">{{ \Carbon\Carbon::parse($schedule->start_time)->format('h:i A') }}</div>
                                    <span class="dir-person-meta">{{ \Carbon\Carbon::parse($schedule->end_time)->format('h:i A') }}</span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="dir-dot" style="background: {{ $schedule->color ?: '#3d5ee1' }};"></span>
                                        <div>
                                            <span class="dir-person-name">{{ $schedule->subject->subject_name ?? 'N/A' }}</span>
                                            @if($schedule->subject?->class)
                                                <span class="dir-person-meta">{{ $schedule->subject->class }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div>{{ $schedule->section->name ?? 'N/A' }}</div>
                                    @if($schedule->section?->grade_level)
                                        <span class="dir-person-meta">{{ $schedule->section->grade_level }}</span>
                                    @endif
                                </td>
                                <td>{{ $schedule->teacher->full_name ?? 'N/A' }}</td>
                                <td>{{ $schedule->room->room_name ?? 'TBD' }}</td>
                                <td>
                                    <span class="dir-chip">{{ ucfirst($schedule->class_type ?? 'lecture') }}</span>
                                </td>
                                <td>
                                    @if($schedule->is_active)
                                        <span class="dir-badge dir-badge--active">Active</span>
                                    @else
                                        <span class="dir-badge dir-badge--disabled">Inactive</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <div class="d-inline-flex gap-1">
                                        <a href="{{ route('admin.schedules.edit', $schedule) }}" class="dir-icon-btn" title="Edit">
                                            <i class="fas fa-pen"></i>
                                        </a>
                                        <form action="{{ route('admin.schedules.destroy', $schedule) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this class schedule?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="dir-icon-btn is-danger" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9">
                                    <div class="dir-empty">
                                        <i class="fas fa-clock d-block"></i>
                                        <h5 class="mt-2 mb-1">No class schedules found</h5>
                                        <p class="mb-3">Create a class schedule to show it on teacher, student, and parent calendars.</p>
                                        <a href="{{ route('admin.schedules.create') }}" class="btn btn-primary dir-btn">
                                            <i class="fas fa-plus me-1"></i> Create Schedule
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($schedules->hasPages())
                <div class="px-3 py-3">
                    {{ $schedules->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/directory-modern.css') }}?v=20260914c">
@endpush
