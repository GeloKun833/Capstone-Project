<?php $__env->startSection('content'); ?>

<?php
    $typeColors = [
        'exam' => '#dc2626',
        'activity' => '#ea580c',
        'meeting' => '#0f766e',
        'deadline' => '#ca8a04',
        'holiday' => '#64748b',
        'other' => '#475569',
    ];
?>

<div class="page-wrapper">
    <div class="content container-fluid ams-student-calendar">

        <div class="page-header">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h3 class="page-title mb-1">Calendar &amp; Events</h3>
                    <p class="ams-stu-subtitle mb-0">View your classes, exams, activities, school events, and important dates.</p>
                </div>
                <div class="col-md-4 text-md-end mt-3 mt-md-0">
                    <button type="button" class="btn btn-outline-secondary btn-sm" id="btnRefreshCalendar">
                        <i class="fas fa-sync-alt"></i> Refresh
                    </button>
                </div>
            </div>
        </div>

        <div class="ams-stu-chips mb-3">
            <button type="button" class="ams-chip active" data-filter-group="">All Events</button>
            <button type="button" class="ams-chip" data-filter-group="exams">Exams</button>
            <button type="button" class="ams-chip" data-filter-group="activities">Activities</button>
            <button type="button" class="ams-chip" data-filter-group="school">School Events</button>
            <button type="button" class="ams-chip" data-filter-group="deadlines">Deadlines</button>
            <button type="button" class="ams-chip" data-filter-group="holidays">Holidays</button>
            <button type="button" class="ams-chip" data-filter-group="subjects">Subject Events</button>
        </div>

        <div class="card ams-stu-card mb-3">
            <div class="card-body py-3">
                <div class="row g-2 align-items-end">
                    <div class="col-md-5">
                        <label class="form-label ams-stu-label">Search</label>
                        <input type="text" class="form-control form-control-sm" id="filter_search" placeholder="Search events...">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label ams-stu-label">Event Type</label>
                        <select class="form-control form-control-sm" id="filter_event_type">
                            <option value="">All Types</option>
                            <?php $__currentLoopData = $eventTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($type); ?>"><?php echo e(ucfirst($type)); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label ams-stu-label">Subject</label>
                        <select class="form-control form-control-sm" id="filter_subject">
                            <option value="">All Subjects</option>
                            <?php $__currentLoopData = $subjects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $subject): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($subject->id); ?>"><?php echo e($subject->subject_name); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
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

        <div class="ams-stu-legend mb-3">
            <?php $__currentLoopData = $typeColors; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type => $color): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <span class="ams-stu-legend-item">
                    <span class="ams-stu-dot" style="background:<?php echo e($color); ?>"></span>
                    <?php echo e(ucfirst($type)); ?>

                </span>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>

        <div class="card ams-stu-card">
            <div class="card-header ams-stu-card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">My Academic Calendar</h5>
                <small class="text-muted" id="calLastUpdated">—</small>
            </div>
            <div class="card-body">
                <div id="calEmptyHint" class="ams-stu-empty d-none">No events scheduled for this period.</div>
                <div id="calendar"></div>
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
<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.css" rel="stylesheet">
<style>
    .ams-student-calendar {
        --stu-accent: #ea580c;
        --stu-accent-dark: #c2410c;
        --stu-border: #e5e7eb;
    }
    .ams-stu-subtitle { color: #6b7280; font-size: 0.9rem; }
    .ams-stu-card {
        border: 1px solid var(--stu-border);
        border-radius: 12px;
        box-shadow: 0 1px 3px rgba(0,0,0,.04);
    }
    .ams-stu-card-header { background: #fff; border-bottom: 1px solid var(--stu-border); }
    .ams-stu-label { font-size: 0.75rem; font-weight: 600; color: #6b7280; margin-bottom: 0.25rem; }
    .ams-stu-chips { display: flex; flex-wrap: wrap; gap: 0.45rem; }
    .ams-chip {
        border: 1px solid #fed7aa; background: #fff; color: #9a3412;
        border-radius: 999px; padding: 0.3rem 0.75rem; font-size: 0.8rem; cursor: pointer;
    }
    .ams-chip:hover { background: #fff7ed; }
    .ams-chip.active {
        background: var(--stu-accent); border-color: var(--stu-accent); color: #fff; font-weight: 600;
    }
    .ams-stu-legend { display: flex; flex-wrap: wrap; gap: 0.75rem 1.1rem; }
    .ams-stu-legend-item { display: inline-flex; align-items: center; gap: 0.35rem; font-size: 0.8rem; color: #4b5563; }
    .ams-stu-dot { width: 10px; height: 10px; border-radius: 50%; display: inline-block; }
    .ams-stu-empty {
        text-align: center; color: #9ca3af; font-size: 0.875rem;
        padding: 0.4rem 0 0.7rem; border-bottom: 1px dashed #e5e7eb; margin-bottom: 0.5rem;
    }
    .ams-stu-detail-title { font-size: 1.25rem; font-weight: 700; color: #111827; margin-bottom: 0.35rem; }
    .ams-stu-detail-meta { color: #4b5563; margin-bottom: 1rem; }
    .ams-stu-detail-row { margin-bottom: 0.55rem; font-size: 0.92rem; }
    .ams-stu-detail-row strong { color: #374151; display: inline-block; min-width: 5.5rem; }
    .ams-type-badge {
        display: inline-block; padding: 0.15rem 0.55rem; border-radius: 999px;
        font-size: 0.75rem; color: #fff; font-weight: 600; margin-bottom: 0.65rem;
    }
    .fc .fc-button-primary {
        background-color: var(--stu-accent) !important;
        border-color: var(--stu-accent) !important;
        text-transform: lowercase;
        border-radius: 6px !important;
        box-shadow: none !important;
    }
    .fc .fc-button-primary:not(:disabled).fc-button-active,
    .fc .fc-button-primary:hover {
        background-color: var(--stu-accent-dark) !important;
        border-color: var(--stu-accent-dark) !important;
    }
    .fc .fc-toolbar-title { font-size: 1.15rem !important; font-weight: 700; color: #111827; }
    .fc-daygrid-day.fc-day-today { background: rgba(234, 88, 12, 0.08) !important; }
    .fc .fc-daygrid-day.fc-day-today .fc-daygrid-day-number {
        background: var(--stu-accent); color: #fff; border-radius: 50%;
        width: 1.75rem; height: 1.75rem; display: inline-flex;
        align-items: center; justify-content: center;
    }
    .fc-event {
        cursor: pointer; border: none !important; border-radius: 7px !important; font-size: 0.75rem;
        box-shadow: 0 2px 6px rgba(15,23,42,.12); transition: transform .18s ease, box-shadow .18s ease;
    }
    .fc-event:hover { transform: translateY(-1px) scale(1.02); box-shadow: 0 6px 14px rgba(15,23,42,.18); }
    .fc-event.ams-event-click { animation: amsEventClick .45s cubic-bezier(.22,1,.36,1); }
    @keyframes amsEventClick {
        0% { transform: scale(1); }
        35% { transform: scale(1.12); box-shadow: 0 10px 22px rgba(234,88,12,.35); }
        100% { transform: scale(1); }
    }
    .fc-list-event-title a { color: inherit; }
    #eventDetailsModal .modal-content { border: 0; border-radius: 16px; overflow: hidden; box-shadow: 0 18px 40px rgba(15,23,42,.12); }
    #eventDetailsModal.modal.fade .modal-dialog {
        transform: translateY(18px) scale(.96); opacity: 0;
        transition: transform .28s cubic-bezier(.22,1,.36,1), opacity .28s ease;
    }
    #eventDetailsModal.modal.show .modal-dialog { transform: translateY(0) scale(1); opacity: 1; }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startPush('scripts'); ?>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js"></script>
<script>
(function () {
    const TYPE_COLORS = <?php echo json_encode($typeColors, 15, 512) ?>;
    const INDEX_URL = '<?php echo e(route("calendar.index")); ?>';
    let calendar = null;
    let activeFilterGroup = '';
    let searchTimer = null;

    function markUpdated() {
        const el = document.getElementById('calLastUpdated');
        if (el) el.textContent = 'Updated ' + new Date().toLocaleTimeString();
    }

    function filterParams() {
        return {
            search: $('#filter_search').val() || '',
            event_type: $('#filter_event_type').val() || '',
            subject_id: $('#filter_subject').val() || '',
            filter_group: activeFilterGroup || '',
        };
    }

    function formatDateTime(date) {
        if (!date) return '—';
        return new Date(date).toLocaleString(undefined, {
            year: 'numeric', month: 'long', day: 'numeric',
            hour: 'numeric', minute: '2-digit'
        });
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

    function showEventDetails(event, clickEl) {
        const p = event.extendedProps || {};
        const color = TYPE_COLORS[p.event_type] || '#ea580c';
        const typeLabel = p.event_type ? p.event_type.charAt(0).toUpperCase() + p.event_type.slice(1) : 'Event';

        if (clickEl) {
            clickEl.classList.remove('ams-event-click');
            void clickEl.offsetWidth;
            clickEl.classList.add('ams-event-click');
            setTimeout(function () { clickEl.classList.remove('ams-event-click'); }, 480);
        }

        let html = '<span class="ams-type-badge" style="background:' + color + '">' + typeLabel + '</span>';
        if (p.is_recurring) html += ' <span class="badge bg-secondary">Recurring</span>';
        html += '<div class="ams-stu-detail-title">' + (event.title || '') + '</div>';
        html += '<div class="ams-stu-detail-meta">' + formatDateOnly(event.start) + '<br>' + formatTimeRange(event.start, event.end, p.is_all_day || event.allDay) + '</div>';

        if (p.subject) html += '<div class="ams-stu-detail-row"><strong>Subject:</strong> ' + p.subject + '</div>';
        if (p.teacher) html += '<div class="ams-stu-detail-row"><strong>Teacher:</strong> ' + p.teacher + '</div>';
        if (p.room) html += '<div class="ams-stu-detail-row"><strong>Room:</strong> ' + p.room + '</div>';
        if (p.subject_class) html += '<div class="ams-stu-detail-row"><strong>Class:</strong> ' + p.subject_class + '</div>';
        if (p.organizer) html += '<div class="ams-stu-detail-row"><strong>Organizer:</strong> ' + p.organizer + '</div>';
        if (p.description) html += '<div class="ams-stu-detail-row"><strong>Description:</strong> ' + p.description + '</div>';
        if (p.is_recurring) {
            html += '<div class="ams-stu-detail-row"><strong>Repeats:</strong> ' + (p.recurrence_pattern || '') +
                (p.recurrence_end_date ? ' until ' + p.recurrence_end_date : '') + '</div>';
        }

        $('#eventDetailsBody').html(html);
        setTimeout(function () {
            bootstrap.Modal.getOrCreateInstance(document.getElementById('eventDetailsModal')).show();
        }, 120);
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
                showEventDetails(arg.event, arg.el);
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

        $('.ams-chip').on('click', function () {
            $('.ams-chip').removeClass('active');
            $(this).addClass('active');
            activeFilterGroup = $(this).data('filter-group') || '';
            if (calendar) calendar.refetchEvents();
        });

        $('#filter_event_type, #filter_subject').on('change', function () {
            if (calendar) calendar.refetchEvents();
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
            activeFilterGroup = '';
            $('.ams-chip').removeClass('active');
            $('.ams-chip[data-filter-group=""]').addClass('active');
            if (calendar) calendar.refetchEvents();
        });
    });
})();
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Laravel\Capstone-Project\lms\resources\views\calendar\student.blade.php ENDPATH**/ ?>