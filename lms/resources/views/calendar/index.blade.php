@extends('layouts.master')
@section('content')
    {{-- message --}}
    {!! Toastr::message() !!}
    <div class="page-wrapper">
        <div class="content container-fluid">

            <div class="page-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="page-title">Calendar Management</h3>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active">Calendar</li>
                        </ul>
                    </div>
                    <div class="col-auto text-end float-end ms-auto download-grp">
                        <a href="{{ route('calendar.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Add Event
                        </a>
                        <button type="button" class="btn btn-secondary ms-2" onclick="refreshCalendar()">
                            <i class="fas fa-sync-alt"></i> Refresh
                        </button>
                    </div>
                </div>
            </div>

            <!-- Filter Form -->
            <div class="student-group-form">
                <div class="row">
                    <div class="col-lg-3 col-md-6">
                        <div class="form-group">
                            <label>View</label>
                            <select class="form-control" id="calendar_view">
                                <option value="dayGridMonth" {{ $view == 'month' ? 'selected' : '' }}>Month</option>
                                <option value="timeGridWeek" {{ $view == 'week' ? 'selected' : '' }}>Week</option>
                                <option value="timeGridDay" {{ $view == 'day' ? 'selected' : '' }}>Day</option>
                                <option value="listWeek" {{ $view == 'list' ? 'selected' : '' }}>List</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="form-group">
                            <label>Subject</label>
                            <select class="form-control" id="subject_filter">
                                <option value="">All Subjects</option>
                                @foreach($subjects as $subject)
                                    <option value="{{ $subject->id }}">{{ $subject->subject_name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="form-group">
                            <label>Teacher</label>
                            <select class="form-control" id="teacher_filter">
                                <option value="">All Teachers</option>
                                @foreach($teachers as $teacher)
                                    <option value="{{ $teacher->id }}">{{ $teacher->full_name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="form-group">
                            <label>Event Type</label>
                            <select class="form-control" id="event_type_filter">
                                <option value="">All Types</option>
                                <option value="exam">Exam</option>
                                <option value="activity">Activity</option>
                                <option value="meeting">Meeting</option>
                                <option value="deadline">Deadline</option>
                                <option value="holiday">Holiday</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Calendar Container -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div id="calendar"></div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Hidden input for session data -->
            <input type="hidden" id="hasSuccessMessage" value="{{ session('success') ? 'true' : 'false' }}">
            <input type="hidden" id="refreshCalendar" value="{{ session('refresh_calendar') ? 'true' : 'false' }}">

            <!-- Event Legend -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">Event Types</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-2">
                                    <div class="d-flex align-items-center">
                                        <div class="event-legend" style="background-color: #dc3545;"></div>
                                        <span class="ms-2">Exam</span>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="d-flex align-items-center">
                                        <div class="event-legend" style="background-color: #28a745;"></div>
                                        <span class="ms-2">Activity</span>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="d-flex align-items-center">
                                        <div class="event-legend" style="background-color: #17a2b8;"></div>
                                        <span class="ms-2">Meeting</span>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="d-flex align-items-center">
                                        <div class="event-legend" style="background-color: #ffc107;"></div>
                                        <span class="ms-2">Deadline</span>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="d-flex align-items-center">
                                        <div class="event-legend" style="background-color: #6c757d;"></div>
                                        <span class="ms-2">Holiday</span>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="d-flex align-items-center">
                                        <div class="event-legend" style="background-color: #3d5ee1;"></div>
                                        <span class="ms-2">Other</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Event Modal -->
    <div class="modal fade" id="eventModal" tabindex="-1" aria-labelledby="eventModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="eventModalLabel">Event Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="eventModalBody">
                    <!-- Event details will be loaded here -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="editEventBtn">Edit Event</button>
                    <button type="button" class="btn btn-danger" id="deleteEventBtn">Delete Event</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.css" rel="stylesheet">
<style>
    .event-legend {
        width: 20px;
        height: 20px;
        border-radius: 3px;
        display: inline-block;
    }
    
    .fc-event {
        cursor: pointer;
    }
    
    .fc-event:hover {
        opacity: 0.8;
    }
    
    .fc-toolbar-title {
        font-size: 1.5rem !important;
        font-weight: 600;
    }
    
    .fc-button-primary {
        background-color: #3d5ee1 !important;
        border-color: #3d5ee1 !important;
    }
    
    .fc-button-primary:hover {
        background-color: #2d4ed1 !important;
        border-color: #2d4ed1 !important;
    }
    
    .fc-button-primary:focus {
        background-color: #2d4ed1 !important;
        border-color: #2d4ed1 !important;
        box-shadow: 0 0 0 0.2rem rgba(61, 94, 225, 0.25) !important;
    }
    
    .fc-daygrid-day.fc-day-today {
        background-color: rgba(61, 94, 225, 0.1) !important;
    }
    
    .fc-highlight {
        background-color: rgba(61, 94, 225, 0.2) !important;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js"></script>
<script>
let calendar;
let selectedEvent = null;

document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM Content Loaded - Starting calendar initialization');
    
    // Initialize calendar
    initializeCalendar();
    setupFilters();
    
    // Check if calendar needs to be refreshed after event creation
    if (document.getElementById('refreshCalendar').value === 'true') {
        console.log('Refresh calendar flag detected, refreshing calendar...');
        setTimeout(function() {
            if (calendar) {
                calendar.refetchEvents();
                console.log('Calendar refreshed after event creation');
            }
        }, 1500);
    }
});

function initializeCalendar() {
    const calendarEl = document.getElementById('calendar');
    
    console.log('Initializing calendar...');
    console.log('Calendar element:', calendarEl);
    
    if (!calendarEl) {
        console.error('Calendar element not found!');
        return;
    }
    
    // Check if FullCalendar is loaded
    if (typeof FullCalendar === 'undefined') {
        console.error('FullCalendar library is not loaded!');
        alert('FullCalendar library failed to load. Please refresh the page.');
        return;
    }
    
    console.log('FullCalendar library is loaded successfully');
    
    calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek'
        },
        height: 'auto',
        editable: true,
        selectable: true,
        selectMirror: true,
        dayMaxEvents: true,
        weekends: true,
        events: {
            url: '{{ route("calendar.index") }}',
            method: 'GET',
            extraParams: function() {
                const params = {
                    subject_id: $('#subject_filter').val(),
                    teacher_id: $('#teacher_filter').val(),
                    event_type: $('#event_type_filter').val()
                };
                console.log('Sending parameters to server:', params);
                return params;
            },
            failure: function(error) {
                console.error('Failed to load events:', error);
                console.error('Error details:', error.responseText);
            },
            success: function(events) {
                console.log('Events loaded successfully:', events);
                console.log('Number of events:', events.length);
                if (events.length > 0) {
                    console.log('Sample event:', events[0]);
                    console.log('First event title:', events[0].title);
                    console.log('First event start:', events[0].start);
                    console.log('First event end:', events[0].end);
                } else {
                    console.log('No events found in the current date range');
                }
            }
        },
        select: function(arg) {
            // Navigate to create event page with pre-filled date
            const startDate = arg.startStr.split('T')[0];
            const endDate = arg.endStr.split('T')[0];
            window.location.href = '{{ route("calendar.create") }}?start_date=' + startDate + '&end_date=' + endDate;
        },
        eventClick: function(arg) {
            showEventDetails(arg.event);
        },
        eventDrop: function(arg) {
            updateEventDates(arg.event);
        },
        eventResize: function(arg) {
            updateEventDates(arg.event);
        },
        loading: function(isLoading) {
            console.log('Calendar loading:', isLoading);
        }
    });
    
    console.log('Calendar object created:', calendar);
    
    try {
        calendar.render();
        console.log('Calendar rendered successfully');
    } catch (error) {
        console.error('Error rendering calendar:', error);
        alert('Error rendering calendar: ' + error.message);
    }
}

function setupFilters() {
    $('#calendar_view').on('change', function() {
        calendar.changeView($(this).val());
    });
    
    $('#subject_filter, #teacher_filter, #event_type_filter').on('change', function() {
        calendar.refetchEvents();
    });
}

function refreshCalendar() {
    console.log('Manual refresh triggered');
    if (calendar) {
        calendar.refetchEvents();
    }
}

function showEventDetails(event) {
    selectedEvent = event;
    
    const modalBody = `
        <div class="row">
            <div class="col-md-12">
                <h4>${event.title}</h4>
                <hr>
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Type:</strong> <span class="badge bg-primary">${event.extendedProps.event_type}</span></p>
                        <p><strong>Start:</strong> ${formatDateTime(event.start)}</p>
                        <p><strong>End:</strong> ${formatDateTime(event.end)}</p>
                        ${event.extendedProps.subject ? `<p><strong>Subject:</strong> ${event.extendedProps.subject}</p>` : ''}
                    </div>
                    <div class="col-md-6">
                        ${event.extendedProps.teacher ? `<p><strong>Teacher:</strong> ${event.extendedProps.teacher}</p>` : ''}
                        ${event.extendedProps.room ? `<p><strong>Room:</strong> ${event.extendedProps.room}</p>` : ''}
                        ${event.extendedProps.description ? `<p><strong>Description:</strong> ${event.extendedProps.description}</p>` : ''}
                    </div>
                </div>
            </div>
        </div>
    `;
    
    $('#eventModalBody').html(modalBody);
    $('#eventModal').modal('show');
}

function formatDateTime(date) {
    if (!date) return 'N/A';
    return new Date(date).toLocaleString();
}

function updateEventDates(event) {
    const data = {
        start_time: event.start.toISOString(),
        end_time: event.end.toISOString(),
        _token: '{{ csrf_token() }}'
    };
    
    $.ajax({
        url: `/calendar/${event.id}`,
        method: 'PUT',
        data: data,
        success: function(response) {
            if (response.success) {
                toastr.success('Event updated successfully');
            } else {
                toastr.error('Failed to update event');
                calendar.refetchEvents(); // Refresh to revert changes
            }
        },
        error: function() {
            toastr.error('Failed to update event');
            calendar.refetchEvents(); // Refresh to revert changes
        }
    });
}

// Event modal button handlers
$('#editEventBtn').on('click', function() {
    if (selectedEvent) {
        window.location.href = `/calendar/${selectedEvent.id}/edit`;
    }
});

$('#deleteEventBtn').on('click', function() {
    if (selectedEvent && confirm('Are you sure you want to delete this event?')) {
        $.ajax({
            url: `/calendar/${selectedEvent.id}`,
            method: 'DELETE',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    toastr.success('Event deleted successfully');
                    $('#eventModal').modal('hide');
                    calendar.refetchEvents();
                } else {
                    toastr.error('Failed to delete event');
                }
            },
            error: function() {
                toastr.error('Failed to delete event');
            }
        });
    }
});
</script>
@endpush 