@extends('layouts.master')
@section('content')
    {{-- message --}}
    {!! Toastr::message() !!}
    <div class="page-wrapper">
        <div class="content container-fluid">

            <div class="page-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="page-title">Academic Years</h3>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active">Academic Years</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="student-group-form">
                <div class="row">
                    <div class="col-lg-4 col-md-6">
                        <div class="form-group">
                            <input type="text" class="form-control" id="searchAcademicYear" placeholder="Search Academic Year...">
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6">
                        <div class="form-group">
                            <select class="form-control" id="status_filter">
                                <option value="">All Status</option>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                                <option value="upcoming">Upcoming</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6">
                        <div class="search-student-btn">
                            <button type="button" class="btn btn-primary" id="filterAcademicYears">Filter</button>
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
                                        <h3 class="page-title">Academic Year Management</h3>
                                    </div>
                                    <div class="col-auto text-end float-end ms-auto download-grp">
                                        <a href="{{ route('academic_years.create') }}" class="btn btn-primary">
                                            <i class="fas fa-plus"></i> Add Academic Year
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <!-- Academic Year Summary Cards -->
                            <div class="row mb-4">
                                <div class="col-lg-3 col-md-6">
                                    <div class="card bg-primary text-white">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between">
                                                <div>
                                                    <h4 class="mb-0" id="totalAcademicYears">{{ $academicYears->count() }}</h4>
                                                    <p class="mb-0">Total Academic Years</p>
                                                </div>
                                                <div class="align-self-center">
                                                    <i class="fas fa-calendar-alt fa-2x"></i>
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
                                                    <h4 class="mb-0" id="activeYears">{{ $academicYears->where('is_active', true)->count() }}</h4>
                                                    <p class="mb-0">Active Years</p>
                                                </div>
                                                <div class="align-self-center">
                                                    <i class="fas fa-check-circle fa-2x"></i>
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
                                                    <h4 class="mb-0" id="upcomingYears">{{ $academicYears->where('start_date', '>', now())->count() }}</h4>
                                                    <p class="mb-0">Upcoming Years</p>
                                                </div>
                                                <div class="align-self-center">
                                                    <i class="fas fa-clock fa-2x"></i>
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
                                                    <h4 class="mb-0" id="currentYear">{{ $academicYears->where('start_date', '<=', now())->where('end_date', '>=', now())->count() }}</h4>
                                                    <p class="mb-0">Current Year</p>
                                                </div>
                                                <div class="align-self-center">
                                                    <i class="fas fa-star fa-2x"></i>
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
                                                    <input class="form-check-input" type="checkbox" value="something" id="selectAllAcademicYears">
                                                </div>
                                            </th>
                                            <th>Academic Year Name</th>
                <th>Start Date</th>
                <th>End Date</th>
                                            <th>Duration</th>
                                            <th>Status</th>
                                            <th class="text-end">Action</th>
            </tr>
        </thead>
                                    <tbody id="academicYearTableBody">
                                        @forelse($academicYears as $year)
                                            <tr>
                                                <td>
                                                    <div class="form-check check-tables">
                                                        <input class="form-check-input" type="checkbox" value="{{ $year->id }}">
                                                    </div>
                                                </td>
                                                <td>
                                                    <h2>
                                                        <a>{{ $year->name }}</a>
                                                    </h2>
                                                    <small class="text-muted">ID: {{ $year->id }}</small>
                                                </td>
                                                <td>
                                                    <strong>{{ \Carbon\Carbon::parse($year->start_date)->format('M d, Y') }}</strong>
                                                </td>
                                                <td>
                                                    <strong>{{ \Carbon\Carbon::parse($year->end_date)->format('M d, Y') }}</strong>
                                                </td>
                                                <td>
                                                    @php
                                                        $startDate = \Carbon\Carbon::parse($year->start_date);
                                                        $endDate = \Carbon\Carbon::parse($year->end_date);
                                                        $duration = $startDate->diffInDays($endDate);
                                                    @endphp
                                                    <span class="badge bg-info">{{ $duration }} days</span>
                                                </td>
                                                <td>
                                                    @php
                                                        $now = \Carbon\Carbon::now();
                                                        $startDate = \Carbon\Carbon::parse($year->start_date);
                                                        $endDate = \Carbon\Carbon::parse($year->end_date);
                                                        
                                                        if ($now->between($startDate, $endDate)) {
                                                            $status = 'Current';
                                                            $badgeClass = 'bg-success';
                                                        } elseif ($now->lt($startDate)) {
                                                            $status = 'Upcoming';
                                                            $badgeClass = 'bg-warning';
                                                        } else {
                                                            $status = 'Completed';
                                                            $badgeClass = 'bg-secondary';
                                                        }
                                                    @endphp
                                                    <span class="badge {{ $badgeClass }}">{{ $status }}</span>
                                                </td>
                                                <td class="text-end">
                                                    <div class="actions">
                                                        <a href="{{ route('academic_years.edit', $year) }}" class="btn btn-sm bg-danger-light">
                                                            <i class="far fa-edit"></i>
                                                        </a>
                        <form action="{{ route('academic_years.destroy', $year) }}" method="POST" style="display:inline-block;">
                            @csrf
                            @method('DELETE')
                                                            <button type="submit" class="btn btn-sm bg-danger-light" onclick="return confirm('Are you sure you want to delete this academic year?')">
                                                                <i class="far fa-trash-alt"></i>
                                                            </button>
                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="7" class="text-center py-5">
                                                    <div class="text-muted">
                                                        <i class="fas fa-calendar-alt fa-3x mb-3"></i>
                                                        <h5>No academic years found</h5>
                                                        <p>No academic years have been created yet.</p>
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

/* Actions */
.actions {
    display: flex;
    justify-content: end;
}

.actions a, .actions button {
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-left: 5px;
    border: none;
    background: transparent;
}

.actions a:hover, .actions button:hover {
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

.badge.bg-secondary {
    background-color: #6c757d !important;
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
    // Filter academic years
    $('#filterAcademicYears').on('click', function() {
        const searchTerm = $('#searchAcademicYear').val().toLowerCase();
        const statusFilter = $('#status_filter').val();
        
        // Show loading state
        $(this).html('<i class="fas fa-spinner fa-spin me-2"></i>Filtering...').prop('disabled', true);
        
        // Filter table rows
        $('#academicYearTableBody tr').each(function() {
            const row = $(this);
            const yearName = row.find('td:nth-child(2) h2 a').text().toLowerCase();
            const status = row.find('td:nth-child(6) .badge').text().toLowerCase();
            
            let showRow = true;
            
            // Search filter
            if (searchTerm && !yearName.includes(searchTerm)) {
                showRow = false;
            }
            
            // Status filter
            if (statusFilter && status !== statusFilter) {
                showRow = false;
            }
            
            row.toggle(showRow);
        });
        
        // Reset button
        $(this).html('Filter').prop('disabled', false);
    });
    
    // Select all functionality
    $('#selectAllAcademicYears').on('change', function() {
        $('.form-check-input').prop('checked', $(this).is(':checked'));
    });
    
    // Auto-filter on search input
    $('#searchAcademicYear').on('keyup', function() {
        $('#filterAcademicYears').click();
    });
    
    // Auto-filter on status change
    $('#status_filter').on('change', function() {
        $('#filterAcademicYears').click();
    });
});
</script>
@endpush

@endsection 