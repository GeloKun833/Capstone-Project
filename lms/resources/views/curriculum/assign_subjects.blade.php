@extends('layouts.master')
@section('content')
<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">Assign Subjects to Curriculum: {{ $curriculum->grade_level }}</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('curriculum.index') }}">Curriculum Management</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('curriculum.show', $curriculum->id) }}">{{ $curriculum->grade_level }}</a></li>
                        <li class="breadcrumb-item active">Assign Subjects</li>
                    </ul>
                </div>
            </div>
        </div>
        
        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">Subject Assignment</h5>
                <p class="card-text text-muted">Select the subjects to include in the {{ $curriculum->grade_level }} curriculum.</p>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('curriculum.assignSubjects', $curriculum->id) }}">
                    @csrf
                    
                    @if($subjects->count() > 0)
                        <div class="row mb-3">
                            <div class="col-12">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h6>Available Subjects ({{ $subjects->count() }})</h6>
                                    <div>
                                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="selectAll()">Select All</button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="deselectAll()">Deselect All</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            @foreach($subjects as $subject)
                                <div class="col-md-4 col-lg-3 mb-3">
                                    <div class="subject-checkbox-card">
                                        <div class="form-check">
                                            <input class="form-check-input subject-checkbox" type="checkbox" 
                                                   name="subject_ids[]" value="{{ $subject->id }}" 
                                                   id="subject{{ $subject->id }}" 
                                                   {{ in_array($subject->id, $assigned) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="subject{{ $subject->id }}">
                                                <div class="subject-info">
                                                    <strong>{{ $subject->subject_name }}</strong>
                                                    @if($subject->class)
                                                        <br><small class="text-muted">Class: {{ $subject->class }}</small>
                                                    @endif
                                                </div>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        
                        @error('subject_ids')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        
                        <div class="text-end mt-4">
                            <a href="{{ route('curriculum.show', $curriculum->id) }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Save Assignments
                            </button>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-exclamation-triangle fa-3x text-warning mb-3"></i>
                            <h5>No subjects available</h5>
                            <p class="text-muted">There are no subjects in the system to assign to this curriculum.</p>
                            <a href="{{ route('curriculum.show', $curriculum->id) }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Back to Curriculum
                            </a>
                        </div>
                    @endif
                </form>
            </div>
        </div>
    </div>
</div>

<style>
.subject-checkbox-card {
    background-color: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 0.375rem;
    padding: 1rem;
    transition: all 0.2s ease-in-out;
    height: 100%;
}

.subject-checkbox-card:hover {
    background-color: #e9ecef;
    border-color: #adb5bd;
}

.subject-checkbox-card .form-check {
    margin: 0;
}

.subject-checkbox-card .form-check-input {
    margin-top: 0.25rem;
}

.subject-info {
    margin-left: 0.5rem;
}

.subject-info strong {
    color: #495057;
    font-size: 0.9rem;
}

.subject-info small {
    font-size: 0.8rem;
}
</style>

<script>
function selectAll() {
    document.querySelectorAll('.subject-checkbox').forEach(function(checkbox) {
        checkbox.checked = true;
    });
}

function deselectAll() {
    document.querySelectorAll('.subject-checkbox').forEach(function(checkbox) {
        checkbox.checked = false;
    });
}
</script>
@endsection 