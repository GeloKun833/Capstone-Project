@php
    $studentId = $studentId ?? null;
    if (!$studentId && Auth::user()->role_name === 'Student') {
        $student = Auth::user()->student;
        if ($student) {
            $studentId = $student->id;
        }
    }
    
    if ($studentId) {
        $nextDaysSchedule = App\Models\ClassSchedule::getNextDaysSchedule($studentId, 5);
        $todaySchedule = App\Models\ClassSchedule::getTodaySchedule($studentId);
    } else {
        $nextDaysSchedule = collect();
        $todaySchedule = collect();
    }
@endphp

<div class="card flex-fill comman-shadow">
    <div class="card-header d-flex align-items-center">
        <h5 class="card-title">My Weekly Schedule</h5>
        <ul class="chart-list-out student-ellips">
            <li class="star-menus">
                <a href="{{ route('schedule.index') }}" class="btn btn-sm btn-primary">
                    <i class="fas fa-external-link-alt"></i> View Full Schedule
                </a>
            </li>
        </ul>
    </div>
    <div class="card-body">
        @if(count($nextDaysSchedule) > 0)
            <div class="schedule-widget">
                @foreach(array_slice($nextDaysSchedule, 0, 3) as $dayData)
                    <div class="day-schedule-item mb-3">
                        <div class="day-header d-flex justify-content-between align-items-center">
                            <h6 class="mb-0">
                                <i class="fas fa-calendar-day text-primary"></i>
                                {{ $dayData['day_name'] }}
                            </h6>
                            <small class="text-muted">{{ \Carbon\Carbon::parse($dayData['date'])->format('M j') }}</small>
                        </div>
                        
                        @if($dayData['schedules']->count() > 0)
                            <div class="schedule-items">
                                @foreach($dayData['schedules']->take(3) as $schedule)
                                    <div class="schedule-item" 
                                         data-bs-toggle="tooltip" 
                                         data-bs-placement="top" 
                                         title="{{ $schedule->subject->subject_name }} - {{ $schedule->teacher->full_name }} - {{ $schedule->room ? $schedule->room->room_name : 'TBD' }}">
                                        <div class="schedule-time">
                                            <small class="text-muted">{{ $schedule->time_range }}</small>
                                        </div>
                                        <div class="schedule-subject">
                                            <span class="subject-badge" style="background-color: {{ $schedule->subject_color }}; color: white;">
                                                {{ $schedule->subject->subject_name }}
                                            </span>
                                        </div>
                                    </div>
                                @endforeach
                                
                                @if($dayData['schedules']->count() > 3)
                                    <div class="schedule-more">
                                        <small class="text-muted">
                                            +{{ $dayData['schedules']->count() - 3 }} more classes
                                        </small>
                                    </div>
                                @endif
                            </div>
                        @else
                            <div class="no-classes">
                                <small class="text-muted">No classes scheduled</small>
                            </div>
                        @endif
                    </div>
                @endforeach
                
                @if(count($nextDaysSchedule) > 3)
                    <div class="text-center mt-3">
                        <a href="{{ route('schedule.index') }}" class="btn btn-sm btn-outline-primary">
                            View Next {{ count($nextDaysSchedule) - 3 }} Days
                        </a>
                    </div>
                @endif
            </div>
        @else
            <div class="text-center py-4">
                <i class="fas fa-calendar-times fa-2x text-muted mb-3"></i>
                <h6>No Schedule Available</h6>
                <p class="text-muted small">Your class schedule will appear here once it's set up.</p>
            </div>
        @endif
    </div>
</div>

<style>
.schedule-widget {
    max-height: 400px;
    overflow-y: auto;
}

.day-schedule-item {
    border-bottom: 1px solid #f0f0f0;
    padding-bottom: 15px;
}

.day-schedule-item:last-child {
    border-bottom: none;
}

.day-header {
    margin-bottom: 10px;
}

.schedule-items {
    margin-left: 20px;
}

.schedule-item {
    display: flex;
    align-items: center;
    margin-bottom: 8px;
    padding: 5px 0;
    border-radius: 4px;
    transition: background-color 0.2s;
}

.schedule-item:hover {
    background-color: #f8f9fa;
}

.schedule-time {
    min-width: 80px;
    margin-right: 10px;
}

.schedule-subject {
    flex: 1;
}

.subject-badge {
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 500;
}

.schedule-more {
    margin-top: 5px;
    margin-left: 90px;
}

.no-classes {
    margin-left: 20px;
    font-style: italic;
}

.schedule-widget::-webkit-scrollbar {
    width: 4px;
}

.schedule-widget::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 2px;
}

.schedule-widget::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 2px;
}

.schedule-widget::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script> 