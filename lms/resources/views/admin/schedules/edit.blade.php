@extends('layouts.master')
@section('content')


<div class="page-wrapper">
    <div class="content container-fluid">
        
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">Edit Class Schedule</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.schedules.index') }}">Schedules</a></li>
                        <li class="breadcrumb-item active">Edit Schedule</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-body">
                        <form action="{{ route('admin.schedules.update', $schedule) }}" method="POST">
                            @csrf
                            @method('PUT')
                            
                            <div class="row">
                                <div class="col-12">
                                    <h5 class="form-title"><span>Class Information</span></h5>
                                </div>
                                
                                <div class="col-12 col-sm-6">
                                    <div class="form-group local-forms">
                                        <label>Section <span class="login-danger">*</span></label>
                                        <select class="form-control" name="section_id" required>
                                            <option value="">Select Section</option>
                                            @foreach($sections as $section)
                                                <option value="{{ $section->id }}" {{ (old('section_id', $schedule->section_id) == $section->id) ? 'selected' : '' }}>
                                                    {{ $section->name }} ({{ $section->grade_level }})
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('section_id')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                
                                <div class="col-12 col-sm-6">
                                    <div class="form-group local-forms">
                                        <label>Subject <span class="login-danger">*</span></label>
                                        <select class="form-control" name="subject_id" required>
                                            <option value="">Select Subject</option>
                                            @foreach($subjects as $subject)
                                                <option value="{{ $subject->id }}" {{ (old('subject_id', $schedule->subject_id) == $subject->id) ? 'selected' : '' }}>
                                                    {{ $subject->subject_name }} ({{ $subject->class }})
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('subject_id')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                
                                <div class="col-12 col-sm-6">
                                    <div class="form-group local-forms">
                                        <label>Teacher <span class="login-danger">*</span></label>
                                        <select class="form-control" name="teacher_id" required>
                                            <option value="">Select Teacher</option>
                                            @foreach($teachers as $teacher)
                                                <option value="{{ $teacher->id }}" {{ (old('teacher_id', $schedule->teacher_id) == $teacher->id) ? 'selected' : '' }}>
                                                    {{ $teacher->full_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('teacher_id')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                
                                <div class="col-12 col-sm-6">
                                    <div class="form-group local-forms">
                                        <label>Room</label>
                                        <select class="form-control" name="room_id">
                                            <option value="">Select Room (Optional)</option>
                                            @foreach($rooms as $room)
                                                <option value="{{ $room->id }}" {{ (old('room_id', $schedule->room_id) == $room->id) ? 'selected' : '' }}>
                                                    {{ $room->room_name }} - {{ $room->room_type }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('room_id')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-12">
                                    <h5 class="form-title"><span>Schedule Details</span></h5>
                                </div>
                                
                                <div class="col-12 col-sm-4">
                                    <div class="form-group local-forms">
                                        <label>Day of Week <span class="login-danger">*</span></label>
                                        <select class="form-control" name="day_of_week" required>
                                            <option value="">Select Day</option>
                                            <option value="monday" {{ old('day_of_week', $schedule->day_of_week) == 'monday' ? 'selected' : '' }}>Monday</option>
                                            <option value="tuesday" {{ old('day_of_week', $schedule->day_of_week) == 'tuesday' ? 'selected' : '' }}>Tuesday</option>
                                            <option value="wednesday" {{ old('day_of_week', $schedule->day_of_week) == 'wednesday' ? 'selected' : '' }}>Wednesday</option>
                                            <option value="thursday" {{ old('day_of_week', $schedule->day_of_week) == 'thursday' ? 'selected' : '' }}>Thursday</option>
                                            <option value="friday" {{ old('day_of_week', $schedule->day_of_week) == 'friday' ? 'selected' : '' }}>Friday</option>
                                            <option value="saturday" {{ old('day_of_week', $schedule->day_of_week) == 'saturday' ? 'selected' : '' }}>Saturday</option>
                                            <option value="sunday" {{ old('day_of_week', $schedule->day_of_week) == 'sunday' ? 'selected' : '' }}>Sunday</option>
                                        </select>
                                        @error('day_of_week')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                
                                <div class="col-12 col-sm-4">
                                    <div class="form-group local-forms">
                                        <label>Start Time <span class="login-danger">*</span></label>
                                        <input type="time" class="form-control" name="start_time" value="{{ old('start_time', Carbon\Carbon::parse($schedule->start_time)->format('H:i')) }}" required>
                                        @error('start_time')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                
                                <div class="col-12 col-sm-4">
                                    <div class="form-group local-forms">
                                        <label>End Time <span class="login-danger">*</span></label>
                                        <input type="time" class="form-control" name="end_time" value="{{ old('end_time', Carbon\Carbon::parse($schedule->end_time)->format('H:i')) }}" required>
                                        @error('end_time')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                
                                <div class="col-12 col-sm-4">
                                    <div class="form-group local-forms">
                                        <label>Class Type <span class="login-danger">*</span></label>
                                        <select class="form-control" name="class_type" required>
                                            <option value="">Select Type</option>
                                            <option value="lecture" {{ old('class_type', $schedule->class_type) == 'lecture' ? 'selected' : '' }}>Lecture</option>
                                            <option value="laboratory" {{ old('class_type', $schedule->class_type) == 'laboratory' ? 'selected' : '' }}>Laboratory</option>
                                            <option value="tutorial" {{ old('class_type', $schedule->class_type) == 'tutorial' ? 'selected' : '' }}>Tutorial</option>
                                            <option value="exam" {{ old('class_type', $schedule->class_type) == 'exam' ? 'selected' : '' }}>Exam</option>
                                            <option value="other" {{ old('class_type', $schedule->class_type) == 'other' ? 'selected' : '' }}>Other</option>
                                        </select>
                                        @error('class_type')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                
                                <div class="col-12 col-sm-4">
                                    <div class="form-group local-forms">
                                        <label>Color</label>
                                        <input type="color" class="form-control" name="color" value="{{ old('color', $schedule->color) }}" style="height: 45px;">
                                        <small class="text-muted">Choose a color for calendar display</small>
                                        @error('color')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                
                                <div class="col-12 col-sm-4">
                                    <div class="form-group local-forms">
                                        <label>Status</label>
                                        <select class="form-control" name="is_active">
                                            <option value="1" {{ old('is_active', $schedule->is_active) == 1 ? 'selected' : '' }}>Active</option>
                                            <option value="0" {{ old('is_active', $schedule->is_active) == 0 ? 'selected' : '' }}>Inactive</option>
                                        </select>
                                        @error('is_active')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                
                                <div class="col-12">
                                    <div class="form-group local-forms">
                                        <label>Notes</label>
                                        <textarea class="form-control" name="notes" rows="3" placeholder="Add any notes or special instructions">{{ old('notes', $schedule->notes) }}</textarea>
                                        @error('notes')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                
                                <div class="col-12">
                                    <div class="student-submit">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save"></i> Update Schedule
                                        </button>
                                        <a href="{{ route('admin.schedules.index') }}" class="btn btn-secondary">
                                            <i class="fas fa-times"></i> Cancel
                                        </a>
                                    </div>
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

