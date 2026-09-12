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
    $showChildSelector = $children->count() > 1;
@endphp

<div class="page-wrapper">
    <div class="content container-fluid ams-parent-calendar">

        <div class="page-header">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h3 class="page-title mb-1">Calendar &amp; Events</h3>
                    <p class="ams-par-subtitle mb-0">Stay updated with your child's academic activities, school events, examinations, and important dates.</p>
                </div>
                <div class="col-md-4 text-md-end mt-3 mt-md-0">
                    <button type="button" class="btn btn-outline-secondary btn-sm" id="btnRefreshCalendar">
                        <i class="fas fa-sync-alt"></i> Refresh
                    </button>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-9">
                <div class="card ams-par-card mb-3">
                    <div class="card-body py-3">
                        <div class="row g-2 align-items-end">
                            @if($showChildSelector)
                                <div class="col-md-3">
                                    <label class="form-label ams-par-label">Child</label>
                                    <select class="form-control form-control-sm" id="filter_child">
                                        <option value="">All Children</option>
                                        @foreach($children as $child)
                                            <option value="{{ $child->id }}" {{ (int) $selectedChildId === (int) $child->id ? 'selected' : '' }}>
                                                {{ $child->full_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            @else
                                <input type="hidden" id="filter_child" value="{{ $children->first()->id ?? '' }}">
                            @endif
                            <div class="{{ $showChildSelector ? 'col-md-3' : 'col-md-4' }}">
                                <label class="form-label ams-par-label">Search</label>
                                <input type="text" class="form-control form-control-sm" id="filter_search" placeholder="Search events...">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label ams-par-label">Event Type</label>
                                <select class="form-control form-control-sm" id="filter_event_type">
                                    <option value="">All</option>
                                    @foreach($eventTypes as $type)
                                        <option value="{{ $type }}">{{ ucfirst($type) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label ams-par-label">Subject</label>
                                <select class="form-control form-control-sm" id="filter_subject">
                                    <option value="">All Subjects</option>
                                    @foreach($subjects as $subject)
                                        <option value="{{ $subject->id }}">{{ $subject->subject_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-1">
                                <button type="button" class="btn btn-sm btn-outline-secondary w-100" id="btnClearFilters" title="Clear">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="ams-par-legend mb-3">
                    @foreach($typeColors as $type => $color)
                        <span class="ams-par-legend-item">
                            <span class="ams-par-dot" style="background:{{ $color }}"></span>
                            {{ ucfirst($type) }}
                        </span>
                    @endforeach
                </div>

                <div class="card ams-par-card">
                    <div class="card-header ams-par-card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Family Academic Calendar</h5>
                        <small class="text-muted" id="calLastUpdated">—</small>
                    </div>
                    <div class="card-body">
                        <div id="calEmptyHint" class="ams-par-empty d-none">No events scheduled for this period.</div>
                        <div id="calendar"></div>
                    </div>
                </div>
            </div>

            <div class="col-lg-3">
                <div class="card ams-par-card ams-upcoming-card">
                    <div class="card-header ams-par-card-header">
                        <h6 class="card-title mb-0">Upcoming Events</h6>
                    </div>
                    <div class="card-body p-0">
                        @forelse($upcomingEvents as $event)
                            <button type="button"
                                class="ams-upcoming-item upcoming-event-btn"
                                data-event-id="{{ $event->id }}"
                                data-title="{{ $event->title }}"
                                data-type="{{ $event->event_type }}"
                                data-start="{{ $event->start_time->toIso8601String() }}"
                                data-end="{{ $event->end_time->toIso8601String() }}"
                                data-all-day="{{ $event->is_all_day ? 1 : 0 }}"
                                data-subject="{{ $event->subject?->subject_name }}"
                                data-teacher="{{ $event->teacher?->full_name }}"
                                data-room="{{ $event->room?->full_name ?? $event->room?->room_name }}"
                                data-description="{{ $event->description }}"
                                data-recurring="{{ $event->is_recurring ? 1 : 0 }}"
                                data-pattern="{{ $event->recurrence_pattern }}"
                                data-until="{{ optional($event->recurrence_end_date)?->format('Y-m-d') }}">
                                <div class="ams-upcoming-date">
                                    @if($event->start_time->isToday())
                                        Today
                                    @elseif($event->start_time->isTomorrow())
                                        Tomorrow
                                    @else
                                        {{ $event->start_time->format('M j') }}
                                    @endif
                                </div>
                                <div class="ams-upcoming-title">{{ $event->title }}</div>
                                <div class="ams-upcoming-time">
                                    @if($event->is_all_day)
                                        All day
                                    @else
                                        {{ $event->start_time->format('g:i A') }}
                                    @endif
                                </div>
                            </button>
                        @empty
                            <div class="p-3 text-muted small">No upcoming events.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="eventDetailsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Event Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="eventDetailsBody"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.css" rel="stylesheet">
<style>
    .ams-parent-calendar {
        --par-accent: #ea580c;
        --par-accent-dark: #c2410c;
        --par-border: #e5e7eb;
    }
    .ams-par-subtitle { color: #6b7280; font-size: 0.9rem; }
    .ams-par-card {
        border: 1px solid var(--par-border);
        border-radius: 12px;
        box-shadow: 0 1px 3px rgba(0,0,0,.04);
    }
    .ams-par-card-header { background: #fff; border-bottom: 1px solid var(--par-border); }
    .ams-par-label { font-size: 0.75rem; font-weight: 600; color: #6b7280; margin-bottom: 0.25rem; }
    .ams-par-legend { display: flex; flex-wrap: wrap; gap: 0.75rem 1.1rem; }
    .ams-par-legend-item { display: inline-flex; align-items: center; gap: 0.35rem; font-size: 0.8rem; color: #4b5563; }
    .ams-par-dot { width: 10px; height: 10px; border-radius: 50%; display: inline-block; }
    .ams-par-empty {
        text-align: center; color: #9ca3af; font-size: 0.875rem;
        padding: 0.4rem 0 0.7rem; border-bottom: 1px dashed #e5e7eb; margin-bottom: 0.5rem;
    }
    .ams-upcoming-item {
        display: block; width: 100%; text-align: left; border: 0; border-bottom: 1px solid #f1f5f9;
        background: #fff; padding: 0.75rem 1rem; cursor: pointer;
    }
    .ams-upcoming-item:hover { background: #fff7ed; }
    .ams-upcoming-date { font-size: 0.72rem; font-weight: 700; color: var(--par-accent); text-transform: uppercase; }
    .ams-upcoming-title { font-size: 0.88rem; font-weight: 600; color: #111827; margin: 0.15rem 0; }
    .ams-upcoming-time { font-size: 0.78rem; color: #6b7280; }
    .ams-par-detail-title { font-size: 1.25rem; font-weight: 700; color: #111827; margin-bottom: 0.35rem; }
    .ams-par-detail-meta { color: #4b5563; margin-bottom: 1rem; }
    .ams-par-detail-row { margin-bottom: 0.55rem; font-size: 0.92rem; }
    .ams-par-detail-row strong { color: #374151; display: inline-block; min-width: 5.5rem; }
    .ams-type-badge {
        display: inline-block; padding: 0.15rem 0.55rem; border-radius: 999px;
        font-size: 0.75rem; color: #fff; font-weight: 600; margin-bottom: 0.65rem;
    }
    .fc .fc-button-primary {
        background-color: var(--par-accent) !important;
        border-color: var(--par-accent) !important;
        text-transform: lowercase;
        border-radius: 6px !important;
        box-shadow: none !important;
    }
    .fc .fc-button-primary:not(:disabled).fc-button-active,
    .fc .fc-button-primary:hover {
        background-color: var(--par-accent-dark) !important;
        border-color: var(--par-accent-dark) !important;
    }
    .fc .fc-toolbar-title { font-size: 1.15rem !important; font-weight: 700; color: #111827; }
    .fc-daygrid-day.fc-day-today { background: rgba(234, 88, 12, 0.08) !important; }
    .fc .fc-daygrid-day.fc-day-today .fc-daygrid-day-number {
        background: var(--par-accent); color: #fff; border-radius: 50%;
        width: 1.75rem; height: 1.75rem; display: inline-flex;
        align-items: center; justify-content: center;
    }
    .fc-event { cursor: pointer; border: none !important; border-radius: 7px !important; font-size: 0.75rem;
        box-shadow: 0 2px 6px rgba(15,23,42,.12); transition: transform .18s ease, box-shadow .18s ease; }
    .fc-event:hover { transform: translateY(-1px) scale(1.02); box-shadow: 0 6px 14px rgba(15,23,42,.18); }
    .fc-event.ams-event-click { animation: amsEventClick .45s cubic-bezier(.22,1,.36,1); }
    @keyframes amsEventClick {
        0% { transform: scale(1); }
        35% { transform: scale(1.12); box-shadow: 0 10px 22px rgba(234,88,12,.35); }
        100% { transform: scale(1); }
    }
    #eventDetailsModal .modal-content { border: 0; border-radius: 16px; overflow: hidden; box-shadow: 0 18px 40px rgba(15,23,42,.12); }
    #eventDetailsModal.modal.fade .modal-dialog {
        transform: translateY(18px) scale(.96); opacity: 0;
        transition: transform .28s cubic-bezier(.22,1,.36,1), opacity .28s ease;
    }
    #eventDetailsModal.modal.show .modal-dialog { transform: translateY(0) scale(1); opacity: 1; }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js"></script>
<script>
(function () {
    const TYPE_COLORS = @json($typeColors);
    const INDEX_URL = '{{ route("calendar.index") }}';
    const MULTI_CHILD = @json($showChildSelector);
    let calendar = null;
    let searchTimer = null;

    function markUpdated() {
        const el = document.getElementById('calLastUpdated');
        if (el) el.textContent = 'Updated ' + new Date().toLocaleTimeString();
    }

    function filterParams() {
        const childVal = $('#filter_child').val() || '';
        return {
            search: $('#filter_search').val() || '',
            event_type: $('#filter_event_type').val() || '',
            subject_id: $('#filter_subject').val() || '',
            // Empty = All Children; for single child the hidden input still sends their id
            child_id: MULTI_CHILD ? childVal : childVal,
        };
    }

    function formatDateOnly(date) {
        if (!date) return '—';
        return new Date(date).toLocaleDateString(undefined, {
            year: 'numeric', month: 'long', day: 'numeric'
        });
    }

    function formatTimeRange(start, end, allDay) {
        if (allDay) return 'All day';
        const opts = { hour: 'numeric', minute: '2-digit' };
        const s = start ? new Date(start).toLocaleTimeString(undefined, opts) : '';
        const e = end ? new Date(end).toLocaleTimeString(undefined, opts) : '';
        if (s && e) return s + ' – ' + e;
        return s || e || '—';
    }

    function renderDetails(data) {
        const color = TYPE_COLORS[data.event_type] || '#ea580c';
        const typeLabel = data.event_type ? data.event_type.charAt(0).toUpperCase() + data.event_type.slice(1) : 'Event';
        let html = '<span class="ams-type-badge" style="background:' + color + '">' + typeLabel + '</span>';
        if (data.is_recurring) html += ' <span class="badge bg-secondary">Recurring</span>';
        html += '<div class="ams-par-detail-title">' + (data.title || '') + '</div>';
        html += '<div class="ams-par-detail-meta">' + formatDateOnly(data.start) + '<br>' +
            '<strong>Start:</strong> ' + (data.allDay ? 'All day' : new Date(data.start).toLocaleTimeString(undefined, {hour:'numeric', minute:'2-digit'})) + '<br>' +
            (data.end ? ('<strong>End:</strong> ' + (data.allDay ? 'All day' : new Date(data.end).toLocaleTimeString(undefined, {hour:'numeric', minute:'2-digit'}))) : '') +
            '</div>';

        if (data.child_names) html += '<div class="ams-par-detail-row"><strong>Child:</strong> ' + data.child_names + '</div>';
        if (data.children && data.children.length === 1) {
            const c = data.children[0];
            if (c.grade) html += '<div class="ams-par-detail-row"><strong>Grade:</strong> ' + c.grade + '</div>';
            if (c.section) html += '<div class="ams-par-detail-row"><strong>Section:</strong> ' + c.section + '</div>';
        }
        if (data.subject) html += '<div class="ams-par-detail-row"><strong>Subject:</strong> ' + data.subject + '</div>';
        if (data.teacher) html += '<div class="ams-par-detail-row"><strong>Teacher:</strong> ' + data.teacher + '</div>';
        if (data.room) html += '<div class="ams-par-detail-row"><strong>Room:</strong> ' + data.room + '</div>';
        if (data.organizer) html += '<div class="ams-par-detail-row"><strong>Organizer:</strong> ' + data.organizer + '</div>';
        if (data.description) html += '<div class="ams-par-detail-row"><strong>Description:</strong> ' + data.description + '</div>';
        if (data.is_recurring) {
            html += '<div class="ams-par-detail-row"><strong>Repeats:</strong> ' + (data.recurrence_pattern || '') +
                (data.recurrence_end_date ? ' until ' + data.recurrence_end_date : '') + '</div>';
        }

        $('#eventDetailsBody').html(html);
        bootstrap.Modal.getOrCreateInstance(document.getElementById('eventDetailsModal')).show();
    }

    function showEventDetails(event) {
        const p = event.extendedProps || {};
        renderDetails({
            title: event.title,
            event_type: p.event_type,
            start: event.start,
            end: event.end,
            allDay: p.is_all_day || event.allDay,
            subject: p.subject,
            teacher: p.teacher,
            room: p.room,
            description: p.description,
            organizer: p.organizer,
            is_recurring: p.is_recurring,
            recurrence_pattern: p.recurrence_pattern,
            recurrence_end_date: p.recurrence_end_date,
            children: p.children || [],
            child_names: p.child_names || '',
        });
    }

    function initCalendar() {
        const el = document.getElementById('calendar');
        if (!el || typeof FullCalendar === 'undefined') return;

        calendar = new FullCalendar.Calendar(el, {
            initialView: 'dayGridMonth',
            firstDay: 0,
            height: 'auto',
            editable: false,
            selectable: false,
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
                    url: INDEX_URL,
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
                    }
                });
            },
            eventClick: function (arg) {
                arg.jsEvent.preventDefault();
                if (arg.el) {
                    arg.el.classList.remove('ams-event-click');
                    void arg.el.offsetWidth;
                    arg.el.classList.add('ams-event-click');
                    setTimeout(function () { arg.el.classList.remove('ams-event-click'); }, 480);
                }
                setTimeout(function () { showEventDetails(arg.event); }, 120);
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
            // soft reload upcoming list via page refresh is fine; keep calendar fast
        });

        $('#filter_child, #filter_event_type, #filter_subject').on('change', function () {
            if (calendar) calendar.refetchEvents();
            // Reload page filters for upcoming panel when child changes
            if (this.id === 'filter_child') {
                const childId = $('#filter_child').val();
                const url = new URL(window.location.href);
                if (childId) url.searchParams.set('child_id', childId);
                else url.searchParams.delete('child_id');
                window.location.href = url.toString();
            }
        });

        $('#filter_search').on('input', function () {
            clearTimeout(searchTimer);
            searchTimer = setTimeout(function () {
                if (calendar) calendar.refetchEvents();
            }, 350);
        });

        $('#btnClearFilters').on('click', function () {
            $('#filter_search').val('');
            $('#filter_event_type, #filter_subject').val('');
            if (MULTI_CHILD) $('#filter_child').val('');
            if (calendar) calendar.refetchEvents();
        });

        $('.upcoming-event-btn').on('click', function () {
            const btn = $(this);
            renderDetails({
                title: btn.data('title'),
                event_type: btn.data('type'),
                start: btn.data('start'),
                end: btn.data('end'),
                allDay: String(btn.data('all-day')) === '1',
                subject: btn.data('subject') || '',
                teacher: btn.data('teacher') || '',
                room: btn.data('room') || '',
                description: btn.data('description') || '',
                is_recurring: String(btn.data('recurring')) === '1',
                recurrence_pattern: btn.data('pattern') || '',
                recurrence_end_date: btn.data('until') || '',
                children: [],
                child_names: '',
            });
        });
    });
})();
</script>
@endpush
