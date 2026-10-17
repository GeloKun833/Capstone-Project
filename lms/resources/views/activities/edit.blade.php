@extends('layouts.master')
@section('content')

<div class="page-wrapper">
    <div class="content container-fluid dir-page">
        <div class="page-header">
            <div class="row align-items-start">
                <div class="col">
                    <h3 class="page-title mb-1">{{ $activity->isPublished() ? 'Edit posted activity' : 'Edit draft' }}</h3>
                    <p class="dir-subtitle">{{ $activity->title }}</p>
                </div>
                <div class="col-auto">
                    <a href="{{ route('lessons.activities.show', [$lesson, $activity]) }}" class="btn btn-outline-secondary dir-btn">Back</a>
                </div>
            </div>
        </div>

        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger">
                <strong>The activity was not saved.</strong>
                <ul class="mb-0 mt-2">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @include('activities.partials.form')
    </div>
</div>

@endsection
