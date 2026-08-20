{{-- Submission Details for Grading --}}
<div class="alert alert-info mb-3">
    <h6 class="mb-1"><i class="fas fa-user me-2"></i>{{ $submission->student->first_name }} {{ $submission->student->last_name }}</h6>
    <p class="mb-0 small">
        <i class="fas fa-calendar me-1"></i>Submitted: {{ $submission->submitted_at->format('M d, Y h:i A') }}
        @if($submission->isLate())
            <span class="badge bg-warning ms-2">Late</span>
        @else
            <span class="badge bg-success ms-2">On Time</span>
        @endif
    </p>
</div>

<div class="mb-3">
    <label class="form-label"><i class="fas fa-file me-2"></i>Submitted File:</label>
    <div class="border p-2 rounded bg-light">
        <i class="fas fa-file-{{ $submission->file_type }} me-2"></i>
        <a href="{{ $submission->file_url }}" target="_blank" class="text-decoration-none">
            {{ $submission->file_name }}
        </a>
        <a href="{{ $submission->file_url }}" download class="btn btn-sm btn-primary ms-2">
            <i class="fas fa-download"></i> Download
        </a>
    </div>
</div>

@if($submission->comments)
<div class="mb-3">
    <label class="form-label"><i class="fas fa-comment me-2"></i>Student Comments:</label>
    <div class="border p-2 rounded bg-light">
        {{ $submission->comments }}
    </div>
</div>
@endif

