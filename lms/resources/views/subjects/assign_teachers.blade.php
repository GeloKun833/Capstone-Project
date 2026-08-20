@extends('layouts.master')
@section('content')
<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row">
                <div class="col-sm-12">
                    <h3 class="page-title">Assign Teachers to Subject: {{ $subject->subject_name }}</h3>
                </div>
            </div>
        </div>
        <form method="POST" action="{{ route('subjects.assignTeachers', $subject->id) }}">
            @csrf
            <div class="card">
                <div class="card-body">
                    <div class="form-group">
                        <label>Select Teachers</label>
                        <div class="row">
                            @foreach($teachers as $teacher)
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="teacher_ids[]" value="{{ $teacher->id }}" id="teacher{{ $teacher->id }}" {{ in_array($teacher->id, $assigned) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="teacher{{ $teacher->id }}">
                                            {{ $teacher->full_name ?: $teacher->user->name ?? 'Unknown Teacher' }}
                                        </label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Assign Teachers</button>
                    <a href="{{ route('subject/list/page') }}" class="btn btn-secondary">Back</a>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection 