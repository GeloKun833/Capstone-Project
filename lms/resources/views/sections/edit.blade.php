@extends('layouts.master')
@section('content')
<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">Edit Section</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('sections.index') }}">Sections</a></li>
                        <li class="breadcrumb-item active">Edit</li>
                    </ul>
                </div>
            </div>
        </div>
        <form method="POST" action="{{ route('sections.update', $section->id) }}">
            @csrf
            @method('PUT')
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Section / Block Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" id="name" class="form-control" required
                                    value="{{ old('name', $section->name) }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="grade_level">Grade Level <span class="text-danger">*</span></label>
                                <select name="grade_level" id="grade_level" class="form-control" required>
                                    <option value="">Select Grade</option>
                                    @foreach($gradeLevels as $grade)
                                        <option value="{{ $grade }}" {{ old('grade_level', $section->grade_level) === $grade ? 'selected' : '' }}>
                                            {{ $grade }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="adviser_id">Adviser</label>
                                <select name="adviser_id" id="adviser_id" class="form-control">
                                    <option value="">-- Select Adviser --</option>
                                    @foreach($teachers as $teacher)
                                        <option value="{{ $teacher->id }}" {{ (string) old('adviser_id', $section->adviser_id) === (string) $teacher->id ? 'selected' : '' }}>
                                            {{ $teacher->full_name ?: ($teacher->user->name ?? 'Unknown Teacher') }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="capacity">Capacity</label>
                                <input type="number" name="capacity" id="capacity" class="form-control" min="1"
                                    value="{{ old('capacity', $section->capacity) }}">
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group">
                                <label for="description">Description</label>
                                <textarea name="description" id="description" class="form-control" rows="3">{{ old('description', $section->description) }}</textarea>
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Update Section</button>
                    <a href="{{ route('sections.index') }}" class="btn btn-secondary">Cancel</a>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
