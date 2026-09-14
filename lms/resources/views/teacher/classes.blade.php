@extends('layouts.master')
@section('content')

<div class="page-wrapper">
    <div class="content container-fluid dir-page">
        <div class="page-header">
            <div class="row align-items-start">
                <div class="col">
                    <h3 class="page-title mb-1">My Classes &amp; Subjects</h3>
                    <p class="dir-subtitle">Teaching load assigned from Classes &amp; Subjects and Class Schedules.</p>
                </div>
                <div class="col-auto text-end">
                    <ul class="breadcrumb justify-content-end mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">My Classes &amp; Subjects</li>
                    </ul>
                </div>
            </div>
        </div>

        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <div class="row g-3 mb-3">
            <div class="col-xl-3 col-sm-6">
                <div class="dir-card dir-stat">
                    <span class="dir-stat-label">Teaching classes</span>
                    <div class="dir-stat-value">{{ $stats['classes'] }}</div>
                </div>
            </div>
            <div class="col-xl-3 col-sm-6">
                <div class="dir-card dir-stat">
                    <span class="dir-stat-label">Assigned subjects</span>
                    <div class="dir-stat-value">{{ $stats['subjects'] }}</div>
                </div>
            </div>
            <div class="col-xl-3 col-sm-6">
                <div class="dir-card dir-stat">
                    <span class="dir-stat-label">Assigned sections</span>
                    <div class="dir-stat-value">{{ $stats['sections'] }}</div>
                </div>
            </div>
            <div class="col-xl-3 col-sm-6">
                <div class="dir-card dir-stat">
                    <span class="dir-stat-label">With schedule</span>
                    <div class="dir-stat-value">{{ $stats['scheduled'] }}</div>
                </div>
            </div>
        </div>

        <div class="dir-card">
            <div class="dir-toolbar">
                <div>
                    <h5 class="dir-toolbar-title">Assigned teaching load</h5>
                    <span class="dir-count mt-1">{{ $assignments->count() }} assignment{{ $assignments->count() === 1 ? '' : 's' }}</span>
                </div>
            </div>
            @if($assignments->isNotEmpty())
                <div class="table-responsive">
                    <table class="table dir-table mb-0">
                        <thead>
                            <tr>
                                <th>Subject</th>
                                <th>Section / Class</th>
                                <th>Grade</th>
                                <th>Students</th>
                                <th>Schedule</th>
                                <th>Source</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($assignments as $row)
                                @php
                                    $subject = $row['subject'];
                                    $section = $row['section'];
                                    $sourceLabel = match ($row['source']) {
                                        'schedule' => 'Class Schedule',
                                        'section_subject' => 'Section Subject',
                                        'assignment' => 'Teacher Assignment',
                                        default => 'Subject Only',
                                    };
                                @endphp
                                <tr>
                                    <td>
                                        <span class="dir-person-name">{{ $subject->subject_name }}</span>
                                        <span class="dir-person-meta">{{ $subject->subject_id ?? ('ID ' . $subject->id) }}</span>
                                    </td>
                                    <td>
                                        @if($section)
                                            <div>{{ $section->name }}</div>
                                            @if($row['is_adviser'])
                                                <span class="dir-badge dir-badge--inactive">Adviser</span>
                                            @endif
                                        @else
                                            <span class="dir-muted">No section linked yet</span>
                                        @endif
                                    </td>
                                    <td><span class="dir-chip dir-chip--soft">{{ $section->grade_level ?? $subject->class ?? 'N/A' }}</span></td>
                                    <td>{{ $row['students_count'] }}</td>
                                    <td>
                                        <div>{{ $row['schedule_summary'] }}</div>
                                        @if($row['schedules']->isNotEmpty())
                                            <span class="dir-person-meta">
                                                @foreach($row['schedules']->take(2) as $sch)
                                                    {{ ucfirst($sch->day_of_week) }}
                                                    {{ \Carbon\Carbon::parse($sch->start_time)->format('g:i A') }}–
                                                    {{ \Carbon\Carbon::parse($sch->end_time)->format('g:i A') }}@if(!$loop->last); @endif
                                                @endforeach
                                                @if($row['schedules']->count() > 2)
                                                    +{{ $row['schedules']->count() - 2 }} more
                                                @endif
                                            </span>
                                        @endif
                                    </td>
                                    <td><span class="dir-chip">{{ $sourceLabel }}</span></td>
                                    <td class="text-end">
                                        <div class="d-inline-flex gap-1">
                                            @if(Route::has('attendance.index'))
                                                <a href="{{ route('attendance.index') }}" class="dir-icon-btn" title="Attendance"><i class="fas fa-calendar-check"></i></a>
                                            @endif
                                            @if(Route::has('assignments.create'))
                                                <a href="{{ route('assignments.create') }}" class="dir-icon-btn" title="Create Assignment"><i class="fas fa-tasks"></i></a>
                                            @endif
                                            @if(Route::has('lessons.create'))
                                                <a href="{{ route('lessons.create') }}" class="dir-icon-btn" title="Create Lesson"><i class="fas fa-book-open"></i></a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="dir-empty">
                    <i class="fas fa-chalkboard-teacher d-block"></i>
                    <h5 class="mt-2 mb-1">No teaching assignments yet</h5>
                    <p class="mb-0">Classes appear here after Admin assigns you under Classes &amp; Subjects or Class Schedules.</p>
                </div>
            @endif
        </div>

        <div class="row g-3 mt-1">
            <div class="col-lg-6">
                <div class="dir-card">
                    <div class="dir-toolbar">
                        <h5 class="dir-toolbar-title mb-0">Assigned subjects</h5>
                    </div>
                    <div class="p-3">
                        @forelse($assignedSubjects as $subject)
                            <div class="d-flex justify-content-between align-items-center py-2 {{ !$loop->last ? 'border-bottom' : '' }}">
                                <div>
                                    <div class="dir-person-name">{{ $subject->subject_name }}</div>
                                    <span class="dir-person-meta">{{ $subject->subject_id }} · {{ $subject->class ?? 'N/A' }}</span>
                                </div>
                                <span class="dir-chip">Subject</span>
                            </div>
                        @empty
                            <p class="dir-muted mb-0">No subjects assigned yet.</p>
                        @endforelse
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="dir-card">
                    <div class="dir-toolbar">
                        <h5 class="dir-toolbar-title mb-0">Assigned sections</h5>
                    </div>
                    <div class="p-3">
                        @forelse($assignedSections as $section)
                            <div class="d-flex justify-content-between align-items-center py-2 {{ !$loop->last ? 'border-bottom' : '' }}">
                                <div>
                                    <div class="dir-person-name">{{ $section->name }}</div>
                                    <span class="dir-person-meta">{{ $section->grade_level ?? 'N/A' }}</span>
                                </div>
                                <span class="dir-chip dir-chip--soft">Section</span>
                            </div>
                        @empty
                            <p class="dir-muted mb-0">No sections assigned yet.</p>
                        @endforelse

                        @if($adviserSections->isNotEmpty())
                            <div class="dir-person-meta text-uppercase mt-3 mb-1">Homeroom (Adviser)</div>
                            @foreach($adviserSections as $section)
                                <div class="d-flex justify-content-between align-items-center py-2">
                                    <div>
                                        <div class="dir-person-name">{{ $section->name }}</div>
                                        <span class="dir-person-meta">{{ $section->grade_level ?? 'N/A' }} · {{ $section->students_count }} students</span>
                                    </div>
                                    <span class="dir-badge dir-badge--inactive">Adviser</span>
                                </div>
                            @endforeach
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/directory-modern.css') }}?v=20260914e">
@endpush
