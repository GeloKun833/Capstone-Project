@extends('layouts.master')
@section('content')
<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="card">
            <div class="card-body">
                <h4>Attendance Record</h4>
                <p><strong>Student:</strong> {{ $attendance->student->first_name }} {{ $attendance->student->last_name }}</p>
                <p><strong>Subject:</strong> {{ $attendance->subject->subject_name ?? 'N/A' }}</p>
                <p><strong>Date:</strong> {{ \Carbon\Carbon::parse($attendance->date)->format('M d, Y') }}</p>
                <p><strong>Status:</strong> <span class="badge {{ $attendance->status === 'present' ? 'bg-success' : 'bg-danger' }}">{{ ucfirst($attendance->status) }}</span></p>
                <p><strong>Remarks:</strong> {{ $attendance->remarks ?? 'None' }}</p>
                <a href="{{ route('attendance.edit', $attendance) }}" class="btn btn-primary">Edit</a>
            </div>
        </div>
    </div>
</div>
@endsection
