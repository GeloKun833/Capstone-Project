@extends('layouts.master')
@section('content')
    {{-- message --}}
    {!! Toastr::message() !!}
    <div class="page-wrapper">
        <div class="content container-fluid">
            <div class="page-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="page-title">{{ $child->full_name }} - Attendance</h3>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active">{{ $child->full_name }} - Attendance</li>
                        </ul>
                    </div>
                    <div class="col-auto text-end float-end ms-auto">
                        <a href="{{ route('dashboard') }}" class="btn btn-primary">
                            <i class="fas fa-arrow-left"></i> Back to Dashboard
                        </a>
                    </div>
                </div>
            </div>

            <!-- Student Info -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-md-2 text-center">
                                    <img src="{{ $child->upload ? asset('storage/' . $child->upload) : URL::to('assets/img/profiles/avatar-01.jpg') }}" 
                                         alt="Student Photo" class="rounded-circle" style="width: 80px; height: 80px; object-fit: cover;">
                                </div>
                                <div class="col-md-6">
                                    <h4 class="mb-2">{{ $child->full_name }}</h4>
                                    <p class="text-muted mb-1">
                                        <strong>Student ID:</strong> {{ $child->admission_id }}
                                    </p>
                                    <p class="text-muted mb-1">
                                        <strong>Section:</strong> {{ $child->sections->first()->name ?? 'Not Assigned' }}
                                    </p>
                                </div>
                                <div class="col-md-4 text-end">
                                    <div class="d-flex flex-column">
                                        <h5 class="text-primary mb-2">Attendance Rate</h5>
                                        <h3 class="mb-0">{{ $attendance->count() > 0 ? round(($attendance->where('status', 'present')->count() / $attendance->count()) * 100, 1) : 0 }}%</h3>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Attendance Summary -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">Attendance Summary</h5>
                        </div>
                        <div class="card-body">
                            <div class="row text-center">
                                <div class="col-md-3">
                                    <div class="bg-success text-white rounded p-4">
                                        <h3>{{ $attendance->where('status', 'present')->count() }}</h3>
                                        <p class="mb-0">Present</p>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="bg-danger text-white rounded p-4">
                                        <h3>{{ $attendance->where('status', 'absent')->count() }}</h3>
                                        <p class="mb-0">Absent</p>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="bg-warning text-white rounded p-4">
                                        <h3>{{ $attendance->where('status', 'late')->count() }}</h3>
                                        <p class="mb-0">Late</p>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="bg-info text-white rounded p-4">
                                        <h3>{{ $attendance->where('status', 'excused')->count() }}</h3>
                                        <p class="mb-0">Excused</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Attendance Details -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">Attendance Details</h5>
                        </div>
                        <div class="card-body">
                            @if($attendance->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-hover table-center mb-0">
                                        <thead>
                                            <tr>
                                                <th>Date</th>
                                                <th>Subject</th>
                                                <th>Status</th>
                                                <th>Teacher</th>
                                                <th>Notes</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($attendance as $record)
                                                <tr>
                                                    <td>
                                                        <div>
                                                            <div class="text-dark fw-medium">{{ $record->date->format('M d, Y') }}</div>
                                                            <small class="text-muted">{{ $record->date->format('l') }}</small>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <h2 class="table-avatar">
                                                            <a href="#" class="avatar avatar-sm me-2">
                                                                <i class="fas fa-book text-primary"></i>
                                                            </a>
                                                            <a href="#">{{ $record->subject->subject_name ?? 'N/A' }}</a>
                                                        </h2>
                                                    </td>
                                                    <td>
                                                        @if($record->status == 'present')
                                                            <span class="badge bg-success">Present</span>
                                                        @elseif($record->status == 'absent')
                                                            <span class="badge bg-danger">Absent</span>
                                                        @elseif($record->status == 'late')
                                                            <span class="badge bg-warning">Late</span>
                                                        @elseif($record->status == 'excused')
                                                            <span class="badge bg-info">Excused</span>
                                                        @else
                                                            <span class="badge bg-secondary">{{ ucfirst($record->status) }}</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <h2 class="table-avatar">
                                                            <a href="#" class="avatar avatar-sm me-2">
                                                                <img class="avatar-img rounded-circle" src="{{ $record->teacher->avatar ?? URL::to('assets/img/profiles/avatar-01.jpg') }}" alt="User Image">
                                                            </a>
                                                            <a href="#">{{ $record->teacher->full_name ?? 'N/A' }}</a>
                                                        </h2>
                                                    </td>
                                                    <td>
                                                        <span class="text-muted">-</span>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>

                                <!-- Pagination -->
                                <div class="d-flex justify-content-center mt-4">
                                    {{ $attendance->links() }}
                                </div>
                            @else
                                <div class="text-center py-5">
                                    <div class="mb-4">
                                        <i class="fas fa-calendar-check text-muted" style="font-size: 4rem; opacity: 0.6;"></i>
                                    </div>
                                    <h4 class="fw-bold text-dark mb-3">No Attendance Records</h4>
                                    <p class="text-muted mb-4">No attendance records have been found for this student.</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection 