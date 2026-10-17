@extends('layouts.master')
@section('content')

<div class="page-wrapper">
    <div class="content container-fluid dir-page">
        <div class="page-header">
            <div class="row align-items-start">
                <div class="col">
                    <h3 class="page-title mb-1">Create activity</h3>
                    <p class="dir-subtitle">Save a draft while you work, then post it to one class.</p>
                </div>
                <div class="col-auto">
                    <a href="{{ route('lessons.activities.index', $lesson) }}" class="btn btn-outline-secondary dir-btn">Back</a>
                </div>
            </div>
        </div>

        @if($errors->any())
            <div class="alert alert-danger">
                <strong>The activity was not posted.</strong>
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
