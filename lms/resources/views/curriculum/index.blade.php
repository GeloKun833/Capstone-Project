@extends('layouts.master')
@section('content')
<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row">
                <div class="col-sm-12">
                    <h3 class="page-title">Curriculum Management</h3>
                </div>
            </div>
        </div>
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        <!-- Curriculum Statistics -->
        <div class="curriculum-stats">
            <div class="row">
                <div class="col-md-3">
                    <div class="stat-item">
                        <span>Total Curricula</span>
                        <span class="stat-value">{{ $curricula->count() }}</span>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-item">
                        <span>Total Subjects</span>
                        <span class="stat-value">{{ $curricula->sum(function($c) { return $c->subjects->count(); }) }}</span>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-item">
                        <span>Active Curricula</span>
                        <span class="stat-value">{{ $curricula->where('subjects', '>', 0)->count() }}</span>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-item">
                        <span>Latest Update</span>
                        <span class="stat-value">{{ $curricula->max('updated_at') ? $curricula->max('updated_at')->format('M d') : 'N/A' }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-graduation-cap text-primary me-2"></i>
                        Curriculum List
                    </h5>
                    <a href="{{ route('curriculum.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add Curriculum
                    </a>
                </div>
                <table class="table table-bordered table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>Grade Level</th>
                            <th>Description</th>
                            <th>Subjects Count</th>
                            <th>Last Updated</th>
                            <th width="280">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($curricula as $curriculum)
                        <tr>
                            <td>
                                <strong>{{ $curriculum->grade_level }}</strong>
                            </td>
                            <td>
                                @if($curriculum->description)
                                    {{ Str::limit($curriculum->description, 50) }}
                                @else
                                    <span class="text-muted">No description</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-primary">{{ $curriculum->subjects->count() }} subjects</span>
                            </td>
                            <td>
                                <small class="text-muted">{{ $curriculum->updated_at->format('M d, Y') }}</small>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('curriculum.show', $curriculum->id) }}" class="btn btn-sm btn-success" title="View Curriculum Details">
                                        <i class="fas fa-info-circle"></i> Details
                                    </a>
                                    <a href="{{ route('curriculum.edit', $curriculum->id) }}" class="btn btn-sm btn-warning" title="Edit Curriculum">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="{{ route('curriculum.assignSubjectsForm', $curriculum->id) }}" class="btn btn-sm btn-primary" title="Assign/Manage Subjects">
                                        <i class="fas fa-link"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-danger delete-btn" title="Delete Curriculum" 
                                            data-id="{{ $curriculum->id }}" data-grade="{{ $curriculum->grade_level }}">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                                
                                <!-- Hidden form for delete -->
                                <form id="delete-form-{{ $curriculum->id }}" action="{{ route('curriculum.destroy', $curriculum->id) }}" method="POST" style="display:none;">
                                    @csrf
                                    @method('DELETE')
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Help Information -->
        <div class="card mt-4">
            <div class="card-header bg-info text-white">
                <h5 class="card-title mb-0">
                    <i class="fas fa-info-circle me-2"></i>
                    How to Use Curriculum Management
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="text-center mb-3">
                            <i class="fas fa-info-circle fa-2x text-success mb-2"></i>
                            <h6>View Details</h6>
                            <small class="text-muted">Click the <strong>Details</strong> button to see comprehensive curriculum information, assigned subjects, and statistics.</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center mb-3">
                            <i class="fas fa-edit fa-2x text-warning mb-2"></i>
                            <h6>Edit Curriculum</h6>
                            <small class="text-muted">Modify grade level, description, and other curriculum settings.</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center mb-3">
                            <i class="fas fa-link fa-2x text-primary mb-2"></i>
                            <h6>Manage Subjects</h6>
                            <small class="text-muted">Assign or remove subjects from the curriculum.</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center mb-3">
                            <i class="fas fa-plus fa-2x text-primary mb-2"></i>
                            <h6>Add New</h6>
                            <small class="text-muted">Create new curricula for different grade levels.</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.table-hover tbody tr:hover {
    background-color: #f8f9fa;
    transition: background-color 0.2s ease;
}

.btn-group .btn {
    margin-right: 2px;
}

.btn-group .btn:last-child {
    margin-right: 0;
}

.btn-success {
    background-color: #28a745;
    border-color: #28a745;
}

.btn-success:hover {
    background-color: #218838;
    border-color: #1e7e34;
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.badge {
    font-size: 0.75rem;
    padding: 0.375rem 0.75rem;
}

.table-dark th {
    background-color: #343a40;
    border-color: #454d55;
    color: #fff;
}

.info-icon {
    color: #17a2b8;
    margin-right: 5px;
}

.curriculum-stats {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 20px;
    border-radius: 10px;
    margin-bottom: 20px;
}

.curriculum-stats h4 {
    color: white;
    margin-bottom: 15px;
}

.stat-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
}

.stat-value {
    font-size: 1.5rem;
    font-weight: bold;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle delete button clicks
    document.querySelectorAll('.delete-btn').forEach(function(button) {
        button.addEventListener('click', function() {
            var id = this.getAttribute('data-id');
            var grade = this.getAttribute('data-grade');
            
            if (confirm('Are you sure you want to delete the curriculum "' + grade + '"? This action cannot be undone.')) {
                document.getElementById('delete-form-' + id).submit();
            }
        });
    });
});
</script>
@endsection 