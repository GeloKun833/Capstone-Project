@extends('layouts.master')
@section('content')
<div class="container py-4">
    <h2>Children's Attendance Records</h2>
    
    <form method="GET" action="{{ route('attendance.parent') }}" class="row g-3 mb-4">
        <div class="col-md-3">
            <label for="student_id" class="form-label">Child</label>
            <select name="student_id" id="student_id" class="form-select">
                <option value="">Select Child</option>
                @foreach($children as $child)
                    <option value="{{ $child->id }}" {{ request('student_id') == $child->id ? 'selected' : '' }}>
                        {{ $child->first_name }} {{ $child->last_name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label for="subject_id" class="form-label">Subject</label>
            <select name="subject_id" id="subject_id" class="form-select">
                <option value="">All Subjects</option>
                @foreach($subjects as $subject)
                    <option value="{{ $subject->id }}" {{ request('subject_id') == $subject->id ? 'selected' : '' }}>
                        {{ $subject->subject_name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label for="month" class="form-label">Month</label>
            <input type="month" name="month" id="month" class="form-control" 
                value="{{ request('month', now()->format('Y-m')) }}">
        </div>
        <div class="col-md-3 d-flex align-items-end">
            <button type="submit" class="btn btn-primary">Filter</button>
        </div>
    </form>

    @if($selectedStudent)
        <div class="alert alert-info">
            Showing attendance for: {{ $selectedStudent->first_name }} {{ $selectedStudent->last_name }}
        </div>

        <div class="card shadow-sm">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Subject</th>
                                <th>Status</th>
                                <th>Remarks</th>
                                <th>Marked By</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($attendances as $attendance)
                                <tr>
                                    <td>{{ \Carbon\Carbon::parse($attendance->date)->format('M d, Y') }}</td>
                                    <td>{{ $attendance->subject->subject_name }}</td>
                                    <td>
                                        @if($attendance->status === 'present')
                                            <span class="badge bg-success">Present</span>
                                        @else
                                            <span class="badge bg-danger">Absent</span>
                                        @endif
                                    </td>
                                    <td>{{ $attendance->remarks ?: '-' }}</td>
                                    <td>{{ $attendance->teacher->full_name }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted">No attendance records found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($attendances->isNotEmpty())
                    <div class="card mt-4">
                        <div class="card-body">
                            <h5 class="card-title">Summary</h5>
                            <div class="row">
                                <div class="col-md-4">
                                    <p class="mb-1">Total Classes: {{ $summary['total'] }}</p>
                                    <p class="mb-1">Present: {{ $summary['present'] }}</p>
                                    <p class="mb-1">Absent: {{ $summary['absent'] }}</p>
                                    <p class="mb-0">Attendance Rate: {{ $summary['percentage'] }}%</p>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    @else
        <div class="alert alert-info">
            Please select a child to view their attendance records.
        </div>
    @endif
</div>
@endsection 