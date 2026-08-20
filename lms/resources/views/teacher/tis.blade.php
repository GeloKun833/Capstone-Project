@extends('layouts.master')
@section('content')
{{-- message --}}
{!! Toastr::message() !!}

<div class="page-wrapper">
    <div class="content container-fluid">
        {{-- Page Header --}}
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
                    <div class="float-end">
                        <a href="{{ route('teacher/list/page') }}" class="btn btn-primary">
                            <i class="fas fa-arrow-left me-2"></i>Back to List
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- Teacher Profile Card --}}
        <div class="row">
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-body text-center">
                        <div class="student-img">
                            @if (!empty($teacher->avatar))
                                <img src="{{ URL::to('images/'.$teacher->avatar) }}" alt="{{ $teacher->full_name }}" class="img-fluid rounded-circle" style="width: 150px; height: 150px; object-fit: cover;">
                            @else
                                <img src="{{ URL::to('images/photo_defaults.jpg') }}" alt="{{ $teacher->full_name }}" class="img-fluid rounded-circle" style="width: 150px; height: 150px; object-fit: cover;">
                            @endif
                        </div>
                        <h4 class="mt-3 mb-1">{{ $teacher->full_name }}</h4>
                        <p class="text-muted mb-1">{{ $user->role_name }}</p>
                        <p class="text-muted mb-3">
                            <i class="fas fa-id-card me-1"></i>{{ $user->user_id }}
                        </p>
                        
                        <div class="d-flex justify-content-center gap-2 mb-3">
                            <span class="badge bg-{{ $user->status === 'active' ? 'success' : 'danger' }}">
                                {{ ucfirst($user->status) }}
                            </span>
                        </div>

                        <div class="text-start mt-4">
                            <h6 class="mb-3"><i class="fas fa-info-circle me-2"></i>Personal Information</h6>
                            <div class="mb-2">
                                <strong>Email:</strong><br>
                                <a href="mailto:{{ $user->email }}">{{ $user->email }}</a>
                            </div>
                            <div class="mb-2">
                                <strong>Phone:</strong><br>
                                {{ $teacher->phone_number }}
                            </div>
                            <div class="mb-2">
                                <strong>Gender:</strong><br>
                                {{ $teacher->gender ?: 'Not specified' }}
                            </div>
                            <div class="mb-2">
                                <strong>Date of Birth:</strong><br>
                                {{ $teacher->date_of_birth ?: 'Not specified' }}
                            </div>
                            <div class="mb-2">
                                <strong>Join Date:</strong><br>
                                {{ $user->join_date }}
                            </div>
                        </div>

                        <div class="text-start mt-4">
                            <h6 class="mb-3"><i class="fas fa-map-marker-alt me-2"></i>Address</h6>
                            <p class="mb-1">{{ $teacher->address }}</p>
                            <p class="mb-1">{{ $teacher->city }}, {{ $teacher->state }} {{ $teacher->zip_code }}</p>
                            <p class="mb-0">{{ $teacher->country }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                {{-- Professional Information --}}
                <div class="card mb-3">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-graduation-cap me-2"></i>Professional Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <strong>Qualification:</strong><br>
                                {{ $teacher->qualification ?: 'Not specified' }}
                            </div>
                            <div class="col-md-6 mb-3">
                                <strong>Experience:</strong><br>
                                {{ $teacher->experience ?: 'Not specified' }}
                            </div>
                            <div class="col-md-6 mb-3">
                                <strong>Department:</strong><br>
                                {{ $user->department ?: 'Not assigned' }}
                            </div>
                            <div class="col-md-6 mb-3">
                                <strong>Position:</strong><br>
                                {{ $user->position ?: 'Teacher' }}
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Assigned Subjects --}}
                <div class="card mb-3">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="fas fa-book me-2"></i>Assigned Subjects ({{ $assignedSubjects->count() }})</h5>
                    </div>
                    <div class="card-body">
                        @if($assignedSubjects->isNotEmpty())
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
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
                                            <td>
                                                <span class="badge bg-primary">{{ $subject->class }}</span>
                                            </td>
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
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>No subjects assigned yet.
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Assigned Sections --}}
                <div class="card mb-3">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="fas fa-users me-2"></i>Assigned Sections ({{ $assignedSections->count() }})</h5>
                    </div>
                    <div class="card-body">
                        @if($assignedSections->isNotEmpty())
                            <div class="row">
                                @foreach($assignedSections as $section)
                                <div class="col-md-4 mb-3">
                                    <div class="card bg-light">
                                        <div class="card-body text-center">
                                            <h5>{{ $section->name }}</h5>
                                            <p class="mb-0 text-muted">Grade {{ $section->grade_level }}</p>
                                            <small class="text-muted">Room: {{ $section->room_number ?: 'N/A' }}</small>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        @else
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>No sections assigned yet.
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Grade Levels --}}
                @if($gradeLevels->isNotEmpty())
                <div class="card mb-3">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="mb-0"><i class="fas fa-layer-group me-2"></i>Teaching Grade Levels</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex flex-wrap gap-2">
                            @foreach($gradeLevels as $gradeLevel)
                                <span class="badge bg-warning text-dark px-3 py-2">{{ $gradeLevel->grade_level }}</span>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endif

                {{-- Quick Actions --}}
                <div class="card">
                    <div class="card-header bg-secondary text-white">
                        <h5 class="mb-0"><i class="fas fa-cogs me-2"></i>Quick Actions</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex flex-wrap gap-2">
                            <a href="{{ url('teacher/edit/'.$user->user_id) }}" class="btn btn-primary">
                                <i class="fas fa-edit me-2"></i>Edit Teacher Info
                            </a>
                            <a href="{{ url('view/user/edit/'.$user->user_id) }}" class="btn btn-info">
                                <i class="fas fa-user-edit me-2"></i>Edit User Account
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

