@extends('layouts.master')
@section('content')
<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row">
                <div class="col-sm-12">
                    <h3 class="page-title">Assign Students to Section: {{ $section->name }} ({{ $section->grade_level }})</h3>
                </div>
            </div>
        </div>
        <form method="POST" action="{{ route('sections.assignStudents', $section->id) }}">
            @csrf
            <div class="card">
                <div class="card-body">
                    <div class="form-group">
                        <label>Select Students</label>
                        <div class="row">
                            @foreach($students as $student)
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="student_ids[]" value="{{ $student->id }}" id="student{{ $student->id }}" {{ in_array($student->id, $assigned) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="student{{ $student->id }}">
                                            {{ $student->first_name }} {{ $student->last_name }}
                                        </label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Assign Students</button>
                    <a href="{{ route('sections.index') }}" class="btn btn-secondary">Back</a>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection 