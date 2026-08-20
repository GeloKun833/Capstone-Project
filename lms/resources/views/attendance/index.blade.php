@extends('layouts.master')
@section('content')
    {{-- message --}}
    {!! Toastr::message() !!}
    <div class="page-wrapper">
        <div class="content container-fluid">

            <div class="page-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="page-title">Attendance Summary</h3>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active">Attendance Summary</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="student-group-form">
                <div class="row">
                    <div class="col-lg-4 col-md-6">
                        <div class="form-group">
                            <select class="form-control" id="subject_filter" name="subject_id">
                <option value="">All Subjects</option>
                @foreach($subjects as $subject)
                    <option value="{{ $subject->id }}" {{ request('subject_id') == $subject->id ? 'selected' : '' }}>{{ $subject->subject_name }}</option>
                @endforeach
            </select>
        </div>
                    </div>
                    <div class="col-lg-4 col-md-6">
                        <div class="form-group">
                            <input type="month" class="form-control" id="month_filter" name="month" value="{{ request('month', now()->format('Y-m')) }}">
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6">
                        <div class="search-student-btn">
                            <button type="button" class="btn btn-primary" id="filterAttendance">Filter</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-12">
                    <div class="card card-table">
                        <div class="card-body">
                            <div class="page-header">
                                <div class="row align-items-center">
                                    <div class="col">
                                        <h3 class="page-title">Student Attendance Records</h3>
                                    </div>
                                    <div class="col-auto text-end float-end ms-auto download-grp">
                                        <a href="{{ route('attendance.export', array_merge(request()->all(), ['format' => 'excel'])) }}" class="btn btn-outline-primary me-2">
                                            <i class="fas fa-download"></i> Export Excel
                                        </a>
                                        <a href="{{ route('attendance.export', array_merge(request()->all(), ['format' => 'pdf'])) }}" class="btn btn-outline-danger me-2">
                                            <i class="fas fa-file-pdf"></i> Export PDF
                                        </a>
                                        <a href="{{ route('attendance.create') }}" class="btn btn-primary">
                                            <i class="fas fa-plus"></i> Mark Attendance
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <!-- Attendance Summary Cards -->
                            <div class="row mb-4">
                                <div class="col-lg-3 col-md-6">
                                    <div class="card bg-primary text-white">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between">
                                                <div>
                                                    <h4 class="mb-0" id="totalStudents">{{ $students->count() }}</h4>
                                                    <p class="mb-0">Total Students</p>
                                                </div>
                                                <div class="align-self-center">
                                                    <i class="fas fa-users fa-2x"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-3 col-md-6">
                                    <div class="card bg-success text-white">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between">
                                                <div>
                                                    <h4 class="mb-0" id="avgAttendance">{{ number_format($students->count() > 0 ? collect($summary)->avg('percentage') : 0, 1) }}%</h4>
                                                    <p class="mb-0">Average Attendance</p>
                                                </div>
                                                <div class="align-self-center">
                                                    <i class="fas fa-chart-line fa-2x"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-3 col-md-6">
                                    <div class="card bg-warning text-white">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between">
                                                <div>
                                                    <h4 class="mb-0" id="totalDays">{{ count($days) }}</h4>
                                                    <p class="mb-0">Total Days</p>
                                                </div>
                                                <div class="align-self-center">
                                                    <i class="fas fa-calendar fa-2x"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-3 col-md-6">
                                    <div class="card bg-info text-white">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between">
                                                <div>
                                                    <h4 class="mb-0" id="presentToday">{{ collect($summary)->sum('present') }}</h4>
                                                    <p class="mb-0">Total Present</p>
                                                </div>
                                                <div class="align-self-center">
                                                    <i class="fas fa-check-circle fa-2x"></i>
                                                </div>
                                            </div>
                                        </div>
        </div>
        </div>
        </div>

    <div class="table-responsive">
                                <table class="table border-0 star-student table-hover table-center mb-0 datatable table-striped">
                                    <thead class="student-thread">
                                        <tr>
                                            <th>
                                                <div class="form-check check-tables">
                                                    <input class="form-check-input" type="checkbox" value="something" id="selectAllAttendance">
                                                </div>
                                            </th>
                                            <th>Student Name</th>
                    @foreach($days as $day)
                        <th class="text-center">{{ $day }}</th>
                    @endforeach
                    <th class="text-center">Present</th>
                    <th class="text-center">Total</th>
                                            <th class="text-center">Percentage</th>
                                            <th class="text-end">Action</th>
                </tr>
            </thead>
                                    <tbody id="attendanceTableBody">
                @forelse($students as $student)
                    <tr>
                                                <td>
                                                    <div class="form-check check-tables">
                                                        <input class="form-check-input" type="checkbox" value="{{ $student->id }}">
                                                    </div>
                                                </td>
                                                <td>
                                                    <h2>
                                                        <a>{{ $student->first_name }} {{ $student->last_name }}</a>
                                                    </h2>
                                                    <small class="text-muted">Student ID: {{ $student->id }}</small>
                                                </td>
                        @foreach($days as $day)
                            <td class="text-center">
                                @php $status = $attendanceMap[$student->id][$day] ?? null; @endphp
                                @if($status === 'present')
                                    <span class="badge bg-success">P</span>
                                @elseif($status === 'absent')
                                    <span class="badge bg-danger">A</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                        @endforeach
                                                <td class="text-center">
                                                    <strong>{{ $summary[$student->id]['present'] ?? 0 }}</strong>
                                                </td>
                                                <td class="text-center">
                                                    <strong>{{ $summary[$student->id]['total'] ?? 0 }}</strong>
                                                </td>
                                                <td class="text-center">
                                                    @php
                                                        $percentage = $summary[$student->id]['percentage'] ?? 0;
                                                        $badgeClass = $percentage >= 90 ? 'bg-success' : ($percentage >= 75 ? 'bg-warning' : 'bg-danger');
                                                    @endphp
                                                    <span class="badge {{ $badgeClass }}">{{ $percentage }}%</span>
                                                </td>
                                                <td class="text-end">
                                                    <div class="actions">
                                                        <a href="#" class="btn btn-sm bg-danger-light">
                                                            <i class="far fa-eye"></i>
                                                        </a>
                                                        <a href="#" class="btn btn-sm bg-danger-light">
                                                            <i class="far fa-edit"></i>
                                                        </a>
                                                    </div>
                                                </td>
                    </tr>
                @empty
                    <tr>
                                                <td colspan="{{ count($days) + 7 }}" class="text-center py-5">
                                                    <div class="text-muted">
                                                        <i class="fas fa-users fa-3x mb-3"></i>
                                                        <h5>No students found</h5>
                                                        <p>No students are enrolled in the selected criteria.</p>
                                                    </div>
                                                </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@push('styles')
<style>
/* Admin-style form controls */
.student-group-form {
    background: #fff;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 0 31px 3px rgba(44,50,63,.02);
    margin-bottom: 20px;
}

.student-group-form .form-group {
    margin-bottom: 0;
}

.student-group-form .form-control {
    border: 1px solid #ddd;
    border-radius: 5px;
    height: 45px;
    padding: 10px 15px;
    font-size: 15px;
}

.student-group-form .form-control:focus {
    border-color: #3d5ee1;
    box-shadow: none;
    outline: 0;
}

.search-student-btn .btn {
    height: 45px;
    padding: 10px 20px;
    font-weight: 600;
}

/* Card styling */
.card-table {
    border: 0;
    border-radius: 10px;
    box-shadow: 0 0 31px 3px rgba(44,50,63,.02);
    margin-bottom: 1.875rem;
}

.card-table .card-body {
    padding: 1.5rem;
}

/* Summary cards */
.card.bg-primary {
    background-color: #3d5ee1 !important;
}

.card.bg-success {
    background-color: #7bb13c !important;
}

.card.bg-warning {
    background-color: #ffc107 !important;
}

.card.bg-info {
    background-color: #17a2b8 !important;
}

/* Table styling */
.table {
    color: #333;
    max-width: 100%;
    margin-bottom: 0;
    width: 100%;
}

.table thead th {
    vertical-align: bottom;
    border-bottom: 1px solid #dee2e6;
    font-weight: 600;
    color: #000;
    background-color: #f8f9fa;
    border-color: #eff2f7;
    padding: 15px;
}

.table tbody tr {
    border-bottom: 1px solid #dee2e6;
}

.table tbody td {
    padding: 15px;
    vertical-align: middle;
}

.table-hover tbody tr:hover {
    background-color: #f7f7f7;
}

.table-hover tbody tr:hover td {
    color: #474648;
}

/* Buttons */
.btn {
    border-radius: 5px;
    font-weight: 600;
    transition: all .4s ease;
}

.btn-primary {
    background-color: #3d5ee1;
    border: 1px solid #3d5ee1;
}

.btn-primary:hover {
    background-color: #18aefa;
    border: 1px solid #18aefa;
}

.btn-outline-primary {
    color: #3d5ee1;
    border-color: #3d5ee1;
}

.btn-outline-primary:hover {
    background-color: #18aefa;
    border-color: #18aefa;
    color: #fff;
}

.btn-outline-danger {
    color: #dc3545;
    border-color: #dc3545;
}

.btn-outline-danger:hover {
    background-color: #dc3545;
    border-color: #dc3545;
    color: #fff;
}

/* Actions */
.actions {
    display: flex;
    justify-content: end;
}

.actions a {
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-left: 5px;
}

.actions a:hover {
    background-color: #3d5ee1 !important;
    color: #fff !important;
}

/* Checkbox styling */
.form-check-input {
    width: 18px;
    height: 18px;
    margin-top: 0;
}

.form-check-input:checked {
    background-color: #3d5ee1;
    border-color: #3d5ee1;
}

/* Badge styling */
.badge {
    font-size: 0.75rem;
    padding: 0.375rem 0.75rem;
}

.badge.bg-primary {
    background-color: #3d5ee1 !important;
}

.badge.bg-success {
    background-color: #7bb13c !important;
}

.badge.bg-warning {
    background-color: #ffc107 !important;
    color: #000 !important;
}

.badge.bg-danger {
    background-color: #dc3545 !important;
}

.badge.bg-info {
    background-color: #17a2b8 !important;
}

/* Page header */
.page-header {
    margin-bottom: 1.875rem;
}

.page-header .breadcrumb {
    background-color: transparent;
    color: #6c757d;
    font-size: 1rem;
    font-weight: 500;
    margin-bottom: 0;
    padding: 0;
    margin-left: auto;
}

.page-header .breadcrumb a {
    color: #333;
}

.page-title {
    font-size: 22px;
    font-weight: 500;
    color: #2c323f;
    margin-bottom: 5px;
}

/* Download group */
.download-grp {
    display: flex;
    align-items: center;
}

/* Text styling */
.text-muted {
    color: #6c757d !important;
}

/* Responsive */
@media (max-width: 768px) {
    .student-group-form {
        padding: 15px;
    }
    
    .card-table .card-body {
        padding: 1rem;
    }
    
    .table-responsive {
        font-size: 0.875rem;
    }
    
    .table th, .table td {
        padding: 10px 8px;
    }
}
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // Filter attendance
    $('#filterAttendance').on('click', function() {
        const subjectId = $('#subject_filter').val();
        const month = $('#month_filter').val();
        
        // Show loading state
        $(this).html('<i class="fas fa-spinner fa-spin me-2"></i>Filtering...').prop('disabled', true);
        
        // Build URL with parameters
        let url = '{{ route("attendance.index") }}?';
        if (subjectId) url += 'subject_id=' + subjectId + '&';
        if (month) url += 'month=' + month;
        
        // Redirect to filtered page
        window.location.href = url;
    });
    
    // Select all functionality
    $('#selectAllAttendance').on('change', function() {
        $('.form-check-input').prop('checked', $(this).is(':checked'));
    });
    
    // Auto-submit form on filter change
    $('#subject_filter, #month_filter').on('change', function() {
        $('#filterAttendance').click();
    });
});
</script>
@endpush

@endsection 