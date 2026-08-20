@extends('layouts.master')
@section('content')
{{-- message --}}
{!! Toastr::message() !!}

<div class="page-wrapper">
    <div class="content container-fluid">
        
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">Create Class Post</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('class-posts.index') }}">Class Posts</a></li>
                        <li class="breadcrumb-item active">Create Post</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        {{-- Display Errors --}}
                        @if($errors->any())
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <strong><i class="fas fa-exclamation-triangle"></i> Error!</strong>
                                <ul class="mb-0 mt-2">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        <form action="{{ route('class-posts.store') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            
                            {{-- Basic Information --}}
                            <div class="row">
                                <div class="col-md-12">
                                    <h5 class="mb-3">Post Information</h5>
                                </div>
                                
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label class="form-label">Title <span class="text-danger">*</span></label>
                                        <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" 
                                               value="{{ old('title') }}" placeholder="Enter post title" required>
                                        @error('title')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label class="form-label">Content <span class="text-danger">*</span></label>
                                        <textarea name="content" rows="6" class="form-control @error('content') is-invalid @enderror" 
                                                  placeholder="Enter post content" required>{{ old('content') }}</textarea>
                                        @error('content')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">Subject <span class="text-danger">*</span></label>
                                        <select name="subject_id" class="form-control @error('subject_id') is-invalid @enderror" required>
                                            <option value="">Select Subject</option>
                                            @foreach($subjects as $subject)
                                                <option value="{{ $subject->id }}" {{ old('subject_id') == $subject->id ? 'selected' : '' }}>
                                                    {{ $subject->subject_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('subject_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">Section <span class="text-danger">*</span></label>
                                        <select name="section_id" class="form-control @error('section_id') is-invalid @enderror" required>
                                            <option value="">Select Section</option>
                                            @foreach($sections as $section)
                                                <option value="{{ $section->id }}" {{ old('section_id') == $section->id ? 'selected' : '' }}>
                                                    {{ $section->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('section_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">Academic Year <span class="text-danger">*</span></label>
                                        <select name="academic_year_id" class="form-control @error('academic_year_id') is-invalid @enderror" required>
                                            <option value="">Select Academic Year</option>
                                            @foreach($academicYears as $year)
                                                <option value="{{ $year->id }}" {{ old('academic_year_id') == $year->id ? 'selected' : '' }}>
                                                    {{ $year->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('academic_year_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">Semester <span class="text-danger">*</span></label>
                                        <select name="semester_id" class="form-control @error('semester_id') is-invalid @enderror" required>
                                            <option value="">Select Semester</option>
                                            @foreach($semesters as $semester)
                                                <option value="{{ $semester->id }}" {{ old('semester_id') == $semester->id ? 'selected' : '' }}>
                                                    {{ $semester->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('semester_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">Type <span class="text-danger">*</span></label>
                                        <select name="type" class="form-control @error('type') is-invalid @enderror" required>
                                            <option value="">Select Type</option>
                                            <option value="announcement" {{ old('type') == 'announcement' ? 'selected' : '' }}>Announcement</option>
                                            <option value="resource" {{ old('type') == 'resource' ? 'selected' : '' }}>Resource</option>
                                            <option value="discussion" {{ old('type') == 'discussion' ? 'selected' : '' }}>Discussion</option>
                                            <option value="reminder" {{ old('type') == 'reminder' ? 'selected' : '' }}>Reminder</option>
                                        </select>
                                        @error('type')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">Priority <span class="text-danger">*</span></label>
                                        <select name="priority" class="form-control @error('priority') is-invalid @enderror" required>
                                            <option value="">Select Priority</option>
                                            <option value="low" {{ old('priority') == 'low' ? 'selected' : '' }}>Low</option>
                                            <option value="normal" {{ old('priority') == 'normal' ? 'selected' : '' }}>Normal</option>
                                            <option value="high" {{ old('priority') == 'high' ? 'selected' : '' }}>High</option>
                                            <option value="urgent" {{ old('priority') == 'urgent' ? 'selected' : '' }}>Urgent</option>
                                        </select>
                                        @error('priority')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">Expires At (Optional)</label>
                                        <input type="date" name="expires_at" class="form-control @error('expires_at') is-invalid @enderror" 
                                               value="{{ old('expires_at') }}" min="{{ date('Y-m-d') }}">
                                        @error('expires_at')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">Attachment (Optional)</label>
                                        <input type="file" name="post_file" class="form-control @error('post_file') is-invalid @enderror" 
                                               accept=".pdf,.doc,.docx,.ppt,.pptx,.txt,.jpg,.jpeg,.png">
                                        <small class="form-text text-muted">Max size: 10MB. Allowed: PDF, DOC, DOCX, PPT, PPTX, TXT, JPG, PNG</small>
                                        @error('post_file')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                {{-- Post Options --}}
                                <div class="col-md-12">
                                    <h5 class="mb-3 mt-3">Post Options</h5>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="is_pinned" id="is_pinned" 
                                               {{ old('is_pinned') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_pinned">
                                            <i class="fas fa-thumbtack"></i> Pin this post
                                        </label>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="allows_comments" id="allows_comments" 
                                               {{ old('allows_comments', true) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="allows_comments">
                                            <i class="fas fa-comments"></i> Allow comments
                                        </label>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="requires_confirmation" id="requires_confirmation" 
                                               {{ old('requires_confirmation') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="requires_confirmation">
                                            <i class="fas fa-check-circle"></i> Require confirmation
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-4">
                                <button type="submit" class="btn btn-primary" id="submitBtn">
                                    <i class="fas fa-save"></i> Create Post
                                </button>
                                <a href="{{ route('class-posts.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> Cancel
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- Success Modal -->
<div class="modal fade" id="successModal" tabindex="-1" role="dialog" aria-labelledby="successModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content" style="border-radius: 15px; border: none; box-shadow: 0 10px 40px rgba(0,0,0,0.2);">
            <div class="modal-body text-center" style="padding: 40px;">
                <div style="width: 80px; height: 80px; background: linear-gradient(135deg, #28a745, #20c997); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 25px; box-shadow: 0 8px 20px rgba(40, 167, 69, 0.3);">
                    <i class="fas fa-check" style="font-size: 40px; color: white;"></i>
                </div>
                <h4 style="color: #2c323f; font-weight: 700; margin-bottom: 15px; font-size: 1.5rem;">Post Created Successfully!</h4>
                <p style="color: #6c757d; margin-bottom: 30px; font-size: 1rem;">Your class post has been published and is now visible to students.</p>
                <button type="button" class="btn btn-success" onclick="closeSuccessModal()" style="padding: 12px 40px; font-size: 1rem; font-weight: 600; border-radius: 8px; background: linear-gradient(135deg, #28a745, #20c997); border: none; box-shadow: 0 4px 12px rgba(40, 167, 69, 0.3);">
                    <i class="fas fa-check"></i> Got it!
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Show success modal if there's a success message
@if(session('success'))
    $(document).ready(function() {
        // Show immediate Toastr notification
        toastr.success('{{ session('success') }}', 'Success', {
            closeButton: true,
            progressBar: true,
            timeOut: 3000
        });
        
        // Also show the modal
        setTimeout(function() {
            showSuccessModal();
        }, 100);
    });
@endif

function showSuccessModal() {
    const modal = document.getElementById('successModal');
    
    // Try Bootstrap 5 modal first
    if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
        const bsModal = new bootstrap.Modal(modal);
        bsModal.show();
    } 
    // Try jQuery/Bootstrap 4 modal
    else if (typeof $ !== 'undefined' && typeof $.fn.modal !== 'undefined') {
        $('#successModal').modal('show');
    }
    // Pure JavaScript fallback
    else {
        modal.style.display = 'block';
        modal.classList.add('show');
        modal.classList.add('d-block');
        document.body.classList.add('modal-open');
        
        // Create backdrop
        const backdrop = document.createElement('div');
        backdrop.className = 'modal-backdrop fade show';
        backdrop.id = 'customBackdrop';
        document.body.appendChild(backdrop);
    }
}

function closeSuccessModal() {
    const modal = document.getElementById('successModal');
    
    // Try Bootstrap 5 modal first
    if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
        const bsModal = bootstrap.Modal.getInstance(modal);
        if (bsModal) {
            bsModal.hide();
        }
    }
    // Try jQuery/Bootstrap 4 modal
    else if (typeof $ !== 'undefined' && typeof $.fn.modal !== 'undefined') {
        $('#successModal').modal('hide');
    }
    // Pure JavaScript fallback
    else {
        modal.style.display = 'none';
        modal.classList.remove('show');
        modal.classList.remove('d-block');
        document.body.classList.remove('modal-open');
        
        // Remove backdrop
        const backdrop = document.getElementById('customBackdrop');
        if (backdrop) {
            backdrop.remove();
        }
    }
}

// Client-side file validation
document.querySelector('input[name="post_file"]')?.addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        // Check file size (10MB = 10240 KB)
        const maxSize = 10240 * 1024; // 10MB in bytes
        if (file.size > maxSize) {
            alert('File size must not exceed 10MB.');
            this.value = '';
            return;
        }
        
        // Check file type
        const allowedExtensions = ['pdf', 'doc', 'docx', 'ppt', 'pptx', 'txt', 'jpg', 'jpeg', 'png'];
        const fileExtension = file.name.split('.').pop().toLowerCase();
        
        if (!allowedExtensions.includes(fileExtension)) {
            alert('Invalid file type. Allowed types: PDF, DOC, DOCX, PPT, PPTX, TXT, JPG, PNG.');
            this.value = '';
            return;
        }
    }
});

// Add loading state to submit button
document.querySelector('form').addEventListener('submit', function() {
    const submitBtn = document.getElementById('submitBtn');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creating...';
});
</script>
@endpush

@endsection

