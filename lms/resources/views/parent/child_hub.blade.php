@extends('layouts.master')
@section('content')
@php
    $tabs = [
        'overview' => ['label' => 'Overview', 'icon' => 'fa-home'],
        'grades' => ['label' => 'Grades', 'icon' => 'fa-clipboard-list'],
        'attendance' => ['label' => 'Attendance', 'icon' => 'fa-user-check'],
        'activities' => ['label' => 'Activities', 'icon' => 'fa-tasks'],
        'assignments' => ['label' => 'Assignments', 'icon' => 'fa-file-alt'],
        'feedback' => ['label' => 'Teacher Feedback', 'icon' => 'fa-comment-dots'],
    ];
@endphp

<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title"><i class="fas fa-user me-2"></i>{{ $child->full_name }}</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('parent.index') }}">Parent Portal</a></li>
                        <li class="breadcrumb-item active">{{ $child->first_name }}</li>
                    </ul>
                </div>
            </div>
        </div>

        @if($children->count() > 1)
            <div class="card card-table comman-shadow mb-3">
                <div class="card-body py-3">
                    <form method="GET" action="{{ route('parent.child.hub', $child->id) }}" class="d-flex align-items-center gap-3 flex-wrap">
                        <input type="hidden" name="tab" value="{{ $tab }}">
                        <label class="form-label mb-0 fw-semibold">Switch child:</label>
                        <select name="childId" class="form-control form-select" style="max-width:280px;" onchange="window.location.href='{{ url('/parent/child') }}/' + this.value + '?tab={{ $tab }}'">
                            @foreach($children as $linkedChild)
                                <option value="{{ $linkedChild->id }}" @selected($linkedChild->id === $child->id)>
                                    {{ $linkedChild->full_name }}
                                </option>
                            @endforeach
                        </select>
                    </form>
                </div>
            </div>
        @endif

        <ul class="nav nav-pills parent-hub-tabs mb-4 flex-wrap gap-2">
            @foreach($tabs as $key => $meta)
                <li class="nav-item">
                    <a class="nav-link {{ $tab === $key ? 'active' : '' }}"
                       href="{{ route('parent.child.hub', ['childId' => $child->id, 'tab' => $key]) }}">
                        <i class="fas {{ $meta['icon'] }} me-1"></i>{{ $meta['label'] }}
                    </a>
                </li>
            @endforeach
        </ul>

        @if(in_array($tab, ['overview', 'grades'], true))
            <div class="card card-table comman-shadow mb-4">
                <div class="card-body">
                    <form method="GET" action="{{ route('parent.child.hub', $child->id) }}" class="row g-3 align-items-end">
                        <input type="hidden" name="tab" value="{{ $tab }}">
                        <div class="col-md-4">
                            <label class="form-label">Academic Year</label>
                            <select name="academic_year_id" class="form-control form-select" onchange="this.form.submit()">
                                @foreach($academicYears as $year)
                                    <option value="{{ $year->id }}" @selected($academicYear && $academicYear->id == $year->id)>
                                        {{ $year->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </form>
                </div>
            </div>
        @endif

        @if($tab === 'overview')
            <div class="row">
                <div class="col-md-3 mb-3"><div class="card text-center p-3"><h4 class="text-primary mb-0">{{ number_format($overview['averageGrade'], 1) }}%</h4><small class="text-muted">Average Grade</small></div></div>
                <div class="col-md-3 mb-3"><div class="card text-center p-3"><h4 class="text-success mb-0">{{ $overview['attendancePercentage'] }}%</h4><small class="text-muted">Attendance Rate</small></div></div>
                <div class="col-md-3 mb-3"><div class="card text-center p-3"><h4 class="text-info mb-0">{{ $overview['enrollments']->count() }}</h4><small class="text-muted">Active Enrollments</small></div></div>
                <div class="col-md-3 mb-3"><div class="card text-center p-3"><h4 class="text-warning mb-0">{{ $overview['currentGpa']->gpa ?? '—' }}</h4><small class="text-muted">Latest GPA</small></div></div>
            </div>

            <div class="row">
                <div class="col-lg-6 mb-4">
                    <div class="card card-table comman-shadow h-100">
                        <div class="card-header"><h5 class="mb-0">Recent Grades</h5></div>
                        <div class="card-body">
                            @forelse($overview['recentGrades'] as $grade)
                                <div class="d-flex justify-content-between border-bottom py-2">
                                    <span>{{ $grade->subject->subject_name ?? 'Subject' }}</span>
                                    <strong>{{ number_format($grade->percentage ?? 0, 1) }}%</strong>
                                </div>
                            @empty
                                <p class="text-muted mb-0">No grades yet.</p>
                            @endforelse
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 mb-4">
                    <div class="card card-table comman-shadow h-100">
                        <div class="card-header"><h5 class="mb-0">Recent Attendance</h5></div>
                        <div class="card-body">
                            @forelse($overview['recentAttendance'] as $record)
                                <div class="d-flex justify-content-between border-bottom py-2">
                                    <span>{{ $record->date?->format('M d, Y') }} — {{ $record->subject->subject_name ?? 'Subject' }}</span>
                                    <span class="badge bg-{{ $record->status === 'present' ? 'success' : 'danger' }}">{{ ucfirst($record->status) }}</span>
                                </div>
                            @empty
                                <p class="text-muted mb-0">No attendance records yet.</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        @endif

        @if($tab === 'grades')
            <div class="mb-3 text-end">
                @if($academicYear)
                    <a class="btn btn-primary" target="_blank"
                       href="{{ route('parent.child.report-card', ['childId' => $child->id, 'academic_year_id' => $academicYear->id]) }}">
                        <i class="fas fa-print me-1"></i> Printable Report Card
                    </a>
                @endif
            </div>

            <div class="card card-table comman-shadow mb-4">
                <div class="card-header bg-success text-white"><h5 class="mb-0 text-uppercase">Report on Learner&rsquo;s Observed Values</h5></div>
                <div class="card-body table-responsive">
                    <table class="table table-bordered mb-0">
                        <thead>
                            <tr>
                                <th>Core Values</th>
                                <th>Behavior Statements</th>
                                <th class="text-center">1</th>
                                <th class="text-center">2</th>
                                <th class="text-center">3</th>
                                <th class="text-center">4</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($observedIndicators ?? [] as $core => $items)
                                @foreach($items as $i => $indicator)
                                    @php $rating = ($observedRatings ?? collect())->get($indicator->id); @endphp
                                    <tr>
                                        @if($i === 0)
                                            <td rowspan="{{ $items->count() }}" class="fw-bold">{{ $core }}</td>
                                        @endif
                                        <td>{{ $indicator->statement }}</td>
                                        @foreach(['quarter_1','quarter_2','quarter_3','quarter_4'] as $qf)
                                            <td class="text-center">{{ optional($rating)->{$qf} ?: '—' }}</td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            @empty
                                <tr><td colspan="6" class="text-center text-muted py-3">No observed values yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                    <p class="small text-muted mt-2 mb-0">AO = Always Observed | SO = Sometimes Observed | RO = Rarely Observed</p>
                </div>
            </div>

            <div class="card card-table comman-shadow mb-4">
                <div class="card-header bg-primary text-white"><h5 class="mb-0 text-uppercase">Report on Learning Progress and Achievement</h5></div>
                <div class="card-body table-responsive">
                    <table class="table table-bordered table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Learning Areas</th>
                                <th class="text-center">Q1</th>
                                <th class="text-center">Q2</th>
                                <th class="text-center">Q3</th>
                                <th class="text-center">Q4</th>
                                <th class="text-center">Final</th>
                                <th class="text-center">Remarks</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($quarterlyGrades as $qg)
                                <tr>
                                    <td>{{ $qg->subject->subject_name ?? 'N/A' }}</td>
                                    <td class="text-center">{{ $qg->quarter_1 !== null ? number_format($qg->quarter_1, 0) : '—' }}</td>
                                    <td class="text-center">{{ $qg->quarter_2 !== null ? number_format($qg->quarter_2, 0) : '—' }}</td>
                                    <td class="text-center">{{ $qg->quarter_3 !== null ? number_format($qg->quarter_3, 0) : '—' }}</td>
                                    <td class="text-center">{{ $qg->quarter_4 !== null ? number_format($qg->quarter_4, 0) : '—' }}</td>
                                    <td class="text-center"><strong>{{ $qg->final_grade !== null ? number_format($qg->final_grade, 0) : '—' }}</strong></td>
                                    <td class="text-center">
                                        {{ $qg->remarks ?? \App\Services\ReportCardService::remarkForScore($qg->final_grade !== null ? (float)$qg->final_grade : null) }}
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="text-center text-muted py-4">No quarterly grades posted yet.</td></tr>
                            @endforelse
                            @if($quarterlyGrades->isNotEmpty())
                                <tr class="table-secondary">
                                    <td class="fw-bold text-end">General Average</td>
                                    @foreach(['q1','q2','q3','q4','final'] as $k)
                                        <td class="text-center fw-bold">
                                            {{ isset($generalAverages[$k]) && $generalAverages[$k] !== null ? number_format($generalAverages[$k], 2) : '—' }}
                                        </td>
                                    @endforeach
                                    <td class="text-center fw-bold">
                                        {{ \App\Services\ReportCardService::remarkForScore($generalAverages['final'] ?? null) }}
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        @if($tab === 'attendance')
            <div class="row mb-3">
                <div class="col-md-3"><div class="card p-3 text-center"><strong>{{ $attendanceSummary['present'] }}</strong><div class="text-muted small">Present</div></div></div>
                <div class="col-md-3"><div class="card p-3 text-center"><strong>{{ $attendanceSummary['absent'] }}</strong><div class="text-muted small">Absent</div></div></div>
                <div class="col-md-3"><div class="card p-3 text-center"><strong>{{ $attendanceSummary['total'] }}</strong><div class="text-muted small">Total Records</div></div></div>
                <div class="col-md-3"><div class="card p-3 text-center"><strong>{{ $attendanceSummary['percentage'] }}%</strong><div class="text-muted small">Rate</div></div></div>
            </div>
            <div class="card card-table comman-shadow">
                <div class="card-body table-responsive">
                    <table class="table table-hover mb-0">
                        <thead><tr><th>Date</th><th>Subject</th><th>Status</th><th>Remarks</th></tr></thead>
                        <tbody>
                            @forelse($attendance as $record)
                                <tr>
                                    <td>{{ $record->date?->format('M d, Y') }}</td>
                                    <td>{{ $record->subject->subject_name ?? 'N/A' }}</td>
                                    <td><span class="badge bg-{{ $record->status === 'present' ? 'success' : 'danger' }}">{{ ucfirst($record->status) }}</span></td>
                                    <td>{{ $record->remarks ?? '—' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-muted py-4">No attendance records for this period.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        @if($tab === 'activities')
            <div class="card card-table comman-shadow mb-4">
                <div class="card-header"><h5 class="mb-0">Assigned Activities</h5></div>
                <div class="card-body table-responsive">
                    <table class="table table-hover mb-0">
                        <thead><tr><th>Activity</th><th>Subject</th><th>Due Date</th><th>Status</th></tr></thead>
                        <tbody>
                            @forelse($activities as $activity)
                                @php $submission = $submissions->firstWhere('activity_id', $activity->id); @endphp
                                <tr>
                                    <td>{{ $activity->title }}</td>
                                    <td>{{ $activity->lesson->subject->subject_name ?? 'N/A' }}</td>
                                    <td>{{ $activity->due_date ? \Carbon\Carbon::parse($activity->due_date)->format('M d, Y') : '—' }}</td>
                                    <td>{{ $submission ? ucfirst($submission->status) : 'Not submitted' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-muted py-4">No activities found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        @if($tab === 'assignments')
            <div class="card card-table comman-shadow">
                <div class="card-header"><h5 class="mb-0">Assignment Submissions</h5></div>
                <div class="card-body table-responsive">
                    <table class="table table-hover mb-0">
                        <thead><tr><th>Assignment</th><th>Subject</th><th>Submitted</th><th>Score</th><th>Status</th></tr></thead>
                        <tbody>
                            @forelse($assignmentSubmissions as $submission)
                                <tr>
                                    <td>{{ $submission->assignment->title ?? 'Assignment' }}</td>
                                    <td>{{ $submission->assignment->subject->subject_name ?? 'N/A' }}</td>
                                    <td>{{ $submission->submitted_at?->format('M d, Y h:i A') ?? '—' }}</td>
                                    <td>{{ $submission->score !== null ? number_format($submission->score, 2) . ' / ' . number_format($submission->max_score ?? 100, 0) : '—' }}</td>
                                    <td>{{ ucfirst($submission->status ?? 'pending') }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center text-muted py-4">No assignment submissions yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        @if($tab === 'feedback')
            <div class="card card-table comman-shadow">
                <div class="card-header bg-success text-white"><h5 class="mb-0">Teacher Feedback & Graded Work</h5></div>
                <div class="card-body">
                    @forelse($feedbackItems as $item)
                        <div class="border rounded p-3 mb-3">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div>
                                    <span class="badge bg-secondary me-2">{{ $item->type }}</span>
                                    <strong>{{ $item->title }}</strong>
                                    <div class="text-muted small">{{ $item->subject }}</div>
                                </div>
                                <small class="text-muted">{{ $item->date ? \Carbon\Carbon::parse($item->date)->format('M d, Y') : '' }}</small>
                            </div>
                            @if($item->score !== null)
                                <p class="mb-2"><strong>Score:</strong> {{ $item->score }}@if($item->max_score) / {{ $item->max_score }}@endif</p>
                            @endif
                            <p class="mb-0">{{ $item->feedback ?: 'No written feedback provided.' }}</p>
                        </div>
                    @empty
                        <p class="text-muted text-center py-4 mb-0">No teacher feedback available yet.</p>
                    @endforelse
                </div>
            </div>
        @endif
    </div>
</div>

@push('styles')
<style>
.parent-hub-tabs .nav-link {
    border-radius: 999px;
    padding: 0.55rem 1rem;
    color: #475569;
    font-weight: 500;
}
.parent-hub-tabs .nav-link.active {
    background: #2563eb;
    color: #fff;
}
</style>
@endpush
@endsection
