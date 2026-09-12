@extends('layouts.master')
@section('content')

    <div class="page-wrapper">
        <div class="content container-fluid">
            <div class="page-header">
                <div class="row align-items-center">
                    <div class="col-sm-8">
                        <div class="page-sub-header">
                            <h3 class="page-title">My Classes & Subjects</h3>
                            <ul class="breadcrumb">
                                <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                                <li class="breadcrumb-item active">My Classes & Subjects</li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-sm-4 text-sm-end mt-2 mt-sm-0">
                        <a href="{{ route('teacher.my-schedule') }}" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-calendar-alt me-1"></i> My Schedule
                        </a>
                    </div>
                </div>
            </div>

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="row">
                <div class="col-xl-3 col-sm-6 col-12 d-flex">
                    <div class="card bg-comman w-100">
                        <div class="card-body">
                            <div class="db-widgets d-flex justify-content-between align-items-center">
                                <div class="db-info">
                                    <h6>Teaching Classes</h6>
                                    <h3>{{ $stats['classes'] }}</h3>
                                </div>
                                <div class="db-icon">
                                    <img src="{{ URL::to('assets/img/icons/teacher-icon-02.svg') }}" alt="">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-sm-6 col-12 d-flex">
                    <div class="card bg-comman w-100">
                        <div class="card-body">
                            <div class="db-widgets d-flex justify-content-between align-items-center">
                                <div class="db-info">
                                    <h6>Assigned Subjects</h6>
                                    <h3>{{ $stats['subjects'] }}</h3>
                                </div>
                                <div class="db-icon">
                                    <img src="{{ URL::to('assets/img/icons/teacher-icon-01.svg') }}" alt="">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-sm-6 col-12 d-flex">
                    <div class="card bg-comman w-100">
                        <div class="card-body">
                            <div class="db-widgets d-flex justify-content-between align-items-center">
                                <div class="db-info">
                                    <h6>Assigned Sections</h6>
                                    <h3>{{ $stats['sections'] }}</h3>
                                </div>
                                <div class="db-icon">
                                    <img src="{{ URL::to('assets/img/icons/dash-icon-01.svg') }}" alt="">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-sm-6 col-12 d-flex">
                    <div class="card bg-comman w-100">
                        <div class="card-body">
                            <div class="db-widgets d-flex justify-content-between align-items-center">
                                <div class="db-info">
                                    <h6>With Schedule</h6>
                                    <h3>{{ $stats['scheduled'] }}</h3>
                                </div>
                                <div class="db-icon">
                                    <img src="{{ URL::to('assets/img/icons/teacher-icon-03.svg') }}" alt="">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card flex-fill comman-shadow">
                        <div class="card-header">
                            <div class="row align-items-center">
                                <div class="col-md-8">
                                    <h5 class="card-title mb-0">Assigned Teaching Load</h5>
                                    <small class="text-muted">Based on Admin Classes &amp; Subjects / Class Schedules assignment</small>
                                </div>
                                <div class="col-md-4 text-md-end mt-2 mt-md-0">
                                    <span class="text-muted small">{{ now()->format('M d, Y g:i A') }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            @if($assignments->isNotEmpty())
                                <div class="table-responsive">
                                    <table class="table table-hover table-center mb-0">
                                        <thead class="thead-light">
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
                                                    $sourceClass = match ($row['source']) {
                                                        'schedule' => 'bg-success',
                                                        'section_subject' => 'bg-info',
                                                        'assignment' => 'bg-primary',
                                                        default => 'bg-secondary',
                                                    };
                                                @endphp
                                                <tr>
                                                    <td>
                                                        <div class="fw-semibold">{{ $subject->subject_name }}</div>
                                                        <small class="text-muted">{{ $subject->subject_id ?? ('ID ' . $subject->id) }}</small>
                                                    </td>
                                                    <td>
                                                        @if($section)
                                                            <div class="fw-semibold">{{ $section->name }}</div>
                                                            @if($row['is_adviser'])
                                                                <span class="badge bg-warning text-dark">Adviser</span>
                                                            @endif
                                                        @else
                                                            <span class="text-muted">No section linked yet</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-light text-dark">
                                                            {{ $section->grade_level ?? $subject->class ?? 'N/A' }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-success-light">{{ $row['students_count'] }}</span>
                                                    </td>
                                                    <td>
                                                        <div>{{ $row['schedule_summary'] }}</div>
                                                        @if($row['schedules']->isNotEmpty())
                                                            <small class="text-muted">
                                                                @foreach($row['schedules']->take(2) as $sch)
                                                                    {{ ucfirst($sch->day_of_week) }}
                                                                    {{ \Carbon\Carbon::parse($sch->start_time)->format('g:i A') }}–
                                                                    {{ \Carbon\Carbon::parse($sch->end_time)->format('g:i A') }}@if(!$loop->last); @endif
                                                                @endforeach
                                                                @if($row['schedules']->count() > 2)
                                                                    +{{ $row['schedules']->count() - 2 }} more
                                                                @endif
                                                            </small>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <span class="badge {{ $sourceClass }}">{{ $sourceLabel }}</span>
                                                    </td>
                                                    <td class="text-end">
                                                        <div class="actions">
                                                            @if(Route::has('attendance.index'))
                                                                <a href="{{ route('attendance.index') }}" class="btn btn-sm bg-success-light me-1" title="Attendance">
                                                                    <i class="fas fa-calendar-check"></i>
                                                                </a>
                                                            @endif
                                                            @if(Route::has('assignments.create'))
                                                                <a href="{{ route('assignments.create') }}" class="btn btn-sm bg-primary-light me-1" title="Create Assignment">
                                                                    <i class="fas fa-tasks"></i>
                                                                </a>
                                                            @endif
                                                            @if(Route::has('lessons.create'))
                                                                <a href="{{ route('lessons.create') }}" class="btn btn-sm bg-info-light" title="Create Lesson">
                                                                    <i class="fas fa-book-open"></i>
                                                                </a>
                                                            @endif
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center py-5">
                                    <i class="fas fa-chalkboard-teacher fa-3x text-muted mb-3"></i>
                                    <h5>No teaching assignments yet</h5>
                                    <p class="text-muted mb-0">
                                        Your classes and subjects will appear here after Admin assigns you under
                                        <strong>Classes &amp; Subjects</strong> or creates a <strong>Class Schedule</strong>.
                                    </p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-6 d-flex">
                    <div class="card flex-fill comman-shadow">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Assigned Subjects</h5>
                        </div>
                        <div class="card-body">
                            @forelse($assignedSubjects as $subject)
                                <div class="d-flex justify-content-between align-items-center border-bottom py-2">
                                    <div>
                                        <div class="fw-semibold">{{ $subject->subject_name }}</div>
                                        <small class="text-muted">{{ $subject->subject_id }} · {{ $subject->class ?? 'N/A' }}</small>
                                    </div>
                                    <span class="badge bg-primary-light text-primary">Subject</span>
                                </div>
                            @empty
                                <p class="text-muted mb-0">No subjects assigned yet.</p>
                            @endforelse
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 d-flex">
                    <div class="card flex-fill comman-shadow">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Assigned Sections</h5>
                        </div>
                        <div class="card-body">
                            @forelse($assignedSections as $section)
                                <div class="d-flex justify-content-between align-items-center border-bottom py-2">
                                    <div>
                                        <div class="fw-semibold">{{ $section->name }}</div>
                                        <small class="text-muted">{{ $section->grade_level ?? 'N/A' }}</small>
                                    </div>
                                    <span class="badge bg-info-light text-info">Section</span>
                                </div>
                            @empty
                                <p class="text-muted mb-0">No sections assigned yet.</p>
                            @endforelse

                            @if($adviserSections->isNotEmpty())
                                <hr>
                                <h6 class="text-muted mb-2">Homeroom (Adviser)</h6>
                                @foreach($adviserSections as $section)
                                    <div class="d-flex justify-content-between align-items-center border-bottom py-2">
                                        <div>
                                            <div class="fw-semibold">{{ $section->name }}</div>
                                            <small class="text-muted">{{ $section->grade_level ?? 'N/A' }} · {{ $section->students_count }} students</small>
                                        </div>
                                        <span class="badge bg-warning text-dark">Adviser</span>
                                    </div>
                                @endforeach
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            @if($assignments->isNotEmpty())
            <div class="row mt-1">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Quick Actions</h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                @if(Route::has('assignments.create'))
                                <div class="col-md-3">
                                    <a href="{{ route('assignments.create') }}" class="btn btn-primary w-100 py-3">
                                        <i class="fas fa-tasks d-block mb-1"></i> Create Assignment
                                    </a>
                                </div>
                                @endif
                                @if(Route::has('class-posts.create'))
                                <div class="col-md-3">
                                    <a href="{{ route('class-posts.create') }}" class="btn btn-success w-100 py-3">
                                        <i class="fas fa-bullhorn d-block mb-1"></i> Create Class Post
                                    </a>
                                </div>
                                @endif
                                @if(Route::has('lessons.create'))
                                <div class="col-md-3">
                                    <a href="{{ route('lessons.create') }}" class="btn btn-info w-100 py-3">
                                        <i class="fas fa-book-open d-block mb-1"></i> Create Lesson
                                    </a>
                                </div>
                                @endif
                                @if(Route::has('attendance.index'))
                                <div class="col-md-3">
                                    <a href="{{ route('attendance.index') }}" class="btn btn-warning w-100 py-3">
                                        <i class="fas fa-calendar-check d-block mb-1"></i> Take Attendance
                                    </a>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>

@endsection
