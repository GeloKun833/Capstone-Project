@extends('layouts.master')
@section('content')
<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">Add New Semester</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('semesters.index') }}">Semester Management</a></li>
                        <li class="breadcrumb-item active">Add Semester</li>
                    </ul>
                </div>
                <div class="col-auto text-end float-end ms-auto">
                    <a href="{{ route('semesters.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to List
                    </a>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">Semester Information</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('semesters.store') }}" method="POST">
                            @csrf
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="name" class="form-label">
                                            Semester Name <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                               id="name" name="name" value="{{ old('name') }}" 
                                               placeholder="e.g., First Semester, Second Semester">
                                        @error('name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="academic_year_id" class="form-label">
                                            Academic Year <span class="text-danger">*</span>
                                        </label>
                                        <select class="form-control @error('academic_year_id') is-invalid @enderror" 
                                                id="academic_year_id" name="academic_year_id">
                                            <option value="">Select Academic Year</option>
                                            @foreach($academicYears as $year)
                                                <option value="{{ $year->id }}" 
                                                    {{ old('academic_year_id') == $year->id ? 'selected' : '' }}>
                                                    {{ $year->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('academic_year_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row mt-4">
                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Create Semester
                                    </button>
                                    <a href="{{ route('semesters.index') }}" class="btn btn-secondary">
                                        <i class="fas fa-times"></i> Cancel
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">Quick Tips</h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <h6><i class="fas fa-lightbulb"></i> Semester Naming</h6>
                            <p class="mb-0">Use clear, descriptive names like:</p>
                            <ul class="mb-0 mt-2">
                                <li>First Semester</li>
                                <li>Second Semester</li>
                                <li>Summer Term</li>
                                <li>Mid-Year Term</li>
                            </ul>
                        </div>
                        
                        <div class="alert alert-warning">
                            <h6><i class="fas fa-exclamation-triangle"></i> Important</h6>
                            <p class="mb-0">Each semester must be associated with an academic year. Make sure the academic year exists before creating semesters.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.form-group {
    margin-bottom: 1.5rem;
}

.form-label {
    font-weight: 600;
    color: #495057;
    margin-bottom: 0.5rem;
}

.text-danger {
    color: #dc3545 !important;
}

.alert {
    border-radius: 0.375rem;
    border: none;
}

.alert-info {
    background-color: #d1ecf1;
    color: #0c5460;
}

.alert-warning {
    background-color: #fff3cd;
    color: #856404;
}

.alert h6 {
    margin-bottom: 0.5rem;
    font-weight: 600;
}

.alert ul {
    padding-left: 1.2rem;
}

.alert li {
    margin-bottom: 0.25rem;
}
</style>
@endsection 