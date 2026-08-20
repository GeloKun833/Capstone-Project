@extends('layouts.master')
@section('content')
    {{-- message --}}
    {!! Toastr::message() !!}
    <div class="page-wrapper">
        <div class="content container-fluid">

            <div class="page-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="page-title">Compose Message</h3>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('messages.index') }}">Messages</a></li>
                            <li class="breadcrumb-item active">Compose Message</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-12">
                    <div class="card">
                        <div class="card-body">
                            <form action="{{ route('messages.store') }}" method="POST" id="messageForm">
                                @csrf
                                
                                <div class="row">
                                    <div class="col-12">
                                        <h5 class="form-title"><span>Message Information</span></h5>
                                    </div>
                                    
                                    <div class="col-12 col-sm-6">
                                        <div class="form-group">
                                            <label>Recipient <span class="text-danger">*</span></label>
                                            <select class="form-control @error('recipient_id') is-invalid @enderror" name="recipient_id" required>
                                                <option value="">Select Recipient</option>
                                                @foreach($recipients as $recipient)
                                                    <option value="{{ $recipient->id }}" {{ old('recipient_id') == $recipient->id ? 'selected' : '' }}>
                                                        {{ $recipient->name }} ({{ ucfirst($recipient->role_name) }})
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('recipient_id')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    
                                    <div class="col-12 col-sm-6">
                                        <div class="form-group">
                                            <label>Subject <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control @error('subject') is-invalid @enderror" 
                                                   name="subject" value="{{ old('subject') }}" required>
                                            @error('subject')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    
                                    <div class="col-12 col-sm-6">
                                        <div class="form-group">
                                            <label>Message Type <span class="text-danger">*</span></label>
                                            <select class="form-control @error('type') is-invalid @enderror" name="type" required>
                                                <option value="">Select Type</option>
                                                <option value="general" {{ old('type') == 'general' ? 'selected' : '' }}>General</option>
                                                <option value="academic" {{ old('type') == 'academic' ? 'selected' : '' }}>Academic</option>
                                                <option value="behavioral" {{ old('type') == 'behavioral' ? 'selected' : '' }}>Behavioral</option>
                                                <option value="attendance" {{ old('type') == 'attendance' ? 'selected' : '' }}>Attendance</option>
                                                <option value="grade" {{ old('type') == 'grade' ? 'selected' : '' }}>Grade</option>
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
                                    
                                    <div class="col-12 col-sm-6">
                                        <div class="form-group">
                                            <label>Related Student (Optional)</label>
                                            <select class="form-control" name="student_id">
                                                <option value="">Select Student (Optional)</option>
                                                @foreach($students as $student)
                                                    <option value="{{ $student->id }}" {{ old('student_id') == $student->id ? 'selected' : '' }}>
                                                        {{ $student->full_name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <small class="form-text text-muted">Link this message to a specific student</small>
                                        </div>
                                    </div>
                                    
                                    <div class="col-12">
                                        <div class="form-group">
                                            <label>Message Content <span class="text-danger">*</span></label>
                                            <textarea class="form-control @error('content') is-invalid @enderror" 
                                                      name="content" rows="8" required placeholder="Type your message here...">{{ old('content') }}</textarea>
                                            @error('content')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    
                                    <div class="col-12">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-paper-plane me-2"></i>Send Message
                                        </button>
                                        <a href="{{ route('messages.index') }}" class="btn btn-secondary">Cancel</a>
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
        // Form validation
        $('#messageForm').on('submit', function(e) {
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
        
        // Character counter for content
        $('textarea[name="content"]').on('input', function() {
            const maxLength = 1000;
            const currentLength = $(this).val().length;
            const remaining = maxLength - currentLength;
            
            if (!$(this).next('.char-counter').length) {
                $(this).after('<small class="char-counter text-muted"></small>');
            }
            
            $(this).next('.char-counter').text(`${currentLength}/${maxLength} characters`);
            
            if (currentLength > maxLength) {
                $(this).next('.char-counter').addClass('text-danger').removeClass('text-muted');
            } else {
                $(this).next('.char-counter').removeClass('text-danger').addClass('text-muted');
            }
        });
    });
</script>
@endsection 