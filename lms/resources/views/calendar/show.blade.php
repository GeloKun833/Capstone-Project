@extends('layouts.master')
@section('content')
    {{-- message --}}
    {!! Toastr::message() !!}
    <div class="page-wrapper">
        <div class="content container-fluid">

            <div class="page-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="page-title">Event Details</h3>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('calendar.events.list') }}">Calendar Management</a></li>
                            <li class="breadcrumb-item active">Event Details</li>
                        </ul>
                    </div>
                    <div class="col-auto text-end float-end ms-auto download-grp">
                        <a href="{{ route('calendar.edit', $calendarEvent->id) }}" class="btn btn-primary">
                            <i class="fas fa-edit"></i> Edit Event
                        </a>
                        <a href="{{ route('calendar.events.list') }}" class="btn btn-secondary ms-2">
                            <i class="fas fa-arrow-left"></i> Back to List
                        </a>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">{{ $calendarEvent->title }}</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="event-detail">
                                        <h6><i class="fas fa-calendar-alt text-primary"></i> Event Information</h6>
                                        <table class="table table-borderless">
                                            <tr>
                                                <td><strong>Event Type:</strong></td>
                                                <td>
                                                    <span class="badge" style="background-color: {{ $calendarEvent->event_color }}; color: white;">
                                                        {{ ucfirst($calendarEvent->event_type) }}
                                                    </span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><strong>Start Time:</strong></td>
                                                <td>{{ $calendarEvent->start_time->format('M d, Y g:i A') }}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>End Time:</strong></td>
                                                <td>{{ $calendarEvent->end_time->format('M d, Y g:i A') }}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Duration:</strong></td>
                                                <td>{{ $calendarEvent->start_time->diffForHumans($calendarEvent->end_time, true) }}</td>
                                            </tr>
                                            @if($calendarEvent->is_all_day)
                                                <tr>
                                                    <td><strong>All Day:</strong></td>
                                                    <td><span class="badge bg-success">Yes</span></td>
                                                </tr>
                                            @endif
                                            @if($calendarEvent->is_recurring)
                                                <tr>
                                                    <td><strong>Recurring:</strong></td>
                                                    <td>
                                                        <span class="badge bg-info">Yes</span>
                                                        ({{ ucfirst($calendarEvent->recurrence_pattern) }})
                                                        @if($calendarEvent->recurrence_end_date)
                                                            until {{ $calendarEvent->recurrence_end_date->format('M d, Y') }}
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endif
                                        </table>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="event-detail">
                                        <h6><i class="fas fa-info-circle text-info"></i> Additional Details</h6>
                                        <table class="table table-borderless">
                                            @if($calendarEvent->subject)
                                                <tr>
                                                    <td><strong>Subject:</strong></td>
                                                    <td>{{ $calendarEvent->subject->subject_name }}</td>
                                                </tr>
                                            @endif
                                            @if($calendarEvent->teacher)
                                                <tr>
                                                    <td><strong>Teacher:</strong></td>
                                                    <td>{{ $calendarEvent->teacher->full_name }}</td>
                                                </tr>
                                            @endif
                                            @if($calendarEvent->room)
                                                <tr>
                                                    <td><strong>Room:</strong></td>
                                                    <td>{{ $calendarEvent->room->room_name }}</td>
                                                </tr>
                                            @endif
                                            <tr>
                                                <td><strong>Created by:</strong></td>
                                                <td>{{ $calendarEvent->createdBy->name ?? 'System' }}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Created on:</strong></td>
                                                <td>{{ $calendarEvent->created_at->format('M d, Y g:i A') }}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Last updated:</strong></td>
                                                <td>{{ $calendarEvent->updated_at->format('M d, Y g:i A') }}</td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            @if($calendarEvent->description)
                                <div class="row mt-4">
                                    <div class="col-12">
                                        <div class="event-detail">
                                            <h6><i class="fas fa-align-left text-secondary"></i> Description</h6>
                                            <div class="description-content">
                                                {!! nl2br(e($calendarEvent->description)) !!}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">Quick Actions</h5>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <a href="{{ route('calendar.edit', $calendarEvent->id) }}" class="btn btn-primary">
                                    <i class="fas fa-edit"></i> Edit Event
                                </a>
                                <form action="{{ route('calendar.destroy', $calendarEvent->id) }}" method="POST" 
                                      onsubmit="return confirm('Are you sure you want to delete this event?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger w-100">
                                        <i class="fas fa-trash"></i> Delete Event
                                    </button>
                                </form>
                                <a href="{{ route('calendar.events.list') }}" class="btn btn-secondary">
                                    <i class="fas fa-list"></i> Back to Events List
                                </a>
                            </div>
                        </div>
                    </div>

                    @if($calendarEvent->subject || $calendarEvent->teacher || $calendarEvent->room)
                        <div class="card mt-3">
                            <div class="card-header">
                                <h5 class="card-title">Related Information</h5>
                            </div>
                            <div class="card-body">
                                @if($calendarEvent->subject)
                                    <div class="related-item">
                                        <h6><i class="fas fa-book text-primary"></i> Subject</h6>
                                        <p class="mb-2">{{ $calendarEvent->subject->subject_name }}</p>
                                        @if($calendarEvent->subject->class)
                                            <small class="text-muted">Class: {{ $calendarEvent->subject->class }}</small>
                                        @endif
                                    </div>
                                @endif

                                @if($calendarEvent->teacher)
                                    <div class="related-item mt-3">
                                        <h6><i class="fas fa-chalkboard-teacher text-success"></i> Teacher</h6>
                                        <p class="mb-2">{{ $calendarEvent->teacher->full_name }}</p>
                                        @if($calendarEvent->teacher->qualification)
                                            <small class="text-muted">{{ $calendarEvent->teacher->qualification }}</small>
                                        @endif
                                    </div>
                                @endif

                                @if($calendarEvent->room)
                                    <div class="related-item mt-3">
                                        <h6><i class="fas fa-door-open text-warning"></i> Room</h6>
                                        <p class="mb-2">{{ $calendarEvent->room->room_name }}</p>
                                        @if($calendarEvent->room->capacity)
                                            <small class="text-muted">Capacity: {{ $calendarEvent->room->capacity }} students</small>
                                        @endif
                                        @if($calendarEvent->room->location)
                                            <br><small class="text-muted">Location: {{ $calendarEvent->room->location }}</small>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif

                    <div class="card mt-3">
                        <div class="card-header">
                            <h5 class="card-title">Event Timeline</h5>
                        </div>
                        <div class="card-body">
                            <div class="timeline">
                                <div class="timeline-item">
                                    <div class="timeline-marker bg-primary"></div>
                                    <div class="timeline-content">
                                        <h6>Event Created</h6>
                                        <small class="text-muted">{{ $calendarEvent->created_at->format('M d, Y g:i A') }}</small>
                                    </div>
                                </div>
                                @if($calendarEvent->updated_at != $calendarEvent->created_at)
                                    <div class="timeline-item">
                                        <div class="timeline-marker bg-info"></div>
                                        <div class="timeline-content">
                                            <h6>Event Updated</h6>
                                            <small class="text-muted">{{ $calendarEvent->updated_at->format('M d, Y g:i A') }}</small>
                                        </div>
                                    </div>
                                @endif
                                <div class="timeline-item">
                                    <div class="timeline-marker bg-success"></div>
                                    <div class="timeline-content">
                                        <h6>Event Scheduled</h6>
                                        <small class="text-muted">{{ $calendarEvent->start_time->format('M d, Y g:i A') }}</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
<style>
.event-detail {
    background-color: #f8f9fa;
    padding: 15px;
    border-radius: 5px;
    margin-bottom: 15px;
}

.event-detail h6 {
    margin-bottom: 15px;
    color: #333;
    font-weight: 600;
}

.description-content {
    background-color: white;
    padding: 15px;
    border-radius: 5px;
    border-left: 4px solid #3d5ee1;
}

.related-item {
    padding: 10px 0;
    border-bottom: 1px solid #eee;
}

.related-item:last-child {
    border-bottom: none;
}

.related-item h6 {
    margin-bottom: 5px;
    font-size: 0.9rem;
    font-weight: 600;
}

.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline-item {
    position: relative;
    margin-bottom: 20px;
}

.timeline-marker {
    position: absolute;
    left: -35px;
    top: 5px;
    width: 12px;
    height: 12px;
    border-radius: 50%;
}

.timeline-content h6 {
    margin-bottom: 5px;
    font-size: 0.9rem;
    font-weight: 600;
}

.timeline-content small {
    font-size: 0.8rem;
}

.badge {
    font-size: 0.75rem;
    padding: 0.375rem 0.75rem;
}
</style>
@endpush 