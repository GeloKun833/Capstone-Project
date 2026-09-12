@extends('layouts.master')
@section('content')


<div class="page-wrapper">
    <div class="content container-fluid">
        
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">{{ $assignment->title }}</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('student.assignments.index') }}">Assignments</a></li>
                        <li class="breadcrumb-item active">{{ $assignment->title }}</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="row">
            {{-- Assignment Details --}}
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="card-title mb-0 text-white">
                            <i class="fas fa-info-circle"></i> Assignment Details
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <p class="mb-2"><strong><i class="fas fa-book text-primary"></i> Subject:</strong> {{ $assignment->subject->subject_name ?? 'N/A' }}</p>
                                <p class="mb-2"><strong><i class="fas fa-user text-info"></i> Teacher:</strong> {{ $assignment->teacher->full_name ?? 'N/A' }}</p>
                                <p class="mb-2"><strong><i class="fas fa-users text-success"></i> Section:</strong> {{ $assignment->section->name ?? 'N/A' }}</p>
                            </div>
                            <div class="col-md-6">
                                <p class="mb-2"><strong><i class="fas fa-calendar-alt text-warning"></i> Due Date:</strong> {{ $assignment->dueDateTime->format('M d, Y') }}</p>
                                <p class="mb-2"><strong><i class="fas fa-clock text-danger"></i> Due Time:</strong> {{ $assignment->dueDateTime->format('h:i A') }}</p>
                                <p class="mb-2"><strong><i class="fas fa-star text-warning"></i> Max Score:</strong> {{ $assignment->max_score }} points</p>
                            </div>
                        </div>

                        <hr>

                        <div class="mb-3">
                            <h6 class="mb-2"><i class="fas fa-file-alt"></i> Description:</h6>
                            <div class="p-3 bg-light rounded">
                                {!! nl2br(e($assignment->description)) !!}
                            </div>
                        </div>

                        @if($assignment->file_path)
                            <div class="mb-3">
                                <h6 class="mb-2"><i class="fas fa-paperclip"></i> Attached Files:</h6>
                                <a href="{{ Storage::url($assignment->file_path) }}" target="_blank" class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-download"></i> Download: {{ $assignment->file_name }}
                                </a>
                            </div>
                        @endif

                        @if($assignment->instructions)
                            <div class="mb-3">
                                <h6 class="mb-2"><i class="fas fa-list-ol"></i> Instructions:</h6>
                                <div class="p-3 bg-light rounded">
                                    {!! nl2br(e($assignment->instructions)) !!}
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Submission Section --}}
            <div class="col-lg-4">
                @if($submission)
                    {{-- Already Submitted --}}
                    <div class="card border-success">
                        <div class="card-header bg-success text-white">
                            <h5 class="card-title mb-0 text-white">
                                <i class="fas fa-check-circle"></i> Your Submission
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle me-2"></i>
                                <strong>Submitted Successfully!</strong>
                            </div>

                            <p class="mb-2">
                                <strong><i class="fas fa-calendar-check"></i> Submitted:</strong><br>
                                {{ $submission->submitted_at->format('M d, Y h:i A') }}
                            </p>

                            @if($submission->is_late)
                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    <strong>Late Submission</strong><br>
                                    <small>{{ $submission->late_minutes }} minutes late</small>
                                </div>
                            @else
                                <div class="alert alert-success">
                                    <i class="fas fa-check me-2"></i>
                                    <strong>On Time!</strong>
                                </div>
                            @endif

                            <p class="mb-2">
                                <strong><i class="fas fa-file"></i> File:</strong><br>
                                <a href="{{ Storage::url($submission->file_path) }}" target="_blank" class="btn btn-outline-primary btn-sm w-100">
                                    <i class="fas fa-download"></i> {{ $submission->file_name }}
                                </a>
                            </p>

                            @if($submission->comments)
                                <p class="mb-2">
                                    <strong><i class="fas fa-comment"></i> Your Comments:</strong><br>
                                    <small class="text-muted">{{ $submission->comments }}</small>
                                </p>
                            @endif

                            <hr>

                            @if($submission->score !== null)
                                <div class="alert alert-info">
                                    <div class="text-center">
                                        <h3 class="mb-0">{{ $submission->score }}/{{ $submission->max_score }}</h3>
                                        <small>Your Score</small>
                                    </div>
                                </div>
                            @else
                                <div class="alert alert-warning">
                                    <i class="fas fa-hourglass-half me-2"></i>
                                    <strong>Awaiting Grading</strong>
                                </div>
                            @endif

                            @if($submission->teacher_feedback)
                                <div class="alert alert-info">
                                    <p class="mb-0">
                                        <strong><i class="fas fa-comment-dots"></i> Teacher Feedback:</strong><br>
                                        <small>{{ $submission->teacher_feedback }}</small>
                                    </p>
                                </div>
                            @endif

                            <a href="{{ route('student.assignments.submission', $assignment->id) }}" class="btn btn-primary w-100">
                                <i class="fas fa-eye"></i> View Full Submission
                            </a>
                        </div>
                    </div>
                @else
                    {{-- Submission Form --}}
                    <div class="card {{ now() > $assignment->dueDateTime ? 'border-danger' : 'border-primary' }}">
                        <div class="card-header {{ now() > $assignment->dueDateTime ? 'bg-danger' : 'bg-primary' }} text-white">
                            <h5 class="card-title mb-0 text-white">
                                <i class="fas fa-upload"></i> Submit Assignment
                            </h5>
                        </div>
                        <div class="card-body">
                            @if(!$assignment->canSubmit())
                                <div class="alert alert-danger">
                                    <i class="fas fa-lock me-2"></i>
                                    <strong>Sarado na ang Assignment</strong>
                                    <p class="mb-0 mt-1">
                                        @if($assignment->status === 'closed')
                                            Ang assignment na ito ay sarado na at <strong>hindi na tumatanggap ng mga magpapasa</strong>.
                                        @elseif($assignment->is_overdue && !$assignment->allows_late_submission)
                                            Ang assignment na ito ay lumipas na sa due date at <strong>hindi na tumatanggap ng mga magpapasa</strong>.
                                        @else
                                            Ang assignment na ito ay <strong>hindi na tumatanggap ng mga magpapasa</strong>.
                                        @endif
                                    </p>
                                </div>
                            @else
                                @if(now() > $assignment->dueDateTime)
                                    <div class="alert alert-warning">
                                        <i class="fas fa-exclamation-triangle me-2"></i>
                                        <strong>Past Due Date</strong>
                                        <p class="mb-0 mt-1">Your submission will be marked as late.</p>
                                    </div>
                                @else
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle me-2"></i>
                                        <strong>Time Remaining:</strong><br>
                                        {{ now()->diffForHumans($assignment->dueDateTime, true) }}
                                    </div>
                                @endif

                                @if($assignment->requires_file_upload)
                                    <div class="alert alert-warning border-warning">
                                        <i class="fas fa-exclamation-triangle me-2"></i>
                                        <strong>Required file types</strong>
                                        <p class="mb-1 mt-2">Your teacher only accepts:</p>
                                        <p class="mb-1 fw-bold text-dark">{{ $assignment->submissionAllowedLabels() }}</p>
                                        <small class="text-muted">Max size: {{ $assignment->submissionMaxMb() }}MB. Wrong file type cannot be submitted.</small>
                                    </div>
                                @else
                                    <div class="alert alert-light border">
                                        <i class="fas fa-paperclip me-2"></i>
                                        <strong>Allowed file types:</strong> {{ $assignment->submissionAllowedLabels() }}
                                        <br><small class="text-muted">Max size: {{ $assignment->submissionMaxMb() }}MB</small>
                                    </div>
                                @endif

                                <form action="{{ route('student.assignments.submit', $assignment->id) }}" method="POST" enctype="multipart/form-data" id="studentSubmitForm"
                                      data-allowed='@json($assignment->submissionAllowedExtensions())'
                                      data-max-mb="{{ $assignment->submissionMaxMb() }}"
                                      data-requires="{{ $assignment->requires_file_upload ? '1' : '0' }}"
                                      data-labels="{{ $assignment->submissionAllowedLabels() }}">
                                    @csrf
                                    
                                    <div class="form-group">
                                        <label class="form-label">Upload File <span class="text-danger">*</span></label>
                                        <input type="file" name="submission_file" id="submission_file"
                                               class="form-control @error('submission_file') is-invalid @enderror" 
                                               accept="{{ $assignment->submissionAcceptAttribute() }}" required>
                                        <small class="form-text text-muted">
                                            Max: {{ $assignment->submissionMaxMb() }}MB. Allowed: {{ $assignment->submissionAllowedLabels() }}
                                        </small>
                                        <div id="submissionFileClientError" class="text-danger small mt-1" style="display:none;"></div>
                                        @error('submission_file')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="form-group">
                                        <label class="form-label">Comments (Optional)</label>
                                        <textarea name="comments" rows="3" class="form-control @error('comments') is-invalid @enderror" 
                                                  placeholder="Add any comments or notes for your teacher...">{{ old('comments') }}</textarea>
                                        @error('comments')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <button type="button" class="btn btn-success w-100" onclick="showSubmitConfirmation(event)">
                                        <i class="fas fa-paper-plane"></i> Submit Assignment
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>

                    {{-- Due Date Info --}}
                    <div class="card">
                        <div class="card-body text-center">
                            @if(now() < $assignment->dueDateTime)
                                <i class="fas fa-clock fa-2x text-info mb-2"></i>
                                <h6>Time Remaining</h6>
                                <h4 class="text-primary">{{ now()->diffForHumans($assignment->dueDateTime, true) }}</h4>
                            @else
                                <i class="fas fa-exclamation-triangle fa-2x text-danger mb-2"></i>
                                <h6>Overdue By</h6>
                                <h4 class="text-danger">{{ $assignment->dueDateTime->diffForHumans(now(), true) }}</h4>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        </div>

    </div>
</div>

{{-- Custom Confirmation Modal --}}
<div class="modal fade" id="submitConfirmationModal" tabindex="-1" aria-labelledby="submitConfirmationModalLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="background-color: #2d3748; color: #e2e8f0; border-radius: 0.75rem; border: none; box-shadow: 0 10px 20px rgba(0, 0, 0, 0.5);">
            <div class="modal-header" style="border-bottom: none; padding-bottom: 0;">
                <h5 class="modal-title fw-bold" id="submitConfirmationModalLabel" style="color: #e2e8f0;">
                    <i class="fas fa-exclamation-circle me-2 text-warning"></i>Confirm Submission
                </h5>
            </div>
            <div class="modal-body" style="padding-top: 0.5rem; padding-bottom: 1.5rem; font-size: 1rem; line-height: 1.5;">
                <p class="mb-2">Are you sure you want to submit this assignment?</p>
                <div id="modalFileRequirement" class="p-3 mb-3 rounded" style="background:#1a202c;border:1px solid #f6ad55;">
                    <p class="mb-1 text-warning fw-bold"><i class="fas fa-file me-1"></i> File requirement</p>
                    <p class="mb-1" id="modalAllowedTypes">Allowed: —</p>
                    <p class="mb-0 small" id="modalSelectedFile">Selected: —</p>
                </div>
                <p class="mb-0 text-warning">
                    <i class="fas fa-info-circle me-1"></i>
                    <strong>You cannot edit it after submission.</strong>
                </p>
            </div>
            <div class="modal-footer" style="border-top: none; padding-top: 0; justify-content: flex-end; gap: 0.75rem;">
                <button type="button" class="btn" data-bs-dismiss="modal" style="background-color: #1e3a8a; border: 1px solid #1e3a8a; color: #ffffff; font-weight: 600; padding: 0.5rem 1.5rem; border-radius: 0.5rem;">
                    <i class="fas fa-times me-2"></i>Cancel
                </button>
                <button type="button" class="btn" id="confirmSubmitBtn" onclick="confirmSubmit()" style="background-color: #10b981; border: 1px solid #10b981; color: #ffffff; font-weight: 600; padding: 0.5rem 1.5rem; border-radius: 0.5rem;">
                    <i class="fas fa-check me-2"></i>Yes, Submit
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('script')
<script>
let submitForm = null;

function getFormRules(form) {
    let allowed = [];
    try {
        allowed = JSON.parse(form.getAttribute('data-allowed') || '[]');
    } catch (e) {
        allowed = [];
    }
    return {
        allowed: allowed.map(String),
        maxMb: parseInt(form.getAttribute('data-max-mb') || '10', 10) || 10,
        labels: form.getAttribute('data-labels') || '',
        requires: form.getAttribute('data-requires') === '1',
    };
}

function fileExtension(name) {
    const parts = String(name || '').split('.');
    return parts.length > 1 ? parts.pop().toLowerCase() : '';
}

function validateSubmissionFile(form) {
    const rules = getFormRules(form);
    const fileInput = form.querySelector('input[name="submission_file"]');
    const errorEl = document.getElementById('submissionFileClientError');
    const showError = (msg) => {
        if (errorEl) {
            errorEl.style.display = 'block';
            errorEl.textContent = msg;
        }
        if (fileInput) {
            fileInput.classList.add('is-invalid');
        }
        return false;
    };
    const clearError = () => {
        if (errorEl) {
            errorEl.style.display = 'none';
            errorEl.textContent = '';
        }
        if (fileInput) {
            fileInput.classList.remove('is-invalid');
        }
        return true;
    };

    if (!fileInput || !fileInput.files || !fileInput.files.length) {
        return showError('Please choose a file to upload before submitting.');
    }

    const file = fileInput.files[0];
    const ext = fileExtension(file.name);

    if (rules.allowed.length && !rules.allowed.includes(ext)) {
        return showError(
            (rules.requires ? 'Teacher requires these file types only: ' : 'Allowed file types: ')
            + rules.labels + '. Your file (.' + ext + ') is not allowed.'
        );
    }

    if (file.size > rules.maxMb * 1024 * 1024) {
        return showError('File is too large. Maximum size is ' + rules.maxMb + 'MB.');
    }

    return clearError();
}

function showSubmitConfirmation(event) {
    event.preventDefault();
    submitForm = event.target.closest('form') || document.getElementById('studentSubmitForm');
    if (!submitForm) {
        return;
    }

    if (!validateSubmissionFile(submitForm)) {
        return;
    }

    const rules = getFormRules(submitForm);
    const file = submitForm.querySelector('input[name="submission_file"]').files[0];
    const allowedEl = document.getElementById('modalAllowedTypes');
    const selectedEl = document.getElementById('modalSelectedFile');
    if (allowedEl) {
        allowedEl.textContent = (rules.requires ? 'Teacher requires: ' : 'Allowed: ') + rules.labels
            + ' (max ' + rules.maxMb + 'MB)';
    }
    if (selectedEl) {
        selectedEl.textContent = 'Selected: ' + file.name + ' (.' + fileExtension(file.name) + ')';
    }

    const confirmBtn = document.getElementById('confirmSubmitBtn');
    if (confirmBtn) {
        confirmBtn.disabled = false;
    }
    
    const modal = document.getElementById('submitConfirmationModal');
    if (modal) {
        try {
            if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                const bsModal = new bootstrap.Modal(modal);
                bsModal.show();
            } else if (typeof $ !== 'undefined' && $.fn.modal) {
                $('#submitConfirmationModal').modal('show');
            } else {
                modal.classList.add('show');
                modal.style.display = 'block';
                modal.setAttribute('aria-modal', 'true');
                modal.setAttribute('role', 'dialog');
                
                const backdrop = document.createElement('div');
                backdrop.className = 'modal-backdrop fade show';
                backdrop.id = 'submitConfirmationBackdrop';
                document.body.appendChild(backdrop);
                document.body.classList.add('modal-open');
            }
        } catch (e) {
            console.error('Error showing modal:', e);
            if (confirm('Are you sure you want to submit this assignment? You cannot edit it after submission.')) {
                submitForm.submit();
            }
        }
    }
}

function confirmSubmit() {
    if (!submitForm) {
        return;
    }
    if (!validateSubmissionFile(submitForm)) {
        closeModal();
        return;
    }

    closeModal();
    
    const submitBtn = submitForm.querySelector('button[type="button"]');
    if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Submitting...';
    }
    
    submitForm.submit();
}

function closeModal() {
    const modal = document.getElementById('submitConfirmationModal');
    const backdrop = document.getElementById('submitConfirmationBackdrop');
    
    if (modal) {
        try {
            if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                const bsModal = bootstrap.Modal.getInstance(modal);
                if (bsModal) {
                    bsModal.hide();
                }
            } else if (typeof $ !== 'undefined' && $.fn.modal) {
                $('#submitConfirmationModal').modal('hide');
            } else {
                modal.classList.remove('show');
                modal.style.display = 'none';
                modal.removeAttribute('aria-modal');
                modal.removeAttribute('role');
                
                if (backdrop) {
                    backdrop.remove();
                }
                
                document.body.classList.remove('modal-open');
            }
        } catch (e) {
            console.error('Error closing modal:', e);
        }
    }
}

document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('studentSubmitForm');
    const input = document.getElementById('submission_file');
    if (form && input) {
        input.addEventListener('change', function () {
            validateSubmissionFile(form);
        });
    }
});
</script>
@endsection

