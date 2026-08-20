@extends('layouts.master')
@section('content')
    {{-- message --}}
    {!! Toastr::message() !!}
    <div class="page-wrapper">
        <div class="content container-fluid">
            <div class="page-header">
                <div class="row">
                    <div class="col-sm-12">
                        <div class="page-sub-header">
                            <h3 class="page-title">My Class List</h3>
                            <ul class="breadcrumb">
                                <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                                <li class="breadcrumb-item active">My Classes</li>
                            </ul>
                        </div>
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
                                    <h6>Total Subjects</h6>
                                    <h3>{{ $teacherSubjects->count() }}</h3>
                                </div>
                                <div class="db-icon">
                                    <img src="{{ URL::to('assets/img/icons/teacher-icon-01.svg') }}" alt="Dashboard Icon">
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
                                    <h6>Total Students</h6>
                                    <h3>{{ $teacherSubjects->sum('enrollments_count') }}</h3>
                                </div>
                                <div class="db-icon">
                                    <img src="{{ URL::to('assets/img/icons/dash-icon-01.svg') }}" alt="Dashboard Icon">
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
                                    <h6>Total Sections</h6>
                                    <h3>{{ $teacherSections->count() }}</h3>
                                </div>
                                <div class="db-icon">
                                    <img src="{{ URL::to('assets/img/icons/teacher-icon-02.svg') }}" alt="Dashboard Icon">
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
                                    <h6>Average Attendance</h6>
                                    <h3>{{ $attendanceStats->count() > 0 ? round($attendanceStats->avg(function($stat) { return $stat->total_records > 0 ? ($stat->present_count / $stat->total_records) * 100 : 0; }), 1) : 0 }}%</h3>
                                </div>
                                <div class="db-icon">
                                    <img src="{{ URL::to('assets/img/icons/teacher-icon-03.svg') }}" alt="Dashboard Icon">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12 col-lg-8 col-xl-8 d-flex">
                    <div class="card flex-fill comman-shadow">
                        <div class="card-header">
                            <div class="row align-items-center">
                                <div class="col-6">
                                    <h5 class="card-title">My Subjects</h5>
                                </div>
                                <div class="col-6">
                                    <span class="float-end view-link"><a href="#">View All Subjects</a></span>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover table-center mb-0">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>Subject ID</th>
                                            <th>Subject Name</th>
                                            <th>Class</th>
                                            <th>Students</th>
                                            <th>Attendance Rate</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($teacherSubjects as $subject)
                                        <tr>
                                            <td>{{ $subject->subject_id }}</td>
                                            <td>
                                                <h2 class="table-avatar">
                                                    <a href="#" class="avatar avatar-sm me-2">
                                                        <img class="avatar-img rounded-circle" src="{{ URL::to('assets/img/profiles/avatar-01.jpg') }}" alt="Subject">
                                                    </a>
                                                    <a href="#">{{ $subject->subject_name }}</a>
                                                </h2>
                                            </td>
                                            <td>{{ $subject->class }}</td>
                                            <td>{{ $subject->enrollments_count }}</td>
                                            <td>
                                                @php
                                                    $attendanceRate = 0;
                                                    if (isset($attendanceStats[$subject->id])) {
                                                        $stat = $attendanceStats[$subject->id];
                                                        $attendanceRate = $stat->total_records > 0 ? round(($stat->present_count / $stat->total_records) * 100, 1) : 0;
                                                    }
                                                @endphp
                                                <div class="progress" style="height: 6px;">
                                                    <div class="progress-bar bg-success" role="progressbar" style="width: {{ $attendanceRate }}%"></div>
                                                </div>
                                                <small>{{ $attendanceRate }}%</small>
                                            </td>
                                            <td>
                                                <div class="actions">
                                                    <a href="#" class="btn btn-sm bg-success-light me-2">
                                                        <i class="fas fa-eye"></i> View
                                                    </a>
                                                    <a href="#" class="btn btn-sm bg-primary-light">
                                                        <i class="fas fa-edit"></i> Edit
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="6" class="text-center text-muted">
                                                <i class="fas fa-info-circle me-2"></i>No subjects assigned yet
                                            </td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-lg-4 col-xl-4 d-flex">
                    <div class="card flex-fill comman-shadow">
                        <div class="card-header">
                            <div class="row align-items-center">
                                <div class="col-12">
                                    <h5 class="card-title">My Sections</h5>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover table-center mb-0">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>Section Name</th>
                                            <th>Grade Level</th>
                                            <th>Students</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($teacherSections as $section)
                                        <tr>
                                            <td>
                                                <h2 class="table-avatar">
                                                    <a href="#" class="avatar avatar-sm me-2">
                                                        <img class="avatar-img rounded-circle" src="{{ URL::to('assets/img/profiles/avatar-01.jpg') }}" alt="Section">
                                                    </a>
                                                    <a href="#">{{ $section->name }}</a>
                                                </h2>
                                            </td>
                                            <td>{{ $section->grade_level }}</td>
                                            <td>{{ $section->students_count }}</td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="3" class="text-center text-muted">
                                                <i class="fas fa-info-circle me-2"></i>No sections assigned as adviser
                                            </td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12 col-lg-12 col-xl-12 d-flex">
                    <div class="card flex-fill comman-shadow">
                        <div class="card-header">
                            <div class="row align-items-center">
                                <div class="col-6">
                                    <h5 class="card-title">Recent Enrollments</h5>
                                </div>
                                <div class="col-6">
                                    <span class="float-end view-link"><a href="#">View All Enrollments</a></span>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover table-center mb-0">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>Student ID</th>
                                            <th>Student Name</th>
                                            <th>Subject</th>
                                            <th>Enrollment Date</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($recentEnrollments as $enrollment)
                                        <tr>
                                            <td>{{ $enrollment->student->admission_id ?? 'STU' . $enrollment->student->id }}</td>
                                            <td>
                                                <h2 class="table-avatar">
                                                    <a href="#" class="avatar avatar-sm me-2">
                                                        <img class="avatar-img rounded-circle" src="{{ URL::to('assets/img/profiles/avatar-01.jpg') }}" alt="Student">
                                                    </a>
                                                    <a href="#">{{ $enrollment->student->first_name ?? 'Student' }} {{ $enrollment->student->last_name ?? '' }}</a>
                                                </h2>
                                            </td>
                                            <td>{{ $enrollment->subject->subject_name ?? 'Subject' }}</td>
                                            <td>{{ $enrollment->created_at ? $enrollment->created_at->format('M d, Y') : 'N/A' }}</td>
                                            <td>
                                                <span class="badge bg-success-light">{{ ucfirst($enrollment->status) }}</span>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="5" class="text-center text-muted">
                                                <i class="fas fa-info-circle me-2"></i>No recent enrollments
                                            </td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection 