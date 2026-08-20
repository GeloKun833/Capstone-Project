@extends('layouts.master')
@section('content')
<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">Add Curriculum</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('curriculum.index') }}">Curriculum Management</a></li>
                        <li class="breadcrumb-item active">Add Curriculum</li>
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
                <h5 class="card-title">Curriculum Information</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('curriculum.store') }}">
                    @csrf
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="grade_level">Grade Level <span class="text-danger">*</span></label>
                                <input type="text" name="grade_level" id="grade_level" 
                                       class="form-control @error('grade_level') is-invalid @enderror" 
                                       required value="{{ old('grade_level') }}" 
                                       placeholder="e.g., Grade 1, Grade 2, etc.">
                                <small class="form-text text-muted">Enter the grade level for this curriculum.</small>
                                @error('grade_level')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="description">Description</label>
                                <textarea name="description" id="description" rows="4"
                                          class="form-control @error('description') is-invalid @enderror"
                                          placeholder="Enter a description for this curriculum...">{{ old('description') }}</textarea>
                                <small class="form-text text-muted">Provide a brief description of this curriculum.</small>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <div class="text-end">
                        <a href="{{ route('curriculum.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Create Curriculum
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection 