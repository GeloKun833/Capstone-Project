@extends('layouts.master')

@section('content')
@php
    $hubRoute = $routePrefix === 'admin' ? 'admin.grading.performance-hub' : 'teacher.grading.performance-hub';
    $resolveRoute = $routePrefix === 'admin' ? 'admin.grading.resolve-alert' : 'teacher.grading.resolve-alert';
    $exportRoute = $routePrefix === 'admin' ? 'admin.grading.export-gpa' : 'teacher.grading.export-gpa';
    $clearQuery = array_filter([
        'semester_id' => $selectedSemesterId,
    ]);
    $filtersActive = $search !== '' || $selectedGrade !== '' || $selectedSectionId || $selectedSubjectId || $performanceStatus !== '';
    $advancedOpen = (bool) ($selectedSubjectId || $performanceStatus !== '');
    $chartPayload = $charts ?? [
        'distribution_labels' => [],
        'distribution_values' => [],
        'grade_labels' => [],
        'grade_values' => [],
        'quarter_labels' => [],
        'quarter_values' => [],
        'subject_labels' => [],
        'subject_values' => [],
    ];
    $passing = \App\Support\AcademicThresholds::PASSING_PERCENTAGE;
    $attendanceLine = \App\Support\AcademicThresholds::ATTENDANCE_CONCERN_PERCENTAGE;
    $absenceLine = \App\Support\AcademicThresholds::RECURRING_ABSENCE_COUNT;
    $attendancePayload = $attendanceSummary ?? [
        'months' => [],
        'month_rates' => [],
        'grades' => [],
        'grade_absences' => [],
    ];
    $trendLabels = is_array($analytics) ? ($analytics['trend_labels'] ?? []) : [];
    $trendValues = is_array($analytics) ? ($analytics['trend_values'] ?? []) : [];
@endphp

