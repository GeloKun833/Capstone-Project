@extends('layouts.master')
@section('content')
    {{-- message --}}
    {!! Toastr::message() !!}
    <div class="page-wrapper">
        <div class="content container-fluid">

            <div class="page-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="page-title">Add Calendar Event</h3>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('calendar.index') }}">Calendar</a></li>
                            <li class="breadcrumb-item active">Add Event</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">Event Details</h5>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('calendar.store') }}" method="POST" id="eventForm">
                                @csrf
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Event Title <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" name="title" value="{{ old('title') }}" required>
                                            @error('title')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Event Type <span class="text-danger">*</span></label>
                                            <select class="form-control" name="event_type" required>
                                                <option value="">Select Event Type</option>
                                                @foreach($eventTypes as $type)
                                                    <option value="{{ $type }}" {{ old('event_type') == $type ? 'selected' : '' }}>
                                                        {{ ucfirst($type) }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('event_type')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Start Date & Time <span class="text-danger">*</span></label>
                                            <input type="datetime-local" class="form-control" name="start_time" 
                                                   value="{{ old('start_time', request('start_date') ? request('start_date') . 'T09:00' : '') }}" required>
                                            @error('start_time')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>End Date & Time <span class="text-danger">*</span></label>
                                            <input type="datetime-local" class="form-control" name="end_time" 
                                                   value="{{ old('end_time', request('end_date') ? request('end_date') . 'T10:00' : '') }}" required>
                                            @error('end_time')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Subject</label>
                                            <select class="form-control" name="subject_id">
                                                <option value="">Select Subject</option>
                                                @foreach($subjects as $subject)
                                                    <option value="{{ $subject->id }}" {{ old('subject_id') == $subject->id ? 'selected' : '' }}>
                                                        {{ $subject->subject_name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('subject_id')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Teacher</label>
                                            <select class="form-control" name="teacher_id" id="teacher_id">
                                                <option value="">Select Teacher</option>
                                                @foreach($teachers as $teacher)
                                                    <option value="{{ $teacher->id }}" {{ old('teacher_id') == $teacher->id ? 'selected' : '' }}>
                                                        {{ $teacher->full_name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('teacher_id')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Room</label>
                                            <select class="form-control" name="room_id" id="room_id">
                                                <option value="">Select Room</option>
                                                @foreach($rooms as $room)
                                                    <option value="{{ $room->id }}" {{ old('room_id') == $room->id ? 'selected' : '' }}>
                                                        {{ $room->full_name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('room_id')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label>Description</label>
                                    <textarea class="form-control" name="description" rows="3">{{ old('description') }}</textarea>
                                    @error('description')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="is_all_day" value="1" id="is_all_day" {{ old('is_all_day') ? 'checked' : '' }}>
                                            <label class="form-check-label" for="is_all_day">
                                                All Day Event
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="is_recurring" value="1" id="is_recurring" {{ old('is_recurring') ? 'checked' : '' }}>
                                            <label class="form-check-label" for="is_recurring">
                                                Recurring Event
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <div class="row" id="recurrence_options" style="display: none;">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Recurrence Pattern</label>
                                            <select class="form-control" name="recurrence_pattern">
                                                <option value="">Select Pattern</option>
                                                <option value="daily" {{ old('recurrence_pattern') == 'daily' ? 'selected' : '' }}>Daily</option>
                                                <option value="weekly" {{ old('recurrence_pattern') == 'weekly' ? 'selected' : '' }}>Weekly</option>
                                                <option value="monthly" {{ old('recurrence_pattern') == 'monthly' ? 'selected' : '' }}>Monthly</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>End Date</label>
                                            <input type="date" class="form-control" name="recurrence_end_date" value="{{ old('recurrence_end_date') }}">
                                        </div>
                                    </div>
                                </div>

                                <div class="row mt-3">
                                    <div class="col-12">
                                        <button type="submit" class="btn btn-primary">Create Event</button>
                                        <a href="{{ route('calendar.index') }}" class="btn btn-secondary">Cancel</a>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">Available Time Slots</h5>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label>Date</label>
                                <input type="date" class="form-control" id="slot_date" value="{{ date('Y-m-d') }}">
                            </div>
                            <div class="form-group">
                                <label>Duration (minutes)</label>
                                <select class="form-control" id="slot_duration">
                                    <option value="30">30 minutes</option>
                                    <option value="60" selected>1 hour</option>
                                    <option value="90">1.5 hours</option>
                                    <option value="120">2 hours</option>
                                </select>
                            </div>
                            <button type="button" class="btn btn-info btn-sm" onclick="checkAvailableSlots()">
                                Check Available Slots
                            </button>
                            <div id="available_slots" class="mt-3">
                                <!-- Available slots will be displayed here -->
                            </div>
                        </div>
                    </div>

                    <div class="card mt-3">
                        <div class="card-header">
                            <h5 class="card-title">Conflict Check</h5>
                        </div>
                        <div class="card-body">
                            <button type="button" class="btn btn-warning btn-sm" onclick="checkConflicts()">
                                Check for Conflicts
                            </button>
                            <div id="conflict_results" class="mt-3">
                                <!-- Conflict results will be displayed here -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Show/hide recurrence options
    $('#is_recurring').change(function() {
        if ($(this).is(':checked')) {
            $('#recurrence_options').show();
        } else {
            $('#recurrence_options').hide();
        }
    });

    // Show/hide time inputs for all-day events
    $('#is_all_day').change(function() {
        if ($(this).is(':checked')) {
            $('input[name="start_time"], input[name="end_time"]').attr('type', 'date');
        } else {
            $('input[name="start_time"], input[name="end_time"]').attr('type', 'datetime-local');
        }
    });

    // Form validation
    $('#eventForm').submit(function(e) {
        const startTime = new Date($('input[name="start_time"]').val());
        const endTime = new Date($('input[name="end_time"]').val());
        
        if (endTime <= startTime) {
            e.preventDefault();
            toastr.error('End time must be after start time');
            return false;
        }
    });
});

function checkAvailableSlots() {
    const date = $('#slot_date').val();
    const duration = $('#slot_duration').val();
    const teacherId = $('#teacher_id').val();
    const roomId = $('#room_id').val();

    if (!date) {
        toastr.error('Please select a date');
        return;
    }

    $.ajax({
        url: '{{ route("calendar.available-slots") }}',
        method: 'GET',
        data: {
            date: date,
            duration: duration,
            teacher_id: teacherId,
            room_id: roomId
        },
        success: function(response) {
            let html = '<h6>Available Time Slots:</h6>';
            if (response.length > 0) {
                html += '<ul class="list-unstyled">';
                response.forEach(function(slot) {
                    html += `<li><span class="badge bg-success">${slot.start} - ${slot.end}</span></li>`;
                });
                html += '</ul>';
            } else {
                html += '<p class="text-muted">No available slots for this date.</p>';
            }
            $('#available_slots').html(html);
        },
        error: function() {
            toastr.error('Failed to check available slots');
        }
    });
}

function checkConflicts() {
    const startTime = $('input[name="start_time"]').val();
    const endTime = $('input[name="end_time"]').val();
    const teacherId = $('#teacher_id').val();
    const roomId = $('#room_id').val();

    if (!startTime || !endTime) {
        toastr.error('Please set start and end times first');
        return;
    }

    $.ajax({
        url: '{{ route("calendar.check-conflicts") }}',
        method: 'GET',
        data: {
            start_time: startTime,
            end_time: endTime,
            teacher_id: teacherId,
            room_id: roomId
        },
        success: function(response) {
            let html = '<h6>Conflict Check Results:</h6>';
            if (response.has_conflicts) {
                html += '<div class="alert alert-danger">';
                html += '<strong>Conflicts detected!</strong><br>';
                if (response.conflicts.teacher) {
                    html += 'Teacher has conflicting events.<br>';
                }
                if (response.conflicts.room) {
                    html += 'Room is already booked.<br>';
                }
                html += '</div>';
                
                if (response.available_slots && response.available_slots.length > 0) {
                    html += '<h6>Suggested Available Slots:</h6>';
                    html += '<ul class="list-unstyled">';
                    response.available_slots.slice(0, 5).forEach(function(slot) {
                        html += `<li><span class="badge bg-info">${slot.start} - ${slot.end}</span></li>`;
                    });
                    html += '</ul>';
                }
            } else {
                html += '<div class="alert alert-success">No conflicts detected!</div>';
            }
            $('#conflict_results').html(html);
        },
        error: function() {
            toastr.error('Failed to check conflicts');
        }
    });
}
</script>
@endpush 