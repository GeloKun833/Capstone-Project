@extends('layouts.master')
@section('content')
<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="gc-wrap">
            <div class="page-header">
                <h3 class="page-title mb-1">Create announcement</h3>
                <ul class="breadcrumb mb-3">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('announcements.index') }}">Announcements</a></li>
                    <li class="breadcrumb-item active">Create</li>
                </ul>
            </div>
            @include('announcements.partials.composer', [
                'formAction' => route('announcements.store'),
                'sections' => $sections,
            ])
        </div>
    </div>
</div>
@endsection

@push('styles')
@include('announcements.partials.classroom-styles')
@endpush

