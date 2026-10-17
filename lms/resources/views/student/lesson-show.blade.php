@extends('layouts.master')
@section('content')

<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">{{ $lesson->title }}</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                        <li class="breadcrumb-item">
                            <a href="{{ route('student.class.detail', ['enrollmentId' => $enrollment->id, 'tab' => 'lessons']) }}">
                                {{ $enrollment->subject->subject_name ?? 'Class' }}
                            </a>
                        </li>
                        <li class="breadcrumb-item active">Lesson</li>
                    </ul>
                </div>
                <div class="col-auto">
                    <a href="{{ route('student.class.detail', ['enrollmentId' => $enrollment->id, 'tab' => 'lessons']) }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Class
                    </a>
                </div>
            </div>
        </div>

        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <span class="badge {{ $lesson->status_badge }}">{{ ucfirst($lesson->status) }}</span>
                                <span class="badge bg-light text-dark ms-1">Lesson #{{ $lesson->id }}</span>
                            </div>
                            <div class="text-muted">
                                {{ optional($lesson->lesson_date)->format('M d, Y') }}
                            </div>
                        </div>

                        <h4 class="mb-3">{{ $lesson->title }}</h4>
                        <p class="mb-4" style="white-space: pre-line;">{{ $lesson->description }}</p>

                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <div class="border rounded p-3 h-100">
                                    <small class="text-muted d-block">Subject</small>
                                    <strong>{{ $lesson->subject->subject_name ?? 'N/A' }}</strong>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="border rounded p-3 h-100">
                                    <small class="text-muted d-block">Section</small>
                                    <strong>{{ $lesson->section->name ?? 'N/A' }}</strong>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="border rounded p-3 h-100">
                                    <small class="text-muted d-block">Teacher</small>
                                    <strong>{{ optional($lesson->teacher)->full_name ?? optional($lesson->teacher)->name ?? 'N/A' }}</strong>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="border rounded p-3 h-100">
                                    <small class="text-muted d-block">Academic Period</small>
                                    <strong>
                                        {{ optional($lesson->academicYear)->name ?? 'N/A' }}
                                        —
                                        {{ optional($lesson->semester)->name ?? 'N/A' }}
                                    </strong>
                                </div>
                            </div>
                        </div>

                        @if($lesson->file_url)
                            @php
                                $materialName = $lesson->file_name ?: 'Lesson material';
                                $materialExt = strtoupper(pathinfo($materialName, PATHINFO_EXTENSION));
                                $materialKind = $materialExt === 'PDF'
                                    ? 'pdf'
                                    : (in_array($materialExt, ['JPG', 'JPEG', 'PNG', 'GIF', 'WEBP'], true) ? 'image' : 'file');
                            @endphp
                            <div class="mt-3">
                                <h6 class="mb-2">Lesson Materials</h6>
                                <p class="mb-2"><strong>{{ $materialName }}</strong></p>
                                @if($materialKind === 'pdf')
                                    <iframe class="lesson-material-frame" src="{{ $lesson->file_url }}" title="{{ $materialName }}"></iframe>
                                @elseif($materialKind === 'image')
                                    <img class="lesson-material-image" src="{{ $lesson->file_url }}" alt="{{ $materialName }}">
                                @else
                                    <p class="text-muted mb-0">{{ $materialName }} is attached to this lesson.</p>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>

                <div class="card mt-3">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Activities</h5>
                    </div>
                    <div class="card-body">
                        @forelse($lesson->activities as $activity)
                            @php
                                $submission = ($mySubmissions ?? collect())->get($activity->id);
                                $isGraded = $submission && $submission->status === 'graded';
                            @endphp
                            <div class="border rounded p-3 mb-3">
                                <div class="d-flex justify-content-between align-items-start gap-3">
                                    <div class="flex-grow-1">
                                        <div class="fw-semibold">{{ $activity->title ?? ('Activity #' . $activity->id) }}</div>
                                        <small class="text-muted d-block mb-2">
                                            Due: {{ $activity->formattedDue() }}
                                            @if($activity->isQuiz() && $activity->available_at && $activity->available_at->gt(now('Asia/Manila')))
                                                · Starts {{ $activity->available_at->timezone('Asia/Manila')->format('M j, g:i A') }}
                                            @endif
                                        </small>

                                        @if($isGraded)
                                            <div class="d-flex flex-wrap gap-2 mb-2">
                                                <span class="badge" style="background:#198754;color:#fff;">Graded</span>
                                                <span class="badge" style="background:#0d6efd;color:#fff;">
                                                    Score: {{ $submission->total_score }}{{ $submission->max_possible_score ? ' / ' . $submission->max_possible_score : '' }}
                                                </span>
                                                @if($submission->percentage !== null)
                                                    <span class="badge" style="background:#0dcaf0;color:#062830;">
                                                        {{ number_format((float) $submission->percentage, 1) }}%
                                                    </span>
                                                @endif
                                                @if($submission->letter_grade)
                                                    <span class="badge" style="background:#198754;color:#fff;">{{ $submission->letter_grade }}</span>
                                                @endif
                                            </div>
                                            @if($submission->feedback)
                                                <div class="bg-light border rounded p-2 mt-1">
                                                    <small class="text-muted d-block mb-1"><i class="fas fa-comment me-1"></i>Teacher Comments</small>
                                                    <div style="white-space: pre-line;">{{ $submission->feedback }}</div>
                                                </div>
                                            @else
                                                <small class="text-muted">No teacher comments yet.</small>
                                            @endif
                                        @elseif($submission)
                                            <span class="badge bg-warning text-dark">Submitted — waiting for grade</span>
                                        @else
                                            <span class="badge bg-secondary">Not submitted</span>
                                        @endif
                                    </div>
                                    <div class="text-nowrap">
                                        @php
                                            $quizOpensLater = $activity->isQuiz()
                                                && $activity->available_at
                                                && $activity->available_at->gt(now('Asia/Manila'));
                                        @endphp
                                        @if(Route::has('student.activities.show'))
                                            <a href="{{ route('student.activities.show', [$lesson->id, $activity->id]) }}" class="btn btn-sm {{ $quizOpensLater ? 'btn-outline-secondary' : 'btn-outline-primary' }}">
                                                {{ $quizOpensLater ? 'Starts '.$activity->available_at->timezone('Asia/Manila')->format('g:i A') : 'Open' }}
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @empty
                            <p class="text-muted mb-0">No activities added to this lesson yet.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
.lesson-material-frame {
    width: 100%;
    height: 70vh;
    min-height: 480px;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    background: #f8fafc;
}
.lesson-material-image {
    display: block;
    max-width: 100%;
    height: auto;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
}
</style>
@endpush

@endsection
