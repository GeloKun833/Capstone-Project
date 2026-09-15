@extends('layouts.master')
@section('content')

<div class="page-wrapper">
    <div class="content container-fluid dir-page">
        <div class="page-header">
            <div class="row align-items-start">
                <div class="col">
                    <h3 class="page-title mb-1">Edit Announcement</h3>
                    <p class="dir-subtitle">Update the notice, audience, or schedule.</p>
                </div>
                <div class="col-auto text-end">
                    <ul class="breadcrumb justify-content-end mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('announcements.index') }}">Announcements</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('announcements.show', $announcement->id) }}">View</a></li>
                        <li class="breadcrumb-item active">Edit Announcement</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="dir-card">
            <div class="p-4">
                <form action="{{ route('announcements.update', $announcement->id) }}" method="POST" id="announcementForm" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <h5 class="dir-toolbar-title mb-3">Announcement information</h5>
                    <div class="row">
                        <div class="col-12 col-sm-6">
                            <div class="form-group">
                                <label>Title <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('title') is-invalid @enderror"
                                       name="title" value="{{ old('title', $announcement->title) }}" required>
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
                                        <option value="{{ $type }}" {{ old('type', $announcement->type) == $type ? 'selected' : '' }}>{{ ucfirst($type) }}</option>
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
                                        <option value="{{ $priority }}" {{ old('priority', $announcement->priority) == $priority ? 'selected' : '' }}>{{ ucfirst($priority) }}</option>
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
                                    <option value="all" {{ old('target_audience', $announcement->target_audience) == 'all' ? 'selected' : '' }}>All Users</option>
                                    <option value="students" {{ old('target_audience', $announcement->target_audience) == 'students' ? 'selected' : '' }}>Students Only</option>
                                    <option value="teachers" {{ old('target_audience', $announcement->target_audience) == 'teachers' ? 'selected' : '' }}>Teachers Only</option>
                                    <option value="parents" {{ old('target_audience', $announcement->target_audience) == 'parents' ? 'selected' : '' }}>Parents Only</option>
                                    <option value="admins" {{ old('target_audience', $announcement->target_audience) == 'admins' ? 'selected' : '' }}>Admins Only</option>
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
                                          name="content" rows="6" required>{{ old('content', $announcement->content) }}</textarea>
                                @error('content')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group">
                                <label>Current attachments</label>
                                @php $files = $announcement->attachments ?? []; @endphp
                                @if(empty($files))
                                    <p class="text-muted mb-2">No files attached yet.</p>
                                @else
                                    @foreach($files as $file)
                                        <label class="dir-file-row">
                                            <span>
                                                <i class="fas fa-paperclip me-2 text-muted"></i>
                                                {{ $file['original_name'] ?? basename($file['path'] ?? 'file') }}
                                            </span>
                                            <span class="d-inline-flex align-items-center gap-2">
                                                <a class="btn btn-sm btn-outline-secondary dir-btn" target="_blank"
                                                   href="{{ asset('storage/' . ($file['path'] ?? '')) }}">View</a>
                                                <label class="mb-0 small text-muted">
                                                    <input type="checkbox" name="remove_attachments[]" value="{{ $file['path'] ?? '' }}"> Remove
                                                </label>
                                            </span>
                                        </label>
                                    @endforeach
                                @endif
                                <label class="form-label mt-3">Add more files</label>
                                <input type="file" class="form-control" name="attachments[]" multiple
                                       accept=".pdf,.docx,.jpg,.jpeg,.png,.gif,.webp,.txt,.xls,.xlsx,.ppt,.pptx,image/*">
                                <small class="text-muted">PDF, Word (DOCX), Excel, PowerPoint, images, or TXT. Max 5 total, 10MB each.</small>
                            </div>
                        </div>
                    </div>

                    <h5 class="dir-toolbar-title mt-2 mb-3">Advanced options</h5>
                    <div class="row">
                        <div class="col-12 col-sm-6">
                            <div class="form-group">
                                <label>Specific Roles (Optional)</label>
                                <select class="form-control select2" name="target_roles[]" multiple>
                                    <option value="students" {{ in_array('students', old('target_roles', $announcement->target_roles ?? [])) ? 'selected' : '' }}>Students</option>
                                    <option value="teachers" {{ in_array('teachers', old('target_roles', $announcement->target_roles ?? [])) ? 'selected' : '' }}>Teachers</option>
                                    <option value="parents" {{ in_array('parents', old('target_roles', $announcement->target_roles ?? [])) ? 'selected' : '' }}>Parents</option>
                                    <option value="admins" {{ in_array('admins', old('target_roles', $announcement->target_roles ?? [])) ? 'selected' : '' }}>Admins</option>
                                </select>
                                <small class="text-muted">Leave empty to use target audience above</small>
                            </div>
                        </div>
                        <div class="col-12 col-sm-6">
                            <div class="form-group">
                                <label>Specific Sections (Optional)</label>
                                <select class="form-control select2" name="target_sections[]" multiple>
                                    @foreach($sections as $section)
                                        <option value="{{ $section->id }}"
                                                {{ in_array($section->id, old('target_sections', $announcement->target_sections ?? [])) ? 'selected' : '' }}>
                                            {{ $section->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <small class="text-muted">Leave empty to target all sections</small>
                            </div>
                        </div>
                        <div class="col-12 col-sm-6">
                            <div class="form-group mdp-field">
                                <label>Scheduled Date (Optional)</label>
                                <input type="datetime-local" class="form-control js-event-datetime" name="scheduled_at"
                                       value="{{ old('scheduled_at', $announcement->scheduled_at ? $announcement->scheduled_at->format('Y-m-d\TH:i') : '') }}"
                                       autocomplete="off" placeholder="YYYY-MM-DD HH:mm">
                                <small class="text-muted">Leave empty to publish immediately</small>
                            </div>
                        </div>
                        <div class="col-12 col-sm-6">
                            <div class="form-group mdp-field">
                                <label>Expiration Date (Optional)</label>
                                <input type="datetime-local" class="form-control js-event-datetime" name="expires_at"
                                       value="{{ old('expires_at', $announcement->expires_at ? $announcement->expires_at->format('Y-m-d\TH:i') : '') }}"
                                       autocomplete="off" placeholder="YYYY-MM-DD HH:mm">
                                <small class="text-muted">Leave empty for no expiration</small>
                            </div>
                        </div>
                        <div class="col-12 col-sm-6 mb-3">
                            <label class="dir-check mb-0">
                                <input type="checkbox" class="form-check-input m-0" name="is_pinned" id="is_pinned"
                                       value="1" {{ old('is_pinned', $announcement->is_pinned) ? 'checked' : '' }}>
                                Pin this announcement to the top
                            </label>
                        </div>
                        <div class="col-12 col-sm-6 mb-3">
                            <label class="dir-check mb-0">
                                <input type="checkbox" class="form-check-input m-0" name="is_scheduled" id="is_scheduled"
                                       value="1" {{ old('is_scheduled', $announcement->is_scheduled) ? 'checked' : '' }}>
                                Schedule this announcement for later
                            </label>
                        </div>
                        <div class="col-12 d-flex gap-2">
                            <button type="submit" class="btn btn-primary dir-btn">Update Announcement</button>
                            <a href="{{ route('announcements.show', $announcement->id) }}" class="btn btn-outline-secondary dir-btn">Cancel</a>
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
