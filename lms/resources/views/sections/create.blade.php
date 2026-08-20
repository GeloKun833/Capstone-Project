@extends('layouts.master')
@section('content')
<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">Add Section</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('class-subject.unified-management') }}">Class & Subject Management</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('sections.index') }}">Sections</a></li>
                        <li class="breadcrumb-item active">Add Section</li>
                    </ul>
                </div>
            </div>
        </div>
        <form method="POST" action="{{ route('sections.store') }}">
            @csrf
            <div class="card">
                <div class="card-body">
                    <div class="form-group">
                        <label for="name">Section Name</label>
                        <input type="text" name="name" id="name" class="form-control" required value="{{ old('name') }}">
                    </div>
                    <div class="form-group">
                        <label for="grade_level">Grade Level</label>
                        <input type="text" name="grade_level" id="grade_level" class="form-control" required value="{{ old('grade_level') }}">
                    </div>
                    <div class="form-group">
                        <label for="adviser_id">Adviser</label>
                        <select name="adviser_id" id="adviser_id" class="form-control">
                            <option value="">-- Select Adviser --</option>
                            @foreach($teachers as $teacher)
                                <option value="{{ $teacher->id }}" {{ old('adviser_id') == $teacher->id ? 'selected' : '' }}>
                                    {{ $teacher->full_name ?: ($teacher->user->name ?? 'Unknown Teacher') }}
                                </option>
                            @endforeach
                        </select>
                        @if($teachers->isEmpty())
                            <small class="text-danger">
                                <i class="fas fa-exclamation-triangle"></i> 
                                No teachers available. Please create teacher users first or sync existing teachers.
                            </small>
                        @else
                            <small class="text-muted">
                                <i class="fas fa-info-circle"></i> 
                                {{ $teachers->count() }} teacher(s) available
                            </small>
                        @endif
                    </div>
                    <div class="form-group">
                        <label for="capacity">Capacity</label>
                        <input type="number" name="capacity" id="capacity" class="form-control" value="{{ old('capacity') }}">
                    </div>
                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea name="description" id="description" class="form-control">{{ old('description') }}</textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Create Section</button>
                    <a href="{{ route('class-subject.unified-management') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Management
                    </a>
                </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection 