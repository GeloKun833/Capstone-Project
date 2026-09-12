@extends('layouts.master')
@section('content')

<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col-12 col-sm-6">
                    <h3 class="page-title">Teacher Information System (TIS)</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('teacher/list/page') }}">Teachers</a></li>
                        <li class="breadcrumb-item active">{{ $teacher->full_name }}</li>
                    </ul>
                </div>
                <div class="col-12 col-sm-6">
                    <div class="float-end d-flex flex-wrap gap-2">
                        <a href="{{ url('teacher/edit/'.$user->user_id) }}" class="btn btn-outline-primary">
                            <i class="fas fa-edit me-1"></i>Edit Teacher
                        </a>
                        <a href="{{ url('view/user/edit/'.$user->user_id) }}" class="btn btn-outline-info">
                            <i class="fas fa-user-edit me-1"></i>Edit User
                        </a>
                        <a href="{{ route('teacher/list/page') }}" class="btn btn-primary">
                            <i class="fas fa-arrow-left me-1"></i>Back
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- Compact identity strip --}}
        <div class="card mb-3">
            <div class="card-body">
                <div class="d-flex flex-wrap align-items-center gap-3">
                    @if (!empty($teacher->avatar))
                        <img src="{{ URL::to('images/'.$teacher->avatar) }}" alt="{{ $teacher->full_name }}" class="rounded-circle" style="width: 72px; height: 72px; object-fit: cover;">
                    @else
                        <img src="{{ URL::to('images/photo_defaults.jpg') }}" alt="{{ $teacher->full_name }}" class="rounded-circle" style="width: 72px; height: 72px; object-fit: cover;">
                    @endif
                    <div class="flex-grow-1">
                        <h4 class="mb-1">{{ $teacher->full_name }}</h4>
                        <div class="d-flex flex-wrap gap-2 align-items-center">
                            <span class="badge bg-primary">{{ $user->role_name }}</span>
                            <span class="badge bg-{{ strtolower($user->status) === 'active' ? 'success' : 'danger' }}">
                                {{ ucfirst($user->status) }}
                            </span>
                            <small class="text-muted">User ID: {{ $user->user_id }}</small>
                        </div>
                    </div>
                    <div class="d-flex gap-3 text-center">
                        <div>
                            <div class="fw-bold text-info">{{ $assignedSubjects->count() }}</div>
                            <small class="text-muted">Subjects</small>
                        </div>
                        <div>
                            <div class="fw-bold text-success">{{ $assignedSections->count() }}</div>
                            <small class="text-muted">Sections</small>
                        </div>
                        <div>
                            <div class="fw-bold text-dark">{{ $teachingSchedule->count() }}</div>
                            <small class="text-muted">Schedules</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            {{-- 1. Personal Information --}}
            <div class="col-lg-6 mb-3">
                <div class="card h-100">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-user me-2"></i>Personal Information</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-borderless table-sm mb-3">
                            <tr>
                                <td class="fw-bold" style="width: 40%;">Full Name:</td>
                                <td>{{ $teacher->full_name }}</td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Email:</td>
                                <td><a href="mailto:{{ $user->email }}">{{ $user->email }}</a></td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Phone:</td>
                                <td>{{ $teacher->phone_number ?: 'N/A' }}</td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Gender:</td>
                                <td>{{ $teacher->gender ?: 'Not specified' }}</td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Date of Birth:</td>
                                <td>{{ $teacher->date_of_birth ?: 'Not specified' }}</td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Join Date:</td>
                                <td>{{ $user->join_date ?: 'N/A' }}</td>
                            </tr>
                        </table>
                        <h6 class="fw-bold mb-2"><i class="fas fa-map-marker-alt me-1"></i>Address</h6>
                        <p class="mb-1">{{ $teacher->address ?: 'N/A' }}</p>
                        <p class="mb-0 text-muted">
                            {{ trim(collect([$teacher->city, $teacher->state, $teacher->zip_code])->filter()->implode(', ')) }}
                            @if($teacher->country)<br>{{ $teacher->country }}@endif
                        </p>
                    </div>
                </div>
            </div>

            {{-- 2. Professional Information (TIS equivalent of academic) --}}
            <div class="col-lg-6 mb-3">
                <div class="card h-100">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="fas fa-graduation-cap me-2"></i>Professional Information</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-borderless table-sm mb-0">
                            <tr>
                                <td class="fw-bold" style="width: 40%;">Qualification:</td>
                                <td>{{ $teacher->qualification ?: 'Not specified' }}</td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Experience:</td>
                                <td>{{ $teacher->experience ?: 'Not specified' }}</td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Department:</td>
                                <td>{{ $user->department ?: 'Not assigned' }}</td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Position:</td>
                                <td>{{ $user->position ?: 'Teacher' }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>

            {{-- 3. Assigned Subjects --}}
            <div class="col-12 mb-3">
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="fas fa-book me-2"></i>Assigned Subjects ({{ $assignedSubjects->count() }})</h5>
                    </div>
                    <div class="card-body">
                        @if($assignedSubjects->isNotEmpty())
                            <div class="table-responsive">
                                <table class="table table-striped table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th>Subject Name</th>
                                            <th>Class/Grade</th>
                                            <th>Sections</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($assignedSubjects as $subject)
                                        <tr>
                                            <td><strong>{{ $subject->subject_name }}</strong></td>
                                            <td><span class="badge bg-primary">{{ $subject->class }}</span></td>
                                            <td>
                                                @if($subject->sections && $subject->sections->isNotEmpty())
                                                    @foreach($subject->sections as $section)
                                                        <span class="badge bg-secondary">{{ $section->name }}</span>
                                                    @endforeach
                                                @else
                                                    <span class="text-muted">No sections</span>
                                                @endif
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="alert alert-info mb-0">
                                <i class="fas fa-info-circle me-2"></i>No subjects assigned yet.
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- 4. Assigned Sections --}}
            <div class="col-12 mb-3">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="fas fa-users me-2"></i>Assigned Sections ({{ $assignedSections->count() }})</h5>
                    </div>
                    <div class="card-body">
                        @if($assignedSections->isNotEmpty())
                            <div class="row">
                                @foreach($assignedSections as $section)
                                <div class="col-md-4 mb-3">
                                    <div class="card bg-light h-100">
                                        <div class="card-body text-center">
                                            <h5 class="mb-1">{{ $section->name }}</h5>
                                            <p class="mb-0 text-muted">{{ $section->grade_level }}</p>
                                            <small class="text-muted">Room: {{ $section->room_number ?: 'N/A' }}</small>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        @else
                            <div class="alert alert-info mb-0">
                                <i class="fas fa-info-circle me-2"></i>No sections assigned yet.
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- More details: buttons → modals --}}
            <div class="col-12 mb-3">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-folder-open me-2"></i>More Details</h5>
                    </div>
                    <div class="card-body">
                        <p class="text-muted small mb-3">Additional records are hidden to keep this page clear. Open any item below to view.</p>
                        <div class="d-flex flex-wrap gap-2">
                            <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#tisGradeLevelsModal">
                                <i class="fas fa-layer-group me-1"></i> Teaching Grade Levels
                            </button>
                            <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#tisScheduleModal">
                                <i class="fas fa-calendar-alt me-1"></i> Teaching Schedule
                            </button>
                            <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#tisPostsModal">
                                <i class="fas fa-bullhorn me-1"></i> Recent Class Posts
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Grade Levels Modal --}}
<div class="modal fade" id="tisGradeLevelsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-layer-group me-2"></i>Teaching Grade Levels</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                @if($gradeLevels->isNotEmpty())
                    <div class="d-flex flex-wrap gap-2">
                        @foreach($gradeLevels as $gradeLevel)
                            <span class="badge bg-warning text-dark px-3 py-2">{{ $gradeLevel->grade_level }}</span>
                        @endforeach
                    </div>
                @else
                    <div class="alert alert-info mb-0"><i class="fas fa-info-circle me-2"></i>No teaching grade levels assigned.</div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Teaching Schedule Modal --}}
