@extends('layouts.master')
@section('content')
    {{-- message --}}
    {!! Toastr::message() !!}
    <div class="page-wrapper">
        <div class="content container-fluid">

            <div class="page-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="page-title">Activity Rubric Management</h3>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('lessons.index') }}">Lesson Planner</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('lessons.show', $lesson) }}">{{ $lesson->title }}</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('lessons.activities.index', $lesson) }}">Activities</a></li>
                            <li class="breadcrumb-item active">Rubric: {{ $activity->title }}</li>
                        </ul>
                    </div>
                    <div class="col-auto text-end float-end ms-auto download-grp">
                        <a href="{{ route('lessons.activities.index', $lesson) }}" class="btn btn-outline-primary me-2">
                            <i class="fas fa-arrow-left"></i> Back to Activities
                        </a>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addRubricModal">
                            <i class="fas fa-plus"></i> Add Rubric Category
                        </button>
                    </div>
                </div>
            </div>

            <!-- Activity Summary Card -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-8">
                                    <h5 class="card-title">{{ $activity->title }}</h5>
                                    <p class="card-text">{{ $activity->instructions }}</p>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <small class="text-muted">
                                                <i class="fas fa-calendar"></i> Due: {{ $activity->due_date->format('M d, Y') }}
                                            </small>
                                        </div>
                                        <div class="col-md-4">
                                            <small class="text-muted">
                                                <i class="fas fa-upload"></i> Submissions: {{ $activity->submissions->count() }}
                                            </small>
                                        </div>
                                        <div class="col-md-4">
                                            <small class="text-muted">
                                                <i class="fas fa-check-circle"></i> Graded: {{ $activity->submissions->where('status', 'graded')->count() }}
                                            </small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4 text-end">
                                    <div class="alert alert-info mb-0">
                                        <strong>Total Weight: <span id="totalWeight">0</span>%</strong><br>
                                        <small>Rubric categories should total 100%</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Rubric Categories -->
            <div class="row">
                <div class="col-sm-12">
                    <div class="card card-table">
                        <div class="card-body">
                            <div class="page-header">
                                <div class="row align-items-center">
                                    <div class="col">
                                        <h3 class="page-title">Rubric Categories</h3>
                                    </div>
                                </div>
                            </div>

                            @if($rubrics->count() > 0)
                                <div class="table-responsive">
                                    <table class="table border-0 star-student table-hover table-center mb-0 datatable table-striped">
                                        <thead class="student-thread">
                                            <tr>
                                                <th>Category</th>
                                                <th>Description</th>
                                                <th>Max Score</th>
                                                <th>Weight (%)</th>
                                                <th>Status</th>
                                                <th class="text-end">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($rubrics as $rubric)
                                                <tr>
                                                    <td>
                                                        <h2 class="table-avatar">
                                                            <a>{{ $rubric->category_name }}</a>
                                                        </h2>
                                                    </td>
                                                    <td>{{ Str::limit($rubric->description, 50) }}</td>
                                                    <td><strong>{{ $rubric->max_score }}</strong></td>
                                                    <td>
                                                        <span class="badge bg-primary">{{ $rubric->weight }}%</span>
                                                    </td>
                                                    <td>
                                                        @if($rubric->is_active)
                                                            <span class="badge bg-success">Active</span>
                                                        @else
                                                            <span class="badge bg-secondary">Inactive</span>
                                                        @endif
                                                    </td>
                                                    <td class="text-end">
                                                        <div class="actions">
                                                            <button type="button" class="btn btn-sm btn-outline-primary me-2" 
                                                                    onclick="editRubric({{ $rubric->id }})">
                                                                <i class="fas fa-edit"></i>
                                                            </button>
                                                            <form action="{{ route('lessons.activities.destroy-rubric', [$lesson, $activity, $rubric]) }}" 
                                                                  method="POST" class="d-inline">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="btn btn-sm btn-outline-danger" 
                                                                        onclick="return confirm('Are you sure you want to delete this rubric category?')">
                                                                    <i class="fas fa-trash"></i>
                                                                </button>
                                                            </form>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center py-5">
                                    <i class="fas fa-clipboard-list fa-3x text-muted mb-3"></i>
                                    <h5 class="text-muted">No Rubric Categories</h5>
                                    <p class="text-muted">Add rubric categories to start grading this activity.</p>
                                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addRubricModal">
                                        <i class="fas fa-plus"></i> Add First Category
                                    </button>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Submissions Overview -->
            @if($activity->allows_submission && $activity->submissions->count() > 0)
                <div class="row">
                    <div class="col-sm-12">
                        <div class="card card-table">
                            <div class="card-body">
                                <div class="page-header">
                                    <div class="row align-items-center">
                                        <div class="col">
                                            <h3 class="page-title">Student Submissions</h3>
                                        </div>
                                        <div class="col-auto text-end float-end ms-auto download-grp">
                                            <a href="{{ route('lessons.activities.submissions', [$lesson, $activity]) }}" class="btn btn-primary">
                                                <i class="fas fa-eye"></i> View All Submissions
                                            </a>
                                        </div>
                                    </div>
                                </div>

                                <div class="table-responsive">
                                    <table class="table border-0 star-student table-hover table-center mb-0 datatable table-striped">
                                        <thead class="student-thread">
                                            <tr>
                                                <th>Student</th>
                                                <th>Submitted</th>
                                                <th>File</th>
                                                <th>Status</th>
                                                <th>Score</th>
                                                <th class="text-end">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($activity->submissions->take(5) as $submission)
                                                <tr>
                                                    <td>
                                                        <h2 class="table-avatar">
                                                            <a>{{ $submission->student->first_name }} {{ $submission->student->last_name }}</a>
                                                        </h2>
                                                    </td>
                                                    <td>{{ $submission->created_at->format('M d, Y H:i') }}</td>
                                                    <td>
                                                        @if($submission->file_path)
                                                            <a href="{{ asset('storage/' . $submission->file_path) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                                                <i class="fas fa-download"></i> Download
                                                            </a>
                                                        @else
                                                            <span class="text-muted">No file</span>
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
                                                            @endif
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Add Rubric Modal -->
    <div class="modal fade" id="addRubricModal" tabindex="-1" aria-labelledby="addRubricModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form action="{{ route('lessons.activities.store-rubric', [$lesson, $activity]) }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="addRubricModalLabel">Add Rubric Category</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="category_name">Category Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="category_name" name="category_name" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="max_score">Max Score <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="max_score" name="max_score" min="1" max="100" required>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="description">Description <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
                        </div>
                        <div class="form-group">
                            <label for="weight">Weight (%) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="weight" name="weight" min="1" max="100" required>
                            <small class="text-muted">This should be a percentage of the total grade</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Category</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Rubric Modal -->
    <div class="modal fade" id="editRubricModal" tabindex="-1" aria-labelledby="editRubricModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form id="editRubricForm" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                        <h5 class="modal-title" id="editRubricModalLabel">Edit Rubric Category</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="edit_category_name">Category Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="edit_category_name" name="category_name" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="edit_max_score">Max Score <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="edit_max_score" name="max_score" min="1" max="100" required>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="edit_description">Description <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="edit_description" name="description" rows="3" required></textarea>
                        </div>
                        <div class="form-group">
                            <label for="edit_weight">Weight (%) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="edit_weight" name="weight" min="1" max="100" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Category</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('styles')
<style>
    .table-avatar h2 a {
        color: #333;
        font-weight: 500;
        text-decoration: none;
    }
    
    .badge {
        font-size: 0.75rem;
    }
    
    .actions {
        display: flex;
        gap: 5px;
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
    // Calculate total weight
    function updateTotalWeight() {
        let total = 0;
        $('.badge.bg-primary').each(function() {
            total += parseInt($(this).text().replace('%', ''));
        });
        $('#totalWeight').text(total);
        
        if (total > 100) {
            $('#totalWeight').parent().removeClass('alert-info').addClass('alert-warning');
        } else if (total === 100) {
            $('#totalWeight').parent().removeClass('alert-info alert-warning').addClass('alert-success');
        } else {
            $('#totalWeight').parent().removeClass('alert-success alert-warning').addClass('alert-info');
        }
    }
    
    updateTotalWeight();
    
    // Weight validation
    $('#weight, #edit_weight').on('input', function() {
        let value = parseInt($(this).val());
        if (value > 100) {
            $(this).addClass('is-invalid');
            $(this).next('.invalid-feedback').remove();
            $(this).after('<div class="invalid-feedback">Weight cannot exceed 100%</div>');
        } else {
            $(this).removeClass('is-invalid');
            $(this).next('.invalid-feedback').remove();
        }
    });
});

function editRubric(rubricId) {
    // Fetch rubric data and populate modal
    $.get(`/lessons/{{ $lesson->id }}/activities/{{ $activity->id }}/rubric/${rubricId}/edit`, function(data) {
        $('#edit_category_name').val(data.category_name);
        $('#edit_max_score').val(data.max_score);
        $('#edit_description').val(data.description);
        $('#edit_weight').val(data.weight);
        $('#editRubricForm').attr('action', `/lessons/{{ $lesson->id }}/activities/{{ $activity->id }}/rubric/${rubricId}`);
        $('#editRubricModal').modal('show');
    });
}
</script>
@endpush 