@extends('layouts.master')
@section('content')
<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="card">
            <div class="card-body">
                <form action="{{ route('attendance.update', $attendance) }}" method="POST">
                    @csrf @method('PUT')
                    <p><strong>Student:</strong> {{ $attendance->student->first_name }} {{ $attendance->student->last_name }}</p>
                    <p><strong>Subject:</strong> {{ $attendance->subject->subject_name ?? 'N/A' }}</p>
                    <p><strong>Date:</strong> {{ \Carbon\Carbon::parse($attendance->date)->format('M d, Y') }}</p>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-control" required>
                            <option value="present" {{ $attendance->status === 'present' ? 'selected' : '' }}>Present</option>
                            <option value="absent" {{ $attendance->status === 'absent' ? 'selected' : '' }}>Absent</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Remarks</label>
                        <input type="text" name="remarks" class="form-control" value="{{ $attendance->remarks }}">
                    </div>
                    <button type="submit" class="btn btn-primary">Save</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