<div class="modal fade" id="tisScheduleModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-calendar-alt me-2"></i>Teaching Schedule ({{ $teachingSchedule->count() }})</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                @if($teachingSchedule->isNotEmpty())
                    <div class="table-responsive">
                        <table class="table table-striped table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Day</th>
                                    <th>Time</th>
                                    <th>Subject</th>
                                    <th>Section</th>
                                    <th>Room</th>
                                    <th>Type</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($teachingSchedule as $schedule)
                                <tr>
                                    <td>{{ ucfirst($schedule->day_of_week) }}</td>
                                    <td>{{ $schedule->time_range }}</td>
                                    <td><strong>{{ $schedule->subject->subject_name ?? 'N/A' }}</strong></td>
                                    <td>{{ $schedule->section->name ?? 'N/A' }}</td>
                                    <td>{{ $schedule->room->name ?? ($schedule->room->room_name ?? 'N/A') }}</td>
                                    <td>{{ $schedule->class_type_display ?? ucfirst($schedule->class_type ?? 'lecture') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="alert alert-info mb-0"><i class="fas fa-info-circle me-2"></i>No active class schedules assigned yet.</div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Class Posts Modal --}}
<div class="modal fade" id="tisPostsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-bullhorn me-2"></i>Recent Class Posts ({{ $classPosts->count() }})</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                @if($classPosts->isNotEmpty())
                    @foreach($classPosts as $post)
                        <div class="border rounded p-3 mb-3">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h6 class="mb-0">{{ $post->title }}</h6>
                                <small class="text-muted">{{ $post->created_at?->format('M d, Y') }}</small>
                            </div>
                            <p class="text-muted small mb-2">
                                <span class="badge bg-secondary me-1">{{ ucfirst($post->type ?? 'announcement') }}</span>
                                @if($post->subject)
                                    {{ $post->subject->subject_name }}
                                @endif
                            </p>
                            <p class="mb-0">{{ \Illuminate\Support\Str::limit(strip_tags($post->content), 200) }}</p>
                        </div>
                    @endforeach
                @else
                    <div class="alert alert-info mb-0"><i class="fas fa-info-circle me-2"></i>No class posts published yet.</div>
                @endif
            </div>
        </div>
    </div>
</div>

@endsection
