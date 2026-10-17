@extends('layouts.master')
@section('content')
<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">Demo course outlines</h3>
                    <p class="text-muted mb-0">DEMO — NOT OFFICIAL. These samples are for testing only. They are not a DepEd curriculum, an approved school curriculum, or an official OPTLB document.</p>
                </div>
                <div class="col-auto">
                    @if($teacherView)
                        <a href="{{ route('lessons.index') }}" class="btn btn-outline-secondary">My Lessons</a>
                    @else
                        <a href="{{ route('curriculum.index') }}" class="btn btn-outline-secondary">Curriculum</a>
                    @endif
                </div>
            </div>
        </div>

        @forelse($groups as $grade => $outlines)
            <h5 class="mt-3">{{ $grade }}</h5>
            <div class="row g-3 mb-3">
                @foreach($outlines as $outline)
                    <div class="col-md-6 col-xl-4">
                        <a class="card h-100 text-decoration-none text-dark"
                            href="{{ route('curriculum.demo.show', $outline) }}">
                            <div class="card-body">
                                <span class="badge bg-warning text-dark mb-2">{{ $outline->document_type }}</span>
                                <h6 class="mb-1">{{ $outline->subject->subject_name ?? 'Subject' }}</h6>
                                <p class="text-muted small mb-2">{{ $outline->academicYear->name ?? '2026–2027' }} · {{ $outline->status }}</p>
                                <p class="small mb-0">{{ $outline->topics[0]['title'] ?? 'Sample topics' }} and {{ max(count($outline->topics) - 1, 0) }} more</p>
                            </div>
                        </a>
                    </div>
                @endforeach
            </div>
        @empty
            <div class="alert alert-light border">No published demo outlines are available for your assigned subjects.</div>
        @endforelse
    </div>
</div>
@endsection
