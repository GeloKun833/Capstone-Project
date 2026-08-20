@extends('layouts.master')
@section('content')

<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header"><h3 class="page-title">Study Recommendations</h3></div>
        @if($analysis)
            <div class="card mb-4">
                <div class="card-body">
                    <h5>Performance Summary</h5>
                    <p><strong>Overall Average:</strong> {{ $analysis['overall_average'] ?? 'N/A' }}%</p>
                    <p><strong>Subjects Needing Improvement:</strong> {{ $analysis['improvement_needed'] ?? 0 }}%</p>
                </div>
            </div>
        @endif
        <div class="card">
            <div class="card-header"><h5>Recommended Lessons</h5></div>
            <div class="card-body">
                @forelse($recommendations ?? [] as $lesson)
                    <div class="border-bottom pb-3 mb-3">
                        <h6>{{ $lesson->title }}</h6>
                        <p class="text-muted mb-1">{{ $lesson->subject->subject_name ?? 'Subject' }} — Relevance: {{ $lesson->relevance_score ?? 0 }}</p>
                        <small>{{ $lesson->relevance_reason ?? 'Recommended based on your performance.' }}</small>
                    </div>
                @empty
                    <p class="text-muted">No recommendations available yet. Keep studying!</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
