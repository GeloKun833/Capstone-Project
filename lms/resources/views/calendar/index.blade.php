@extends('layouts.master')
@section('content')

@php
    $typeColors = [
        'exam' => '#dc2626',
        'activity' => '#ea580c',
        'meeting' => '#0f766e',
        'deadline' => '#ca8a04',
        'holiday' => '#64748b',
        'other' => '#475569',
    ];
@endphp

<div class="page-wrapper">
    <div class="content container-fluid ams-calendar-page">

        <div class="page-header ams-cal-header">
            <div class="row align-items-center">
                <div class="col-md-7">
                    <h3 class="page-title mb-1">Calendar &amp; Events</h3>
                    <p class="ams-cal-subtitle mb-0">
                        @if(!empty($isAdmin))
                            Manage school events, academic schedules, activities, and important dates.
                        @else
                            View all school events and create your own. You can edit or delete only events you created.
                        @endif
                    </p>
                </div>
                <div class="col-md-5 text-md-end mt-3 mt-md-0">
                    <button type="button" class="btn btn-outline-secondary ams-cal-btn" id="btnRefreshCalendar">
                        <i class="fas fa-sync-alt"></i> Refresh
                    </button>
                    <button type="button" class="btn btn-primary ams-cal-btn ms-2" id="btnOpenCreateEvent">
                        <i class="fas fa-plus"></i> Create Event
                    </button>
                </div>
            </div>
        </div>

        <div class="card ams-cal-filters-card mb-3">
            <div class="card-body py-3">
                <div class="row g-2 align-items-end">
                    <div class="col-lg-3 col-md-6">
                        <label class="form-label ams-cal-label">Search</label>
                        <input type="text" class="form-control form-control-sm" id="filter_search" placeholder="Search events...">
                    </div>
                    <div class="col-lg-2 col-md-6">
                        <label class="form-label ams-cal-label">Event Type</label>
                        <select class="form-control form-control-sm" id="filter_event_type">
                            <option value="">All Types</option>
                            @foreach($eventTypes as $type)
                                <option value="{{ $type }}">{{ ucfirst($type) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-2 col-md-6">
                        <label class="form-label ams-cal-label">Teacher</label>
                        <select class="form-control form-control-sm" id="filter_teacher">
                            <option value="">All Teachers</option>
                            @foreach($teachers as $teacher)
                                <option value="{{ $teacher->id }}">{{ $teacher->full_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-2 col-md-6">
                        <label class="form-label ams-cal-label">Subject</label>
                        <select class="form-control form-control-sm" id="filter_subject">
                            <option value="">All Subjects</option>
                            @foreach($subjects as $subject)
                                <option value="{{ $subject->id }}">{{ $subject->subject_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-2 col-md-6">
                        <label class="form-label ams-cal-label">Room</label>
                        <select class="form-control form-control-sm" id="filter_room">
                            <option value="">All Rooms</option>
                            @foreach($rooms as $room)
                                <option value="{{ $room->id }}">{{ $room->full_name ?? $room->room_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-1 col-md-6">
                        <button type="button" class="btn btn-sm btn-outline-secondary w-100" id="btnClearFilters" title="Clear filters">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="ams-cal-legend mb-3">
            @foreach($typeColors as $type => $color)
                <span class="ams-cal-legend-item">
                    <span class="ams-cal-dot" style="background:{{ $color }}"></span>
                    {{ ucfirst($type) }}
                </span>
            @endforeach
        </div>

        <div class="card ams-cal-main-card">
            <div class="card-header ams-cal-card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">School Calendar</h5>
                <small class="text-muted" id="calLastUpdated">—</small>
            </div>
            <div class="card-body position-relative">
                <div id="calEmptyHint" class="ams-cal-empty-hint d-none">
                    No events scheduled for this period.
                </div>
                <div id="calendar"></div>
            </div>
        </div>
    </div>
</div>

{{-- Event Details Modal --}}
<div class="modal fade" id="eventDetailsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content ams-cal-modal">
            <div class="modal-header">
                <h5 class="modal-title">Event Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="eventDetailsBody"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-outline-primary" id="btnEditFromDetails">Edit Event</button>
                <button type="button" class="btn btn-danger" id="btnDeleteFromDetails">Delete Event</button>
            </div>
        </div>
    </div>
</div>

{{-- Delete Confirm Modal --}}
<div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content ams-cal-modal">
            <div class="modal-header">
                <h5 class="modal-title">Delete this event?</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="mb-1">Event: <strong id="deleteEventTitle"></strong></p>
                <p class="text-muted mb-0 small">This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="btnConfirmDelete">Delete Event</button>
            </div>
        </div>
    </div>
</div>

{{-- Create / Edit Event Modal --}}
<div class="modal fade" id="eventFormModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable modal-dialog-centered">
        <div class="modal-content ams-cal-modal">
            <div class="modal-header">
                <h5 class="modal-title" id="eventFormModalTitle">Create Event</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="calendarEventForm" class="modal-body" novalidate>
                <input type="hidden" id="form_event_id" value="">

                <div class="ams-cal-section">
                    <h6 class="ams-cal-section-title">Event Information</h6>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label>Event Title <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="title" id="form_title" required>
                                <div class="invalid-feedback field-error" data-field="title"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label>Event Type <span class="text-danger">*</span></label>
                                <select class="form-control" name="event_type" id="form_event_type" required>
                                    <option value="">Select Event Type</option>
                                    @foreach($eventTypes as $type)
                                        <option value="{{ $type }}">{{ ucfirst($type) }}</option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback field-error" data-field="event_type"></div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group mb-0">
                        <label>Description</label>
                        <textarea class="form-control" name="description" id="form_description" rows="2"></textarea>
                    </div>
                </div>

                <div class="ams-cal-section">
                    <h6 class="ams-cal-section-title">Schedule</h6>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label>Start Date &amp; Time <span class="text-danger">*</span></label>
                                <input type="datetime-local" class="form-control" name="start_time" id="form_start_time" required>
                                <div class="invalid-feedback field-error" data-field="start_time"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label>End Date &amp; Time <span class="text-danger">*</span></label>
                                <input type="datetime-local" class="form-control" name="end_time" id="form_end_time" required>
                                <div class="invalid-feedback field-error" data-field="end_time"></div>
                            </div>
                        </div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-md-6">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="form_is_all_day" value="1">
                                <label class="form-check-label" for="form_is_all_day">All Day Event</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="form_is_recurring" value="1">
                                <label class="form-check-label" for="form_is_recurring">Recurring Event</label>
                            </div>
                        </div>
                    </div>
                    <div class="row" id="recurrence_options" style="display:none;">
                        <div class="col-md-6">
                            <div class="form-group mb-0">
                                <label>Repeat</label>
                                <select class="form-control" name="recurrence_pattern" id="form_recurrence_pattern">
                                    <option value="">Select</option>
                                    <option value="daily">Daily</option>
                                    <option value="weekly">Weekly</option>
                                    <option value="monthly">Monthly</option>
                                    <option value="custom">Custom</option>
                                </select>
                                <div class="invalid-feedback field-error" data-field="recurrence_pattern"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-0">
                                <label>Until</label>
                                <input type="date" class="form-control" name="recurrence_end_date" id="form_recurrence_end_date">
                                <div class="invalid-feedback field-error" data-field="recurrence_end_date"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="ams-cal-section">
                    <h6 class="ams-cal-section-title">Academic Assignment</h6>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group mb-0">
                                <label>Subject</label>
                                <select class="form-control" name="subject_id" id="form_subject_id">
                                    <option value="">Select Subject</option>
                                    @foreach($subjects as $subject)
                                        <option value="{{ $subject->id }}">{{ $subject->subject_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group mb-0">
                                <label>Teacher</label>
                                <select class="form-control" name="teacher_id" id="form_teacher_id">
                                    <option value="">Select Teacher</option>
                                    @foreach($teachers as $teacher)
                                        <option value="{{ $teacher->id }}">{{ $teacher->full_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group mb-0">
                                <label>Room</label>
                                <select class="form-control" name="room_id" id="form_room_id">
                                    <option value="">Select Room</option>
                                    @foreach($rooms as $room)
                                        <option value="{{ $room->id }}">{{ $room->full_name ?? $room->room_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div id="subject_preference_hint" class="ams-smart-hint mt-2 d-none"></div>
                    <div id="workload_panel" class="ams-workload-panel mt-2"></div>
                </div>

                <div class="ams-cal-section">
                    <h6 class="ams-cal-section-title">Available Time Slots</h6>
                    <div class="row align-items-end">
                        <div class="col-md-4">
                            <div class="form-group mb-2">
                                <label>Date</label>
                                <input type="date" class="form-control" id="slot_date">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group mb-2">
                                <label>Duration</label>
                                <select class="form-control" id="slot_duration">
                                    <option value="30">30 minutes</option>
                                    <option value="60" selected>1 hour</option>
                                    <option value="90">1.5 hours</option>
                                    <option value="120">2 hours</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4 mb-2">
                            <button type="button" class="btn btn-outline-primary btn-sm w-100" id="btnCheckSlots">
                                Find Available Slots
                            </button>
                        </div>
                    </div>
                    <div id="available_slots_panel" class="ams-slots-panel"></div>
                </div>

                <div class="ams-cal-section mb-0">
                    <h6 class="ams-cal-section-title">Conflict Check</h6>
                    <button type="button" class="btn btn-warning btn-sm mb-2" id="btnCheckConflicts">
                        Run Conflict Check
                    </button>
                    <div id="conflict_results_panel" class="ams-conflict-panel">
                        <p class="text-muted small mb-0">Run conflict check before saving when a teacher or room is assigned.</p>
                    </div>
                </div>
            </form>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary" id="btnSaveEvent" form="calendarEventForm">Create Event</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.css" rel="stylesheet">
<style>
    .ams-calendar-page {
        --ams-accent: #3d5ee1;
        --ams-accent-dark: #2d4ed1;
        --ams-accent-soft: #eef2ff;
        --ams-border: #e8ecf4;
        --ams-radius: 14px;
        --ams-shadow: 0 8px 24px rgba(15, 23, 42, 0.06);
        --ams-shadow-lg: 0 18px 40px rgba(15, 23, 42, 0.12);
    }
    .ams-cal-subtitle { color: #6b7280; font-size: 0.9rem; }
    .ams-cal-btn {
        border-radius: 10px;
        transition: transform .18s ease, box-shadow .18s ease, background-color .18s ease;
    }
    .ams-cal-btn:hover { transform: translateY(-1px); }
    .ams-cal-filters-card, .ams-cal-main-card {
        border: 1px solid var(--ams-border);
        border-radius: var(--ams-radius);
        box-shadow: var(--ams-shadow);
        overflow: hidden;
    }
    .ams-cal-card-header {
        background: linear-gradient(180deg, #fff 0%, #fafbff 100%);
        border-bottom: 1px solid var(--ams-border);
    }
    .ams-cal-label { font-size: 0.75rem; font-weight: 600; color: #6b7280; margin-bottom: 0.25rem; }
    .ams-cal-legend { display: flex; flex-wrap: wrap; gap: 0.75rem 1.25rem; }
    .ams-cal-legend-item { display: inline-flex; align-items: center; gap: 0.4rem; font-size: 0.8rem; color: #4b5563; }
    .ams-cal-dot { width: 10px; height: 10px; border-radius: 50%; display: inline-block; box-shadow: 0 0 0 2px #fff, 0 1px 2px rgba(0,0,0,.12); }
    .ams-cal-empty-hint {
        text-align: center; color: #9ca3af; font-size: 0.875rem;
        padding: 0.5rem 0 0.75rem; border-bottom: 1px dashed #e5e7eb; margin-bottom: 0.5rem;
    }
    .ams-cal-section {
        border: 1px solid var(--ams-border); border-radius: 12px;
        padding: 1rem; margin-bottom: 1rem; background: #fbfcff;
    }
    .ams-cal-section-title {
        font-size: 0.8rem; font-weight: 700; text-transform: uppercase;
        letter-spacing: .03em; color: #374151; margin-bottom: 0.85rem;
    }
    .ams-slots-panel { max-height: 180px; overflow-y: auto; }
    .ams-slot-btn {
        display: inline-block; margin: 0.2rem; padding: 0.25rem 0.55rem;
        border-radius: 8px; border: 1px solid #c7d2fe; background: #eef2ff;
        color: #3730a3; font-size: 0.8rem; cursor: pointer;
        transition: transform .15s ease, background .15s ease;
    }
    .ams-slot-btn:hover { background: #e0e7ff; transform: translateY(-1px); }
    .ams-slot-busy {
        display: block; font-size: 0.8rem; color: #9ca3af; padding: 0.15rem 0;
    }
    .ams-conflict-alert {
        border-radius: 10px; padding: 0.75rem 0.9rem; font-size: 0.875rem;
        border: 1px solid #fecaca; background: #fef2f2; color: #991b1b;
    }
    .ams-conflict-ok {
        border-radius: 10px; padding: 0.75rem 0.9rem; font-size: 0.875rem;
        border: 1px solid #bbf7d0; background: #f0fdf4; color: #166534;
    }
    .ams-type-badge {
        display: inline-block; padding: 0.2rem 0.65rem; border-radius: 999px;
        font-size: 0.75rem; color: #fff; font-weight: 600;
        box-shadow: 0 2px 8px rgba(0,0,0,.12);
    }
    .ams-detail-title {
        font-size: 1.35rem; font-weight: 700; color: #111827; margin: 0.35rem 0 0.75rem;
        letter-spacing: -0.02em;
    }
    .ams-detail-row {
        display: flex; gap: 0.5rem; padding: 0.45rem 0;
        border-bottom: 1px solid #f1f5f9; font-size: 0.92rem;
    }
    .ams-detail-row:last-child { border-bottom: 0; }
    .ams-detail-row strong { min-width: 6rem; color: #64748b; font-weight: 600; }
    .ams-detail-row span { color: #111827; }

    .fc .fc-toolbar { gap: 0.5rem; margin-bottom: 1rem !important; }
    .fc .fc-button-primary {
        background-color: var(--ams-accent) !important;
        border-color: var(--ams-accent) !important;
        text-transform: lowercase;
        border-radius: 10px !important;
        box-shadow: 0 2px 8px rgba(61, 94, 225, 0.25) !important;
        transition: transform .15s ease, box-shadow .15s ease, background-color .15s ease !important;
        padding: 0.4rem 0.7rem !important;
    }
    .fc .fc-button-primary:not(:disabled).fc-button-active,
    .fc .fc-button-primary:hover {
        background-color: var(--ams-accent-dark) !important;
        border-color: var(--ams-accent-dark) !important;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(61, 94, 225, 0.3) !important;
    }
    .fc .fc-button-primary:disabled { opacity: .55; }
    .fc .fc-toolbar-title { font-size: 1.2rem !important; font-weight: 700; color: #111827; letter-spacing: -0.02em; }
    .fc .fc-col-header-cell-cushion { color: var(--ams-accent); font-weight: 600; padding: 0.65rem 0.25rem; }
    .fc .fc-scrollgrid { border-radius: 12px; overflow: hidden; border-color: var(--ams-border) !important; }
    .fc .fc-scrollgrid td, .fc .fc-scrollgrid th { border-color: #eef2f7 !important; }
    .fc-daygrid-day.fc-day-today { background: rgba(61, 94, 225, 0.07) !important; }
    .fc .fc-daygrid-day.fc-day-today .fc-daygrid-day-number {
        background: var(--ams-accent); color: #fff; border-radius: 50%;
        width: 1.85rem; height: 1.85rem; display: inline-flex;
        align-items: center; justify-content: center;
        box-shadow: 0 4px 10px rgba(61, 94, 225, 0.35);
        transition: transform .2s ease;
    }
    .fc-event {
        cursor: pointer; border: none !important; border-radius: 7px !important;
        font-size: 0.75rem; padding: 1px 4px !important;
        box-shadow: 0 2px 6px rgba(15, 23, 42, 0.12);
        transition: transform .18s ease, box-shadow .18s ease, filter .18s ease;
        transform-origin: center;
    }
    .fc-event:hover {
        filter: brightness(1.05);
        transform: translateY(-1px) scale(1.02);
        box-shadow: 0 6px 14px rgba(15, 23, 42, 0.18);
        z-index: 5;
    }
    .fc-event.ams-event-click {
        animation: amsEventClick .45s cubic-bezier(.22, 1, .36, 1);
    }
    @keyframes amsEventClick {
        0% { transform: scale(1); box-shadow: 0 2px 6px rgba(15, 23, 42, 0.12); }
        35% { transform: scale(1.12); box-shadow: 0 10px 22px rgba(61, 94, 225, 0.35); }
        100% { transform: scale(1); box-shadow: 0 2px 6px rgba(15, 23, 42, 0.12); }
    }
    .fc-daygrid-day:hover { background: rgba(61, 94, 225, 0.03); cursor: pointer; }
    .fc-daygrid-day-number {
        transition: color .15s ease, background .15s ease;
        border-radius: 50%;
        padding: 0.15rem 0.35rem;
    }

    .ams-cal-modal {
        border: 0;
        border-radius: 16px !important;
        overflow: hidden;
        box-shadow: var(--ams-shadow-lg);
    }
    .ams-cal-modal .modal-header {
        border-bottom: 1px solid var(--ams-border);
        background: linear-gradient(180deg, #fff, #f8faff);
        padding: 1rem 1.25rem;
    }
    .ams-cal-modal .modal-title { font-weight: 700; letter-spacing: -0.01em; }
    .ams-cal-modal .modal-body { padding: 1.15rem 1.25rem; }
    .ams-cal-modal .modal-footer {
        border-top: 1px solid var(--ams-border);
        background: #fff;
        gap: 0.4rem;
        padding: 0.9rem 1.25rem;
    }
    .ams-cal-modal .btn { border-radius: 10px; transition: transform .15s ease, box-shadow .15s ease; }
    .ams-cal-modal .btn:hover { transform: translateY(-1px); }

    #eventDetailsModal.modal.fade .modal-dialog,
    #eventFormModal.modal.fade .modal-dialog,
    #deleteConfirmModal.modal.fade .modal-dialog {
        transform: translateY(18px) scale(0.96);
        opacity: 0;
        transition: transform .28s cubic-bezier(.22, 1, .36, 1), opacity .28s ease;
    }
    #eventDetailsModal.modal.show .modal-dialog,
    #eventFormModal.modal.show .modal-dialog,
    #deleteConfirmModal.modal.show .modal-dialog {
        transform: translateY(0) scale(1);
        opacity: 1;
    }
    #eventDetailsModal .modal-content {
        animation: amsModalGlow .5s ease;
    }
    @keyframes amsModalGlow {
        0% { box-shadow: 0 0 0 0 rgba(61, 94, 225, 0.35); }
        100% { box-shadow: var(--ams-shadow-lg); }
    }

    #eventFormModal .modal-dialog { max-height: calc(100vh - 2rem); }
    #eventFormModal .modal-content { max-height: calc(100vh - 2rem); }
    #eventFormModal form.modal-body { overflow-y: auto; }
    .ams-smart-hint {
        font-size: 0.8rem; color: #3730a3; background: #eef2ff;
        border: 1px solid #c7d2fe; border-radius: 10px; padding: 0.55rem 0.75rem;
    }
    .ams-workload-ok, .ams-workload-warn, .ams-workload-high {
        font-size: 0.8rem; border-radius: 10px; padding: 0.55rem 0.75rem; margin-top: 0.35rem;
    }
    .ams-workload-ok { background: #f8fafc; border: 1px solid #e2e8f0; color: #475569; }
    .ams-workload-warn { background: #fffbeb; border: 1px solid #fde68a; color: #92400e; }
    .ams-workload-high { background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; }
    .ams-suggest-btn {
        display: inline-block; margin: 0.2rem; padding: 0.3rem 0.6rem;
        border-radius: 8px; border: 1px solid #86efac; background: #f0fdf4;
        color: #166534; font-size: 0.78rem; cursor: pointer;
        transition: transform .15s ease, background .15s ease;
    }
    .ams-suggest-btn:hover { background: #dcfce7; transform: translateY(-1px); }
    .ams-calendar-page .btn-primary {
        background-color: var(--ams-accent);
        border-color: var(--ams-accent);
        border-radius: 10px;
        box-shadow: 0 2px 8px rgba(61, 94, 225, 0.25);
    }
    .ams-calendar-page .btn-primary:hover,
    .ams-calendar-page .btn-primary:focus {
        background-color: var(--ams-accent-dark);
        border-color: var(--ams-accent-dark);
    }
    .ams-calendar-page .btn-outline-primary {
        color: var(--ams-accent);
        border-color: var(--ams-accent);
        border-radius: 10px;
    }
    .ams-calendar-page .btn-outline-primary:hover {
        background: var(--ams-accent);
        border-color: var(--ams-accent);
        color: #fff;
    }
    .ams-calendar-page .form-control {
        border-radius: 10px;
        border-color: #e2e8f0;
        transition: border-color .15s ease, box-shadow .15s ease;
    }
    .ams-calendar-page .form-control:focus {
        border-color: #a5b4fc;
        box-shadow: 0 0 0 3px rgba(61, 94, 225, 0.15);
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js"></script>
<script>
(function () {
    const TYPE_COLORS = @json($typeColors);
    const CSRF = '{{ csrf_token() }}';
    const ROUTES = {
        index: '{{ route("calendar.index") }}',
        store: '{{ route("calendar.store") }}',
        update: '{{ url("/calendar") }}',
        destroy: '{{ url("/calendar") }}',
        slots: '{{ route("calendar.available-slots") }}',
        conflicts: '{{ route("calendar.check-conflicts") }}',
        preferences: '{{ route("calendar.subject-preferences") }}',
        workload: '{{ route("calendar.workload") }}',
    };

    let calendar = null;
    let selectedEvent = null;
    let saving = false;
    let searchTimer = null;
    let lastConflictBlocking = false;

    function toastSuccess(msg) {
        if (window.toastr) toastr.success(msg); else alert(msg);
    }
    function toastError(msg) {
        if (window.toastr) toastr.error(msg); else alert(msg);
    }

    function markUpdated() {
        const el = document.getElementById('calLastUpdated');
        if (el) el.textContent = 'Last updated ' + new Date().toLocaleTimeString();
    }

    function filterParams() {
        return {
            search: $('#filter_search').val() || '',
            event_type: $('#filter_event_type').val() || '',
            teacher_id: $('#filter_teacher').val() || '',
            subject_id: $('#filter_subject').val() || '',
            room_id: $('#filter_room').val() || '',
        };
    }

    function formatDateTime(date) {
        if (!date) return '—';
        return new Date(date).toLocaleString(undefined, {
            year: 'numeric', month: 'short', day: 'numeric',
            hour: 'numeric', minute: '2-digit'
        });
    }

    function formatDateOnly(date) {
        if (!date) return '—';
        return new Date(date).toLocaleDateString(undefined, {
            year: 'numeric', month: 'long', day: 'numeric'
        });
    }

    function localInputValue(date) {
        if (!date) return '';
        const d = (date instanceof Date) ? date : new Date(date);
        const pad = (n) => String(n).padStart(2, '0');
        return d.getFullYear() + '-' + pad(d.getMonth() + 1) + '-' + pad(d.getDate())
            + 'T' + pad(d.getHours()) + ':' + pad(d.getMinutes());
    }

    function clearFieldErrors() {
        $('#calendarEventForm .is-invalid').removeClass('is-invalid');
        $('#calendarEventForm .field-error').text('');
    }

    function showFieldErrors(errors) {
        clearFieldErrors();
        if (!errors) return;
        Object.keys(errors).forEach(function (field) {
            const input = $('#calendarEventForm [name="' + field + '"]');
            const msg = Array.isArray(errors[field]) ? errors[field][0] : errors[field];
            input.addClass('is-invalid');
            $('#calendarEventForm .field-error[data-field="' + field + '"]').text(msg).show();
        });
    }

    function resetEventForm() {
        clearFieldErrors();
        lastConflictBlocking = false;
        $('#form_event_id').val('');
        $('#calendarEventForm')[0].reset();
        $('#form_is_all_day, #form_is_recurring').prop('checked', false);
        $('#recurrence_options').hide();
        $('#form_start_time, #form_end_time').attr('type', 'datetime-local').prop('disabled', false);
        $('#available_slots_panel').empty();
        $('#conflict_results_panel').html('<p class="text-muted small mb-0">Run conflict check before saving when a teacher or room is assigned.</p>');
        $('#subject_preference_hint').addClass('d-none').empty();
        $('#workload_panel').empty();
        $('#btnSaveEvent').prop('disabled', false).text('Save Event');
    }

    function openCreateModal(prefillDate) {
        resetEventForm();
        $('#eventFormModalTitle').text('Create Event');
        $('#btnSaveEvent').text('Create Event');

        const base = prefillDate ? new Date(prefillDate + 'T09:00:00') : new Date();
        base.setMinutes(0, 0, 0);
        if (!prefillDate) {
            if (base.getHours() < 8) base.setHours(9);
        }
        const end = new Date(base.getTime() + 60 * 60 * 1000);
        $('#form_start_time').val(localInputValue(base));
        $('#form_end_time').val(localInputValue(end));
        $('#slot_date').val(localInputValue(base).slice(0, 10));

        const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('eventFormModal'));
        modal.show();
    }

    function openEditModal(event) {
        if (!(event.extendedProps && event.extendedProps.can_manage)) {
            toastError('You can only edit events that you created.');
            return;
        }
        resetEventForm();
        selectedEvent = event;
        const p = event.extendedProps || {};
        $('#eventFormModalTitle').text('Edit Event');
        $('#btnSaveEvent').text('Update Event');
        $('#form_event_id').val(event.id);
        $('#form_title').val(event.title || '');
        $('#form_event_type').val(p.event_type || '');
        $('#form_description').val(p.description || '');
        $('#form_start_time').val(p.start_local || localInputValue(event.start));
        $('#form_end_time').val(p.end_local || localInputValue(event.end));
        $('#form_subject_id').val(p.subject_id || '');
        $('#form_teacher_id').val(p.teacher_id || '');
        $('#form_room_id').val(p.room_id || '');
        $('#form_is_all_day').prop('checked', !!p.is_all_day);
        $('#form_is_recurring').prop('checked', !!p.is_recurring);
        $('#form_recurrence_pattern').val(p.recurrence_pattern || '');
        $('#form_recurrence_end_date').val(p.recurrence_end_date || '');
        $('#slot_date').val((p.start_local || localInputValue(event.start)).slice(0, 10));
        toggleAllDay();
        toggleRecurring();

        bootstrap.Modal.getOrCreateInstance(document.getElementById('eventDetailsModal')).hide();
        bootstrap.Modal.getOrCreateInstance(document.getElementById('eventFormModal')).show();
    }

    function toggleAllDay() {
        const allDay = $('#form_is_all_day').is(':checked');
        const start = $('#form_start_time');
        const end = $('#form_end_time');
        if (allDay) {
            const s = (start.val() || '').slice(0, 10);
            const e = (end.val() || s).slice(0, 10);
            start.attr('type', 'date').val(s);
            end.attr('type', 'date').val(e || s);
        } else {
            let s = start.val();
            let e = end.val();
            if (s && s.length === 10) s += 'T09:00';
            if (e && e.length === 10) e += 'T17:00';
            start.attr('type', 'datetime-local').val(s);
            end.attr('type', 'datetime-local').val(e);
        }
    }

    function toggleRecurring() {
        if ($('#form_is_recurring').is(':checked')) {
            $('#recurrence_options').show();
        } else {
            $('#recurrence_options').hide();
            $('#form_recurrence_pattern').val('');
            $('#form_recurrence_end_date').val('');
        }
    }

    function collectFormData() {
        const allDay = $('#form_is_all_day').is(':checked');
        let start = $('#form_start_time').val();
        let end = $('#form_end_time').val();
        if (allDay) {
            if (start && start.length === 10) start += 'T00:00';
            if (end && end.length === 10) end += 'T23:59';
        }
        return {
            title: ($('#form_title').val() || '').trim(),
            event_type: $('#form_event_type').val(),
            description: $('#form_description').val() || '',
            start_time: start,
            end_time: end,
            subject_id: $('#form_subject_id').val() || '',
            teacher_id: $('#form_teacher_id').val() || '',
            room_id: $('#form_room_id').val() || '',
            is_all_day: allDay ? 1 : 0,
            is_recurring: $('#form_is_recurring').is(':checked') ? 1 : 0,
            recurrence_pattern: $('#form_is_recurring').is(':checked') ? ($('#form_recurrence_pattern').val() || '') : '',
            recurrence_end_date: $('#form_is_recurring').is(':checked') ? ($('#form_recurrence_end_date').val() || '') : '',
            _token: CSRF,
        };
    }

    function clientValidate(data) {
        const errors = {};
        if (!data.title) errors.title = ['Event title cannot be empty.'];
        if (!data.event_type) errors.event_type = ['Event type must be selected.'];
        if (!data.start_time) errors.start_time = ['Start date/time is required.'];
        if (!data.end_time) errors.end_time = ['End date/time is required.'];
        if (data.start_time && data.end_time && new Date(data.end_time) <= new Date(data.start_time)) {
            errors.end_time = ['End date/time cannot be earlier than start date/time.'];
        }
        if (data.is_recurring) {
            if (!data.recurrence_pattern) errors.recurrence_pattern = ['Select a repeat pattern.'];
            if (!data.recurrence_end_date) errors.recurrence_end_date = ['Select a recurrence end date.'];
        }
        return errors;
    }

    function renderWorkloadHtml(workload) {
        if (!workload || (!workload.teacher && !workload.room)) {
            return '';
        }
        let html = '';
        [['Teacher', workload.teacher], ['Room', workload.room]].forEach(function (pair) {
            const label = pair[0];
            const data = pair[1];
            if (!data) return;
            const cls = data.level === 'high' ? 'ams-workload-high' : (data.level === 'moderate' ? 'ams-workload-warn' : 'ams-workload-ok');
            const icon = data.level === 'high' ? 'exclamation-triangle' : (data.level === 'moderate' ? 'exclamation-circle' : 'info-circle');
            html += '<div class="' + cls + '"><i class="fas fa-' + icon + '"></i> <strong>' + label + ' workload</strong> (' + (workload.date_display || '') + '): ' + data.message + '</div>';
        });
        return html;
    }

    function renderSuggestionsHtml(suggestions) {
        if (!suggestions || !suggestions.length) {
            return '<p class="text-muted small mt-2 mb-0">No alternative slots found in the next 7 days for the selected teacher/room.</p>';
        }
        let html = '<div class="mt-2"><strong class="small">Suggested next available slots:</strong><div>';
        suggestions.forEach(function (slot) {
            html += '<button type="button" class="ams-suggest-btn" data-date="' + slot.date + '" data-start="' + slot.start + '" data-end="' + slot.end + '">' + slot.label + '</button>';
        });
        html += '</div><p class="text-muted small mt-1 mb-0">Click a suggestion to apply it to Start/End Date &amp; Time.</p></div>';
        return html;
    }

    function renderConflictHtml(response, requestedTitle) {
        let html = '';
        if (!response.has_conflicts) {
            html = '<div class="ams-conflict-ok"><i class="fas fa-check-circle"></i> No scheduling conflicts detected.</div>';
        } else {
            html = '<div class="ams-conflict-alert"><strong>CONFLICT DETECTED</strong>';
            ['room', 'teacher', 'subject'].forEach(function (key) {
                const list = (response.conflicts && response.conflicts[key]) || [];
                list.forEach(function (ev) {
                    html += '<div class="mt-2">';
                    if (key === 'room') html += '<div><strong>Room:</strong> ' + (ev.room || 'Assigned room') + '</div>';
                    if (key === 'teacher') html += '<div><strong>Teacher:</strong> ' + (ev.teacher || 'Assigned teacher') + '</div>';
                    if (key === 'subject') html += '<div><strong>Subject:</strong> ' + (ev.subject || 'Assigned subject') + '</div>';
                    html += '<div><strong>Existing Event:</strong> ' + (ev.title || '') + '</div>';
                    html += '<div><strong>Time:</strong> ' + (ev.start_display || '') + ' – ' + (ev.end_display || '') + '</div>';
                    if (requestedTitle) {
                        html += '<div class="mt-1"><strong>Requested Event:</strong> ' + requestedTitle + '</div>';
                    }
                    if (key === 'room') html += '<div class="mt-1"><em>Room is already occupied during this time.</em></div>';
                    if (key === 'teacher') html += '<div class="mt-1"><em>Teacher is already scheduled during this time.</em></div>';
                    html += '</div>';
                });
            });
            html += '</div>';
            if (response.blocking) {
                html += renderSuggestionsHtml(response.suggestions || []);
            }
        }

        if (response.workload && response.workload.has_warning) {
            html += '<div class="mt-2">' + renderWorkloadHtml(response.workload) + '</div>';
        }

        return html;
    }

    function loadSubjectPreferences() {
        const subjectId = $('#form_subject_id').val();
        const hint = $('#subject_preference_hint');
        if (!subjectId) {
            hint.addClass('d-none').empty();
            return;
        }

        $.ajax({
            url: ROUTES.preferences,
            method: 'GET',
            data: { subject_id: subjectId },
            success: function (pref) {
                if (!pref.teacher_id && !pref.room_id) {
                    hint.addClass('d-none').empty();
                    return;
                }

                let parts = [];
                if (pref.teacher_name) parts.push('Teacher: <strong>' + pref.teacher_name + '</strong> (' + pref.teacher_uses + ' past events)');
                if (pref.room_name) parts.push('Room: <strong>' + pref.room_name + '</strong> (' + pref.room_uses + ' past events)');

                hint.removeClass('d-none').html(
                    '<i class="fas fa-lightbulb"></i> Preferred for this subject — ' + parts.join(' · ') +
                    ' <button type="button" class="btn btn-sm btn-outline-primary ms-2" id="btnApplyPreferences">Apply</button>'
                );
                hint.data('pref', pref);

                // Auto-fill empty teacher/room fields only
                if (!$('#form_teacher_id').val() && pref.teacher_id) {
                    $('#form_teacher_id').val(String(pref.teacher_id));
                }
                if (!$('#form_room_id').val() && pref.room_id) {
                    $('#form_room_id').val(String(pref.room_id));
                }
                loadWorkload();
            }
        });
    }

    function applySubjectPreferences() {
        const pref = $('#subject_preference_hint').data('pref');
        if (!pref) return;
        if (pref.teacher_id) $('#form_teacher_id').val(String(pref.teacher_id));
        if (pref.room_id) $('#form_room_id').val(String(pref.room_id));
        loadWorkload();
        toastSuccess('Preferred teacher/room applied.');
    }

    function loadWorkload() {
        const start = $('#form_start_time').val();
        if (!start) {
            $('#workload_panel').empty();
            return;
        }
        const date = start.slice(0, 10);
        const teacherId = $('#form_teacher_id').val();
        const roomId = $('#form_room_id').val();
        if (!teacherId && !roomId) {
            $('#workload_panel').empty();
            return;
        }

        $.ajax({
            url: ROUTES.workload,
            method: 'GET',
            data: {
                date: date,
                teacher_id: teacherId || null,
                room_id: roomId || null,
                exclude_event_id: $('#form_event_id').val() || null,
            },
            success: function (response) {
                $('#workload_panel').html(renderWorkloadHtml(response));
            }
        });
    }

    function runConflictCheck(callback) {
        const data = collectFormData();
        if (!data.start_time || !data.end_time) {
            toastError('Please set start and end times first.');
            return;
        }
        $.ajax({
            url: ROUTES.conflicts,
            method: 'GET',
            data: {
                start_time: data.start_time,
                end_time: data.end_time,
                teacher_id: data.teacher_id || null,
                room_id: data.room_id || null,
                subject_id: data.subject_id || null,
                exclude_event_id: $('#form_event_id').val() || null,
            },
            success: function (response) {
                lastConflictBlocking = !!response.blocking;
                $('#conflict_results_panel').html(renderConflictHtml(response, data.title));
                if (response.workload) {
                    $('#workload_panel').html(renderWorkloadHtml(response.workload));
                }
                if (callback) callback(response);
            },
            error: function () {
                toastError('Unable to run conflict check.');
            }
        });
    }

    function loadSlots() {
        const date = $('#slot_date').val();
        if (!date) {
            toastError('Please select a date.');
            return;
        }
        $.ajax({
            url: ROUTES.slots,
            method: 'GET',
            data: {
                date: date,
                duration: $('#slot_duration').val(),
                teacher_id: $('#form_teacher_id').val() || null,
                room_id: $('#form_room_id').val() || null,
            },
            success: function (response) {
                const available = response.available || response.slots || (Array.isArray(response) ? response : []);
                const unavailable = response.unavailable || [];
                let html = '';
                if (available.length) {
                    html += '<div class="mb-2"><strong class="small">Available:</strong><div>';
                    available.forEach(function (slot) {
                        const label = slot.start_display || slot.start;
                        html += '<button type="button" class="ams-slot-btn" data-date="' + date + '" data-start="' + slot.start + '" data-end="' + slot.end + '">' + label + '</button>';
                    });
                    html += '</div></div>';
                } else {
                    html += '<p class="text-muted small mb-2">No available slots for this date.</p>';
                }
                if (unavailable.length) {
                    html += '<div><strong class="small">Unavailable:</strong>';
                    unavailable.slice(0, 8).forEach(function (slot) {
                        html += '<span class="ams-slot-busy">' + (slot.start_display || slot.start) + ' — Conflict' + (slot.conflict ? ' (' + slot.conflict + ')' : '') + '</span>';
                    });
                    html += '</div>';
                }
                $('#available_slots_panel').html(html);
            },
            error: function () {
                toastError('Failed to load available slots.');
            }
        });
    }

    function applySlot(btn) {
        const date = btn.getAttribute('data-date');
        const start = btn.getAttribute('data-start');
        const end = btn.getAttribute('data-end');
        $('#form_is_all_day').prop('checked', false);
        toggleAllDay();
        $('#form_start_time').val(date + 'T' + start);
        $('#form_end_time').val(date + 'T' + end);
        $('#slot_date').val(date);
        loadWorkload();
        runConflictCheck();
    }

    function showEventDetails(event, clickEl) {
        selectedEvent = event;
        const p = event.extendedProps || {};
        const color = TYPE_COLORS[p.event_type] || '#3d5ee1';

        if (clickEl) {
            clickEl.classList.remove('ams-event-click');
            // force reflow so animation can replay
            void clickEl.offsetWidth;
            clickEl.classList.add('ams-event-click');
            setTimeout(function () { clickEl.classList.remove('ams-event-click'); }, 480);
        }

        let html = '<div class="mb-2"><span class="ams-type-badge" style="background:' + color + '">' + (p.event_type ? p.event_type.charAt(0).toUpperCase() + p.event_type.slice(1) : 'Event') + '</span>';
        if (p.is_recurring) html += ' <span class="badge bg-secondary">Recurring</span>';
        html += '</div>';
        html += '<div class="ams-detail-title">' + (event.title || '') + '</div>';
        html += '<div class="ams-detail-row"><strong>Date</strong><span>' + formatDateOnly(event.start) + '</span></div>';
        html += '<div class="ams-detail-row"><strong>Start</strong><span>' + formatDateTime(event.start) + '</span></div>';
        html += '<div class="ams-detail-row"><strong>End</strong><span>' + formatDateTime(event.end) + '</span></div>';
        if (p.subject) html += '<div class="ams-detail-row"><strong>Subject</strong><span>' + p.subject + '</span></div>';
        if (p.teacher) html += '<div class="ams-detail-row"><strong>Teacher</strong><span>' + p.teacher + '</span></div>';
        if (p.room) html += '<div class="ams-detail-row"><strong>Room</strong><span>' + p.room + '</span></div>';
        if (p.description) html += '<div class="ams-detail-row"><strong>Description</strong><span>' + p.description + '</span></div>';
        if (p.is_recurring) {
            html += '<div class="ams-detail-row"><strong>Repeats</strong><span>' + (p.recurrence_pattern || '') + (p.recurrence_end_date ? ' until ' + p.recurrence_end_date : '') + '</span></div>';
        }
        if (!p.can_manage) {
            html += '<p class="mb-0 mt-3 text-muted small"><i class="fas fa-lock"></i> You can view this event, but only the creator or an admin can edit or delete it.</p>';
        }
        $('#eventDetailsBody').html(html);
        $('#btnEditFromDetails, #btnDeleteFromDetails').toggle(!!p.can_manage);

        // Slight delay so click pop is felt before modal opens
        setTimeout(function () {
            bootstrap.Modal.getOrCreateInstance(document.getElementById('eventDetailsModal')).show();
        }, 120);
    }

    function saveEvent(e) {
        e.preventDefault();
        if (saving) return;

        const data = collectFormData();
        const errors = clientValidate(data);
        if (Object.keys(errors).length) {
            showFieldErrors(errors);
            toastError('Unable to create event. Please check the highlighted fields.');
            return;
        }

        const eventId = $('#form_event_id').val();
        const isEdit = !!eventId;

        function doSave() {
            saving = true;
            $('#btnSaveEvent').prop('disabled', true).text(isEdit ? 'Updating...' : 'Saving...');
            $.ajax({
                url: isEdit ? (ROUTES.update + '/' + eventId) : ROUTES.store,
                method: isEdit ? 'PUT' : 'POST',
                data: data,
                success: function (response) {
                    saving = false;
                    toastSuccess(response.message || (isEdit ? 'Event updated successfully.' : 'Event created successfully.'));
                    bootstrap.Modal.getOrCreateInstance(document.getElementById('eventFormModal')).hide();
                    if (calendar) calendar.refetchEvents();
                    markUpdated();
                },
                error: function (xhr) {
                    saving = false;
                    $('#btnSaveEvent').prop('disabled', false).text(isEdit ? 'Update Event' : 'Create Event');
                    if (xhr.status === 422) {
                        showFieldErrors(xhr.responseJSON && xhr.responseJSON.errors);
                        toastError((xhr.responseJSON && xhr.responseJSON.error) || 'Unable to create event. Please check the highlighted fields.');
                    } else if (xhr.status === 409) {
                        lastConflictBlocking = true;
                        $('#conflict_results_panel').html(renderConflictHtml(xhr.responseJSON || { has_conflicts: true, conflicts: {} }, data.title));
                        toastError((xhr.responseJSON && xhr.responseJSON.error) || 'Schedule conflict detected. Please select another time or resource.');
                    } else {
                        toastError('Unable to save event. Please try again.');
                    }
                }
            });
        }

        // Always re-check conflicts before save when teacher/room set
        if (data.teacher_id || data.room_id) {
            runConflictCheck(function (response) {
                if (response.blocking) {
                    toastError('Schedule conflict detected. Please select another time or resource.');
                    return;
                }
                doSave();
            });
        } else {
            doSave();
        }
    }

    function confirmDelete() {
        if (!selectedEvent) return;
        if (!(selectedEvent.extendedProps && selectedEvent.extendedProps.can_manage)) {
            toastError('You can only delete events that you created.');
            return;
        }
        $('#deleteEventTitle').text(selectedEvent.title || '');
        bootstrap.Modal.getOrCreateInstance(document.getElementById('eventDetailsModal')).hide();
        bootstrap.Modal.getOrCreateInstance(document.getElementById('deleteConfirmModal')).show();
    }

    function deleteEvent() {
        if (!selectedEvent) return;
        $.ajax({
            url: ROUTES.destroy + '/' + selectedEvent.id,
            method: 'DELETE',
            data: { _token: CSRF },
            success: function (response) {
                toastSuccess(response.message || 'Event deleted successfully.');
                bootstrap.Modal.getOrCreateInstance(document.getElementById('deleteConfirmModal')).hide();
                selectedEvent = null;
                if (calendar) calendar.refetchEvents();
                markUpdated();
            },
            error: function () {
                toastError('Unable to delete event.');
            }
        });
    }

    function updateEventDates(event, revert) {
        $.ajax({
            url: ROUTES.update + '/' + event.id,
            method: 'PUT',
            data: {
                dates_only: 1,
                start_time: localInputValue(event.start).replace('T', ' ') + ':00',
                end_time: localInputValue(event.end || event.start).replace('T', ' ') + ':00',
                _token: CSRF,
            },
            success: function (response) {
                toastSuccess(response.message || 'Event updated successfully.');
                markUpdated();
            },
            error: function (xhr) {
                toastError((xhr.responseJSON && xhr.responseJSON.error) || 'Failed to update event');
                if (revert) revert();
                else if (calendar) calendar.refetchEvents();
            }
        });
    }

    function initCalendar() {
        const el = document.getElementById('calendar');
        if (!el || typeof FullCalendar === 'undefined') {
            toastError('Calendar failed to load. Please refresh the page.');
            return;
        }

        calendar = new FullCalendar.Calendar(el, {
            initialView: 'dayGridMonth',
            firstDay: 0,
            height: 'auto',
            editable: true,
            selectable: true,
            selectMirror: true,
            dayMaxEvents: 3,
            weekends: true,
            nowIndicator: true,
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek'
            },
            buttonText: {
                today: 'today',
                month: 'month',
                week: 'week',
                day: 'day',
                list: 'list'
            },
            events: function (info, successCallback, failureCallback) {
                $.ajax({
                    url: ROUTES.index,
                    method: 'GET',
                    data: Object.assign({
                        start: info.startStr,
                        end: info.endStr,
                        json: 1
                    }, filterParams()),
                    success: function (events) {
                        const list = Array.isArray(events) ? events : [];
                        $('#calEmptyHint').toggleClass('d-none', list.length > 0);
                        successCallback(list);
                        markUpdated();
                    },
                    error: function (err) {
                        failureCallback(err);
                        toastError('Failed to load calendar events.');
                    }
                });
            },
            select: function (arg) {
                calendar.unselect();
                const startDate = arg.startStr.split('T')[0];
                openCreateModal(startDate);
            },
            eventClick: function (arg) {
                arg.jsEvent.preventDefault();
                showEventDetails(arg.event, arg.el);
            },
            eventDrop: function (arg) {
                if (!(arg.event.extendedProps && arg.event.extendedProps.can_manage)) {
                    arg.revert();
                    toastError('You can only edit events that you created.');
                    return;
                }
                updateEventDates(arg.event, arg.revert);
            },
            eventResize: function (arg) {
                if (!(arg.event.extendedProps && arg.event.extendedProps.can_manage)) {
                    arg.revert();
                    toastError('You can only edit events that you created.');
                    return;
                }
                updateEventDates(arg.event, arg.revert);
            },
            eventDidMount: function (info) {
                const type = info.event.extendedProps.event_type;
                if (type && TYPE_COLORS[type]) {
                    info.el.style.backgroundColor = TYPE_COLORS[type];
                    info.el.style.borderColor = TYPE_COLORS[type];
                }
            }
        });

        calendar.render();
        markUpdated();
    }

    document.addEventListener('DOMContentLoaded', function () {
        initCalendar();

        $('#btnRefreshCalendar').on('click', function () {
            if (calendar) calendar.refetchEvents();
        });
        $('#btnOpenCreateEvent').on('click', function () {
            openCreateModal();
        });
        $('#btnClearFilters').on('click', function () {
            $('#filter_search').val('');
            $('#filter_event_type, #filter_teacher, #filter_subject, #filter_room').val('');
            if (calendar) calendar.refetchEvents();
        });
        $('#filter_event_type, #filter_teacher, #filter_subject, #filter_room').on('change', function () {
            if (calendar) calendar.refetchEvents();
        });
        $('#filter_search').on('input', function () {
            clearTimeout(searchTimer);
            searchTimer = setTimeout(function () {
                if (calendar) calendar.refetchEvents();
            }, 350);
        });

        $('#form_is_all_day').on('change', toggleAllDay);
        $('#form_is_recurring').on('change', toggleRecurring);
        $('#btnCheckSlots').on('click', loadSlots);
        $('#slot_date, #slot_duration, #form_teacher_id, #form_room_id').on('change', function () {
            if ($('#slot_date').val()) loadSlots();
            loadWorkload();
        });
        $('#form_start_time, #form_end_time').on('change', loadWorkload);
        $('#form_subject_id').on('change', loadSubjectPreferences);
        $('#subject_preference_hint').on('click', '#btnApplyPreferences', applySubjectPreferences);
        $('#available_slots_panel').on('click', '.ams-slot-btn', function () {
            applySlot(this);
        });
        $('#conflict_results_panel').on('click', '.ams-suggest-btn', function () {
            applySlot(this);
        });
        $('#btnCheckConflicts').on('click', function () { runConflictCheck(); });
        $('#calendarEventForm').on('submit', saveEvent);
        $('#btnEditFromDetails').on('click', function () {
            if (selectedEvent) openEditModal(selectedEvent);
        });
        $('#btnDeleteFromDetails').on('click', confirmDelete);
        $('#btnConfirmDelete').on('click', deleteEvent);
    });
})();
</script>
@endpush
