@extends('layouts.master')
@section('content')
<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row">
                <div class="col-sm-12">
                    <h3 class="page-title">Semester Management</h3>
                </div>
            </div>
        </div>
        
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <!-- Semester Statistics -->
        <div class="semester-stats">
            <div class="row">
                <div class="col-md-4">
                    <div class="stat-item">
                        <span>Total Semesters</span>
                        <span class="stat-value">{{ $semesters->count() }}</span>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-item">
                        <span>Active Academic Years</span>
                        <span class="stat-value">{{ $semesters->pluck('academicYear.name')->unique()->count() }}</span>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-item">
                        <span>Latest Update</span>
                        <span class="stat-value">{{ $semesters->max('updated_at') ? $semesters->max('updated_at')->format('M d') : 'N/A' }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-calendar-week text-primary me-2"></i>
                        Semester List
                    </h5>
                    <a href="{{ route('semesters.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add Semester
                    </a>
                </div>
                
                <table class="table table-bordered table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>Semester Name</th>
                            <th>Academic Year</th>
                            <th>Created Date</th>
                            <th width="200">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($semesters as $semester)
                            <tr>
                                <td>
                                    <strong>{{ $semester->name }}</strong>
                                </td>
                                <td>
                                    <span class="badge bg-info">{{ $semester->academicYear->name ?? 'No Academic Year' }}</span>
                                </td>
                                <td>
                                    <small class="text-muted">{{ $semester->created_at->format('M d, Y') }}</small>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('semesters.edit', $semester) }}" class="btn btn-sm btn-warning" title="Edit Semester">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button type="button" class="btn btn-sm btn-danger delete-btn" title="Delete Semester" 
                                                data-id="{{ $semester->id }}" data-name="{{ $semester->name }}">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                    
                                    <!-- Hidden form for delete -->
                                    <form id="delete-form-{{ $semester->id }}" action="{{ route('semesters.destroy', $semester) }}" method="POST" style="display:none;">
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
                    How to Use Semester Management
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="text-center mb-3">
                            <i class="fas fa-plus fa-2x text-primary mb-2"></i>
                            <h6>Add Semester</h6>
                            <small class="text-muted">Create new semesters and associate them with academic years.</small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-center mb-3">
                            <i class="fas fa-edit fa-2x text-warning mb-2"></i>
                            <h6>Edit Semester</h6>
                            <small class="text-muted">Modify semester names and academic year associations.</small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-center mb-3">
                            <i class="fas fa-calendar-alt fa-2x text-info mb-2"></i>
                            <h6>Academic Years</h6>
                            <small class="text-muted">Manage academic years to organize semesters properly.</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.semester-stats {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 20px;
    border-radius: 10px;
    margin-bottom: 20px;
}

.semester-stats h4 {
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

.badge {
    font-size: 0.75rem;
    padding: 0.375rem 0.75rem;
}

.table-dark th {
    background-color: #343a40;
    border-color: #454d55;
    color: #fff;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle delete button clicks
    document.querySelectorAll('.delete-btn').forEach(function(button) {
        button.addEventListener('click', function() {
            var id = this.getAttribute('data-id');
            var name = this.getAttribute('data-name');
            
            if (confirm('Are you sure you want to delete the semester "' + name + '"? This action cannot be undone.')) {
                document.getElementById('delete-form-' + id).submit();
            }
        });
    });
});
</script>
@endsection 