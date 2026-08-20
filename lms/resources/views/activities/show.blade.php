@extends('layouts.master')
@section('content')
{!! Toastr::message() !!}
<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">{{ $activity->title }}</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('lessons.activities.index', $lesson) }}">Activities</a></li>
                        <li class="breadcrumb-item active">{{ $activity->title }}</li>
                    </ul>
                </div>
                <div class="col-auto">
                    <a href="{{ route('lessons.activities.edit', [$lesson, $activity]) }}" class="btn btn-primary me-2"><i class="fas fa-edit"></i> Edit</a>
                    <a href="{{ route('lessons.activities.submissions', [$lesson, $activity]) }}" class="btn btn-info"><i class="fas fa-inbox"></i> Submissions</a>
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <p><strong>Due Date:</strong> {{ $activity->due_date ? \Carbon\Carbon::parse($activity->due_date)->format('M d, Y') : 'N/A' }}</p>
                <p><strong>Allows Submission:</strong> {{ $activity->allows_submission ? 'Yes' : 'No' }}</p>
                <hr>
                <h5>Instructions</h5>
                <p>{!! nl2br(e($activity->instructions)) !!}</p>
                <p><strong>Submissions:</strong> {{ $activity->submissions->count() }}</p>
            </div>
        </div>
    </div>
</div>
@endsection
