@extends('layouts.master')
@section('content')
<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">Add Curriculum</h3>
                    <p class="text-muted mb-0">Uses the same grade list as Classes &amp; Subjects and Enrollment.</p>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('curriculum.index') }}">Curriculum</a></li>
                        <li class="breadcrumb-item active">Add</li>
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
            <div class="card-body">
                @if(empty($availableGrades))
                    <div class="alert alert-info mb-0">
                        All grade levels already have a curriculum.
                        <a href="{{ route('curriculum.index') }}">Go back</a> and sync from the subject catalog.
                    </div>
                @else
                    <form method="POST" action="{{ route('curriculum.store') }}">
                        @csrf
                        <div class="mb-3">
                            <label for="grade_level" class="form-label">Grade Level <span class="text-danger">*</span></label>
                            <select name="grade_level" id="grade_level" class="form-control @error('grade_level') is-invalid @enderror" required>
                                <option value="">Select grade</option>
                                @foreach($availableGrades as $grade)
                                    <option value="{{ $grade }}" @selected(old('grade_level') === $grade)>{{ $grade }}</option>
                                @endforeach
                            </select>
                            @error('grade_level')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea name="description" id="description" rows="4"
                                class="form-control @error('description') is-invalid @enderror"
                                placeholder="Optional notes for this grade curriculum">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="alert alert-light border">
                            Subjects for the selected grade will be pulled automatically from the subject catalog.
                        </div>
                        <div class="text-end">
                            <a href="{{ route('curriculum.index') }}" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">Create &amp; Sync</button>
                        </div>
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
