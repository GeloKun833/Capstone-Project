@extends('layouts.master')
@section('content')

<div class="page-wrapper">
    <div class="content container-fluid dir-page">
        <div class="page-header">
            <div class="row align-items-start">
                <div class="col">
                    <h3 class="page-title mb-1">Create Announcement</h3>
                    <p class="dir-subtitle">Publish a notice for students, teachers, parents, or staff.</p>
                </div>
                <div class="col-auto text-end">
                    <ul class="breadcrumb justify-content-end mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('announcements.index') }}">Announcements</a></li>
                        <li class="breadcrumb-item active">Create Announcement</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="dir-card">
            <div class="p-4">
                <form action="{{ route('announcements.store') }}" method="POST" id="announcementForm" enctype="multipart/form-data">
                    @csrf
                    <h5 class="dir-toolbar-title mb-3">Announcement information</h5>
                    <div class="row">
                        <div class="col-12 col-sm-6">
                            <div class="form-group">
                                <label>Title <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('title') is-invalid @enderror"
                                       name="title" value="{{ old('title') }}" required>
                                @error('title')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-12 col-sm-6">
                            <div class="form-group">
                                <label>Type <span class="text-danger">*</span></label>
                                <select class="form-control @error('type') is-invalid @enderror" name="type" required>
                                    <option value="">Select Type</option>
                                    @foreach(['general','academic','event','reminder','emergency'] as $type)
                                        <option value="{{ $type }}" {{ old('type') == $type ? 'selected' : '' }}>{{ ucfirst($type) }}</option>
                                    @endforeach
                                </select>
                                @error('type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-12 col-sm-6">
                            <div class="form-group">
                                <label>Priority <span class="text-danger">*</span></label>
                                <select class="form-control @error('priority') is-invalid @enderror" name="priority" required>
                                    <option value="">Select Priority</option>
                                    @foreach(['low','normal','high','urgent'] as $priority)
                                        <option value="{{ $priority }}" {{ old('priority') == $priority ? 'selected' : '' }}>{{ ucfirst($priority) }}</option>
                                    @endforeach
                                </select>
                                @error('priority')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-12 col-sm-6">
                            <div class="form-group">
                                <label>Target Audience <span class="text-danger">*</span></label>
                                <select class="form-control @error('target_audience') is-invalid @enderror" name="target_audience" required>
                                    <option value="">Select Audience</option>
                                    <option value="all" {{ old('target_audience') == 'all' ? 'selected' : '' }}>All Users</option>
                                    <option value="students" {{ old('target_audience') == 'students' ? 'selected' : '' }}>Students Only</option>
                                    <option value="teachers" {{ old('target_audience') == 'teachers' ? 'selected' : '' }}>Teachers Only</option>
                                    <option value="parents" {{ old('target_audience') == 'parents' ? 'selected' : '' }}>Parents Only</option>
                                    <option value="admins" {{ old('target_audience') == 'admins' ? 'selected' : '' }}>Admins Only</option>
                                </select>
                                @error('target_audience')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group">
                                <label>Content <span class="text-danger">*</span></label>
                                <textarea class="form-control @error('content') is-invalid @enderror"
                                          name="content" rows="6" required>{{ old('content') }}</textarea>
                                @error('content')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group">
                                <label>Attachments <span class="text-muted">(optional)</span></label>
                                <input type="file" class="form-control @error('attachments') is-invalid @enderror @error('attachments.*') is-invalid @enderror"
                                       name="attachments[]" multiple
                                       accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.gif,.webp,.txt,.xls,.xlsx,.ppt,.pptx,image/*">
                                <small class="text-muted">PDF, Word, Excel, PowerPoint, images, or TXT. Max 5 files, 10MB each.</small>
                                @error('attachments')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                                @error('attachments.*')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <h5 class="dir-toolbar-title mt-2 mb-3">Advanced options</h5>
                    <div class="row">
                        <div class="col-12 col-sm-6">
                            <div class="form-group">
                                <label>Specific Roles (Optional)</label>
                                <select class="form-control select2" name="target_roles[]" multiple>
                                    <option value="students">Students</option>
                                    <option value="teachers">Teachers</option>
                                    <option value="parents">Parents</option>
                                    <option value="admins">Admins</option>
                                </select>
                                <small class="text-muted">Leave empty to use target audience above</small>
                            </div>
                        </div>
                        <div class="col-12 col-sm-6">
                            <div class="form-group">
                                <label>Specific Sections (Optional)</label>
                                <select class="form-control select2" name="target_sections[]" multiple>
                                    @foreach($sections as $section)
                                        <option value="{{ $section->id }}">{{ $section->name }}</option>
                                    @endforeach
                                </select>
                                <small class="text-muted">Leave empty to target all sections</small>
                            </div>
                        </div>
                        <div class="col-12 col-sm-6">
                            <div class="form-group mdp-field">
                                <label>Scheduled Date (Optional)</label>
                                <input type="datetime-local" class="form-control js-event-datetime" name="scheduled_at"
                                       value="{{ old('scheduled_at') }}" autocomplete="off" placeholder="YYYY-MM-DD HH:mm">
                                <small class="text-muted">Leave empty to publish immediately</small>
                            </div>
                        </div>
                        <div class="col-12 col-sm-6">
                            <div class="form-group mdp-field">
                                <label>Expiration Date (Optional)</label>
                                <input type="datetime-local" class="form-control js-event-datetime" name="expires_at"
                                       value="{{ old('expires_at') }}" autocomplete="off" placeholder="YYYY-MM-DD HH:mm">
                                <small class="text-muted">Leave empty for no expiration</small>
                            </div>
                        </div>
                        <div class="col-12 col-sm-6 mb-3">
                            <label class="dir-check mb-0">
                                <input type="checkbox" class="form-check-input m-0" name="is_pinned" id="is_pinned"
                                       value="1" {{ old('is_pinned') ? 'checked' : '' }}>
                                Pin this announcement to the top
                            </label>
                        </div>
                        <div class="col-12 col-sm-6 mb-3">
                            <label class="dir-check mb-0">
                                <input type="checkbox" class="form-check-input m-0" name="is_scheduled" id="is_scheduled"
                                       value="1" {{ old('is_scheduled') ? 'checked' : '' }}>
                                Schedule this announcement for later
                            </label>
                        </div>
                        <div class="col-12 d-flex gap-2">
                            <button type="submit" class="btn btn-primary dir-btn">Create Announcement</button>
                            <a href="{{ route('announcements.index') }}" class="btn btn-outline-secondary dir-btn">Cancel</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/directory-modern.css') }}?v=20260914c">
@endpush

@push('scripts')
<script>
    $(document).ready(function() {
        $('.select2').select2({
            placeholder: "Select options",
            allowClear: true,
            width: '100%'
        });

        $('#announcementForm').on('submit', function(e) {
            let isValid = true;
            $('input[required], select[required], textarea[required]').each(function() {
                if (!$(this).val()) {
                    $(this).addClass('is-invalid');
                    isValid = false;
                } else {
                    $(this).removeClass('is-invalid');
                }
            });
            if (!isValid) {
                e.preventDefault();
                alert('Please fill in all required fields.');
            }
        });

        $('#is_scheduled').change(function() {
            $('input[name="scheduled_at"]').prop('required', $(this).is(':checked'));
        });
    });
</script>
@endpush
