@extends('layouts.master')
@section('content')
<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title"><i class="fas fa-database me-2"></i>Information Systems Hub</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Information Systems</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card card-table comman-shadow h-100">
                    <div class="card-body text-center">
                        <i class="fas fa-user-graduate fa-2x text-primary mb-3"></i>
                        <h5>Student Information System</h5>
                        <p class="text-muted small">Complete student academic profile, grades, attendance, and enrollment history.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card card-table comman-shadow h-100">
                    <div class="card-body text-center">
                        <i class="fas fa-chalkboard-teacher fa-2x text-success mb-3"></i>
                        <h5>Teacher Information System</h5>
                        <p class="text-muted small">Teacher assignments, schedules, subjects, sections, and class posts.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card card-table comman-shadow h-100">
                    <div class="card-body text-center">
                        <i class="fas fa-users fa-2x text-info mb-3"></i>
                        <h5>Parent Portal</h5>
                        <p class="text-muted small">Parents access child grades, attendance, activities, and teacher feedback through linked accounts.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card card-table comman-shadow">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-search me-2"></i>Search Records</h5>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('sis.hub') }}" class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label">Record Type</label>
                        <select name="type" class="form-control form-select">
                            <option value="students" @selected($type === 'students')>Students</option>
                            @if(in_array(Session::get('role_name'), ['Admin', 'Registrar'], true))
                                <option value="teachers" @selected($type === 'teachers')>Teachers</option>
                            @endif
                        </select>
                    </div>
                    <div class="col-md-7">
                        <label class="form-label">Search by name, email, or ID</label>
                        <input type="text" name="q" class="form-control" value="{{ $query }}" placeholder="Type to search...">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-search me-1"></i> Search
                        </button>
                    </div>
                </form>
            </div>
        </div>

        @if($query !== '')
            <div class="card card-table comman-shadow mt-4">
                <div class="card-body">
                    @if($type === 'teachers')
                        <h5 class="mb-3">Teacher Results</h5>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>User ID</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th class="text-end">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($teachers as $teacher)
                                        <tr>
                                            <td>{{ $teacher->user_id }}</td>
                                            <td>{{ $teacher->name }}</td>
                                            <td>{{ $teacher->email }}</td>
                                            <td class="text-end">
                                                <a href="{{ route('teacher.sis', $teacher->user_id) }}" class="btn btn-sm btn-primary">
                                                    View TIS
                                                </a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="4" class="text-center text-muted py-4">No teachers found.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    @else
                        <h5 class="mb-3">Student Results</h5>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Student</th>
                                        <th>Email</th>
                                        <th>Year Level</th>
                                        <th class="text-end">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($students as $student)
                                        <tr>
                                            <td>{{ $student->full_name }}</td>
                                            <td>{{ $student->email }}</td>
                                            <td>{{ $student->year_level ?? 'N/A' }}</td>
                                            <td class="text-end">
                                                <a href="{{ route('student.sis', $student->user_id) }}" class="btn btn-sm btn-primary">
                                                    View SIS
                                                </a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="4" class="text-center text-muted py-4">No students found.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
