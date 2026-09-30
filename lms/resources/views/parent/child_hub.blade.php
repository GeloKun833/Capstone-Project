@extends('layouts.master')
@section('content')
@php
    $tabs = [
        'overview' => ['label' => 'Overview', 'icon' => 'fa-chart-pie'],
        'grades' => ['label' => 'Grades', 'icon' => 'fa-clipboard-list'],
        'attendance' => ['label' => 'Attendance', 'icon' => 'fa-user-check'],
        'activities' => ['label' => 'Activities', 'icon' => 'fa-tasks'],
        'assignments' => ['label' => 'Assignments', 'icon' => 'fa-file-alt'],
        'feedback' => ['label' => 'Teacher Feedback', 'icon' => 'fa-comment-dots'],
    ];

    $attendanceBadge = function (?string $status): string {
        return match ($status) {
            'present' => 'hub-pill hub-pill--success',
            'late' => 'hub-pill hub-pill--warn',
            'excused' => 'hub-pill hub-pill--info',
            default => 'hub-pill hub-pill--danger',
        };
    };

    $statusPill = function (?string $status): string {
        $key = strtolower((string) $status);
        if (in_array($key, ['submitted', 'graded', 'completed', 'passed'], true)) {
            return 'hub-pill hub-pill--success';
        }
        if (in_array($key, ['pending', 'draft', 'to do', 'todo'], true)) {
            return 'hub-pill hub-pill--warn';
        }
        if (in_array($key, ['late', 'overdue', 'missing', 'not submitted'], true)) {
            return 'hub-pill hub-pill--danger';
        }
        return 'hub-pill hub-pill--muted';
    };
@endphp

<div class="page-wrapper">
    <div class="content container-fluid hub-page">
        <div class="hub-hero">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div>
                    <p class="hub-kicker mb-1">My Children</p>
                    <h3 class="hub-title mb-1">{{ $child->full_name }}</h3>
                    <p class="hub-subtitle mb-0">
                        {{ $child->year_level ?: $child->class ?: 'Grade' }}
                        @if($child->sectionLabel())
                            · {{ $child->sectionLabel() }}
                        @endif
                    </p>
                </div>
                @if($children->count() > 1)
                    <div class="hub-switch">
                        <label class="hub-label mb-1">Switch child</label>
                        <select class="form-select hub-select js-hub-switch-child">
                            @foreach($children as $linkedChild)
                                <option
                                    value="{{ route('parent.child.hub', ['childId' => $linkedChild->id, 'tab' => $tab]) }}"
                                    @selected($linkedChild->id === $child->id)
                                >
                                    {{ $linkedChild->full_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif
            </div>
        </div>

        <div class="hub-tabs" role="tablist">
            @foreach($tabs as $key => $meta)
                <a class="hub-tab {{ $tab === $key ? 'is-active' : '' }}"
                   href="{{ route('parent.child.hub', ['childId' => $child->id, 'tab' => $key]) }}">
                    <i class="fas {{ $meta['icon'] }}"></i>
                    <span>{{ $meta['label'] }}</span>
                </a>
            @endforeach
        </div>

        @if(in_array($tab, ['overview', 'grades'], true))
            <form method="GET" action="{{ route('parent.child.hub', $child->id) }}" class="hub-toolbar">
                <input type="hidden" name="tab" value="{{ $tab }}">
                <div>
                    <label class="hub-label">Academic year</label>
                    <select name="academic_year_id" class="form-select hub-select" onchange="this.form.submit()">
                        @foreach($academicYears as $year)
                            <option value="{{ $year->id }}" @selected($academicYear && $academicYear->id == $year->id)>
                                {{ $year->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @if($tab === 'grades' && $academicYear)
                    <a class="btn hub-btn-primary ms-auto"
                       target="_blank"
                       href="{{ route('parent.child.report-card', ['childId' => $child->id, 'academic_year_id' => $academicYear->id]) }}">
                        <i class="fas fa-print me-2"></i>Print report card
                    </a>
                @endif
            </form>
        @endif

        @if($tab === 'overview')
            <div class="row g-3 mb-4">
                <div class="col-md-6 col-xl-3">
                    <div class="hub-stat">
                        <div class="hub-stat-icon" style="background:linear-gradient(135deg,#4facfe,#00f2fe)"><i class="fas fa-percentage"></i></div>
                        <div>
                            <div class="hub-stat-value">{{ number_format($overview['averageGrade'], 1) }}%</div>
                            <div class="hub-stat-label">Average grade</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-xl-3">
                    <div class="hub-stat">
                        <div class="hub-stat-icon" style="background:linear-gradient(135deg,#667eea,#764ba2)"><i class="fas fa-user-check"></i></div>
                        <div>
                            <div class="hub-stat-value">{{ $overview['attendancePercentage'] }}%</div>
                            <div class="hub-stat-label">Attendance rate</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-xl-3">
                    <div class="hub-stat">
                        <div class="hub-stat-icon" style="background:linear-gradient(135deg,#f093fb,#f5576c)"><i class="fas fa-book-open"></i></div>
                        <div>
                            <div class="hub-stat-value">{{ $overview['enrollments']->count() }}</div>
                            <div class="hub-stat-label">Active enrollments</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-xl-3">
                    <div class="hub-stat">
                        <div class="hub-stat-icon" style="background:linear-gradient(135deg,#fa709a,#fee140)"><i class="fas fa-award"></i></div>
                        <div>
                            <div class="hub-stat-value">{{ $overview['currentGpa']->gpa ?? '—' }}</div>
                            <div class="hub-stat-label">Latest GPA</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-lg-6">
                    <div class="hub-card h-100">
                        <div class="hub-card-head">
                            <h5>Recent grades</h5>
                            <a href="{{ route('parent.child.hub', ['childId' => $child->id, 'tab' => 'grades']) }}">View all</a>
                        </div>
                        <div class="hub-card-body">
                            @forelse($overview['recentGrades'] as $grade)
                                <div class="hub-row">
                                    <div>
                                        <div class="hub-row-title">{{ $grade->subject->subject_name ?? 'Subject' }}</div>
                                        <div class="hub-row-meta">Component score</div>
                                    </div>
                                    <strong class="hub-score">{{ number_format($grade->percentage ?? 0, 1) }}%</strong>
                                </div>
                            @empty
                                <div class="hub-empty">No grades posted yet.</div>
                            @endforelse
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="hub-card h-100">
                        <div class="hub-card-head">
                            <h5>Recent attendance</h5>
                            <a href="{{ route('parent.child.hub', ['childId' => $child->id, 'tab' => 'attendance']) }}">View all</a>
                        </div>
                        <div class="hub-card-body">
                            @forelse($overview['recentAttendance'] as $record)
                                <div class="hub-row">
                                    <div>
                                        <div class="hub-row-title">{{ $record->subject->subject_name ?? 'Subject' }}</div>
                                        <div class="hub-row-meta">{{ $record->date ? \Carbon\Carbon::parse($record->date)->format('M d, Y') : '—' }}</div>
                                    </div>
                                    <span class="{{ $attendanceBadge($record->status) }}">{{ ucfirst($record->status) }}</span>
                                </div>
                            @empty
                                <div class="hub-empty">No attendance records yet.</div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        @endif

        @if($tab === 'grades')
            <div class="hub-card mb-4">
                <div class="hub-card-head">
                    <h5>Observed values</h5>
                    <span class="hub-hint">AO · SO · RO</span>
                </div>
                <div class="hub-card-body table-responsive">
                    <table class="hub-table">
                        <thead>
                            <tr>
                                <th>Core values</th>
                                <th>Behavior statements</th>
                                <th class="text-center">Q1</th>
                                <th class="text-center">Q2</th>
                                <th class="text-center">Q3</th>
                                <th class="text-center">Q4</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($observedIndicators ?? [] as $core => $items)
                                @foreach($items as $i => $indicator)
                                    @php $rating = ($observedRatings ?? collect())->get($indicator->id); @endphp
                                    <tr>
                                        @if($i === 0)
                                            <td rowspan="{{ $items->count() }}" class="hub-core">{{ $core }}</td>
                                        @endif
                                        <td>{{ $indicator->statement }}</td>
                                        @foreach(['quarter_1','quarter_2','quarter_3','quarter_4'] as $qf)
                                            <td class="text-center">{{ optional($rating)->{$qf} ?: '—' }}</td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            @empty
                                <tr><td colspan="6" class="hub-empty">No observed values yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="hub-card">
                <div class="hub-card-head">
                    <h5>Learning progress</h5>
                </div>
                <div class="hub-card-body table-responsive">
                    <table class="hub-table">
                        <thead>
                            <tr>
                                <th>Learning area</th>
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
                                    <td class="fw-semibold">{{ $qg->subject->subject_name ?? 'N/A' }}</td>
                                    <td class="text-center">{{ $qg->quarter_1 !== null ? number_format($qg->quarter_1, 0) : '—' }}</td>
                                    <td class="text-center">{{ $qg->quarter_2 !== null ? number_format($qg->quarter_2, 0) : '—' }}</td>
                                    <td class="text-center">{{ $qg->quarter_3 !== null ? number_format($qg->quarter_3, 0) : '—' }}</td>
                                    <td class="text-center">{{ $qg->quarter_4 !== null ? number_format($qg->quarter_4, 0) : '—' }}</td>
                                    <td class="text-center"><strong>{{ $qg->final_grade !== null ? number_format($qg->final_grade, 0) : '—' }}</strong></td>
                                    <td class="text-center">
                                        <span class="hub-pill hub-pill--muted">
                                            {{ $qg->remarks ?? \App\Services\ReportCardService::remarkForScore($qg->final_grade !== null ? (float) $qg->final_grade : null) }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="hub-empty">No quarterly grades posted yet.</td></tr>
                            @endforelse
                            @if($quarterlyGrades->isNotEmpty())
                                <tr class="hub-total">
                                    <td class="text-end">General average</td>
                                    @foreach(['q1','q2','q3','q4','final'] as $k)
                                        <td class="text-center">
                                            {{ isset($generalAverages[$k]) && $generalAverages[$k] !== null ? number_format($generalAverages[$k], 2) : '—' }}
                                        </td>
                                    @endforeach
                                    <td class="text-center">
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
            <div class="row g-3 mb-4">
                <div class="col-md-6 col-xl-3"><div class="hub-stat"><div class="hub-stat-icon" style="background:#16a34a"><i class="fas fa-check"></i></div><div><div class="hub-stat-value">{{ $attendanceSummary['present'] }}</div><div class="hub-stat-label">Present</div></div></div></div>
                <div class="col-md-6 col-xl-3"><div class="hub-stat"><div class="hub-stat-icon" style="background:#dc2626"><i class="fas fa-times"></i></div><div><div class="hub-stat-value">{{ $attendanceSummary['absent'] }}</div><div class="hub-stat-label">Absent</div></div></div></div>
                <div class="col-md-6 col-xl-3"><div class="hub-stat"><div class="hub-stat-icon" style="background:#6366f1"><i class="fas fa-list"></i></div><div><div class="hub-stat-value">{{ $attendanceSummary['total'] }}</div><div class="hub-stat-label">Total records</div></div></div></div>
                <div class="col-md-6 col-xl-3"><div class="hub-stat"><div class="hub-stat-icon" style="background:linear-gradient(135deg,#667eea,#764ba2)"><i class="fas fa-chart-line"></i></div><div><div class="hub-stat-value">{{ $attendanceSummary['percentage'] }}%</div><div class="hub-stat-label">Attendance rate</div></div></div></div>
            </div>
            <div class="hub-card">
                <div class="hub-card-head"><h5>Attendance log</h5></div>
                <div class="hub-card-body table-responsive">
                    <table class="hub-table">
                        <thead><tr><th>Date</th><th>Subject</th><th>Status</th><th>Remarks</th></tr></thead>
                        <tbody>
                            @forelse($attendance as $record)
                                <tr>
                                    <td>{{ $record->date ? \Carbon\Carbon::parse($record->date)->format('M d, Y') : '—' }}</td>
                                    <td>{{ $record->subject->subject_name ?? 'N/A' }}</td>
                                    <td><span class="{{ $attendanceBadge($record->status) }}">{{ ucfirst($record->status) }}</span></td>
                                    <td>{{ $record->remarks ?? '—' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="hub-empty">No attendance records for this period.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        @if($tab === 'activities')
            <div class="hub-card">
                <div class="hub-card-head"><h5>Assigned activities</h5></div>
                <div class="hub-card-body">
                    @forelse($activities as $activity)
                        @php $submission = $submissions->firstWhere('activity_id', $activity->id); @endphp
                        <div class="hub-row">
                            <div>
                                <div class="hub-row-title">{{ $activity->title }}</div>
                                <div class="hub-row-meta">
                                    {{ $activity->lesson->subject->subject_name ?? 'Subject' }}
                                    · Due {{ $activity->due_date ? \Carbon\Carbon::parse($activity->due_date)->format('M d, Y') : '—' }}
                                </div>
                            </div>
                            <span class="{{ $statusPill($submission ? $submission->status : 'Not submitted') }}">
                                {{ $submission ? ucfirst($submission->status) : 'Not submitted' }}
                            </span>
                        </div>
                    @empty
                        <div class="hub-empty">No activities found.</div>
                    @endforelse
                </div>
            </div>
        @endif

        @if($tab === 'assignments')
            <div class="hub-card">
                <div class="hub-card-head"><h5>Assignment submissions</h5></div>
                <div class="hub-card-body table-responsive">
                    <table class="hub-table">
                        <thead>
                            <tr>
                                <th>Assignment</th>
                                <th>Subject</th>
                                <th>Submitted</th>
                                <th>Score</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($assignmentSubmissions as $submission)
                                <tr>
                                    <td class="fw-semibold">{{ $submission->assignment->title ?? 'Assignment' }}</td>
                                    <td>{{ $submission->assignment->subject->subject_name ?? 'N/A' }}</td>
                                    <td>{{ $submission->submitted_at?->format('M d, Y h:i A') ?? '—' }}</td>
                                    <td>{{ $submission->score !== null ? number_format($submission->score, 2) . ' / ' . number_format($submission->max_score ?? 100, 0) : '—' }}</td>
                                    <td><span class="{{ $statusPill($submission->status ?? 'pending') }}">{{ ucfirst($submission->status ?? 'pending') }}</span></td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="hub-empty">No assignment submissions yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        @if($tab === 'feedback')
            <div class="hub-card">
                <div class="hub-card-head"><h5>Teacher feedback</h5></div>
                <div class="hub-card-body">
                    @forelse($feedbackItems as $item)
                        <article class="hub-feedback">
                            <div class="d-flex justify-content-between gap-3 flex-wrap">
                                <div>
                                    <span class="hub-pill hub-pill--muted">{{ $item->type }}</span>
                                    <h6 class="hub-feedback-title">{{ $item->title }}</h6>
                                    <div class="hub-row-meta">{{ $item->subject }}</div>
                                </div>
                                <small class="text-muted">{{ $item->date ? \Carbon\Carbon::parse($item->date)->format('M d, Y') : '' }}</small>
                            </div>
                            @if($item->score !== null)
                                <p class="hub-feedback-score mb-2">Score: {{ $item->score }}@if($item->max_score) / {{ $item->max_score }}@endif</p>
                            @endif
                            <p class="mb-0">{{ $item->feedback ?: 'No written feedback provided.' }}</p>
                        </article>
                    @empty
                        <div class="hub-empty">No teacher feedback available yet.</div>
                    @endforelse
                </div>
            </div>
        @endif
    </div>
</div>

@push('styles')
<style>
.hub-page { --hub: #667eea; --hub-2: #764ba2; color: #0f172a; }
.hub-hero {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: #fff;
    border-radius: 20px;
    padding: 1.5rem 1.75rem;
    margin-bottom: 1.25rem;
    box-shadow: 0 18px 40px rgba(102, 126, 234, 0.28);
}
.hub-kicker { font-size: .75rem; letter-spacing: .08em; text-transform: uppercase; opacity: .85; font-weight: 600; }
.hub-title { font-weight: 700; letter-spacing: -.02em; }
.hub-subtitle { opacity: .9; }
.hub-switch { min-width: 220px; }
.hub-label { font-size: .72rem; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; color: inherit; opacity: .8; }
.hub-hero .hub-label { color: #fff; }
.hub-select { border-radius: 12px; border: 1px solid #e2e8f0; }
.hub-hero .hub-select { background: rgba(255,255,255,.95); }
.hub-tabs {
    display: flex; flex-wrap: wrap; gap: .5rem;
    background: #fff; border-radius: 16px; padding: .55rem;
    box-shadow: 0 8px 24px rgba(15,23,42,.06); margin-bottom: 1.25rem;
}
.hub-tab {
    display: inline-flex; align-items: center; gap: .45rem;
    padding: .55rem 1rem; border-radius: 999px; color: #475569;
    font-weight: 600; text-decoration: none; font-size: .9rem;
}
.hub-tab:hover { background: #f1f5f9; color: #334155; }
.hub-tab.is-active { background: linear-gradient(135deg, #667eea, #764ba2); color: #fff; }
.hub-toolbar {
    display: flex; flex-wrap: wrap; align-items: end; gap: 1rem;
    background: #fff; border-radius: 16px; padding: 1rem 1.15rem;
    box-shadow: 0 8px 24px rgba(15,23,42,.06); margin-bottom: 1.25rem;
}
.hub-btn-primary {
    background: linear-gradient(135deg, #667eea, #764ba2); border: 0; color: #fff;
    border-radius: 12px; padding: .6rem 1rem; font-weight: 600;
}
.hub-stat {
    background: #fff; border-radius: 16px; padding: 1.1rem 1.2rem;
    display: flex; gap: 1rem; align-items: center;
    box-shadow: 0 8px 24px rgba(15,23,42,.06); height: 100%;
}
.hub-stat-icon {
    width: 52px; height: 52px; border-radius: 14px; color: #fff;
    display: flex; align-items: center; justify-content: center; font-size: 1.2rem;
}
.hub-stat-value { font-size: 1.45rem; font-weight: 800; line-height: 1.1; }
.hub-stat-label { color: #64748b; font-size: .82rem; }
.hub-card {
    background: #fff; border-radius: 18px; overflow: hidden;
    box-shadow: 0 8px 24px rgba(15,23,42,.06);
}
.hub-card-head {
    display: flex; justify-content: space-between; align-items: center;
    padding: 1rem 1.2rem; border-bottom: 1px solid #f1f5f9;
}
.hub-card-head h5 { margin: 0; font-weight: 700; }
.hub-card-head a { font-size: .85rem; font-weight: 600; color: #667eea; text-decoration: none; }
.hub-card-body { padding: 1rem 1.2rem 1.15rem; }
.hub-row {
    display: flex; justify-content: space-between; align-items: center; gap: 1rem;
    padding: .85rem 0; border-bottom: 1px solid #f1f5f9;
}
.hub-row:last-child { border-bottom: 0; }
.hub-row-title { font-weight: 600; }
.hub-row-meta { color: #64748b; font-size: .8rem; }
.hub-score { color: #4f46e5; }
.hub-empty { text-align: center; color: #94a3b8; padding: 1.5rem .5rem; }
.hub-table { width: 100%; margin: 0; }
.hub-table th {
    font-size: .75rem; text-transform: uppercase; letter-spacing: .04em;
    color: #64748b; font-weight: 700; border-bottom: 1px solid #e2e8f0;
    padding: .65rem .5rem; background: #f8fafc;
}
.hub-table td { padding: .75rem .5rem; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }
.hub-core { font-weight: 700; background: #f8fafc; }
.hub-total td { background: #eef2ff; font-weight: 700; }
.hub-pill {
    display: inline-flex; align-items: center; border-radius: 999px;
    padding: .2rem .65rem; font-size: .75rem; font-weight: 700;
}
.hub-pill--success { background: #dcfce7; color: #166534; }
.hub-pill--danger { background: #fee2e2; color: #991b1b; }
.hub-pill--warn { background: #fef3c7; color: #92400e; }
.hub-pill--info { background: #e0f2fe; color: #075985; }
.hub-pill--muted { background: #f1f5f9; color: #475569; }
.hub-hint { font-size: .75rem; color: #64748b; }
.hub-feedback {
    border: 1px solid #eef2ff; border-left: 4px solid #667eea;
    border-radius: 14px; padding: 1rem; margin-bottom: .85rem; background: #fafafe;
}
.hub-feedback:last-child { margin-bottom: 0; }
.hub-feedback-title { margin: .45rem 0 .15rem; font-weight: 700; }
.hub-feedback-score { font-weight: 600; color: #4338ca; }
@media (max-width: 767px) {
    .hub-title { font-size: 1.35rem; }
    .hub-tabs { overflow-x: auto; flex-wrap: nowrap; }
}
</style>
@endpush

@push('scripts')
<script>
    document.querySelectorAll('.js-hub-switch-child').forEach(function (select) {
        select.addEventListener('change', function () {
            window.location.href = this.value;
        });
    });
</script>
@endpush
@endsection
