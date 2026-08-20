@extends('layouts.master')
@section('content')

<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <h3 class="page-title">Submission Details</h3>
        </div>
        <div class="card">
            <div class="card-body">
                <p><strong>Student:</strong> {{ $submission->student->first_name }} {{ $submission->student->last_name }}</p>
                <p><strong>Submitted:</strong> {{ $submission->submitted_at ? $submission->submitted_at->format('M d, Y H:i') : $submission->created_at->format('M d, Y H:i') }}</p>
                <p><strong>Status:</strong> <span class="badge bg-info">{{ ucfirst($submission->status) }}</span></p>
                @if($submission->comments)<p><strong>Comments:</strong> {{ $submission->comments }}</p>@endif
                @if($submission->file_path)
                    <a href="{{ asset('storage/' . $submission->file_path) }}" class="btn btn-primary" target="_blank"><i class="fas fa-download"></i> Download File</a>
                @endif
                @if(Auth::user()->role_name === 'Teacher')
                    <a href="{{ route('lessons.activities.grade-submission', [$lesson, $activity, $submission]) }}" class="btn btn-success">Grade Submission</a>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
