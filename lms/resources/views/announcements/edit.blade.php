@extends('layouts.master')
@section('content')
    {{-- message --}}
    {!! Toastr::message() !!}
    <div class="page-wrapper">
        <div class="content container-fluid">

            <div class="page-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="page-title">Edit Announcement</h3>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('announcements.index') }}">Announcements</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('announcements.show', $announcement->id) }}">View Announcement</a></li>
                            <li class="breadcrumb-item active">Edit Announcement</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-12">
                    <div class="card">
                        <div class="card-body">
                            <form action="{{ route('announcements.update', $announcement->id) }}" method="POST" id="announcementForm">
                                @csrf
                                @method('PUT')
                                
                                <div class="row">
                                    <div class="col-12">
                                        <h5 class="form-title"><span>Announcement Information</span></h5>
                                    </div>
                                    
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
                                                <option value="general" {{ old('type', $announcement->type) == 'general' ? 'selected' : '' }}>General</option>
                                                <option value="academic" {{ old('type', $announcement->type) == 'academic' ? 'selected' : '' }}>Academic</option>
                                                <option value="event" {{ old('type', $announcement->type) == 'event' ? 'selected' : '' }}>Event</option>
                                                <option value="reminder" {{ old('type', $announcement->type) == 'reminder' ? 'selected' : '' }}>Reminder</option>
                                                <option value="emergency" {{ old('type', $announcement->type) == 'emergency' ? 'selected' : '' }}>Emergency</option>
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
                                                <option value="low" {{ old('priority', $announcement->priority) == 'low' ? 'selected' : '' }}>Low</option>
                                                <option value="normal" {{ old('priority', $announcement->priority) == 'normal' ? 'selected' : '' }}>Normal</option>
                                                <option value="high" {{ old('priority', $announcement->priority) == 'high' ? 'selected' : '' }}>High</option>
                                                <option value="urgent" {{ old('priority', $announcement->priority) == 'urgent' ? 'selected' : '' }}>Urgent</option>
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
                                        <h5 class="form-title"><span>Advanced Options</span></h5>
                                    </div>
                                    
                                    <div class="col-12 col-sm-6">
                                        <div class="form-group">
                                            <label>Specific Roles (Optional)</label>
                                            <select class="form-control select2" name="target_roles[]" multiple>
                                                <option value="students" {{ in_array('students', old('target_roles', $announcement->target_roles ?? [])) ? 'selected' : '' }}>Students</option>
                                                <option value="teachers" {{ in_array('teachers', old('target_roles', $announcement->target_roles ?? [])) ? 'selected' : '' }}>Teachers</option>
                                                <option value="parents" {{ in_array('parents', old('target_roles', $announcement->target_roles ?? [])) ? 'selected' : '' }}>Parents</option>
                                                <option value="admins" {{ in_array('admins', old('target_roles', $announcement->target_roles ?? [])) ? 'selected' : '' }}>Admins</option>
                                            </select>
                                            <small class="form-text text-muted">Leave empty to use target audience above</small>
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
                                            <small class="form-text text-muted">Leave empty to target all sections</small>
                                        </div>
                                    </div>
                                    
                                    <div class="col-12 col-sm-6">
                                        <div class="form-group">
                                            <label>Scheduled Date (Optional)</label>
                                            <input type="datetime-local" class="form-control" name="scheduled_at" 
                                                   value="{{ old('scheduled_at', $announcement->scheduled_at ? $announcement->scheduled_at->format('Y-m-d\TH:i') : '') }}">
                                            <small class="form-text text-muted">Leave empty to publish immediately</small>
                                        </div>
                                    </div>
                                    
                                    <div class="col-12 col-sm-6">
                                        <div class="form-group">
                                            <label>Expiration Date (Optional)</label>
                                            <input type="datetime-local" class="form-control" name="expires_at" 
                                                   value="{{ old('expires_at', $announcement->expires_at ? $announcement->expires_at->format('Y-m-d\TH:i') : '') }}">
                                            <small class="form-text text-muted">Leave empty for no expiration</small>
                                        </div>
                                    </div>
                                    
                                    <div class="col-12">
                                        <div class="form-group">
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" name="is_pinned" id="is_pinned" 
                                                       value="1" {{ old('is_pinned', $announcement->is_pinned) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="is_pinned">
                                                    Pin this announcement to the top
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="col-12">
                                        <div class="form-group">
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" name="is_scheduled" id="is_scheduled" 
                                                       value="1" {{ old('is_scheduled', $announcement->is_scheduled) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="is_scheduled">
                                                    Schedule this announcement for later
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="col-12">
                                        <button type="submit" class="btn btn-primary">Update Announcement</button>
                                        <a href="{{ route('announcements.show', $announcement->id) }}" class="btn btn-secondary">Cancel</a>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        // Initialize Select2
        $('.select2').select2({
            placeholder: "Select options",
            allowClear: true
        });
        
        // Form validation
        $('#announcementForm').on('submit', function(e) {
            let isValid = true;
            
            // Check required fields
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
        
        // Scheduled date toggle
        $('#is_scheduled').change(function() {
            if ($(this).is(':checked')) {
                $('input[name="scheduled_at"]').prop('required', true);
            } else {
                $('input[name="scheduled_at"]').prop('required', false);
            }
        });
    });
</script>
@endsection 