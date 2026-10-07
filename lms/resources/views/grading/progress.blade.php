@extends('layouts.master')
@section('content')
@php $quarters = collect($periods ?? [])->mapWithKeys(fn ($period) => [$period['number'] => $period['label']])->all(); @endphp
<div class="page-wrapper">
    <div class="content container-fluid dir-page">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title mb-1">Grading Progress</h3>
                    <p class="dir-subtitle">{{ $academicYear->name ?? 'No current academic year' }}</p>
                </div>
            </div>
        </div>
        <form method="GET" class="mb-3">
            <div class="row g-2 align-items-end">
                <div class="col-md-4">
                    <label class="form-label">Academic Year</label>
                    <input type="text" class="form-control" value="{{ $academicYear->name ?? 'No current academic year' }}" readonly>
                </div>
                <div class="col-md-4">
                    <label class="form-label" for="quarter">Term</label>
                    <select class="form-select" name="quarter" id="quarter" onchange="this.form.submit()">
                        @foreach($quarters as $number => $label)
                            <option value="{{ $number }}" @selected($quarter === $number)>{{ $label }}{{ $number === ($currentQuarter ?? 0) ? ' · Current' : '' }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </form>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Grade</th>
                        <th>Section</th>
                        <th>Subject</th>
                        <th>Teacher</th>
                        <th>Period</th>
                        <th>Method</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $row)
                        <tr>
                            <td>{{ $row['grade'] }}</td>
                            <td>{{ $row['section'] }}</td>
                            <td>{{ $row['subject'] }}</td>
                            <td>{{ $row['teacher'] }}</td>
                            <td>{{ $quarters[$quarter] }}</td>
                            <td>{{ $row['method'] }}</td>
                            <td>{{ $row['status'] }} · {{ $row['done'] }}/{{ $row['total'] }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7">No class schedules are available for the current academic year.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/directory-modern.css') }}?v=20260914m">
@endpush
