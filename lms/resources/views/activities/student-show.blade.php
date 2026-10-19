@extends('layouts.master')
@section('content')

@php
    $enrollment = null;
    if (auth()->user()?->student && $lesson->subject_id) {
        $enrollment = auth()->user()->student->enrollments()
            ->where('subject_id', $lesson->subject_id)
            ->where('status', 'active')
            ->first();
    }
@endphp

<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">{{ $activity->title }}</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                        @if($enrollment)
                            <li class="breadcrumb-item">
                                <a href="{{ route('student.class.detail', ['enrollmentId' => $enrollment->id, 'tab' => 'lessons']) }}">
                                    {{ $lesson->subject->subject_name ?? 'Class' }}
                                </a>
                            </li>
                            <li class="breadcrumb-item">
                                <a href="{{ route('student.lessons.show', [$enrollment->id, $lesson->id]) }}">
                                    {{ $lesson->title }}
                                </a>
                            </li>
                        @else
                            <li class="breadcrumb-item">{{ $lesson->title }}</li>
                        @endif
                        <li class="breadcrumb-item active">{{ $activity->title }}</li>
                    </ul>
                    <p class="text-muted mb-0">
                        {{ $lesson->section?->grade_level }}
                        {{ $lesson->section?->name }}
                        · {{ $lesson->subject->subject_name ?? $lesson->title }}
                        · Due {{ $activity->formattedDue() }}
                        @if($activity->points)
                            · {{ $activity->points }} points
                        @elseif(\App\Support\DescriptiveScale::isDescriptiveGradeLevel($lesson->section?->grade_level))
                            · Descriptive, no point score
                        @endif
                    </p>
                </div>
                <div class="col-auto">
                    @if($enrollment)
                        <a href="{{ route('student.lessons.show', [$enrollment->id, $lesson->id]) }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Lesson
                        </a>
                    @endif
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-body">
                <h5>Instructions</h5>
                <p>{!! nl2br(e($activity->instructions)) !!}</p>
                @if($activity->activity_type)
                    <p class="text-muted mb-0">{{ $activity->typeLabel() }}</p>
                @endif
            </div>
        </div>

        @if($activity->attachments)
            <div class="card mb-4">
                <div class="card-body">
                    <h5>Materials</h5>
                    <div class="d-grid gap-3">
                        @foreach($activity->attachments as $file)
                            <div>
                                <a href="{{ asset('storage/'.$file['path']) }}" target="_blank" rel="noopener">{{ $file['name'] ?? 'Open file' }}</a>
                                @if(\Illuminate\Support\Str::startsWith($file['mime'] ?? '', 'image/'))
                                    <img src="{{ asset('storage/'.$file['path']) }}" alt="{{ $file['name'] ?? 'Attachment' }}" class="d-block mt-2 rounded" style="max-width: 100%; max-height: 240px;">
                                @elseif(($file['mime'] ?? '') === 'application/pdf')
                                    <iframe src="{{ asset('storage/'.$file['path']) }}" title="{{ $file['name'] ?? 'PDF' }}" class="d-block mt-2 w-100 rounded" style="height: 240px; border: 1px solid #e5e7eb;"></iframe>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        @if($activity->isQuiz())
            @if($existingSubmission)
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="alert alert-success mb-3">
                            Your quiz was graded automatically
                            {{ $existingSubmission->submitted_at?->format('M d, Y H:i') ?? $existingSubmission->created_at->format('M d, Y H:i') }}.
                        </div>
                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <div class="border rounded p-3 h-100 text-center">
                                    <small class="text-muted d-block">Score</small>
                                    <strong class="fs-4">{{ $existingSubmission->total_score }} / {{ $existingSubmission->max_possible_score }}</strong>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="border rounded p-3 h-100 text-center">
                                    <small class="text-muted d-block">Percentage</small>
                                    <strong class="fs-4">{{ number_format((float) ($existingSubmission->percentage ?? 0), 1) }}%</strong>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="border rounded p-3 h-100 text-center">
                                    <small class="text-muted d-block">Letter Grade</small>
                                    <span class="badge bg-{{ $existingSubmission->letter_grade_color }} fs-6">{{ $existingSubmission->letter_grade ?? '-' }}</span>
                                </div>
                            </div>
                        </div>
                        @foreach($activity->quiz_questions ?? [] as $index => $question)
                            @php $result = $existingSubmission->quiz_answers[$index] ?? null; @endphp
                            <div class="border rounded p-3 mb-2">
                                <strong>{{ $index + 1 }}. {{ $question['prompt'] ?? '' }}</strong>
                                <p class="mb-1 {{ ! empty($result['correct']) ? 'text-success' : 'text-danger' }}">
                                    {{ ! empty($result['correct']) ? 'Correct' : 'Incorrect' }}
                                    · {{ $result['points'] ?? 0 }} / {{ $question['points'] ?? 0 }}
                                </p>
                                <p class="mb-0 text-muted">Answer key: {{ $question['choices'][$question['answer']] ?? '—' }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            @else
                <div class="card">
                    <div class="card-body">
                        <form action="{{ route('student.activities.submit', [$lesson, $activity]) }}" method="POST">
                            @csrf
                            @foreach($activity->quiz_questions ?? [] as $index => $question)
                                <fieldset class="border rounded p-3 mb-3">
                                    <legend class="float-none w-auto px-2 fs-6">{{ $index + 1 }}. {{ $question['prompt'] ?? '' }} <span class="text-muted">({{ $question['points'] ?? 0 }} pts)</span></legend>
                                    @foreach($question['choices'] ?? [] as $choiceIndex => $choice)
                                        <label class="d-flex gap-2 align-items-start mb-2">
                                            <input type="radio" name="answers[{{ $index }}]" value="{{ $choiceIndex }}" @checked((string) old('answers.'.$index) === (string) $choiceIndex)>
                                            <span>{{ $choice }}</span>
                                        </label>
                                    @endforeach
                                    @error('answers.'.$index)<div class="text-danger small">{{ $message }}</div>@enderror
                                </fieldset>
                            @endforeach
                            <button type="submit" class="btn btn-primary">Submit quiz</button>
                        </form>
                    </div>
                </div>
            @endif
        @elseif($activity->allows_submission)
            @if($existingSubmission)
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="alert alert-success mb-3">
                            You submitted this activity on
                            {{ $existingSubmission->submitted_at?->format('M d, Y H:i') ?? $existingSubmission->created_at->format('M d, Y H:i') }}.
                        </div>

                        @if($existingSubmission->status === 'graded')
                            <h5 class="mb-3">Your Grade</h5>
                            <div class="row g-3 mb-3">
                                <div class="col-md-3">
                                    <div class="border rounded p-3 h-100 text-center">
                                        <small class="text-muted d-block">Score</small>
                                        <strong class="fs-4">
                                            {{ $existingSubmission->total_score }}{{ $existingSubmission->max_possible_score ? ' / ' . $existingSubmission->max_possible_score : '' }}
                                        </strong>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="border rounded p-3 h-100 text-center">
                                        <small class="text-muted d-block">Percentage</small>
                                        <strong class="fs-4">{{ number_format((float) ($existingSubmission->percentage ?? 0), 1) }}%</strong>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="border rounded p-3 h-100 text-center">
                                        <small class="text-muted d-block">Letter Grade</small>
                                        <span class="badge bg-{{ $existingSubmission->letter_grade_color }} fs-6">
                                            {{ $existingSubmission->letter_grade ?? '-' }}
                                        </span>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="border rounded p-3 h-100 text-center">
                                        <small class="text-muted d-block">Graded On</small>
                                        <strong>{{ optional($existingSubmission->graded_at)->format('M d, Y') ?? 'N/A' }}</strong>
                                    </div>
                                </div>
                            </div>

                            <div class="bg-light border rounded p-3">
                                <h6 class="mb-2"><i class="fas fa-comment me-1"></i> Teacher Comments</h6>
                                @if($existingSubmission->feedback)
                                    <p class="mb-0" style="white-space: pre-line;">{{ $existingSubmission->feedback }}</p>
                                @else
                            <p class="text-muted mb-0">No teacher comments provided.</p>
                        @endif
                    </div>
                        @else
                            <span class="badge bg-warning text-dark">Submitted — waiting for teacher grade</span>
                        @endif
                    </div>
                </div>
            @else
                <div class="card">
                    <div class="card-body">
                        <h5 class="mb-3">File submission</h5>
                        @if($activity->isClosed())
                            <div class="alert alert-secondary mb-0">This activity is closed.</div>
                        @else
                        <form action="{{ route('student.activities.submit', [$lesson, $activity]) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label">Upload File (PDF, DOCX, PPT, PPTX, JPG, PNG — max 10MB)</label>
                                <input type="file" name="file" class="form-control" accept=".pdf,.docx,.ppt,.pptx,.jpg,.jpeg,.png" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Comments (optional)</label>
                                <textarea name="comments" class="form-control" rows="3"></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary"><i class="fas fa-upload"></i> Submit Activity</button>
                        </form>
                        @endif
                    </div>
                </div>
            @endif
        @else
            <div class="alert alert-info">This activity does not require an online submission.</div>
        @endif
    </div>
</div>
@endsection
