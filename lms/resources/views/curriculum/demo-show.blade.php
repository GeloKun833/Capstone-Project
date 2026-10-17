@extends('layouts.master')
@section('content')
@php
    $back = route('curriculum.index');
    $fileRoute = 'curriculum.demo.material';
@endphp
<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">{{ $outline->title }}</h3>
                    <p class="mb-0"><span class="badge bg-warning text-dark">{{ $outline->document_type }}</span></p>
                </div>
                <div class="col-auto">
                    <a href="{{ $back }}" class="btn btn-outline-secondary">Back</a>
                </div>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if(str_contains($outline->document_type, 'DEMO'))
            <div class="alert alert-warning">
                Demonstration only. This page is not an official DepEd curriculum, an approved school curriculum, or an official OPTLB document.
            </div>
        @endif

        <div class="card mb-3">
            <div class="card-body">
                <p><strong>Academic year:</strong> {{ $outline->academicYear->name ?? '—' }}</p>
                <p><strong>Grade:</strong> {{ $outline->grade_level }}</p>
                <p><strong>Subject:</strong> {{ $outline->subject->subject_name ?? '—' }}</p>
                <p><strong>Description:</strong> {{ $outline->description }}</p>
                <p><strong>Learning objectives:</strong> {{ $outline->learning_objectives }}</p>
                <p class="mb-0"><strong>Sequence:</strong> {{ $outline->sequence_note }}</p>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header"><h5 class="card-title mb-0">Topics</h5></div>
            <div class="card-body">
                @foreach($outline->topics ?? [] as $topic)
                    <div class="mb-3">
                        <h6>Unit {{ $topic['unit'] ?? '' }}: {{ $topic['title'] ?? '' }}</h6>
                        <p class="mb-1">{{ $topic['competencies'] ?? '' }}</p>
                        <p class="mb-1">{{ $topic['activities'] ?? '' }}</p>
                        <p class="text-muted mb-0">{{ $topic['duration'] ?? '' }}</p>
                    </div>
                @endforeach
                <p class="mb-0"><strong>Assessment:</strong> {{ $outline->assessment_methods }}</p>
            </div>
        </div>

        @if($outline->optlbRecord)
            <div class="card mb-3" id="optlb">
                <div class="card-header d-flex justify-content-between">
                    <h5 class="card-title mb-0">{{ $outline->optlbRecord->title }}</h5>
                    <span class="badge bg-warning text-dark">{{ $outline->optlbRecord->demo_label }}</span>
                </div>
                <div class="card-body">
                    <p>{{ $outline->optlbRecord->learning_objectives }}</p>
                    <p style="white-space: pre-line;">{{ $outline->optlbRecord->topic_sequence }}</p>
                    <p>{{ $outline->optlbRecord->suggested_activities }}</p>
                    <p>{{ $outline->optlbRecord->assessment }}</p>
                    <p class="mb-0">{{ $outline->optlbRecord->references }}</p>
                </div>
            </div>
        @endif

        <div class="card">
            <div class="card-header"><h5 class="card-title mb-0">Demo materials</h5></div>
            <div class="card-body">
                @forelse($outline->materials as $material)
                    <div class="mb-3">
                        <p class="mb-1"><strong>{{ $material->file_name }}</strong> · {{ $material->demo_label }}</p>
                        <iframe src="{{ route($fileRoute, $material) }}" title="{{ $material->file_name }}" style="width:100%;height:420px;border:1px solid #e5e7eb;border-radius:8px;"></iframe>
                    </div>
                @empty
                    <p class="text-muted mb-0">No demo file is attached.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
