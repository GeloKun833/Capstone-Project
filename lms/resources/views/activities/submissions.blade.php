@extends('layouts.master')
@section('content')
    {{-- message --}}
    {!! Toastr::message() !!}
    <div class="page-wrapper">
        <div class="content container-fluid">

            <div class="page-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="page-title">Student Submissions</h3>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('lessons.index') }}">Lesson Planner</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('lessons.show', $lesson) }}">{{ $lesson->title }}</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('lessons.activities.index', $lesson) }}">Activities</a></li>
                            <li class="breadcrumb-item active">Submissions: {{ $activity->title }}</li>
                        </ul>
                    </div>
                    <div class="col-auto text-end float-end ms-auto download-grp">
                        <a href="{{ route('lessons.activities.index', $lesson) }}" class="btn btn-outline-primary me-2">
                            <i class="fas fa-arrow-left"></i> Back to Activities
                        </a>
                        <button type="button" class="btn btn-primary" onclick="exportSubmissions()">
                            <i class="fas fa-download"></i> Export
                        </button>
                    </div>
                </div>
            </div>

            <!-- Activity Summary -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-8">
                                    <h5 class="card-title">{{ $activity->title }}</h5>
                                    <p class="card-text">{{ $activity->instructions }}</p>
                                </div>
                                <div class="col-md-4">
                                    <div class="row text-center">
                                        <div class="col-4">
                                            <div class="stat-item">
                                                <h4>{{ $submissions->count() }}</h4>
                                                <p>Total Submissions</p>
                                            </div>
                                        </div>
                                        <div class="col-4">
                                            <div class="stat-item">
                                                <h4>{{ $submissions->where('status', 'graded')->count() }}</h4>
                                                <p>Graded</p>
                                            </div>
                                        </div>
                                        <div class="col-4">
                                            <div class="stat-item">
                                                <h4>{{ $submissions->where('status', 'submitted')->count() }}</h4>
                                                <p>Pending</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Submissions Table -->
            <div class="row">
                <div class="col-sm-12">
                    <div class="card card-table">
                        <div class="card-body">
                            <div class="page-header">
                                <div class="row align-items-center">
                                    <div class="col">
                                        <h3 class="page-title">All Submissions</h3>
                                    </div>
                                    <div class="col-auto text-end float-end ms-auto download-grp">
                                        <div class="form-group">
                                            <select class="form-control" id="statusFilter">
                                                <option value="">All Status</option>
                                                <option value="submitted">Submitted</option>
                                                <option value="graded">Graded</option>
                                                <option value="late">Late</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            @if($submissions->count() > 0)
                                <div class="table-responsive">
                                    <table class="table border-0 star-student table-hover table-center mb-0 datatable table-striped">
                                        <thead class="student-thread">
                                            <tr>
                                                <th>
                                                    <div class="form-check check-tables">
                                                        <input class="form-check-input" type="checkbox" value="something" id="selectAll">
                                                    </div>
                                                </th>
                                                <th>Student</th>
                                                <th>Submitted</th>
                                                <th>File</th>
                                                <th>Status</th>
                                                <th>Score</th>
                                                <th>Grade</th>
                                                <th class="text-end">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($submissions as $submission)
                                                <tr data-status="{{ $submission->status }}">
                                                    <td>
                                                        <div class="form-check check-tables">
                                                            <input class="form-check-input submission-checkbox" type="checkbox" value="{{ $submission->id }}">
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <h2 class="table-avatar">
                                                            <a>{{ $submission->student->first_name }} {{ $submission->student->last_name }}</a>
                                                            <small>{{ $submission->student->email }}</small>
                                                        </h2>
                                                    </td>
                                                    <td>
                                                        <div>
                                                            <strong>{{ $submission->created_at->format('M d, Y') }}</strong><br>
                                                            <small class="text-muted">{{ $submission->created_at->format('H:i') }}</small>
                                                        </div>
                                                        @if($submission->created_at->gt($activity->due_date))
                                                            <span class="badge bg-danger">Late</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($submission->file_path)
                                                            <a href="{{ asset('storage/' . $submission->file_path) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                                                <i class="fas fa-download"></i> Download
                                                            </a>
                                                            <br>
                                                            <small class="text-muted">{{ $submission->file_name }}</small>
                                                        @else
                                                            <span class="text-muted">No file uploaded</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($submission->status === 'submitted')
                                                            <span class="badge bg-warning">Submitted</span>
                                                        @elseif($submission->status === 'graded')
                                                            <span class="badge bg-success">Graded</span>
                                                        @else
                                                            <span class="badge bg-secondary">Pending</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($submission->status === 'graded')
                                                            <strong>{{ $submission->total_score }}/{{ $submission->max_possible_score }}</strong>
                                                            <br>
                                                            <small class="text-muted">{{ number_format(($submission->total_score / $submission->max_possible_score) * 100, 1) }}%</small>
                                                        @else
                                                            <span class="text-muted">-</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($submission->status === 'graded')
                                                            <span class="badge bg-{{ $submission->letter_grade_color }}">{{ $submission->letter_grade }}</span>
                                                        @else
                                                            <span class="text-muted">-</span>
                                                        @endif
                                                    </td>
                                                    <td class="text-end">
                                                        <div class="actions">
                                                            @if($submission->status === 'submitted')
                                                                <a href="{{ route('lessons.activities.grade-submission', [$lesson, $activity, $submission]) }}" 
                                                                   class="btn btn-sm btn-primary">
                                                                    <i class="fas fa-star"></i> Grade
                                                                </a>
                                                            @elseif($submission->status === 'graded')
                                                                <a href="{{ route('lessons.activities.view-grade', [$lesson, $activity, $submission]) }}" 
                                                                   class="btn btn-sm btn-outline-primary">
                                                                    <i class="fas fa-eye"></i> View
                                                                </a>
                                                                <a href="{{ route('lessons.activities.edit-grade', [$lesson, $activity, $submission]) }}" 
                                                                   class="btn btn-sm btn-outline-warning">
                                                                    <i class="fas fa-edit"></i> Edit
                                                                </a>
                                                            @endif
                                                            <button type="button" class="btn btn-sm btn-outline-info" 
                                                                    onclick="viewSubmissionDetails({{ $submission->id }})">
                                                                <i class="fas fa-info-circle"></i>
                                                            </button>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>

                                <!-- Bulk Actions -->
                                <div class="row mt-3">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <select class="form-control" id="bulkAction">
                                                <option value="">Bulk Actions</option>
                                                <option value="grade">Grade Selected</option>
                                                <option value="export">Export Selected</option>
                                                <option value="delete">Delete Selected</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6 text-end">
                                        <button type="button" class="btn btn-primary" id="applyBulkAction" disabled>
                                            Apply Action
                                        </button>
                                    </div>
                                </div>
                            @else
                                <div class="text-center py-5">
                                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                    <h5 class="text-muted">No Submissions Yet</h5>
                                    <p class="text-muted">Students haven't submitted any work for this activity.</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Submission Details Modal -->
    <div class="modal fade" id="submissionDetailsModal" tabindex="-1" aria-labelledby="submissionDetailsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="submissionDetailsModalLabel">Submission Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="submissionDetailsContent">
                    <!-- Content will be loaded here -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
<style>
    .stat-item {
        padding: 15px;
        border-radius: 8px;
        background-color: #f8f9fa;
    }
    
    .stat-item h4 {
        margin: 0;
        color: #3d5ee1;
        font-weight: 600;
    }
    
    .stat-item p {
        margin: 5px 0 0 0;
        color: #6c757d;
        font-size: 0.875rem;
    }
    
    .table-avatar h2 a {
        color: #333;
        font-weight: 500;
        text-decoration: none;
    }
    
    .table-avatar small {
        color: #6c757d;
        font-size: 0.875rem;
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
    
    .badge.bg-secondary {
        background-color: #6c757d !important;
    }
    
    .actions {
        display: flex;
        gap: 5px;
        justify-content: flex-end;
    }
    
    .btn-sm {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
    }
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // Select all functionality
    $('#selectAll').on('change', function() {
        $('.submission-checkbox').prop('checked', $(this).is(':checked'));
        updateBulkActionButton();
    });
    
    // Individual checkbox change
    $('.submission-checkbox').on('change', function() {
        updateBulkActionButton();
        
        // Update select all checkbox
        let totalCheckboxes = $('.submission-checkbox').length;
        let checkedCheckboxes = $('.submission-checkbox:checked').length;
        
        if (checkedCheckboxes === 0) {
            $('#selectAll').prop('indeterminate', false).prop('checked', false);
        } else if (checkedCheckboxes === totalCheckboxes) {
            $('#selectAll').prop('indeterminate', false).prop('checked', true);
        } else {
            $('#selectAll').prop('indeterminate', true);
        }
    });
    
    // Status filter
    $('#statusFilter').on('change', function() {
        let status = $(this).val();
        if (status) {
            $('tbody tr').hide();
            $('tbody tr[data-status="' + status + '"]').show();
        } else {
            $('tbody tr').show();
        }
    });
    
    // Bulk action
    $('#applyBulkAction').on('click', function() {
        let action = $('#bulkAction').val();
        let selectedIds = $('.submission-checkbox:checked').map(function() {
            return $(this).val();
        }).get();
        
        if (!action) {
            alert('Please select an action');
            return;
        }
        
        if (selectedIds.length === 0) {
            alert('Please select at least one submission');
            return;
        }
        
        if (action === 'delete') {
            if (!confirm('Are you sure you want to delete the selected submissions?')) {
                return;
            }
        }
        
        // Perform bulk action
        performBulkAction(action, selectedIds);
    });
});

function updateBulkActionButton() {
    let checkedCount = $('.submission-checkbox:checked').length;
    $('#applyBulkAction').prop('disabled', checkedCount === 0);
}

function performBulkAction(action, ids) {
    // Implementation for bulk actions
    console.log('Performing', action, 'on', ids);
    // Add AJAX call here
}

function viewSubmissionDetails(submissionId) {
    $.get(`/lessons/{{ $lesson->id }}/activities/{{ $activity->id }}/submissions/${submissionId}/details`, function(data) {
        $('#submissionDetailsContent').html(data);
        $('#submissionDetailsModal').modal('show');
    });
}

function exportSubmissions() {
    window.location.href = `{{ route('lessons.activities.export-submissions', [$lesson, $activity]) }}`;
}
</script>
@endpush 