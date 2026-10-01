@extends('layouts.master')
@section('content')

@php
    $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
    $dayNames = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
    $allSchedules = collect($weeklySchedule ?? [])->flatten();
    $classCount = $allSchedules->count();
    $today = now();
    $todayKey = strtolower($today->format('l'));

    try {
        $focus = request()->filled('week') ? \Carbon\Carbon::parse(request('week')) : $today->copy();
    } catch (\Exception $e) {
        $focus = $today->copy();
    }
    $thisWeekStart = $today->copy()->startOfWeek(\Carbon\Carbon::MONDAY);
    $weekStart = $focus->copy()->startOfWeek(\Carbon\Carbon::MONDAY);
    $weekEnd = $weekStart->copy()->addDays(5);
    $isCurrentWeek = $weekStart->isSameDay($thisWeekStart);
    $prevWeek = $weekStart->copy()->subWeek()->toDateString();
    $nextWeek = $weekStart->copy()->addWeek()->toDateString();

    $clockMinutes = function ($value): int {
        if ($value instanceof \DateTimeInterface) {
            return ((int) $value->format('G')) * 60 + (int) $value->format('i');
        }
        if (preg_match('/(\d{1,2}):(\d{2})/', (string) $value, $match)) {
            return ((int) $match[1]) * 60 + (int) $match[2];
        }
        $parsed = \Carbon\Carbon::parse($value);
        return ((int) $parsed->format('G')) * 60 + (int) $parsed->format('i');
    };

    $formatMinutes = function (int $minutes): string {
        $minutes = (($minutes % 1440) + 1440) % 1440;
        return sprintf('%02d:%02d', intdiv($minutes, 60), $minutes % 60);
    };

    $gridStart = 7 * 60;
    $gridEnd = 17 * 60;
    $spanMinutes = max(60, $gridEnd - $gridStart);
    $timeSlots = range($gridStart, $gridEnd, 30);
    $hourCount = intdiv($spanMinutes, 60);

    $todayClasses = collect($weeklySchedule[$todayKey] ?? [])->sortBy(fn ($s) => $clockMinutes($s->start_time));
    $legend = $allSchedules
        ->groupBy(fn ($s) => $s->subject->id ?? $s->subject_id)
        ->map(function ($items) {
            $first = $items->first();
            return [
                'name' => $first->subject->subject_name ?? 'Subject',
                'color' => $first->color ?: '#7c8cff',
                'count' => $items->count(),
            ];
        })
        ->values();
@endphp

<div class="page-wrapper">
    <div class="content container-fluid dir-page plan-page">
        <div class="page-header">
            <div class="row align-items-start">
                <div class="col">
                    <h3 class="page-title mb-1">My Teaching Schedule</h3>
                    <p class="dir-subtitle">Weekly planner for your assigned classes.</p>
                </div>
                <div class="col-auto text-end">
                    <ul class="breadcrumb justify-content-end mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">My Schedule</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="plan-shell">
            <aside class="plan-side">
                <div class="plan-side-card">
                    <div class="plan-month">{{ $weekStart->format('F Y') }}</div>
                    <div class="plan-weekdays" style="grid-template-columns: repeat({{ count($days) }}, 1fr);">
                        @foreach($days as $index => $day)
                            @php $date = $weekStart->copy()->addDays($index); @endphp
                            <div class="plan-weekday {{ $date->isSameDay($today) ? 'is-today' : '' }}">
                                <span>{{ $dayNames[$index][0] }}</span>
                                <strong>{{ $date->format('j') }}</strong>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="plan-side-card">
                    <h6>Today</h6>
                    @forelse($todayClasses as $item)
                        <div class="plan-today-row">
                            <i style="background: {{ $item->color ?: '#7c8cff' }}"></i>
                            <div>
                                <strong>{{ $item->subject->subject_name }}</strong>
                                <span>{{ $formatMinutes($clockMinutes($item->start_time)) }} – {{ $formatMinutes($clockMinutes($item->end_time)) }}</span>
                                <span>{{ $item->section->name }} · {{ $item->room->room_name ?? 'TBD' }}</span>
                            </div>
                        </div>
                    @empty
                        <p class="plan-empty-note">No classes today.</p>
                    @endforelse
                </div>

                <div class="plan-side-card">
                    <h6>Subjects</h6>
                    @forelse($legend as $item)
                        <div class="plan-cat">
                            <i style="background: {{ $item['color'] }}"></i>
                            <span>{{ $item['name'] }}</span>
                            <em>{{ $item['count'] }}</em>
                        </div>
                    @empty
                        <p class="plan-empty-note">No subjects scheduled.</p>
                    @endforelse
                </div>
            </aside>

            <section class="plan-board">
                <div class="plan-board-bar">
                    <div class="d-flex align-items-center gap-2">
                        <a href="{{ route('teacher.my-schedule', ['week' => $prevWeek]) }}" class="plan-week-btn" title="Previous week"><i class="fas fa-chevron-left"></i></a>
                        <a href="{{ route('teacher.my-schedule', ['week' => $nextWeek]) }}" class="plan-week-btn" title="Next week"><i class="fas fa-chevron-right"></i></a>
                        <div>
                            <div class="plan-board-title">{{ $weekStart->format('M j') }} – {{ $weekEnd->format('M j, Y') }}</div>
                            <div class="plan-board-sub">{{ $classCount }} class{{ $classCount === 1 ? '' : 'es' }} · Monday to Saturday</div>
                        </div>
                    </div>
                    @if(!$isCurrentWeek)
                        <a href="{{ route('teacher.my-schedule') }}" class="plan-week-now">This week</a>
                    @endif
                </div>

                @if($classCount === 0)
                    <div class="dir-empty">
                        <i class="far fa-calendar-alt d-block"></i>
                        <h5 class="mt-2 mb-1">No classes scheduled</h5>
                        <p class="mb-0">Your weekly planner will appear here after Admin assigns a class schedule to you.</p>
                    </div>
                @else
                    <div class="plan-cal">
                        <div class="plan-heads" style="--days: {{ count($days) }};">
                            <div class="plan-head plan-head-time"></div>
                            @foreach($days as $index => $day)
                                @php $date = $weekStart->copy()->addDays($index); @endphp
                                <div class="plan-head {{ $date->isSameDay($today) ? 'is-today' : '' }}">
                                    <span>{{ strtoupper($dayNames[$index]) }}</span>
                                    <strong>{{ $date->format('j') }}</strong>
                                </div>
                            @endforeach
                        </div>
                        <div class="plan-body" style="--days: {{ count($days) }}; --hours: {{ $hourCount }};">
                            <div class="plan-times">
                                @foreach($timeSlots as $slot)
                                    <div class="plan-time" style="top: {{ (($slot - $gridStart) / $spanMinutes) * 100 }}%;">
                                        <span>{{ $formatMinutes($slot) }}</span>
                                    </div>
                                @endforeach
                            </div>
                            @foreach($days as $day)
                                @php $date = $weekStart->copy()->addDays((int) array_search($day, $days, true)); @endphp
                                <div class="plan-col {{ $date->isSameDay($today) ? 'is-today' : '' }}">
                                    @foreach(($weeklySchedule[$day] ?? collect()) as $schedule)
                                        @php
                                            $startM = $clockMinutes($schedule->start_time);
                                            $endM = $clockMinutes($schedule->end_time);
                                            if ($endM <= $startM) {
                                                $endM = $startM + 30;
                                            }
                                            $durationMinutes = $endM - $startM;
                                            $topPct = (($startM - $gridStart) / $spanMinutes) * 100;
                                            $heightPct = ($durationMinutes / $spanMinutes) * 100;
                                            $color = $schedule->color ?: '#7c8cff';
                                            $startLabel = $formatMinutes($startM);
                                            $endLabel = $formatMinutes($endM);
                                            $subjectName = $schedule->subject->subject_name ?? 'Subject';
                                            $sectionName = $schedule->section->name ?? 'Section';
                                            $gradeName = $schedule->section?->grade_level ?? '';
                                            $roomName = $schedule->room->room_name ?? 'Room TBD';
                                        @endphp
                                        <article class="plan-event"
                                                 role="button" tabindex="0"
                                                 aria-label="{{ $subjectName }}, {{ $day }}, {{ $startLabel }} to {{ $endLabel }}. View class details."
                                                 data-subject="{{ $subjectName }}"
                                                 data-code="{{ $schedule->subject->subject_id ?? '' }}"
                                                 data-day="{{ ucfirst($day) }}"
                                                 data-start="{{ $startLabel }}"
                                                 data-end="{{ $endLabel }}"
                                                 data-section="{{ $sectionName }}"
                                                 data-grade="{{ $gradeName }}"
                                                 data-room="{{ $roomName }}"
                                                 data-color="{{ $color }}"
                                                 style="top: {{ $topPct }}%; --block-h: calc({{ $heightPct }}% - 4px); --event: {{ $color }};">
                                            <span class="plan-event-name">{{ $subjectName }}</span>
                                            @if($durationMinutes >= 60)
                                                <span class="plan-event-time">{{ $startLabel }} – {{ $endLabel }}</span>
                                            @endif
                                        </article>
                                    @endforeach
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </section>
        </div>
    </div>
</div>

<div class="modal fade" id="scheduleEventModal" tabindex="-1" aria-labelledby="scheduleEventModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" id="scheduleEventModalHeader">
                <h5 class="modal-title" id="scheduleEventModalLabel">Class details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <dl class="row mb-0">
                    <dt class="col-4">Subject</dt><dd class="col-8" id="scheduleDetailSubject">—</dd>
                    <dt class="col-4">Subject code</dt><dd class="col-8" id="scheduleDetailCode">—</dd>
                    <dt class="col-4">Day</dt><dd class="col-8" id="scheduleDetailDay">—</dd>
                    <dt class="col-4">Time</dt><dd class="col-8" id="scheduleDetailTime">—</dd>
                    <dt class="col-4">Section</dt><dd class="col-8" id="scheduleDetailSection">—</dd>
                    <dt class="col-4">Grade</dt><dd class="col-8" id="scheduleDetailGrade">—</dd>
                    <dt class="col-4">Room</dt><dd class="col-8 mb-0" id="scheduleDetailRoom">—</dd>
                </dl>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/directory-modern.css') }}?v=20260914h">
<style>
.plan-page .page-header { margin-bottom: 0.75rem; }
.plan-shell {
    display: grid;
    grid-template-columns: 220px minmax(0, 1fr);
    gap: 0.9rem;
    height: calc(100vh - 175px);
    min-height: 640px;
    align-items: stretch;
}
.plan-side {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
    min-height: 0;
    overflow: auto;
}
.plan-side-card,
.plan-board {
    background: #fff;
    border: 1px solid #e8eef7;
    border-radius: 20px;
    box-shadow: 0 14px 32px rgba(79, 114, 205, 0.05);
}
.plan-side-card { padding: 0.95rem 1rem; }
.plan-month {
    font-size: 0.98rem;
    font-weight: 750;
    color: #1e293b;
    margin-bottom: 0.7rem;
}
.plan-weekdays {
    display: grid;
    gap: 0.1rem;
    text-align: center;
}
.plan-weekday span,
.plan-head span {
    display: block;
    font-size: 0.62rem;
    font-weight: 700;
    letter-spacing: 0.06em;
    text-transform: uppercase;
    color: #94a3b8;
}
.plan-weekday strong {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 26px;
    height: 26px;
    margin-top: 0.15rem;
    border-radius: 50%;
    font-size: 0.78rem;
    color: #334155;
}
.plan-weekday.is-today strong {
    background: #7c8cff;
    color: #fff;
}
.plan-side-card h6 {
    font-size: 0.72rem;
    font-weight: 750;
    letter-spacing: 0.06em;
    text-transform: uppercase;
    color: #94a3b8;
    margin: 0 0 0.65rem;
}
.plan-today-row,
.plan-cat {
    display: flex;
    align-items: flex-start;
    gap: 0.5rem;
    margin-bottom: 0.65rem;
}
.plan-today-row:last-child,
.plan-cat:last-child { margin-bottom: 0; }
.plan-today-row i,
.plan-cat i {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    margin-top: 0.35rem;
    flex-shrink: 0;
}
.plan-today-row strong,
.plan-cat span {
    display: block;
    font-size: 0.84rem;
    font-weight: 700;
    color: #1e293b;
    line-height: 1.25;
}
.plan-today-row span {
    display: block;
    font-size: 0.7rem;
    color: #94a3b8;
}
.plan-cat { align-items: center; justify-content: space-between; }
.plan-cat i { margin-top: 0; }
.plan-cat em {
    font-style: normal;
    font-size: 0.72rem;
    color: #94a3b8;
}
.plan-empty-note { margin: 0; font-size: 0.8rem; color: #94a3b8; }
.plan-board {
    min-width: 0;
    display: flex;
    flex-direction: column;
    overflow: auto;
}
.plan-board-bar {
    flex: 0 0 auto;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.75rem;
    padding: 0.85rem 1.15rem 0.35rem;
}
.plan-week-btn {
    width: 32px;
    height: 32px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 10px;
    border: 1px solid #e8eef7;
    color: #64748b;
    background: #fff;
    text-decoration: none;
}
.plan-week-btn:hover {
    color: #4f46e5;
    border-color: #c7d2fe;
    background: #f8faff;
}
.plan-week-now {
    font-size: 0.78rem;
    font-weight: 700;
    color: #4f46e5;
    text-decoration: none;
    padding: 0.35rem 0.7rem;
    border-radius: 999px;
    background: #eef2ff;
}
.plan-board-title { font-size: 1.05rem; font-weight: 750; color: #1e293b; }
.plan-board-sub { font-size: 0.75rem; color: #94a3b8; margin-top: 0.1rem; }
.plan-cal {
    flex: 1;
    min-height: 0;
    display: flex;
    flex-direction: column;
    padding: 0 0.35rem 0.65rem;
}
.plan-heads,
.plan-body {
    display: grid;
    grid-template-columns: 52px repeat(var(--days, 5), minmax(0, 1fr));
}
.plan-heads { flex: 0 0 auto; }
.plan-head {
    text-align: center;
    padding: 0.15rem 0 0.45rem;
}
.plan-head strong {
    display: block;
    margin-top: 0.08rem;
    font-size: 1.45rem;
    font-weight: 650;
    color: #c7d2fe;
    line-height: 1;
}
.plan-head.is-today span { color: #6366f1; }
.plan-head.is-today strong { color: #4f46e5; }
.plan-body {
    flex: 1;
    min-height: 0;
    position: relative;
}
.plan-times,
.plan-col { height: 100%; }
.plan-times { position: relative; }
.plan-time {
    height: 0;
    left: 0;
    position: absolute;
    right: 0;
}
.plan-time span {
    position: absolute;
    right: 8px;
    top: 0;
    transform: translateY(-50%);
    font-size: 0.7rem;
    font-weight: 600;
    color: #94a3b8;
}
.plan-col {
    position: relative;
    overflow: visible;
    border-left: 1px solid #edf1f7;
    background-image:
        repeating-linear-gradient(
            to bottom,
            transparent 0,
            transparent calc(100% / var(--hours, 9) / 2 - 1px),
            #f1f4fa calc(100% / var(--hours, 9) / 2 - 1px),
            #f1f4fa calc(100% / var(--hours, 9) / 2),
            transparent calc(100% / var(--hours, 9) / 2),
            transparent calc(100% / var(--hours, 9) - 1px),
            #e6ecf5 calc(100% / var(--hours, 9) - 1px),
            #e6ecf5 calc(100% / var(--hours, 9))
        );
}
.plan-col.is-today { background-color: rgba(124, 140, 255, 0.035); }
.plan-event {
    position: absolute;
    left: 6px;
    right: 6px;
    z-index: 2;
    height: var(--block-h);
    min-height: 0;
    box-sizing: border-box;
    padding: 0 0.45rem 0 0.6rem;
    border-radius: 8px;
    overflow: hidden;
    background: color-mix(in srgb, var(--event) 18%, #fff);
    box-shadow: inset 4px 0 0 var(--event);
    color: var(--event);
    cursor: pointer;
    display: flex;
    flex-direction: column;
    justify-content: center;
}
.plan-event:hover { z-index: 8; }
.plan-event:focus-visible {
    outline: 2px solid var(--event);
    outline-offset: 1px;
}
.plan-event-code {
    font-size: 0.68rem;
    font-weight: 750;
    letter-spacing: 0.03em;
    opacity: 0.8;
    margin-bottom: 0.1rem;
}
.plan-event-name {
    display: block;
    min-width: 0;
    font-size: 0.72rem;
    font-weight: 750;
    line-height: 1.15;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.plan-event-time {
    display: block;
    font-size: 0.64rem;
    font-weight: 650;
    line-height: 1.1;
    white-space: nowrap;
    overflow: hidden;
}
@media (max-width: 1100px) {
    .plan-shell {
        grid-template-columns: 1fr;
        height: auto;
        min-height: 0;
    }
    .plan-board { min-height: 70vh; }
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const modalElement = document.getElementById('scheduleEventModal');
    if (!modalElement || !window.bootstrap) return;

    function showScheduleDetails(eventElement) {
        document.getElementById('scheduleEventModalLabel').textContent = eventElement.dataset.subject || 'Class details';
        document.getElementById('scheduleDetailSubject').textContent = eventElement.dataset.subject || '—';
        document.getElementById('scheduleDetailCode').textContent = eventElement.dataset.code || '—';
        document.getElementById('scheduleDetailDay').textContent = eventElement.dataset.day || '—';
        document.getElementById('scheduleDetailTime').textContent =
            (eventElement.dataset.start || '—') + ' – ' + (eventElement.dataset.end || '—');
        document.getElementById('scheduleDetailSection').textContent = eventElement.dataset.section || '—';
        document.getElementById('scheduleDetailGrade').textContent = eventElement.dataset.grade || '—';
        document.getElementById('scheduleDetailRoom').textContent = eventElement.dataset.room || '—';
        document.getElementById('scheduleEventModalHeader').style.borderTop = '4px solid ' + eventElement.dataset.color;
        bootstrap.Modal.getOrCreateInstance(modalElement).show();
    }

    document.querySelectorAll('.plan-event').forEach(function(eventElement) {
        eventElement.addEventListener('click', function() {
            showScheduleDetails(eventElement);
        });
        eventElement.addEventListener('keydown', function(event) {
            if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                showScheduleDetails(eventElement);
            }
        });
    });
});
</script>
@endpush
