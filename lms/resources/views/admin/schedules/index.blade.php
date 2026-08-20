@extends('layouts.master')
@section('content')


<div class="page-wrapper">
    <div class="content container-fluid">
        
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">Class Schedules</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Class Schedules</li>
                    </ul>
                </div>
                <div class="col-auto text-end">
                    <a href="{{ route('admin.schedules.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Create Schedule
                    </a>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="card">
            <div class="card-body">
                <form method="GET" action="{{ route('admin.schedules.index') }}">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Section</label>
                                <select name="section_id" class="form-control">
                                    <option value="">All Sections</option>
                                    @foreach($sections as $section)
                                        <option value="{{ $section->id }}" {{ request('section_id') == $section->id ? 'selected' : '' }}>
                                            {{ $section->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Teacher</label>
                                <select name="teacher_id" class="form-control">
                                    <option value="">All Teachers</option>
                                    @foreach($teachers as $teacher)
                                        <option value="{{ $teacher->id }}" {{ request('teacher_id') == $teacher->id ? 'selected' : '' }}>
                                            {{ $teacher->full_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Day of Week</label>
                                <select name="day_of_week" class="form-control">
                                    <option value="">All Days</option>
                                    @foreach($days as $day)
                                        <option value="{{ $day }}" {{ request('day_of_week') == $day ? 'selected' : '' }}>
                                            {{ ucfirst($day) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>&nbsp;</label>
                                <button type="submit" class="btn btn-primary form-control">
                                    <i class="fas fa-filter"></i> Filter
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Schedules Table -->
        <div class="row">
            <div class="col-sm-12">
                <div class="card card-table">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover table-center mb-0 datatable table-striped">
                                <thead class="student-thread">
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
                                                <span class="badge bg-primary">{{ ucfirst($schedule->day_of_week) }}</span>
                                            </td>
                                            <td>
                                                <strong>{{ Carbon\Carbon::parse($schedule->start_time)->format('h:i A') }}</strong>
                                                <br>
                                                <small class="text-muted">{{ Carbon\Carbon::parse($schedule->end_time)->format('h:i A') }}</small>
                                            </td>
                                            <td>
                                                <h2 class="table-avatar">
                                                    <span style="width: 30px; height: 30px; border-radius: 50%; background: {{ $schedule->color }}; display: inline-block;"></span>
                                                    <a>{{ $schedule->subject->subject_name ?? 'N/A' }}</a>
                                                </h2>
                                            </td>
                                            <td>{{ $schedule->section->name ?? 'N/A' }}</td>
                                            <td>{{ $schedule->teacher->full_name ?? 'N/A' }}</td>
                                            <td>{{ $schedule->room->room_name ?? 'TBD' }}</td>
                                            <td>
                                                <span class="badge bg-info">{{ ucfirst($schedule->class_type ?? 'lecture') }}</span>
                                            </td>
                                            <td>
                                                @if($schedule->is_active)
                                                    <span class="badge bg-success">Active</span>
                                                @else
                                                    <span class="badge bg-secondary">Inactive</span>
                                                @endif
                                            </td>
                                            <td class="text-end">
                                                <div class="actions">
                                                    <a href="{{ route('admin.schedules.edit', $schedule) }}" class="btn btn-sm bg-success-light me-2">
                                                        <i class="fas fa-pen"></i>
                                                    </a>
                                                    <form action="{{ route('admin.schedules.destroy', $schedule) }}" method="POST" style="display: inline-block;" onsubmit="return confirm('Are you sure you want to delete this schedule?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm bg-danger-light">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="9" class="text-center py-5">
                                                <div class="mb-3">
                                                    <i class="fas fa-calendar-alt text-muted" style="font-size: 3rem; opacity: 0.5;"></i>
                                                </div>
                                                <h5 class="text-muted">No class schedules found</h5>
                                                <p class="text-muted">Create your first class schedule to get started.</p>
                                                <a href="{{ route('admin.schedules.create') }}" class="btn btn-primary mt-3">
                                                    <i class="fas fa-plus"></i> Create Schedule
                                                </a>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        
                        @if($schedules->hasPages())
                            <div class="mt-3">
                                {{ $schedules->links() }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

