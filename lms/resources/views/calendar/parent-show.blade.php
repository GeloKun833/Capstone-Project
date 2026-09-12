@extends('layouts.master')
@section('content')
<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">Event Details</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('calendar.index') }}">Calendar &amp; Events</a></li>
                        <li class="breadcrumb-item active">Details</li>
                    </ul>
                </div>
                <div class="col-auto">
                    <a href="{{ route('calendar.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Calendar
                    </a>
                </div>
            </div>
        </div>

        <div class="card" style="border-radius:12px; border:1px solid #e5e7eb;">
            <div class="card-body">
                <span class="badge mb-2" style="background:{{ $calendarEvent->event_color }}; color:#fff;">
                    {{ ucfirst($calendarEvent->event_type) }}
                </span>
                @if($calendarEvent->is_recurring)
                    <span class="badge bg-secondary mb-2">Recurring</span>
                @endif

                <h3 class="mb-2">{{ $calendarEvent->title }}</h3>
                <p class="text-muted mb-4">
                    {{ $calendarEvent->start_time->format('F j, Y') }}<br>
                    <strong>Start:</strong>
                    @if($calendarEvent->is_all_day)
                        All day
                    @else
                        {{ $calendarEvent->start_time->format('g:i A') }}
                    @endif
                    <br>
                    <strong>End:</strong>
                    @if($calendarEvent->is_all_day)
                        All day
                    @else
                        {{ $calendarEvent->end_time->format('g:i A') }}
                    @endif
                </p>

                @if($relevantChildren->isNotEmpty())
                    <p><strong>Child:</strong> {{ $relevantChildren->pluck('full_name')->implode(', ') }}</p>
                    @if($relevantChildren->count() === 1)
                        @php $child = $relevantChildren->first(); @endphp
                        @if($child->year_level || $child->class)
                            <p><strong>Grade:</strong> {{ $child->year_level ?: $child->class }}</p>
                        @endif
                        @if($child->section)
                            <p><strong>Section:</strong> {{ $child->section }}</p>
                        @endif
                    @endif
                @endif
                @if($calendarEvent->subject)
                    <p><strong>Subject:</strong> {{ $calendarEvent->subject->subject_name }}</p>
                @endif
                @if($calendarEvent->teacher)
                    <p><strong>Teacher:</strong> {{ $calendarEvent->teacher->full_name }}</p>
                @endif
                @if($calendarEvent->room)
                    <p><strong>Room:</strong> {{ $calendarEvent->room->full_name ?? $calendarEvent->room->room_name }}</p>
                @endif
                @if($calendarEvent->createdBy)
                    <p><strong>Organizer:</strong> {{ $calendarEvent->createdBy->name }}</p>
                @endif
                @if($calendarEvent->description)
                    <p><strong>Description:</strong> {{ $calendarEvent->description }}</p>
                @endif
                @if($calendarEvent->is_recurring)
                    <p class="mb-0"><strong>Repeats:</strong>
                        {{ ucfirst($calendarEvent->recurrence_pattern ?? '') }}
                        @if($calendarEvent->recurrence_end_date)
                            until {{ $calendarEvent->recurrence_end_date->format('M j, Y') }}
                        @endif
                    </p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
