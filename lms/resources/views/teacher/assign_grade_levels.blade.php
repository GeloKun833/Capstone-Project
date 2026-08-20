@extends('layouts.master')
@section('content')
<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row">
                <div class="col-sm-12">
                    <h3 class="page-title">Assign Grade Levels to {{ $teacher->full_name }}</h3>
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
        <form method="POST" action="{{ route('teacher.assignGradeLevels', $teacher->id) }}">
            @csrf
            <div class="card">
                <div class="card-body">
                    <div class="form-group">
                        <label>Select Grade Levels <span class="text-danger">*</span></label>
                        <small class="form-text text-muted">Check the grade levels this teacher will handle.</small>
                        <div class="row">
                            @forelse($gradeLevels as $level)
                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input class="form-check-input @error('grade_levels') is-invalid @enderror" type="checkbox" name="grade_levels[]" value="{{ $level }}" id="grade{{ $loop->index }}" {{ in_array($level, $assigned) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="grade{{ $loop->index }}">
                                            {{ $level }}
                                        </label>
                                    </div>
                                </div>
                            @empty
                                <div class="col-12">
                                    <div class="alert alert-warning">No grade levels available to assign.</div>
                                </div>
                            @endforelse
                        </div>
                        @error('grade_levels')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>
                    <button type="submit" class="btn btn-primary">Assign Grade Levels</button>
                    <a href="{{ route('teacher/list/page') }}" class="btn btn-secondary">Back</a>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection 