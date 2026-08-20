@extends('layouts.master')
@section('content')

    <div class="page-wrapper">
        <div class="content container-fluid">
            <div class="page-header">
                <div class="row">
                    <div class="col-sm-12">
                        <div class="page-sub-header">
                            <h3 class="page-title">My Teaching Schedule</h3>
                            <ul class="breadcrumb">
                                <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                                <li class="breadcrumb-item active">My Schedule</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Schedule Grid - Full Page -->
            <div class="row">
                <div class="col-12">
                    <div class="card flex-fill comman-shadow">
                        <div class="card-header d-flex align-items-center">
                            <h5 class="card-title">Weekly Teaching Schedule</h5>
                        </div>
                        <div class="card-body">
                            <div class="schedule-container">
                                <div class="schedule-grid">
                                    <!-- Time column -->
                                    <div class="time-column">
                                        <div class="time-header">Time</div>
                                        @php
                                            $timeSlots = [];
                                            $startTime = \Carbon\Carbon::createFromTime(6, 0, 0);
                                            $endTime = \Carbon\Carbon::createFromTime(20, 0, 0);
                                            
                                            while ($startTime <= $endTime) {
                                                $timeSlots[] = $startTime->format('g:i A');
                                                $startTime->addMinutes(30);
                                            }
                                            
                                            $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
                                            $dayNames = ['MONDAY', 'TUESDAY', 'WEDNESDAY', 'THURSDAY', 'FRIDAY', 'SATURDAY', 'SUNDAY'];
                                        @endphp
                                        @foreach($timeSlots as $timeSlot)
                                            <div class="time-slot">{{ $timeSlot }}</div>
                                        @endforeach
                                    </div>
                                    
                                    <!-- Day columns -->
                                    @foreach($days as $index => $day)
                                        @php
                                            $daySchedules = $weeklySchedule[$day] ?? collect();
                                            $renderedSchedules = [];
                                        @endphp
                                        <div class="day-column">
                                            <div class="day-header">{{ $dayNames[$index] }}</div>
                                            @foreach($timeSlots as $slotIndex => $timeSlot)
                                                @php
                                                    $time = \Carbon\Carbon::createFromFormat('g:i A', $timeSlot);
                                                    $timeStr = $time->format('H:i:s');
                                                    
                                                    // Find if a schedule starts exactly at this time slot
                                                    $schedule = $daySchedules->first(function($s) use ($timeStr) {
                                                        $start = \Carbon\Carbon::parse($s->start_time);
                                                        $slotTime = \Carbon\Carbon::parse($timeStr);
                                                        return $start->format('H:i') === $slotTime->format('H:i');
                                                    });
                                                    
                                                    // Check if this slot is already occupied by a previous schedule
                                                    $isOccupied = false;
                                                    foreach ($renderedSchedules as $rendered) {
                                                        $renderedStart = \Carbon\Carbon::parse($rendered['start']);
                                                        $renderedEnd = \Carbon\Carbon::parse($rendered['end']);
                                                        $currentSlot = \Carbon\Carbon::parse($timeStr);
                                                        
                                                        if ($currentSlot->between($renderedStart, $renderedEnd, false)) {
                                                            $isOccupied = true;
                                                            break;
                                                        }
                                                    }
                                                @endphp
                                                
                                                @if($schedule && !$isOccupied)
                                                    @php
                                                        $duration = \Carbon\Carbon::parse($schedule->start_time)->diffInMinutes(\Carbon\Carbon::parse($schedule->end_time));
                                                        $rowSpan = max(1, ceil($duration / 30));
                                                        $renderedSchedules[] = [
                                                            'id' => $schedule->id,
                                                            'start' => $schedule->start_time,
                                                            'end' => $schedule->end_time
                                                        ];
                                                    @endphp
                                                    <div class="schedule-block" 
                                                         style="background-color: {{ $schedule->color }}; grid-row: span {{ $rowSpan }};"
                                                         data-bs-toggle="tooltip" 
                                                         data-bs-placement="top" 
                                                         title="{{ $schedule->subject->subject_name }} - {{ $schedule->section->name }} - {{ $schedule->room ? $schedule->room->room_name : 'TBD' }}">
                                                        <div class="schedule-content">
                                                            <div class="subject-code">{{ $schedule->subject->subject_id ?? 'N/A' }}</div>
                                                            <div class="subject-name">{{ $schedule->subject->subject_name }}</div>
                                                            <div class="class-type">({{ ucfirst($schedule->class_type ?? 'Lecture') }})</div>
                                                            <div class="section-name">{{ $schedule->section->name }}</div>
                                                            <div class="room-name">{{ $schedule->room ? $schedule->room->room_name : 'TBD' }}</div>
                                                        </div>
                                                    </div>
                                                @elseif(!$isOccupied)
                                                    <div class="empty-slot"></div>
                                                @endif
                                            @endforeach
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@push('styles')
<style>
.schedule-container {
    overflow-x: hidden;
    overflow-y: auto;
    min-height: calc(100vh - 350px);
    max-height: calc(100vh - 300px);
}

.schedule-grid {
    display: grid;
    grid-template-columns: 100px repeat(7, 1fr);
    grid-auto-rows: 60px;
    width: 100%;
    border: 1px solid #e0e0e0;
    background: white;
}

.time-column, .day-column {
    border-right: 1px solid #e0e0e0;
}

.time-column {
    background-color: #f8f9fa;
    position: sticky;
    left: 0;
    z-index: 10;
}

.time-header, .day-header {
    background-color: #3d5ee1;
    color: white;
    padding: 10px 5px;
    text-align: center;
    font-weight: 700;
    font-size: 0.95rem;
    border-bottom: 2px solid #e0e0e0;
    height: 70px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.time-slot {
    padding: 8px 5px;
    font-size: 0.85rem;
    color: #333;
    border-bottom: 1px solid #f0f0f0;
    display: flex;
    align-items: center;
    justify-content: center;
    height: 60px;
    font-weight: 600;
}

.empty-slot {
    border-bottom: 1px solid #f0f0f0;
    height: 60px;
}

.schedule-block {
    border: 2px solid rgba(255, 255, 255, 0.5);
    border-radius: 6px;
    margin: 2px;
    padding: 8px 4px;
    color: white;
    font-size: 0.9rem;
    cursor: pointer;
    transition: all 0.2s ease;
    position: relative;
    overflow: hidden;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.schedule-block:hover {
    transform: scale(1.03);
    box-shadow: 0 6px 16px rgba(0, 0, 0, 0.3);
    z-index: 5;
}

.schedule-content {
    display: flex;
    flex-direction: column;
    height: 100%;
    justify-content: center;
    padding: 6px;
}

.subject-code {
    font-weight: 700;
    font-size: 0.75rem;
    line-height: 1.2;
    margin-bottom: 3px;
}

.subject-name {
    font-weight: 700;
    font-size: 0.9rem;
    line-height: 1.3;
    margin-bottom: 3px;
}

.class-type {
    font-size: 0.75rem;
    opacity: 0.95;
    line-height: 1.2;
    margin-bottom: 2px;
}

.section-name {
    font-size: 0.8rem;
    opacity: 0.95;
    line-height: 1.2;
    margin-bottom: 2px;
}

.room-name {
    font-size: 0.75rem;
    opacity: 0.95;
    line-height: 1.2;
}

/* Hide horizontal scrollbar */
.schedule-container::-webkit-scrollbar {
    width: 12px;
    height: 0px;
}

.schedule-container::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 6px;
}

.schedule-container::-webkit-scrollbar-thumb {
    background: #3d5ee1;
    border-radius: 6px;
}

.schedule-container::-webkit-scrollbar-thumb:hover {
    background: #2d4ed1;
}

.schedule-container::-webkit-scrollbar:horizontal {
    display: none;
}
</style>
@endpush

@endsection

