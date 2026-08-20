@extends('layouts.master')
@section('content')
    {{-- message --}}
    {!! Toastr::message() !!}
    <div class="page-wrapper">
        <div class="content container-fluid">
            <div class="page-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="page-title">{{ $child->full_name }} - Grades</h3>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active">{{ $child->full_name }} - Grades</li>
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
                                    <p class="text-muted mb-0">
                                        <strong>Academic Period:</strong> {{ $academicYear->year ?? 'Not Set' }} - {{ $semester->name ?? 'Not Set' }}
                                    </p>
                                </div>
                                <div class="col-md-4 text-end">
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

            <!-- Grades Summary -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">Grades Summary</h5>
                        </div>
                        <div class="card-body">
                            @if($grades->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-hover table-center mb-0">
                                        <thead>
                                            <tr>
                                                <th>Subject</th>
                                                <th>Component</th>
                                                <th>Score</th>
                                                <th>Percentage</th>
                                                <th>Teacher</th>
                                                <th>Date</th>
                                                <th>Remarks</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($grades as $grade)
                                                <tr>
                                                    <td>
                                                        <h2 class="table-avatar">
                                                            <a href="#" class="avatar avatar-sm me-2">
                                                                <i class="fas fa-book text-primary"></i>
                                                            </a>
                                                            <a href="#">{{ $grade->subject->subject_name ?? 'N/A' }}</a>
                                                        </h2>
                                                    </td>
                                                    <td>{{ $grade->component->name ?? 'N/A' }}</td>
                                                    <td>
                                                        <strong>{{ $grade->score }}/{{ $grade->max_score }}</strong>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-{{ $grade->percentage >= 90 ? 'success' : ($grade->percentage >= 75 ? 'info' : 'warning') }}">
                                                            {{ number_format($grade->percentage, 1) }}%
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <h2 class="table-avatar">
                                                            <a href="#" class="avatar avatar-sm me-2">
                                                                <img class="avatar-img rounded-circle" src="{{ $grade->teacher->avatar ?? URL::to('assets/img/profiles/avatar-01.jpg') }}" alt="User Image">
                                                            </a>
                                                            <a href="#">{{ $grade->teacher->full_name ?? 'N/A' }}</a>
                                                        </h2>
                                                    </td>
                                                    <td>{{ $grade->created_at->format('M d, Y') }}</td>
                                                    <td>
                                                        @if($grade->remarks)
                                                            <span class="text-muted">{{ Str::limit($grade->remarks, 30) }}</span>
                                                        @else
                                                            <span class="text-muted">-</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>

                                <!-- Pagination -->
                                <div class="d-flex justify-content-center mt-4">
                                    {{ $grades->links() }}
                                </div>
                            @else
                                <div class="text-center py-5">
                                    <div class="mb-4">
                                        <i class="fas fa-chart-line text-muted" style="font-size: 4rem; opacity: 0.6;"></i>
                                    </div>
                                    <h4 class="fw-bold text-dark mb-3">No Grades Available</h4>
                                    <p class="text-muted mb-4">No grades have been recorded for this student yet.</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Performance Chart -->
            @if($grades->count() > 0)
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title">Performance Overview</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="stats-info">
                                            <h6>Average Score</h6>
                                            <h4>{{ number_format($grades->avg('percentage'), 1) }}%</h4>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="stats-info">
                                            <h6>Highest Score</h6>
                                            <h4>{{ number_format($grades->max('percentage'), 1) }}%</h4>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="stats-info">
                                            <h6>Lowest Score</h6>
                                            <h4>{{ number_format($grades->min('percentage'), 1) }}%</h4>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="stats-info">
                                            <h6>Total Grades</h6>
                                            <h4>{{ $grades->count() }}</h4>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection 