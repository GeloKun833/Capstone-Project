@extends('layouts.master')
@section('content')
    {{-- message --}}
    {!! Toastr::message() !!}
    <div class="page-wrapper">
        <div class="content container-fluid">
            <div class="page-header">
                <div class="row">
                    <div class="col-sm-12">
                        <div class="page-sub-header">
                            <h3 class="page-title">My Attendance Records</h3>
                            <ul class="breadcrumb">
                                <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                                <li class="breadcrumb-item active">Attendance</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Attendance Summary Cards -->
            <div class="row">
                <div class="col-xl-3 col-sm-6 col-12 d-flex">
                    <div class="card bg-comman w-100">
                        <div class="card-body">
                            <div class="db-widgets d-flex justify-content-between align-items-center">
                                <div class="db-info">
                                    <h6>Total Days</h6>
                                    <h3>{{ $summary['total'] }}</h3>
                                </div>
                                <div class="db-icon">
                                    <i class="fas fa-calendar-alt fa-2x text-primary"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-sm-6 col-12 d-flex">
                    <div class="card bg-comman w-100">
                        <div class="card-body">
                            <div class="db-widgets d-flex justify-content-between align-items-center">
                                <div class="db-info">
                                    <h6>Present</h6>
                                    <h3>{{ $summary['present'] }}</h3>
                                </div>
                                <div class="db-icon">
                                    <i class="fas fa-check-circle fa-2x text-success"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-sm-6 col-12 d-flex">
                    <div class="card bg-comman w-100">
                        <div class="card-body">
                            <div class="db-widgets d-flex justify-content-between align-items-center">
                                <div class="db-info">
                                    <h6>Absent</h6>
                                    <h3>{{ $summary['absent'] }}</h3>
                                </div>
                                <div class="db-icon">
                                    <i class="fas fa-times-circle fa-2x text-danger"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-sm-6 col-12 d-flex">
                    <div class="card bg-comman w-100">
                        <div class="card-body">
                            <div class="db-widgets d-flex justify-content-between align-items-center">
                                <div class="db-info">
                                    <h6>Attendance Rate</h6>
                                    <h3>{{ $summary['percentage'] }}%</h3>
                                </div>
                                <div class="db-icon">
                                    <i class="fas fa-percentage fa-2x text-info"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters Section -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">
                                <i class="fas fa-filter me-2"></i>Filters
                            </h5>
                        </div>
                        <div class="card-body">
                            <form method="GET" action="{{ route('student.attendance') }}" class="row g-3">
                                <div class="col-md-4">
                                    <label for="month" class="form-label">Month</label>
                                    <input type="month" class="form-control" id="month" name="month" 
                                           value="{{ request('month', now()->format('Y-m')) }}">
                                </div>
                                <div class="col-md-4">
                                    <label for="subject_id" class="form-label">Subject</label>
                                    <select class="form-select" id="subject_id" name="subject_id">
                                        <option value="">All Subjects</option>
                                        @foreach($subjects as $subject)
                                            <option value="{{ $subject->id }}" 
                                                {{ request('subject_id') == $subject->id ? 'selected' : '' }}>
                                                {{ $subject->subject_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4 d-flex align-items-end">
                                    <button type="submit" class="btn btn-primary me-2">
                                        <i class="fas fa-search me-1"></i>Filter
                                    </button>
                                    <a href="{{ route('student.attendance') }}" class="btn btn-secondary">
                                        <i class="fas fa-undo me-1"></i>Reset
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Attendance Records Table -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">
                                <i class="fas fa-list me-2"></i>Attendance Records
                            </h5>
                        </div>
                        <div class="card-body">
                            @if($attendances->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Date</th>
                                                <th>Subject</th>
                                                <th>Status</th>
                                                <th>Teacher</th>
                                                <th>Remarks</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($attendances as $attendance)
                                            <tr>
                                                <td>
                                                    <strong>{{ $attendance->date ? \Carbon\Carbon::parse($attendance->date)->format('M d, Y') : 'N/A' }}</strong>
                                                    <br>
                                                    <small class="text-muted">
                                                        {{ $attendance->date ? \Carbon\Carbon::parse($attendance->date)->format('l') : 'N/A' }}
                                                    </small>
                                                </td>
                                                <td>
                                                    <strong>{{ $attendance->subject->subject_name ?? 'N/A' }}</strong>
                                                </td>
                                                <td>
                                                    @if($attendance->status === 'present')
                                                        <span class="badge bg-success">
                                                            <i class="fas fa-check me-1"></i>Present
                                                        </span>
                                                    @else
                                                        <span class="badge bg-danger">
                                                            <i class="fas fa-times me-1"></i>Absent
                                                        </span>
                                                    @endif
                                                </td>
                                                <td>{{ $attendance->teacher->full_name ?? 'N/A' }}</td>
                                                <td>
                                                    @if($attendance->remarks)
                                                        <span class="text-muted">{{ $attendance->remarks }}</span>
                                                    @else
                                                        <span class="text-muted">No remarks</span>
                                                    @endif
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center py-5">
                                    <div class="empty-state">
                                        <i class="fas fa-user-check fa-3x text-muted mb-3"></i>
                                        <h5>No Attendance Records</h5>
                                        <p class="text-muted">No attendance records found for the selected period.</p>
                                        <p class="text-muted">Try selecting a different month or subject.</p>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Attendance Chart (if data exists) -->
            @if($attendances->count() > 0)
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">
                                <i class="fas fa-chart-pie me-2"></i>Attendance Overview
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <canvas id="attendanceChart" width="400" height="200"></canvas>
                                </div>
                                <div class="col-md-6">
                                    <div class="attendance-stats">
                                        <h6>Attendance Statistics</h6>
                                        <div class="progress mb-3">
                                            <div class="progress-bar bg-success" role="progressbar" 
                                                 style="width: {{ $summary['percentage'] }}%" 
                                                 aria-valuenow="{{ $summary['percentage'] }}" 
                                                 aria-valuemin="0" aria-valuemax="100">
                                                {{ $summary['percentage'] }}%
                                            </div>
                                        </div>
                                        <p><strong>Present:</strong> {{ $summary['present'] }} days</p>
                                        <p><strong>Absent:</strong> {{ $summary['absent'] }} days</p>
                                        <p><strong>Total:</strong> {{ $summary['total'] }} days</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>

@push('styles')
<style>
.empty-state {
    padding: 40px 20px;
}

.empty-state i {
    color: #dee2e6;
}

.table th {
    background-color: #f8f9fa;
    border-top: none;
}

.attendance-stats {
    padding: 20px;
    background-color: #f8f9fa;
    border-radius: 8px;
}

.progress {
    height: 25px;
}

.progress-bar {
    font-size: 0.875em;
    line-height: 25px;
}
</style>
@endpush

@push('scripts')
@if($attendances->count() > 0)
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('attendanceChart').getContext('2d');
    const attendanceChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Present', 'Absent'],
            datasets: [{
                data: [{{ $summary['present'] }}, {{ $summary['absent'] }}],
                backgroundColor: [
                    '#28a745',
                    '#dc3545'
                ],
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
});
</script>
@endif
@endpush

@endsection 