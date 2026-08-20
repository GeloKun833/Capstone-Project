{{-- Submission View Details --}}
<div class="row">
    <div class="col-md-6 mb-3">
        <p class="mb-1"><strong><i class="fas fa-user text-primary"></i> Student Name:</strong></p>
        <p class="text-muted">{{ $submission->student->first_name }} {{ $submission->student->last_name }}</p>
    </div>
    <div class="col-md-6 mb-3">
        <p class="mb-1"><strong><i class="fas fa-envelope text-primary"></i> Email:</strong></p>
        <p class="text-muted">{{ $submission->student->email }}</p>
    </div>
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <p class="mb-1"><strong><i class="fas fa-calendar text-success"></i> Submitted On:</strong></p>
        <p class="text-muted">{{ $submission->submitted_at->format('M d, Y h:i A') }}</p>
    </div>
    <div class="col-md-6 mb-3">
        <p class="mb-1"><strong><i class="fas fa-clock text-info"></i> Status:</strong></p>
        <p>
            @if($submission->isLate())
                <span class="badge bg-warning">Late</span>
            @else
                <span class="badge bg-success">On Time</span>
            @endif
        </p>
    </div>
</div>

<div class="row">
    <div class="col-md-12 mb-3">
        <p class="mb-1"><strong><i class="fas fa-file text-primary"></i> Submitted File:</strong></p>
        <div class="border p-3 rounded bg-light">
            <i class="fas fa-file-{{ $submission->file_type }} me-2"></i>
            <a href="{{ $submission->file_url }}" target="_blank" class="text-decoration-none">
                {{ $submission->file_name }}
            </a>
            <a href="{{ $submission->file_url }}" download class="btn btn-sm btn-primary ms-2">
                <i class="fas fa-download"></i> Download
            </a>
        </div>
    </div>
</div>

@if($submission->comments)
<div class="row">
    <div class="col-md-12 mb-3">
        <p class="mb-1"><strong><i class="fas fa-comment text-primary"></i> Student Comments:</strong></p>
        <div class="border p-3 rounded bg-light">
            {{ $submission->comments }}
        </div>
    </div>
</div>
@endif

@if($submission->score)
<div class="row">
    <div class="col-md-6 mb-3">
        <p class="mb-1"><strong><i class="fas fa-star text-warning"></i> Score:</strong></p>
        <h5 class="text-success">{{ $submission->score }} / {{ $submission->max_score }}</h5>
    </div>
    <div class="col-md-6 mb-3">
        <p class="mb-1"><strong><i class="fas fa-percentage text-info"></i> Percentage:</strong></p>
        <h5 class="text-primary">{{ $submission->percentage }}%</h5>
    </div>
</div>

@if($submission->teacher_feedback)
<div class="row">
    <div class="col-md-12">
        <p class="mb-1"><strong><i class="fas fa-chalkboard-teacher text-primary"></i> Teacher Feedback:</strong></p>
        <div class="border p-3 rounded bg-light">
            {{ $submission->teacher_feedback }}
        </div>
    </div>
</div>
@endif
@endif

