@extends('layouts.master')
@section('content')
    {{-- message --}}
    {!! Toastr::message() !!}
    <div class="page-wrapper">
        <div class="content container-fluid">

            <div class="page-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="page-title">Grade Alerts</h3>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active">Grade Alerts</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="student-group-form">
                <div class="row">
                    <div class="col-lg-3 col-md-6">
                        <div class="form-group">
                            <select class="form-control" id="alert_type_filter" name="alert_type">
                                <option value="">All Alert Types</option>
                                <option value="low_grade">Low Grade</option>
                                <option value="performance_drop">Performance Drop</option>
                                <option value="at_risk">At Risk</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="form-group">
                            <select class="form-control" id="status_filter" name="status">
                                <option value="">All Status</option>
                                <option value="active">Active</option>
                                <option value="resolved">Resolved</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="form-group">
                            <select class="form-control" id="student_filter" name="student_id">
                                <option value="">All Students</option>
                                @foreach($students as $student)
                                    <option value="{{ $student->id }}">{{ $student->first_name }} {{ $student->last_name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="search-student-btn">
                            <button type="button" class="btn btn-primary" id="filterAlerts">Filter Alerts</button>
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
                                        <h3 class="page-title">Student Performance Alerts</h3>
                                    </div>
                                    <div class="col-auto text-end float-end ms-auto download-grp">
                                        <button type="button" class="btn btn-outline-primary me-2" id="exportAlerts">
                                            <i class="fas fa-download"></i> Export
                                        </button>
                                        <button type="button" class="btn btn-primary" id="refreshAlerts">
                                            <i class="fas fa-sync-alt"></i> Refresh
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Alert Summary Cards -->
                            <div class="row mb-4">
                                <div class="col-lg-3 col-md-6">
                                    <div class="card bg-danger text-white">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between">
                                                <div>
                                                    <h4 class="mb-0" id="totalAlerts">0</h4>
                                                    <p class="mb-0">Total Alerts</p>
                                                </div>
                                                <div class="align-self-center">
                                                    <i class="fas fa-exclamation-triangle fa-2x"></i>
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
                                                    <h4 class="mb-0" id="activeAlerts">0</h4>
                                                    <p class="mb-0">Active Alerts</p>
                                                </div>
                                                <div class="align-self-center">
                                                    <i class="fas fa-bell fa-2x"></i>
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
                                                    <h4 class="mb-0" id="resolvedAlerts">0</h4>
                                                    <p class="mb-0">Resolved Alerts</p>
                                                </div>
                                                <div class="align-self-center">
                                                    <i class="fas fa-check-circle fa-2x"></i>
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
                                                    <h4 class="mb-0" id="atRiskStudents">0</h4>
                                                    <p class="mb-0">At-Risk Students</p>
                                                </div>
                                                <div class="align-self-center">
                                                    <i class="fas fa-user-times fa-2x"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Alerts Table -->
                            <div class="table-responsive">
                                <table class="table border-0 star-student table-hover table-center mb-0 datatable table-striped">
                                    <thead class="student-thread">
                                        <tr>
                                            <th>
                                                <div class="form-check check-tables">
                                                    <input class="form-check-input" type="checkbox" value="something" id="selectAllAlerts">
                                                </div>
                                            </th>
                                            <th>Student</th>
                                            <th>Alert Type</th>
                                            <th>Subject</th>
                                            <th>Message</th>
                                            <th>Current Value</th>
                                            <th>Threshold</th>
                                            <th>Status</th>
                                            <th>Date</th>
                                            <th class="text-end">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody id="alertsTableBody">
                                        <!-- Sample data will be loaded here -->
                                        <tr>
                                            <td>
                                                <div class="form-check check-tables">
                                                    <input class="form-check-input" type="checkbox" value="1">
                                                </div>
                                            </td>
                                            <td>
                                                <h2>
                                                    <a>John Doe</a>
                                                </h2>
                                                <small class="text-muted">STU001</small>
                                            </td>
                                            <td>
                                                <span class="badge bg-danger">Low Grade</span>
                                            </td>
                                            <td>Mathematics</td>
                                            <td>Student scored below 75% in Mathematics</td>
                                            <td><strong>68%</strong></td>
                                            <td>75%</td>
                                            <td><span class="badge bg-warning">Active</span></td>
                                            <td>2024-01-15</td>
                                            <td class="text-end">
                                                <div class="actions">
                                                    <a href="#" class="btn btn-sm bg-danger-light resolve-alert" data-alert-id="1">
                                                        <i class="fas fa-check"></i>
                                                    </a>
                                                    <a href="#" class="btn btn-sm bg-danger-light">
                                                        <i class="far fa-eye"></i>
                                                    </a>
                                                    <a href="#" class="btn btn-sm bg-danger-light">
                                                        <i class="far fa-edit"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <div class="form-check check-tables">
                                                    <input class="form-check-input" type="checkbox" value="2">
                                                </div>
                                            </td>
                                            <td>
                                                <h2>
                                                    <a>Jane Smith</a>
                                                </h2>
                                                <small class="text-muted">STU002</small>
                                            </td>
                                            <td>
                                                <span class="badge bg-warning">Performance Drop</span>
                                            </td>
                                            <td>Science</td>
                                            <td>Significant performance drop detected</td>
                                            <td><strong>72%</strong></td>
                                            <td>80%</td>
                                            <td><span class="badge bg-warning">Active</span></td>
                                            <td>2024-01-14</td>
                                            <td class="text-end">
                                                <div class="actions">
                                                    <a href="#" class="btn btn-sm bg-danger-light resolve-alert" data-alert-id="2">
                                                        <i class="fas fa-check"></i>
                                                    </a>
                                                    <a href="#" class="btn btn-sm bg-danger-light">
                                                        <i class="far fa-eye"></i>
                                                    </a>
                                                    <a href="#" class="btn btn-sm bg-danger-light">
                                                        <i class="far fa-edit"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <div class="form-check check-tables">
                                                    <input class="form-check-input" type="checkbox" value="3">
                                                </div>
                                            </td>
                                            <td>
                                                <h2>
                                                    <a>Mike Johnson</a>
                                                </h2>
                                                <small class="text-muted">STU003</small>
                                            </td>
                                            <td>
                                                <span class="badge bg-danger">At Risk</span>
                                            </td>
                                            <td>English</td>
                                            <td>Student is at risk of failing</td>
                                            <td><strong>65%</strong></td>
                                            <td>70%</td>
                                            <td><span class="badge bg-success">Resolved</span></td>
                                            <td>2024-01-13</td>
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
.card.bg-danger {
    background-color: #dc3545 !important;
}

.card.bg-warning {
    background-color: #ffc107 !important;
}

.card.bg-success {
    background-color: #7bb13c !important;
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
    // Filter alerts
    $('#filterAlerts').on('click', function() {
        const alertType = $('#alert_type_filter').val();
        const status = $('#status_filter').val();
        const student = $('#student_filter').val();
        
        // Show loading state
        $(this).html('<i class="fas fa-spinner fa-spin me-2"></i>Filtering...').prop('disabled', true);
        
        // Simulate filtering (replace with actual AJAX call)
        setTimeout(function() {
            $('#filterAlerts').html('Filter Alerts').prop('disabled', false);
            
            // Update summary cards
            $('#totalAlerts').text('15');
            $('#activeAlerts').text('8');
            $('#resolvedAlerts').text('7');
            $('#atRiskStudents').text('3');
            
            // Show success message
            alert('Alerts filtered successfully!');
        }, 1000);
    });
    
    // Select all functionality
    $('#selectAllAlerts').on('change', function() {
        $('.form-check-input').prop('checked', $(this).is(':checked'));
    });
    
    // Resolve alert
    $(document).on('click', '.resolve-alert', function() {
        const alertId = $(this).data('alert-id');
        
        if (confirm('Are you sure you want to mark this alert as resolved?')) {
            // Show loading state
            $(this).html('<i class="fas fa-spinner fa-spin"></i>').prop('disabled', true);
            
            // Simulate resolving (replace with actual AJAX call)
            setTimeout(function() {
                // Update the row status
                const row = $(this).closest('tr');
                row.find('td:nth-child(8) .badge').removeClass('bg-warning').addClass('bg-success').text('Resolved');
                row.find('.resolve-alert').remove();
                
                // Update summary cards
                const activeCount = parseInt($('#activeAlerts').text()) - 1;
                const resolvedCount = parseInt($('#resolvedAlerts').text()) + 1;
                $('#activeAlerts').text(activeCount);
                $('#resolvedAlerts').text(resolvedCount);
                
                alert('Alert resolved successfully!');
            }.bind(this), 1000);
        }
    });
    
    // Refresh alerts
    $('#refreshAlerts').on('click', function() {
        $(this).html('<i class="fas fa-spinner fa-spin me-2"></i>Refreshing...').prop('disabled', true);
        
        setTimeout(function() {
            $('#refreshAlerts').html('<i class="fas fa-sync-alt"></i> Refresh').prop('disabled', false);
            alert('Alerts refreshed successfully!');
        }, 1000);
    });
    
    // Export alerts
    $('#exportAlerts').on('click', function() {
        alert('Alert export functionality will be implemented here.');
    });
    
    // Initialize summary cards with sample data
    $('#totalAlerts').text('15');
    $('#activeAlerts').text('8');
    $('#resolvedAlerts').text('7');
    $('#atRiskStudents').text('3');
});
</script>
@endpush

@endsection 