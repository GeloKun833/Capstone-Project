@extends('layouts.master')
@section('content')
<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">Curriculum Details: {{ $curriculum->grade_level }}</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('curriculum.index') }}">Curriculum Management</a></li>
                        <li class="breadcrumb-item active">Curriculum Details</li>
                    </ul>
                </div>
                <div class="col-auto text-end float-end ms-auto">
                    <a href="{{ route('curriculum.edit', $curriculum->id) }}" class="btn btn-warning">
                        <i class="fas fa-edit"></i> Edit Curriculum
                    </a>
                    <a href="{{ route('curriculum.assignSubjectsForm', $curriculum->id) }}" class="btn btn-primary">
                        <i class="fas fa-link"></i> Assign Subjects
                    </a>
                    <a href="{{ route('curriculum.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to List
                    </a>
                </div>
            </div>
        </div>
        
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        
        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">Curriculum Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="info-item">
                                    <label class="font-weight-bold">Grade Level:</label>
                                    <p class="mb-3">{{ $curriculum->grade_level }}</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-item">
                                    <label class="font-weight-bold">Created:</label>
                                    <p class="mb-3">{{ $curriculum->created_at->format('M d, Y g:i A') }}</p>
                                </div>
                            </div>
                        </div>
                        
                        @if($curriculum->description)
                            <div class="info-item">
                                <label class="font-weight-bold">Description:</label>
                                <p class="mb-3">{{ $curriculum->description }}</p>
                            </div>
                        @endif
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">Assigned Subjects ({{ $curriculum->subjects->count() }})</h5>
                    </div>
                    <div class="card-body">
                        @if($curriculum->subjects->count() > 0)
                            <div class="row">
                                @foreach($curriculum->subjects as $subject)
                                    <div class="col-md-4 mb-3">
                                        <div class="subject-card">
                                            <div class="subject-info">
                                                <h6 class="subject-name">{{ $subject->subject_name }}</h6>
                                                @if($subject->class)
                                                    <small class="text-muted">Class: {{ $subject->class }}</small>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-4">
                                <i class="fas fa-book fa-3x text-muted mb-3"></i>
                                <h5>No subjects assigned</h5>
                                <p class="text-muted">This curriculum doesn't have any subjects assigned yet.</p>
                                <a href="{{ route('curriculum.assignSubjectsForm', $curriculum->id) }}" class="btn btn-primary">
                                    <i class="fas fa-link"></i> Assign Subjects
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">Quick Actions</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="{{ route('curriculum.edit', $curriculum->id) }}" class="btn btn-warning">
                                <i class="fas fa-edit"></i> Edit Curriculum
                            </a>
                            <a href="{{ route('curriculum.assignSubjectsForm', $curriculum->id) }}" class="btn btn-primary">
                                <i class="fas fa-link"></i> Manage Subjects
                            </a>
                            <a href="{{ route('curriculum.index') }}" class="btn btn-secondary">
                                <i class="fas fa-list"></i> Back to List
                            </a>
                        </div>
                    </div>
                </div>
                
                <div class="card mt-3">
                    <div class="card-header">
                        <h5 class="card-title">Curriculum Statistics</h5>
                    </div>
                    <div class="card-body">
                        <div class="stat-item">
                            <div class="stat-label">Total Subjects</div>
                            <div class="stat-value">{{ $curriculum->subjects->count() }}</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-label">Last Updated</div>
                            <div class="stat-value">{{ $curriculum->updated_at->format('M d, Y') }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.info-item label {
    color: #6c757d;
    font-size: 0.875rem;
    margin-bottom: 0.25rem;
}

.info-item p {
    color: #333;
    font-size: 1rem;
    margin-bottom: 0;
}

.subject-card {
    background-color: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 0.375rem;
    padding: 1rem;
    transition: all 0.2s ease-in-out;
}

.subject-card:hover {
    background-color: #e9ecef;
    border-color: #adb5bd;
}

.subject-name {
    color: #495057;
    font-weight: 600;
    margin-bottom: 0.25rem;
}

.stat-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.75rem 0;
    border-bottom: 1px solid #dee2e6;
}

.stat-item:last-child {
    border-bottom: none;
}

.stat-label {
    color: #6c757d;
    font-size: 0.875rem;
}

.stat-value {
    color: #333;
    font-weight: 600;
    font-size: 1rem;
}
</style>
@endsection 