@extends('layouts.master')
@section('content')
<div class="page-wrapper">
    <div class="content container-fluid dir-page att-page">
        <div class="page-header">
            <div class="row align-items-start">
                <div class="col">
                    <h3 class="page-title mb-1">Attendance Report</h3>
                    <p class="dir-subtitle">
                        {{ $academicYear->name }} · {{ $section->grade_level ? $section->grade_level.' · ' : '' }}{{ $section->name }} · {{ $subject->subject_name }}
                    </p>
                </div>
                <div class="col-auto">
                    <a href="{{ route('attendance.index', ['section_id' => $sectionId, 'subject_id' => $subjectId]) }}" class="btn btn-outline-secondary dir-btn">Back to attendance</a>
                </div>
            </div>
        </div>

        <div class="att-report-switch">
            <a href="{{ route('attendance.report', ['section_id' => $sectionId, 'subject_id' => $subjectId, 'period' => 'weekly']) }}" class="att-report-tab {{ $period === 'weekly' ? 'is-active' : '' }}">Weekly</a>
            <a href="{{ route('attendance.report', ['section_id' => $sectionId, 'subject_id' => $subjectId, 'period' => 'monthly']) }}" class="att-report-tab {{ $period === 'monthly' ? 'is-active' : '' }}">Monthly</a>
            <span class="att-report-range">
                @if($period === 'weekly')
                    Week of {{ $rangeStart->format('M j') }} – {{ $rangeEnd->format('M j, Y') }}
                @else
                    {{ $rangeStart->format('F Y') }}
                @endif
            </span>
        </div>

        @if(count($rows) === 0)
            <div class="att-empty">
                <h5>No students in this section</h5>
                <p>No students are enrolled in this class for {{ $academicYear->name }}.</p>
            </div>
        @else
            <div class="table-responsive att-table-wrap">
                <table class="table att-table att-report-table mb-0">
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Student Number</th>
                            @foreach($days as $day)
                                <th class="text-center">{{ $period === 'weekly' ? $day->format('D j') : $day->format('j') }}</th>
                            @endforeach
                            <th class="text-center">Present</th>
                            <th class="text-center">Absent</th>
                            <th class="text-center">Rate</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($rows as $row)
                            <tr>
                                <td><strong>{{ $row['student']->last_name }}, {{ $row['student']->first_name }}</strong></td>
                                <td>{{ $row['student']->admission_id ?: '—' }}</td>
                                @foreach($days as $day)
                                    @php $record = $row['cells'][$day->toDateString()] ?? null; @endphp
                                    <td class="text-center">
                                        @if($record?->status === 'present')
                                            <span class="att-mark att-mark-present">{{ $record->time_in ? \Carbon\Carbon::parse($record->time_in)->format('g:i A') : 'Present' }}</span>
                                        @elseif($record?->status === 'absent')
                                            <span class="att-mark att-mark-absent">Absent</span>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                @endforeach
                                <td class="text-center">{{ $row['present'] }}</td>
                                <td class="text-center">{{ $row['absent'] }}</td>
                                <td class="text-center">{{ number_format($row['rate'], 1) }}%</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/directory-modern.css') }}?v=20260914m">
<style>
.att-page .page-header { margin-bottom: 0.75rem; }
.att-report-switch { display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap; margin-bottom: 0.85rem; }
.att-report-tab {
    display: inline-block;
    padding: 0.4rem 0.85rem;
    border-radius: 999px;
    border: 1px solid #e7e5e4;
    background: #fff;
    color: #44403c;
    font-weight: 700;
    font-size: 0.85rem;
}
.att-report-tab.is-active { background: #3d5ee1; border-color: #3d5ee1; color: #fff; }
.att-report-range { color: #78716c; font-size: 0.88rem; margin-left: 0.25rem; }
.att-table-wrap { max-height: 72vh; border: 1px solid #e7e5e4; border-radius: 12px; background: #fff; }
.att-table { margin: 0; }
.att-table thead th {
    position: sticky;
    top: 0;
    z-index: 1;
    background: #fafaf9;
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.03em;
    color: #57534e;
    border-bottom: 1px solid #e7e5e4;
    white-space: nowrap;
}
.att-report-table td:first-child,
.att-report-table th:first-child { position: sticky; left: 0; background: #fff; z-index: 1; }
.att-report-table thead th:first-child { z-index: 2; background: #fafaf9; }
.att-mark { font-size: 0.75rem; font-weight: 700; white-space: nowrap; }
.att-mark-present { color: #166534; }
.att-mark-absent { color: #b91c1c; }
.att-empty {
    background: #fff;
    border: 1px dashed #d6d3d1;
    border-radius: 12px;
    padding: 1.5rem 1rem;
    text-align: center;
}
.att-empty h5 { margin: 0 0 0.25rem; font-size: 1rem; }
.att-empty p { margin: 0; color: #78716c; }
</style>
@endpush