<div class="page-wrapper">
    <div class="content container-fluid hub-page">
        <div class="hub-header">
            <div>
                <h3 class="hub-title">Performance Hub</h3>
                <p class="hub-lead">Monitor academic performance, attendance, and student progress.</p>
            </div>
            <div class="hub-header-meta">
                <div class="hub-year">
                    <span>Academic year</span>
                    <strong>{{ $selectedYear->name ?? 'Not set' }}</strong>
                </div>
                <div class="hub-year">
                    <span>Last updated</span>
                    <strong>
                        @if($lastUpdated)
                            {{ \Carbon\Carbon::parse($lastUpdated)->timezone('Asia/Manila')->format('M j, Y g:i A') }}
                        @else
                            Not available
                        @endif
                    </strong>
                </div>
                <a class="btn btn-outline-secondary hub-btn" href="{{ request()->fullUrl() }}">Refresh</a>
                <a class="btn btn-primary hub-btn" href="{{ route($exportRoute, $clearQuery + ['section_id' => $selectedSectionId, 'format' => 'pdf']) }}">Export PDF</a>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif
        @if($loadError)
            <div class="alert alert-danger d-flex justify-content-between align-items-center flex-wrap gap-2">
                <span>{{ $loadError }}</span>
                <a class="btn btn-sm btn-outline-danger" href="{{ request()->fullUrl() }}">Try again</a>
            </div>
        @endif
        @if(! $selectedYear)
            <div class="alert alert-warning">
                The admin has not set a current academic year, so performance records are not shown.
            </div>
        @elseif($semesters->isEmpty())
            <div class="alert alert-warning">
                The current academic year has no semester yet, so performance records are not shown.
            </div>
        @endif

        <form id="hubFilters" method="GET" action="{{ route($hubRoute) }}" class="hub-card hub-toolbar">
            <input type="hidden" name="student_id" value="{{ $selectedStudentId }}">

            <label class="hub-search">
                <span class="visually-hidden">Search students</span>
                <i class="fas fa-search" aria-hidden="true"></i>
                <input type="search" name="q" value="{{ $search }}" maxlength="80" autocomplete="off"
                       placeholder="Search name, student number, grade, section, or subject"
                       aria-describedby="hubResultCount">
            </label>

            <div class="hub-filters">
                <label>
                    Academic year
                    <input type="text" value="{{ $selectedYear->name ?? 'No current academic year' }}" readonly>
                    <small>This follows the academic year the admin set as current.</small>
                </label>
                <label>
                    Grading period
                    <select name="semester_id">
                        @forelse($semesters as $semester)
                            <option value="{{ $semester->id }}" @selected((int) $selectedSemesterId === (int) $semester->id)>{{ $semester->name }}</option>
                        @empty
                            <option value="">No semesters</option>
                        @endforelse
                    </select>
                </label>
                <label>
                    Grade level
                    <select name="grade_level" id="hubGrade">
                        <option value="">All grades</option>
                        @foreach($gradeLevels as $grade)
                            <option value="{{ $grade }}" @selected($selectedGrade === $grade)>{{ $grade }}</option>
                        @endforeach
                    </select>
                </label>
                <label>
                    Section
                    <select name="section_id" id="hubSection">
                        <option value="">All sections</option>
                        @foreach($sections as $section)
                            <option value="{{ $section->id }}" data-grade="{{ $section->grade_level }}"
                                @selected((int) $selectedSectionId === (int) $section->id)>
                                {{ $section->grade_level ? $section->grade_level.' — ' : '' }}{{ $section->name }}
                            </option>
                        @endforeach
                    </select>
                </label>
            </div>

            <details class="hub-advanced" @if($advancedOpen) open @endif>
                <summary>More filters</summary>
                <div class="hub-filters">
                    <label>
                        Subject
                        <select name="subject_id" id="hubSubject">
                            <option value="">All subjects</option>
                            @foreach($subjects as $subject)
                                <option value="{{ $subject->id }}" @selected((int) $selectedSubjectId === (int) $subject->id)>
                                    {{ $subject->class ? $subject->class.' — ' : '' }}{{ $subject->subject_name }}
                                </option>
                            @endforeach
                        </select>
                    </label>
                    <label>
                        Performance status
                        <select name="performance_status">
                            <option value="" @selected($performanceStatus === '')>All statuses</option>
                            <option value="passed" @selected($performanceStatus === 'passed')>Passed ({{ number_format($passing, 0) }}% and above)</option>
                            <option value="below_passing" @selected($performanceStatus === 'below_passing')>Below passing</option>
                            <option value="incomplete" @selected($performanceStatus === 'incomplete')>Incomplete grades</option>
                            <option value="attendance" @selected($performanceStatus === 'attendance')>Attendance concern</option>
                        </select>
                    </label>
                </div>
            </details>

            <div class="hub-toolbar-foot">
                <p id="hubResultCount" class="hub-count" aria-live="polite">
                    @if($kpis)
                        {{ number_format($matchCount) }} {{ $matchCount === 1 ? 'student matches' : 'students match' }}
                    @else
                        Results appear after an academic year and semester are available.
                    @endif
                </p>
                <p id="hubLoading" class="hub-loading" hidden>Updating results…</p>
                @if($filtersActive)
                    <a class="hub-clear" href="{{ route($hubRoute, $clearQuery) }}">Clear filters</a>
                @endif
            </div>
        </form>

        @if($kpis)
            <section class="hub-kpis" aria-label="Key performance indicators">
                <article class="hub-kpi">
                    <span>Total students</span>
                    <strong>{{ number_format($kpis['students']) }}</strong>
                    <small>In the selected year and filters</small>
                </article>
                <article class="hub-kpi">
                    <span>Average academic performance</span>
                    <strong>{{ $kpis['average'] !== null ? number_format($kpis['average'], 2).'%' : '—' }}</strong>
                    <small>{{ number_format($kpis['graded']) }} students with grades. Passing is {{ number_format($passing, 0) }}%.</small>
                </article>
                <article class="hub-kpi">
                    <span>Attendance rate</span>
                    <strong>{{ $kpis['attendance'] !== null ? number_format($kpis['attendance'], 2).'%' : '—' }}</strong>
                    <small>
                        @if($kpis['attendance_ready'])
                            Present and late, excluding excused marks, inside this year’s dates.
                        @else
                            This year has no start and end dates, so attendance is not mixed in from other years.
                        @endif
                    </small>
                </article>
                <article class="hub-kpi">
                    <span>Below passing</span>
                    <strong>{{ number_format($kpis['below_passing']) }}</strong>
                    <small>General average under {{ number_format($passing, 0) }}%</small>
                </article>
                <article class="hub-kpi">
                    <span>Incomplete grades</span>
                    <strong>{{ number_format($kpis['incomplete']) }}</strong>
                    <small>No general average for this semester</small>
                </article>
            </section>
        @endif

        @if($analytics)
            <section class="hub-card hub-detail">
                <div class="hub-card-head">
                    <div>
                        <h4>{{ $analytics['student']->last_name }}, {{ $analytics['student']->first_name }}</h4>
                        <p>Academic record for {{ $selectedYear->name ?? 'this year' }}.</p>
                    </div>
                    <a class="btn btn-outline-secondary hub-btn" href="{{ route($hubRoute, request()->except('student_id')) }}">Close</a>
                </div>
                <div class="hub-detail-stats">
                    <div><span>General average</span><strong>{{ $analytics['general_average'] !== null ? number_format($analytics['general_average'], 2) : '—' }}</strong></div>
                    <div><span>GPA</span><strong>{{ $analytics['gpa'] ? number_format($analytics['gpa']->gpa, 2) : '—' }}</strong></div>
                    <div><span>Open alerts</span><strong>{{ $analytics['alerts']->count() }}</strong></div>
                </div>
                <canvas id="studentTrendChart" height="110" aria-label="Quarter averages for this student"></canvas>
                <div class="table-responsive">
                    <table class="hub-table">
                        <thead>
                            <tr>
                                <th>Subject</th>
                                <th>Q1</th>
                                <th>Q2</th>
                                <th>Q3</th>
                                <th>Q4</th>
                                <th>Final</th>
                                <th>Remarks</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($analytics['subjects'] as $subject)
                                <tr>
                                    <td>{{ $subject->subject_name }}</td>
                                    <td>{{ $subject->q1 !== null ? number_format($subject->q1, 0) : '—' }}</td>
                                    <td>{{ $subject->q2 !== null ? number_format($subject->q2, 0) : '—' }}</td>
                                    <td>{{ $subject->q3 !== null ? number_format($subject->q3, 0) : '—' }}</td>
                                    <td>{{ $subject->q4 !== null ? number_format($subject->q4, 0) : '—' }}</td>
                                    <td>{{ $subject->final !== null ? number_format($subject->final, 0) : '—' }}</td>
                                    <td>{{ $subject->remarks ?: '—' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="7">No grades for this student in the selected year.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        @endif

        @if($kpis)
        <section class="hub-grid">
            <article class="hub-card">
                <div class="hub-card-head">
                    <div>
                        <h4>Grade distribution</h4>
                        <p>Students grouped by general average.</p>
                    </div>
                </div>
                <div class="hub-chart"><canvas id="distributionChart" aria-label="Grade distribution chart"></canvas></div>
            </article>
            <article class="hub-card">
                <div class="hub-card-head">
                    <div>
                        <h4>Average by grade level</h4>
                        <p>Select a bar to filter that grade.</p>
                    </div>
                </div>
                <div class="hub-chart"><canvas id="gradeChart" aria-label="Average performance by grade level"></canvas></div>
            </article>
            <article class="hub-card">
                <div class="hub-card-head">
                    <div>
                        <h4>Grading periods</h4>
                        <p>Average score in each period of this semester.</p>
                    </div>
                </div>
                <div class="hub-chart"><canvas id="quarterChart" aria-label="Performance by grading period"></canvas></div>
            </article>
            <article class="hub-card">
                <div class="hub-card-head">
                    <div>
                        <h4>Subject comparison</h4>
                        <p>Highest subject averages in the current filters.</p>
                    </div>
                </div>
                <div class="hub-chart"><canvas id="subjectChart" aria-label="Subject performance comparison"></canvas></div>
            </article>
        </section>

        <section class="hub-grid hub-grid-attendance">
            <article class="hub-card">
                <div class="hub-card-head">
                    <div>
                        <h4>Attendance trend</h4>
                        <p>Monthly rate inside {{ $selectedYear->name ?? 'the selected year' }}.</p>
                    </div>
                </div>
                @if($attendanceSummary && $attendanceSummary['ready'] && count($attendanceSummary['months']))
                    <div class="hub-chart"><canvas id="attendanceChart" aria-label="Attendance trend"></canvas></div>
                @else
                    <p class="hub-empty">No attendance records fall inside this academic year’s dates.</p>
                @endif
            </article>
            <article class="hub-card">
                <div class="hub-card-head">
                    <div>
                        <h4>Absences by grade</h4>
                        <p>Select a bar to open that grade.</p>
                    </div>
                </div>
                @if($attendanceSummary && $attendanceSummary['ready'] && count($attendanceSummary['grades']))
                    <div class="hub-chart"><canvas id="absenceChart" aria-label="Absences by grade level"></canvas></div>
                    <a class="hub-inline-link" href="{{ route($hubRoute, array_merge(request()->except(['page', 'performance_status']), ['performance_status' => 'attendance'])) }}">
                        View {{ number_format($attendanceSummary['recurring']) }} students with attendance concern
                    </a>
                @else
                    <p class="hub-empty">Absences appear here when this year has dated attendance records.</p>
                @endif
            </article>
        </section>

        <section class="hub-card">
            <div class="hub-card-head">
                <div>
                    <h4>Students to monitor</h4>
                    <p>
                        Below passing means under {{ number_format($passing, 0) }}%.
                        Attendance concern means under {{ number_format($attendanceLine, 0) }}% or {{ $absenceLine }} or more absences in this year.
                    </p>
                </div>
            </div>
            <div class="table-responsive">
                <table class="hub-table">
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Number</th>
                            <th>Grade</th>
                            <th>Section</th>
                            <th>Performance</th>
                            <th>Attendance</th>
                            <th>Indicator</th>
                            <th>Reason</th>
                            <th>Updated</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($monitor ?? [] as $row)
                            <tr>
                                <td>{{ $row->student->last_name }}, {{ $row->student->first_name }}</td>
                                <td>{{ $row->number }}</td>
                                <td>{{ $row->grade }}</td>
                                <td>{{ $row->section }}</td>
                                <td>{{ $row->average !== null ? number_format($row->average, 2).'%' : '—' }}</td>
                                <td>{{ $row->attendance !== null ? number_format($row->attendance, 2).'%' : '—' }}</td>
                                <td><span class="hub-pill hub-pill-{{ \Illuminate\Support\Str::slug($row->indicator) }}">{{ $row->indicator }}</span></td>
                                <td>{{ $row->reasons ? implode('; ', $row->reasons) : 'No flag' }}</td>
                                <td>{{ $row->updated_at ? \Carbon\Carbon::parse($row->updated_at)->timezone('Asia/Manila')->format('M j, Y') : '—' }}</td>
                                <td>
                                    <a class="hub-inline-link" href="{{ route($hubRoute, array_merge(request()->except('page'), ['student_id' => $row->student->id])) }}">View</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10">
                                    <div class="hub-empty">
                                        @if($filtersActive)
                                            No students match this search. Clear the filters to see the full list for this year.
                                        @else
                                            No students are enrolled or graded for this academic year and semester.
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($monitor)
                <div class="hub-pager">{{ $monitor->links() }}</div>
            @endif
        </section>

        @endif

        <section class="hub-card">
            <div class="hub-card-head">
                <div>
                    <h4>Grade alerts</h4>
                    <p>Alerts already raised from the school’s grade rules for this year and semester.</p>
                </div>
                <div class="hub-alert-filters">
                    <label>
                        Status
                        <select form="hubFilters" name="alert_status" onchange="document.getElementById('hubFilters').requestSubmit()">
                            <option value="open" @selected($alertFilter === 'open')>Open</option>
                            <option value="resolved" @selected($alertFilter === 'resolved')>Resolved</option>
                            <option value="all" @selected($alertFilter === 'all')>All</option>
                        </select>
                    </label>
                    <label>
                        Type
                        <select form="hubFilters" name="alert_type" onchange="document.getElementById('hubFilters').requestSubmit()">
                            <option value="">All types</option>
                            <option value="low_grade" @selected($alertType === 'low_grade')>Low grade</option>
                            <option value="performance_drop" @selected($alertType === 'performance_drop')>Performance drop</option>
                            <option value="at_risk" @selected($alertType === 'at_risk')>At risk</option>
                        </select>
                    </label>
                </div>
            </div>
            <div class="table-responsive">
                <table class="hub-table">
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Type</th>
                            <th>Subject</th>
                            <th>Message</th>
                            <th>Value</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($alerts ?? [] as $alert)
                            <tr>
                                <td>{{ $alert->student->last_name ?? '' }}, {{ $alert->student->first_name ?? '' }}</td>
                                <td>{{ str_replace('_', ' ', ucfirst($alert->alert_type)) }}</td>
                                <td>{{ $alert->subject->subject_name ?? '—' }}</td>
                                <td>{{ $alert->message }}</td>
                                <td>{{ $alert->current_value !== null ? number_format((float) $alert->current_value, 2) : '—' }}</td>
                                <td>{{ $alert->is_resolved ? 'Resolved' : 'Open' }}</td>
                                <td>
                                    @if(! $alert->is_resolved)
                                        <button type="button" class="hub-inline-link hub-resolve" data-action="{{ route($resolveRoute, $alert) }}">Resolve</button>
                                    @endif
                                    <a class="hub-inline-link" href="{{ route($hubRoute, array_merge(request()->except('page'), ['student_id' => $alert->student_id])) }}">View</a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7"><div class="hub-empty">No alerts match the current year, semester, and filters.</div></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($alerts)
                <div class="hub-pager">{{ $alerts->links() }}</div>
            @endif
        </section>
    </div>
</div>

<div class="modal fade" id="resolveAlertModal" tabindex="-1" aria-labelledby="resolveAlertTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" id="resolveAlertForm" action="">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="resolveAlertTitle">Resolve this alert?</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">This marks the alert as resolved. The student’s grades are not changed.</div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Resolve</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.hub-page { color: #292524; }
.hub-header, .hub-header-meta, .hub-toolbar-foot, .hub-card-head, .hub-alert-filters { display: flex; justify-content: space-between; gap: 1rem; flex-wrap: wrap; align-items: flex-end; }
.hub-title { margin: 0; font-size: 1.7rem; font-weight: 700; }
.hub-lead, .hub-card-head p, .hub-kpi small, .hub-count, .hub-empty { color: #57534e; margin: .25rem 0 0; }
.hub-year { background: #fff; border: 1px solid #e7e5e4; border-radius: 12px; padding: .55rem .8rem; min-width: 140px; }
.hub-year span, .hub-filters label, .hub-alert-filters label, .hub-detail-stats span { display: block; font-size: .75rem; color: #78716c; margin-bottom: .2rem; }
.hub-card, .hub-kpi { background: #fff; border: 1px solid #e7e5e4; border-radius: 14px; box-shadow: 0 1px 2px rgba(28, 25, 23, .04); }
.hub-toolbar, .hub-card { padding: 1rem 1.1rem; margin-bottom: 1rem; }
.hub-search { display: flex; align-items: center; gap: .6rem; border: 1px solid #d6d3d1; border-radius: 12px; padding: .65rem .85rem; margin-bottom: .9rem; background: #fafaf9; }
.hub-search input, .hub-filters select, .hub-filters input[readonly], .hub-alert-filters select { width: 100%; border: 1px solid #d6d3d1; border-radius: 10px; min-height: 42px; padding: .4rem .7rem; background: #fff; }
.hub-filters input[readonly] { background: #fafaf9; color: #292524; }
.hub-filters small { display: block; margin-top: .3rem; color: #78716c; font-size: .75rem; }
.hub-search { min-width: 0; }
.hub-search input { border: 0; background: transparent; min-height: 32px; padding: 0; min-width: 0; flex: 1; }
.hub-search input:focus, .hub-filters select:focus, .hub-alert-filters select:focus { outline: 2px solid #fdba74; border-color: #c2410c; }
.hub-filters, .hub-kpis, .hub-grid, .hub-detail-stats { display: grid; gap: .75rem; }
.hub-filters { grid-template-columns: repeat(4, minmax(0, 1fr)); }
.hub-kpis { grid-template-columns: repeat(5, minmax(0, 1fr)); margin-bottom: 1rem; }
.hub-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
.hub-kpi { padding: .9rem 1rem; }
.hub-kpi strong, .hub-detail-stats strong { display: block; font-size: 1.45rem; line-height: 1.2; margin: .15rem 0; }
.hub-kpi span { font-size: .78rem; font-weight: 650; letter-spacing: .01em; }
.hub-advanced { margin-top: .75rem; }
.hub-advanced summary { cursor: pointer; color: #c2410c; font-weight: 600; }
.hub-advanced .hub-filters { margin-top: .7rem; grid-template-columns: repeat(2, minmax(0, 1fr)); }
.hub-btn { border-radius: 10px; }
.btn-primary.hub-btn, .modal .btn-primary { background: #c2410c; border-color: #c2410c; }
.btn-primary.hub-btn:hover, .modal .btn-primary:hover { background: #9a3412; border-color: #9a3412; }
.hub-clear, .hub-inline-link { color: #c2410c; font-weight: 650; background: none; border: 0; padding: 0; text-decoration: underline; }
.hub-loading { color: #c2410c; font-weight: 650; }
.hub-chart { min-height: 240px; }
.hub-table { width: 100%; border-collapse: collapse; font-size: .92rem; }
.hub-table th, .hub-table td { padding: .65rem .55rem; border-bottom: 1px solid #f5f5f4; vertical-align: top; }
.hub-table th { font-size: .75rem; text-transform: uppercase; letter-spacing: .03em; color: #57534e; white-space: nowrap; }
.hub-table tr:hover td { background: #fff7ed; }
.hub-pill { display: inline-block; border-radius: 999px; padding: .15rem .55rem; font-size: .75rem; background: #f5f5f4; }
.hub-pill-below-passing { background: #ffedd5; color: #9a3412; }
.hub-pill-attendance-concern, .hub-pill-open-alert { background: #fef3c7; color: #92400e; }
.hub-pill-incomplete { background: #f5f5f4; color: #44403c; }
.hub-pill-on-track { background: #f5f5f4; color: #365314; }
.hub-empty { padding: 1.2rem 0; }
.hub-pager { margin-top: .8rem; }
.hub-detail-stats { grid-template-columns: repeat(3, minmax(0, 1fr)); margin-bottom: 1rem; }
@media (max-width: 991px) {
    .hub-filters, .hub-kpis, .hub-grid, .hub-advanced .hub-filters { grid-template-columns: 1fr 1fr; }
}
@media (max-width: 640px) {
    .hub-filters, .hub-kpis, .hub-grid, .hub-advanced .hub-filters, .hub-detail-stats { grid-template-columns: 1fr; }
    .hub-header-meta, .hub-page { max-width: 100%; }
}
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
(function () {
    const form = document.getElementById('hubFilters');
    const search = form.querySelector('input[name="q"]');
    const grade = document.getElementById('hubGrade');
    const section = document.getElementById('hubSection');
    const loading = document.getElementById('hubLoading');
    let timer = null;
    let sending = false;

    function syncSections() {
        const selectedGrade = grade.value;
        Array.from(section.options).forEach(function (option) {
            if (!option.value) {
                option.hidden = false;
                return;
            }
            option.hidden = selectedGrade !== '' && option.dataset.grade !== selectedGrade;
        });
        if (section.selectedOptions[0] && section.selectedOptions[0].hidden) {
            section.value = '';
        }
    }

    function submitHub() {
        if (sending) return;
        sending = true;
        loading.hidden = false;
        form.querySelectorAll('input, select, button').forEach(function (field) { field.disabled = false; });
        form.submit();
    }

    syncSections();
    form.querySelectorAll('select').forEach(function (select) {
        if (select.id === 'hubSubject') return;
        select.addEventListener('change', function () {
            if (select === grade) syncSections();
            submitHub();
        });
    });
    search.addEventListener('input', function () {
        clearTimeout(timer);
        timer = setTimeout(submitHub, 400);
    });

    const subjectSelect = document.getElementById('hubSubject');
    if (subjectSelect && window.jQuery && jQuery.fn.select2) {
        jQuery(subjectSelect).select2({ width: '100%', placeholder: 'All subjects', allowClear: true });
        jQuery(subjectSelect).on('change', submitHub);
    } else if (subjectSelect) {
        subjectSelect.addEventListener('change', submitHub);
    }

    document.querySelectorAll('.hub-resolve').forEach(function (button) {
        button.addEventListener('click', function () {
            document.getElementById('resolveAlertForm').action = button.dataset.action;
            bootstrap.Modal.getOrCreateInstance(document.getElementById('resolveAlertModal')).show();
        });
    });

    const payload = @json($chartPayload);
    const attendance = @json($attendancePayload);
    const orange = '#c2410c';
    const ink = '#44403c';
    const bands = ['#9a3412', '#c2410c', '#ea580c', '#fdba74', '#44403c', '#a8a29e'];
    const baseOptions = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { position: 'bottom' } }
    };

    function mount(id, config) {
        const node = document.getElementById(id);
        if (!node || typeof Chart === 'undefined') return null;
        return new Chart(node, config);
    }

    function filterByGrade(label) {
        if (!label || label === '—') return;
        const url = new URL(window.location.href);
        url.searchParams.set('grade_level', label);
        url.searchParams.delete('page');
        url.searchParams.delete('section_id');
        url.searchParams.delete('academic_year_id');
        window.location = url.toString();
    }

    mount('distributionChart', {
        type: 'bar',
        data: { labels: payload.distribution_labels, datasets: [{ label: 'Students', data: payload.distribution_values, backgroundColor: bands }] },
        options: Object.assign({}, baseOptions, { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } })
    });
    mount('gradeChart', {
        type: 'bar',
        data: { labels: payload.grade_labels, datasets: [{ label: 'Average %', data: payload.grade_values, backgroundColor: orange }] },
        options: Object.assign({}, baseOptions, {
            plugins: { legend: { display: false } },
            onClick: function (event, elements, chart) {
                if (elements.length) filterByGrade(chart.data.labels[elements[0].index]);
            },
            scales: { y: { suggestedMin: 70, suggestedMax: 100 } }
        })
    });
    mount('quarterChart', {
        type: 'line',
        data: { labels: payload.quarter_labels, datasets: [{ label: 'Average %', data: payload.quarter_values, borderColor: orange, backgroundColor: 'rgba(194,65,12,.12)', fill: true, spanGaps: false, tension: .2 }] },
        options: Object.assign({}, baseOptions, { scales: { y: { suggestedMin: 70, suggestedMax: 100 } } })
    });
    mount('subjectChart', {
        type: 'bar',
        data: { labels: payload.subject_labels, datasets: [{ label: 'Average %', data: payload.subject_values, backgroundColor: '#ea580c' }] },
        options: Object.assign({}, baseOptions, { indexAxis: 'y', plugins: { legend: { display: false } }, scales: { x: { suggestedMin: 70, suggestedMax: 100 } } })
    });
    mount('attendanceChart', {
        type: 'line',
        data: { labels: attendance.months || [], datasets: [{ label: 'Attendance %', data: attendance.month_rates || [], borderColor: ink, backgroundColor: 'rgba(68,64,60,.08)', fill: true, tension: .2 }] },
        options: Object.assign({}, baseOptions, { scales: { y: { suggestedMin: 0, suggestedMax: 100 } } })
    });
    mount('absenceChart', {
        type: 'bar',
        data: { labels: attendance.grades || [], datasets: [{ label: 'Absences', data: attendance.grade_absences || [], backgroundColor: '#44403c' }] },
        options: Object.assign({}, baseOptions, {
            plugins: { legend: { display: false } },
            onClick: function (event, elements, chart) {
                if (elements.length) filterByGrade(chart.data.labels[elements[0].index]);
            },
            scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
        })
    });

    const trendNode = document.getElementById('studentTrendChart');
    if (trendNode) {
        const trend = @json($trendLabels);
        const trendValues = @json($trendValues);
        mount('studentTrendChart', {
            type: 'line',
            data: { labels: trend, datasets: [{ label: 'Quarter average', data: trendValues, borderColor: orange, tension: .2, spanGaps: true }] },
            options: Object.assign({}, baseOptions, { scales: { y: { suggestedMin: 60, suggestedMax: 100 } } })
        });
    }
})();
</script>
@endpush
