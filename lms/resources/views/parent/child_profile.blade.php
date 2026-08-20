@extends('layouts.master')
@section('content')
    {{-- message --}}
    {!! Toastr::message() !!}
    <div class="page-wrapper">
        <div class="content container-fluid">
            <div class="page-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="page-title">{{ $child->full_name }} - Profile</h3>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active">{{ $child->full_name }} - Profile</li>
                        </ul>
                    </div>
                    <div class="col-auto text-end float-end ms-auto">
                        <a href="{{ route('dashboard') }}" class="btn btn-primary">
                            <i class="fas fa-arrow-left"></i> Back to Dashboard
                        </a>
                    </div>
                </div>
            </div>

            <!-- Student Profile Card -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-md-3 text-center">
                                    <img src="{{ $child->upload ? asset('storage/' . $child->upload) : URL::to('assets/img/profiles/avatar-01.jpg') }}" 
                                         alt="Student Photo" class="rounded-circle" style="width: 120px; height: 120px; object-fit: cover;">
                                    <h4 class="mt-3 mb-1">{{ $child->full_name }}</h4>
                                    <p class="text-muted">Student ID: {{ $child->admission_id }}</p>
                                </div>
                                <div class="col-md-6">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <h6 class="text-muted mb-2">Personal Information</h6>
                                            <p class="mb-1"><strong>Email:</strong> {{ $child->email ?? 'N/A' }}</p>
                                            <p class="mb-1"><strong>Phone:</strong> {{ $child->phone ?? 'N/A' }}</p>
                                            <p class="mb-1"><strong>Date of Birth:</strong> {{ $child->date_of_birth ? \Carbon\Carbon::parse($child->date_of_birth)->format('M d, Y') : 'N/A' }}</p>
                                            <p class="mb-1"><strong>Gender:</strong> {{ $child->gender ?? 'N/A' }}</p>
                                        </div>
                                        <div class="col-md-6">
                                            <h6 class="text-muted mb-2">Academic Information</h6>
                                            <p class="mb-1"><strong>Section:</strong> {{ $child->sections->first()->name ?? 'Not Assigned' }}</p>
                                            <p class="mb-1"><strong>Academic Year:</strong> {{ $academicYear->year ?? 'Not Set' }}</p>
                                            <p class="mb-1"><strong>Semester:</strong> {{ $semester->name ?? 'Not Set' }}</p>
                                            <p class="mb-1"><strong>Status:</strong> 
                                                <span class="badge bg-success">Active</span>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3 text-end">
                                    <div class="d-flex flex-column">
                                        <h5 class="text-primary mb-2">Current GPA</h5>
                                        <h3 class="mb-0">{{ $child->getCurrentGpa($academicYear->id ?? null, $semester->id ?? null)->gpa ?? 'N/A' }}</h3>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body text-center">
                            <div class="d-inline-flex align-items-center justify-content-center bg-primary bg-opacity-10 rounded-circle mb-3" style="width: 60px; height: 60px;">
                                <i class="fas fa-graduation-cap text-primary" style="font-size: 24px;"></i>
                            </div>
                            <h4 class="mb-1">{{ $totalGrades }}</h4>
                            <p class="text-muted mb-0">Total Grades</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body text-center">
                            <div class="d-inline-flex align-items-center justify-content-center bg-success bg-opacity-10 rounded-circle mb-3" style="width: 60px; height: 60px;">
                                <i class="fas fa-chart-line text-success" style="font-size: 24px;"></i>
                            </div>
                            <h4 class="mb-1">{{ number_format($averageGrade, 1) }}%</h4>
                            <p class="text-muted mb-0">Average Grade</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body text-center">
                            <div class="d-inline-flex align-items-center justify-content-center bg-info bg-opacity-10 rounded-circle mb-3" style="width: 60px; height: 60px;">
                                <i class="fas fa-calendar-check text-info" style="font-size: 24px;"></i>
                            </div>
                            <h4 class="mb-1">{{ $totalAttendance }}</h4>
                            <p class="text-muted mb-0">Total Attendance</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body text-center">
                            <div class="d-inline-flex align-items-center justify-content-center bg-warning bg-opacity-10 rounded-circle mb-3" style="width: 60px; height: 60px;">
                                <i class="fas fa-percentage text-warning" style="font-size: 24px;"></i>
                            </div>
                            <h4 class="mb-1">{{ $attendancePercentage }}%</h4>
                            <p class="text-muted mb-0">Attendance Rate</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Enrolled Classes -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">Enrolled Classes</h5>
                        </div>
                        <div class="card-body">
                            @if($enrollments->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-hover table-center mb-0">
                                        <thead>
                                            <tr>
                                                <th>Subject Code</th>
                                                <th>Subject Name</th>
                                                <th>Academic Year</th>
                                                <th>Semester</th>
                                                <th>Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($enrollments as $enrollment)
                                                <tr>
                                                    <td>
                                                        <h2 class="table-avatar">
                                                            <a href="#" class="avatar avatar-sm me-2">
                                                                <i class="fas fa-book text-primary"></i>
                                                            </a>
                                                            <a href="#">{{ $enrollment->subject->subject_code ?? 'N/A' }}</a>
                                                        </h2>
                                                    </td>
                                                    <td>{{ $enrollment->subject->subject_name ?? 'N/A' }}</td>
                                                    <td>{{ $enrollment->academicYear->year ?? 'N/A' }}</td>
                                                    <td>{{ $enrollment->semester->name ?? 'N/A' }}</td>
                                                    <td>
                                                        <span class="badge bg-success">{{ ucfirst($enrollment->status) }}</span>
                                                    </td>
                                                    <td>
                                                        <a href="{{ route('parent.child.grades', $child->id) }}" class="btn btn-sm btn-outline-primary">
                                                            <i class="fas fa-chart-line"></i> Grades
                                                        </a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center py-4">
                                    <i class="fas fa-book-open text-muted" style="font-size: 48px;"></i>
                                    <h5 class="mt-3 text-muted">No Classes Enrolled</h5>
                                    <p class="text-muted">This student is not currently enrolled in any classes.</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="row">
                <!-- Recent Grades -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">Recent Grades</h5>
                            <a href="{{ route('parent.child.grades', $child->id) }}" class="float-end">View All</a>
                        </div>
                        <div class="card-body">
                            @if($recentGrades->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-hover table-center mb-0">
                                        <thead>
                                            <tr>
                                                <th>Subject</th>
                                                <th>Score</th>
                                                <th>Percentage</th>
                                                <th>Date</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($recentGrades as $grade)
                                                <tr>
                                                    <td>{{ $grade->subject->subject_name ?? 'N/A' }}</td>
                                                    <td>{{ $grade->score }}/{{ $grade->max_score }}</td>
                                                    <td>
                                                        <span class="badge bg-{{ $grade->percentage >= 90 ? 'success' : ($grade->percentage >= 75 ? 'info' : 'warning') }}">
                                                            {{ number_format($grade->percentage, 1) }}%
                                                        </span>
                                                    </td>
                                                    <td>{{ \Carbon\Carbon::parse($grade->created_at)->format('M d, Y') }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center py-4">
                                    <i class="fas fa-chart-line text-muted" style="font-size: 48px;"></i>
                                    <h5 class="mt-3 text-muted">No Recent Grades</h5>
                                    <p class="text-muted">No grades have been recorded yet.</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Recent Attendance -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">Recent Attendance</h5>
                            <a href="{{ route('parent.child.attendance', $child->id) }}" class="float-end">View All</a>
                        </div>
                        <div class="card-body">
                            @if($recentAttendance->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-hover table-center mb-0">
                                        <thead>
                                            <tr>
                                                <th>Date</th>
                                                <th>Subject</th>
                                                <th>Status</th>
                                                <th>Time</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($recentAttendance as $attendance)
                                                <tr>
                                                    <td>{{ \Carbon\Carbon::parse($attendance->date)->format('M d, Y') }}</td>
                                                    <td>{{ $attendance->subject->subject_name ?? 'N/A' }}</td>
                                                    <td>
                                                        <span class="badge bg-{{ $attendance->status === 'present' ? 'success' : 'danger' }}">
                                                            {{ ucfirst($attendance->status) }}
                                                        </span>
                                                    </td>
                                                    <td>{{ \Carbon\Carbon::parse($attendance->created_at)->format('h:i A') }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center py-4">
                                    <i class="fas fa-calendar-check text-muted" style="font-size: 48px;"></i>
                                    <h5 class="mt-3 text-muted">No Recent Attendance</h5>
                                    <p class="text-muted">No attendance records found.</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection 