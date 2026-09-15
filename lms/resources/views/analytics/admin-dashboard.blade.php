@extends('layouts.master')
@section('content')
@php
    $overview = $analytics['school_overview'] ?? [];
    $enrollment = $analytics['enrollment_overview'] ?? [];
    $studentsByGrade = $analytics['students_by_grade'] ?? [];
    $gradeOrder = \App\Services\GradeSubjectCatalogService::gradeLevels();
    $orderGrades = function ($groups) use ($gradeOrder) {
        return collect($groups)->sortBy(function ($rows, $grade) use ($gradeOrder) {
            $index = array_search($grade, $gradeOrder, true);
            return $index === false ? 1000 : $index;
        });
    };
    $subjectPerfByGrade = $orderGrades(collect($analytics['subject_performance'] ?? [])->groupBy(fn ($row) => $row['grade'] ?? 'Other'));
    $passFailByGrade = $orderGrades(collect($analytics['pass_fail_rates'] ?? [])->groupBy(fn ($row) => $row['grade'] ?? 'Other'));
@endphp
<div class="page-wrapper">
    <div class="content container-fluid ams-analytics">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">School Analytics</h3>
                    <p class="text-muted mb-0">Live school overview from enrollment, grades, attendance, and sections.</p>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">School Analytics</li>
                    </ul>
                </div>
                <div class="col-auto">
                    {{-- CSV export removed: incomplete report (IT review). Use Performance Hub / Reports instead. --}}
                </div>
            </div>
        </div>

        <form method="GET" action="{{ route('analytics.admin-dashboard') }}" class="ams-filter-panel mb-4">
            <div class="row g-2 align-items-end">
                <div class="col-md-4">
                    <label class="form-label fw-semibold mb-1">Academic Year</label>
                    <select class="form-control" name="academic_year_id" id="academic_year_filter">
                        <option value="">All Academic Years</option>
                        @foreach($academicYears as $year)
                            <option value="{{ $year->id }}" @selected((string)$academicYearId === (string)$year->id)>{{ $year->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold mb-1">Semester</label>
                    <select class="form-control" name="semester_id" id="semester_filter">
                        <option value="">All Semesters</option>
                        @foreach($semesters as $semester)
                            <option value="{{ $semester->id }}"
                                data-year="{{ $semester->academic_year_id }}"
                                @selected((string)$semesterId === (string)$semester->id)>
                                {{ $semester->name }}@if($semester->academicYear) ({{ $semester->academicYear->name }})@endif
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-filter me-1"></i> Apply Filters
                    </button>
                </div>
            </div>
        </form>

        {{-- KPI cards --}}
        <div class="row g-3 mb-4">
            <div class="col-6 col-xl-3">
                <div class="ams-kpi">
                    <div class="ams-kpi-icon ams-kpi-icon--blue"><i class="fas fa-user-graduate"></i></div>
                    <div>
                        <div class="ams-kpi-val">{{ $overview['total_students'] ?? 0 }}</div>
                        <div class="ams-kpi-lbl">Students</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-xl-3">
                <div class="ams-kpi">
                    <div class="ams-kpi-icon ams-kpi-icon--green"><i class="fas fa-chalkboard-teacher"></i></div>
                    <div>
                        <div class="ams-kpi-val">{{ $overview['total_teachers'] ?? 0 }}</div>
                        <div class="ams-kpi-lbl">Teachers</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-xl-3">
                <div class="ams-kpi">
                    <div class="ams-kpi-icon ams-kpi-icon--teal"><i class="fas fa-users"></i></div>
                    <div>
                        <div class="ams-kpi-val">{{ $overview['total_parents'] ?? 0 }}</div>
                        <div class="ams-kpi-lbl">Parents</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-xl-3">
                <div class="ams-kpi">
                    <div class="ams-kpi-icon ams-kpi-icon--indigo"><i class="fas fa-book"></i></div>
                    <div>
                        <div class="ams-kpi-val">{{ $overview['total_subjects'] ?? 0 }}</div>
                        <div class="ams-kpi-lbl">Subjects</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-xl-3">
                <div class="ams-kpi">
                    <div class="ams-kpi-icon ams-kpi-icon--slate"><i class="fas fa-door-open"></i></div>
                    <div>
                        <div class="ams-kpi-val">{{ $overview['total_sections'] ?? 0 }}</div>
                        <div class="ams-kpi-lbl">Sections</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-xl-3">
                <div class="ams-kpi">
                    <div class="ams-kpi-icon ams-kpi-icon--amber"><i class="fas fa-percentage"></i></div>
                    <div>
                        <div class="ams-kpi-val">{{ $overview['average_score'] ?? 0 }}%</div>
                        <div class="ams-kpi-lbl">Avg Score</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-xl-3">
                <div class="ams-kpi">
                    <div class="ams-kpi-icon ams-kpi-icon--ok"><i class="fas fa-check-circle"></i></div>
                    <div>
                        <div class="ams-kpi-val">{{ $overview['pass_rate'] ?? 0 }}%</div>
                        <div class="ams-kpi-lbl">Pass Rate</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-xl-3">
                <div class="ams-kpi">
                    <div class="ams-kpi-icon ams-kpi-icon--cyan"><i class="fas fa-calendar-check"></i></div>
                    <div>
                        <div class="ams-kpi-val">{{ $overview['attendance_rate'] ?? 0 }}%</div>
                        <div class="ams-kpi-lbl">Attendance</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Enrollment + students by grade --}}
        <div class="row g-3 mb-4">
            <div class="col-lg-5">
                <div class="ams-panel h-100">
                    <div class="ams-panel-h">
                        <h5 class="mb-0">Enrollment Pipeline</h5>
                        <small class="text-muted">Applications in registrar queue</small>
                    </div>
                    <div class="ams-panel-b">
                        <div class="row g-2 mb-3">
                            <div class="col-6"><div class="ams-mini"><span>{{ $enrollment['total'] ?? 0 }}</span>Total</div></div>
                            <div class="col-6"><div class="ams-mini ams-mini--warn"><span>{{ $enrollment['pending'] ?? 0 }}</span>Pending</div></div>
                            <div class="col-6"><div class="ams-mini ams-mini--info"><span>{{ $enrollment['under_review'] ?? 0 }}</span>Under Review</div></div>
                            <div class="col-6"><div class="ams-mini ams-mini--docs"><span>{{ $enrollment['needs_documents'] ?? 0 }}</span>Needs Docs</div></div>
                            <div class="col-6"><div class="ams-mini ams-mini--ok"><span>{{ $enrollment['approved'] ?? 0 }}</span>Approved</div></div>
                            <div class="col-6"><div class="ams-mini ams-mini--bad"><span>{{ $enrollment['rejected'] ?? 0 }}</span>Rejected</div></div>
                        </div>
                        <div class="ams-chart-wrap">
                            <canvas id="enrollmentChart" height="180"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-7">
                <div class="ams-panel h-100">
                    <div class="ams-panel-h">
                        <h5 class="mb-0">Students by Grade</h5>
                        <small class="text-muted">Headcount from student records</small>
                    </div>
                    <div class="ams-panel-b">
                        <div class="ams-chart-wrap">
                            <canvas id="studentsByGradeChart" height="220"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Academic charts --}}
        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <div class="ams-panel">
                    <div class="ams-panel-h"><h5 class="mb-0">GPA by Grade Level</h5></div>
                    <div class="ams-panel-b"><div class="ams-chart-wrap"><canvas id="gpaComparisonChart"></canvas></div></div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="ams-panel">
                    <div class="ams-panel-h"><h5 class="mb-0">Pass / Fail by Subject</h5></div>
                    <div class="ams-panel-b"><div class="ams-chart-wrap"><canvas id="passFailRatesChart"></canvas></div></div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="ams-panel">
                    <div class="ams-panel-h"><h5 class="mb-0">Subject Performance</h5></div>
                    <div class="ams-panel-b"><div class="ams-chart-wrap"><canvas id="subjectPerformanceChart"></canvas></div></div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="ams-panel">
                    <div class="ams-panel-h"><h5 class="mb-0">Attendance Trend</h5></div>
                    <div class="ams-panel-b"><div class="ams-chart-wrap"><canvas id="attendanceSummaryChart"></canvas></div></div>
                </div>
            </div>
            <div class="col-12">
                <div class="ams-panel">
                    <div class="ams-panel-h"><h5 class="mb-0">Section Performance</h5></div>
                    <div class="ams-panel-b"><div class="ams-chart-wrap ams-chart-wrap--wide"><canvas id="sectionComparisonChart"></canvas></div></div>
                </div>
            </div>
        </div>

        {{-- Tables as Academic Management-style dropdowns --}}
        <div class="ams-acc" id="amsDetailMenus">
            <div class="ams-acc-item">
                <button type="button" class="ams-acc-toggle" aria-expanded="false">
                    <span class="ams-acc-left">
                        <i class="fas fa-book-open"></i>
                        <span>Student Performance by Subject</span>
                    </span>
                    <span class="ams-acc-arrow"></span>
                </button>
                <div class="ams-acc-panel">
                    @if($subjectPerfByGrade->isEmpty())
                        <p class="text-muted text-center py-4 mb-0">No grade data yet for this filter.</p>
                    @else
                        <div class="ams-acc ams-acc--nested">
                            @foreach($subjectPerfByGrade as $grade => $rows)
                                <div class="ams-acc-item">
                                    <button type="button" class="ams-acc-toggle" aria-expanded="false">
                                        <span class="ams-acc-left">
                                            <i class="fas fa-layer-group"></i>
                                            <span>{{ $grade }}</span>
                                            <span class="ams-count-pill">{{ $rows->count() }}</span>
                                        </span>
                                        <span class="ams-acc-arrow"></span>
                                    </button>
                                    <div class="ams-acc-panel">
                                        <div class="table-responsive">
                                            <table class="table ams-table mb-0">
                                                <thead><tr><th>Subject</th><th>Avg</th><th>Students</th><th>Grades</th></tr></thead>
                                                <tbody>
                                                @foreach($rows as $subject)
                                                    <tr>
                                                        <td>{{ $subject['subject'] }}</td>
                                                        <td><strong>{{ $subject['average_score'] }}%</strong></td>
                                                        <td>{{ $subject['students_count'] }}</td>
                                                        <td>{{ $subject['assignments_count'] }}</td>
                                                    </tr>
                                                @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            <div class="ams-acc-item">
                <button type="button" class="ams-acc-toggle" aria-expanded="false">
                    <span class="ams-acc-left">
                        <i class="fas fa-clipboard-check"></i>
                        <span>Pass / Fail Insights</span>
                    </span>
                    <span class="ams-acc-arrow"></span>
                </button>
                <div class="ams-acc-panel">
                    @if($passFailByGrade->isEmpty())
                        <p class="text-muted text-center py-4 mb-0">No pass/fail data yet.</p>
                    @else
                        <div class="ams-acc ams-acc--nested">
                            @foreach($passFailByGrade as $grade => $rows)
                                <div class="ams-acc-item">
                                    <button type="button" class="ams-acc-toggle" aria-expanded="false">
                                        <span class="ams-acc-left">
                                            <i class="fas fa-layer-group"></i>
                                            <span>{{ $grade }}</span>
                                            <span class="ams-count-pill">{{ $rows->count() }}</span>
                                        </span>
                                        <span class="ams-acc-arrow"></span>
                                    </button>
                                    <div class="ams-acc-panel">
                                        <div class="table-responsive">
                                            <table class="table ams-table mb-0">
                                                <thead><tr><th>Subject</th><th>Pass</th><th>Fail</th><th>Students</th></tr></thead>
                                                <tbody>
                                                @foreach($rows as $rate)
                                                    <tr>
                                                        <td>{{ $rate['subject'] }}</td>
                                                        <td><span class="ams-tag ams-tag--ok">{{ $rate['pass_rate'] }}%</span></td>
                                                        <td><span class="ams-tag ams-tag--bad">{{ $rate['fail_rate'] }}%</span></td>
                                                        <td>{{ $rate['total_students'] }}</td>
                                                    </tr>
                                                @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            <div class="ams-acc-item">
                <button type="button" class="ams-acc-toggle" aria-expanded="false">
                    <span class="ams-acc-left">
                        <i class="fas fa-chart-line"></i>
                        <span>GPA by Grade Level</span>
                    </span>
                    <span class="ams-acc-arrow"></span>
                </button>
                <div class="ams-acc-panel">
                    <div class="table-responsive">
                        <table class="table ams-table mb-0">
                            <thead>
                                <tr>
                                    <th>Grade Level</th>
                                    <th>Avg GPA</th>
                                    <th>Students</th>
                                    <th>Highest</th>
                                    <th>Lowest</th>
                                    <th>Level</th>
                                </tr>
                            </thead>
                            <tbody>
                            @forelse($analytics['gpa_comparison'] as $gpa)
                                <tr>
                                    <td>{{ $gpa['grade_level'] }}</td>
                                    <td><strong>{{ $gpa['average_gpa'] }}</strong></td>
                                    <td>{{ $gpa['students_count'] }}</td>
                                    <td>{{ $gpa['highest_gpa'] }}</td>
                                    <td>{{ $gpa['lowest_gpa'] }}</td>
                                    <td>
                                        @if($gpa['average_gpa'] >= 3.5)
                                            <span class="ams-tag ams-tag--ok">Excellent</span>
                                        @elseif($gpa['average_gpa'] >= 3.0)
                                            <span class="ams-tag ams-tag--info">Good</span>
                                        @elseif($gpa['average_gpa'] >= 2.5)
                                            <span class="ams-tag ams-tag--warn">Average</span>
                                        @else
                                            <span class="ams-tag ams-tag--bad">Needs Improvement</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="text-center text-muted py-4">No GPA records yet. Grades need to be calculated into student GPA.</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="application/json" id="analyticsJson">{!! json_encode([
    'enrollment' => [
        'pending' => $enrollment['pending'] ?? 0,
        'under_review' => $enrollment['under_review'] ?? 0,
        'needs_documents' => $enrollment['needs_documents'] ?? 0,
        'approved' => $enrollment['approved'] ?? 0,
        'rejected' => $enrollment['rejected'] ?? 0,
    ],
    'students_by_grade' => $studentsByGrade,
    'gpa' => $analytics['gpa_comparison'] ?? [],
    'pass_fail' => $analytics['pass_fail_rates'] ?? [],
    'subjects' => $analytics['subject_performance'] ?? [],
    'attendance' => $analytics['attendance_summary'] ?? [],
    'sections' => $analytics['section_comparison'] ?? [],
], JSON_UNESCAPED_UNICODE) !!}</script>
@endsection

@push('styles')
<style>
.ams-analytics { --line:#e5e7eb; --ink:#111827; --muted:#6b7280; --soft:#f8fafc; --blue:#1e3a8a; }
.ams-filter-panel { background:#fff; border:1px solid var(--line); border-radius:14px; padding:1rem 1.1rem; }
.ams-kpi { display:flex; gap:.85rem; align-items:center; background:#fff; border:1px solid var(--line); border-radius:14px; padding:1rem; height:100%; }
.ams-kpi-icon { width:2.6rem; height:2.6rem; border-radius:12px; display:inline-flex; align-items:center; justify-content:center; color:#fff; flex-shrink:0; }
.ams-kpi-icon--blue { background:#1e3a8a; }
.ams-kpi-icon--green { background:#047857; }
.ams-kpi-icon--teal { background:#0f766e; }
.ams-kpi-icon--indigo { background:#4338ca; }
.ams-kpi-icon--slate { background:#475569; }
.ams-kpi-icon--amber { background:#b45309; }
.ams-kpi-icon--ok { background:#15803d; }
.ams-kpi-icon--cyan { background:#0891b2; }
.ams-kpi-val { font-size:1.35rem; font-weight:800; color:var(--ink); line-height:1.1; }
.ams-kpi-lbl { font-size:.8rem; color:var(--muted); font-weight:600; }
.ams-panel { background:#fff; border:1px solid var(--line); border-radius:16px; overflow:hidden; box-shadow:0 8px 20px rgba(15,23,42,.04); }
.ams-panel-h { padding:.9rem 1.1rem; border-bottom:1px solid var(--line); background:linear-gradient(180deg,#fff,var(--soft)); }
.ams-panel-b { padding:1rem 1.1rem; }
.ams-mini { background:var(--soft); border:1px solid var(--line); border-radius:12px; padding:.65rem .75rem; font-size:.8rem; font-weight:600; color:var(--muted); }
.ams-mini span { display:block; font-size:1.15rem; font-weight:800; color:var(--ink); }
.ams-mini--warn span { color:#b45309; }
.ams-mini--info span { color:#1d4ed8; }
.ams-mini--docs span { color:#475569; }
.ams-mini--ok span { color:#15803d; }
.ams-mini--bad span { color:#b91c1c; }
.ams-chart-wrap { position:relative; height:240px; }
.ams-chart-wrap--wide { height:280px; }
.ams-table thead th { background:var(--soft); font-size:.78rem; text-transform:uppercase; letter-spacing:.03em; color:var(--muted); border-bottom:1px solid var(--line); }
.ams-table td { vertical-align:middle; }
.ams-tag { display:inline-flex; border-radius:999px; padding:.2rem .55rem; font-size:.75rem; font-weight:700; }
.ams-tag--ok { background:#d1fae5; color:#065f46; }
.ams-tag--bad { background:#fee2e2; color:#991b1b; }
.ams-tag--info { background:#dbeafe; color:#1e40af; }
.ams-tag--warn { background:#fef3c7; color:#92400e; }
.ams-acc { display:flex; flex-direction:column; gap:.65rem; }
.ams-acc--nested { gap:.4rem; padding:.65rem; }
.ams-acc-item { background:#fff; border:1px solid var(--line); border-radius:16px; overflow:hidden; box-shadow:0 8px 20px rgba(15,23,42,.04); }
.ams-acc--nested .ams-acc-item { border-radius:12px; box-shadow:none; }
.ams-acc-toggle { width:100%; display:flex; align-items:center; justify-content:space-between; gap:.75rem; padding:.95rem 1.1rem; border:0; background:#fff; color:var(--ink); font-weight:700; text-align:left; cursor:pointer; }
.ams-acc-item.is-open > .ams-acc-toggle { background:linear-gradient(180deg,#eef4ff,#fff); color:#1d4ed8; }
.ams-acc-left { display:flex; align-items:center; gap:.7rem; min-width:0; }
.ams-acc-left i { width:1.15rem; color:#3b82f6; }
.ams-acc-arrow { width:.55rem; height:.55rem; border-right:2px solid #94a3b8; border-bottom:2px solid #94a3b8; transform:rotate(-45deg); transition:transform .2s ease; flex-shrink:0; margin-right:.15rem; }
.ams-acc-item.is-open > .ams-acc-toggle .ams-acc-arrow { transform:rotate(45deg); border-color:#2563eb; }
.ams-acc-panel { display:none; border-top:1px solid var(--line); background:#fff; }
.ams-acc-item.is-open > .ams-acc-panel { display:block; }
.ams-count-pill { display:inline-flex; align-items:center; justify-content:center; min-width:1.6rem; padding:.12rem .45rem; border-radius:999px; background:#eef2ff; color:#1e3a8a; font-size:.72rem; font-weight:700; }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
(function () {
    const raw = document.getElementById('analyticsJson');
    if (!raw) return;
    let payload = {};
    try { payload = JSON.parse(raw.textContent || '{}'); } catch (e) { payload = {}; }

    const enrollment = payload.enrollment || {};
    const studentsByGrade = payload.students_by_grade || [];
    const gpa = payload.gpa || [];
    const passFail = payload.pass_fail || [];
    const subjects = payload.subjects || [];
    const attendance = payload.attendance || [];
    const sections = payload.sections || [];

    const palette = ['#1e3a8a', '#047857', '#b45309', '#b91c1c', '#0891b2', '#4338ca', '#475569', '#15803d'];

    function emptyChart(ctx, message) {
        return new Chart(ctx, {
            type: 'bar',
            data: { labels: [message], datasets: [{ data: [0], backgroundColor: '#e2e8f0' }] },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true, max: 1, ticks: { display: false } } }
            }
        });
    }

    // Filter semesters by selected year
    const yearSelect = document.getElementById('academic_year_filter');
    const semSelect = document.getElementById('semester_filter');
    function filterSemesters() {
        if (!yearSelect || !semSelect) return;
        const year = yearSelect.value;
        Array.from(semSelect.options).forEach(function (opt, idx) {
            if (idx === 0) return;
            const y = opt.getAttribute('data-year') || '';
            opt.hidden = !!(year && y && y !== year);
            if (opt.hidden && opt.selected) {
                semSelect.value = '';
            }
        });
    }
    yearSelect?.addEventListener('change', filterSemesters);
    filterSemesters();

    // Enrollment doughnut
    const enrollCtx = document.getElementById('enrollmentChart');
    if (enrollCtx) {
        const labels = ['Pending', 'Under Review', 'Needs Docs', 'Approved', 'Rejected'];
        const data = [
            enrollment.pending || 0,
            enrollment.under_review || 0,
            enrollment.needs_documents || 0,
            enrollment.approved || 0,
            enrollment.rejected || 0
        ];
        if (data.every(function (n) { return n === 0; })) {
            emptyChart(enrollCtx.getContext('2d'), 'No applications');
        } else {
            new Chart(enrollCtx.getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: labels,
                    datasets: [{ data: data, backgroundColor: ['#f59e0b', '#3b82f6', '#64748b', '#16a34a', '#dc2626'], borderWidth: 0 }]
                },
                options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } }
            });
        }
    }

    // Students by grade
    const gradeCtx = document.getElementById('studentsByGradeChart');
    if (gradeCtx) {
        if (!studentsByGrade.length) {
            emptyChart(gradeCtx.getContext('2d'), 'No students');
        } else {
            new Chart(gradeCtx.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: studentsByGrade.map(function (r) { return r.grade; }),
                    datasets: [{
                        label: 'Students',
                        data: studentsByGrade.map(function (r) { return r.count; }),
                        backgroundColor: '#1e3a8a'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
                }
            });
        }
    }

    function barChart(id, labels, datasets, yMax) {
        const el = document.getElementById(id);
        if (!el) return;
        if (!labels.length) {
            emptyChart(el.getContext('2d'), 'No data');
            return;
        }
        new Chart(el.getContext('2d'), {
            type: 'bar',
            data: { labels: labels, datasets: datasets },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: { y: { beginAtZero: true, max: yMax || undefined } }
            }
        });
    }

    barChart(
        'gpaComparisonChart',
        gpa.map(function (r) { return r.grade_level; }),
        [{ label: 'Average GPA', data: gpa.map(function (r) { return r.average_gpa; }), backgroundColor: palette }],
        4
    );

    barChart(
        'passFailRatesChart',
        passFail.map(function (r) { return r.subject; }),
        [
            { label: 'Pass %', data: passFail.map(function (r) { return r.pass_rate; }), backgroundColor: '#16a34a' },
            { label: 'Fail %', data: passFail.map(function (r) { return r.fail_rate; }), backgroundColor: '#dc2626' }
        ],
        100
    );

    barChart(
        'subjectPerformanceChart',
        subjects.map(function (r) { return r.subject; }),
        [{ label: 'Average Score', data: subjects.map(function (r) { return r.average_score; }), backgroundColor: '#4338ca' }],
        100
    );

    const attEl = document.getElementById('attendanceSummaryChart');
    if (attEl) {
        if (!attendance.length) {
            emptyChart(attEl.getContext('2d'), 'No attendance');
        } else {
            new Chart(attEl.getContext('2d'), {
                type: 'line',
                data: {
                    labels: attendance.map(function (r) { return r.month; }),
                    datasets: [{
                        label: 'Attendance %',
                        data: attendance.map(function (r) { return r.attendance_rate; }),
                        borderColor: '#0891b2',
                        backgroundColor: 'rgba(8,145,178,.15)',
                        fill: true,
                        tension: .3
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: { y: { beginAtZero: true, max: 100 } }
                }
            });
        }
    }

    barChart(
        'sectionComparisonChart',
        sections.map(function (r) { return r.section; }),
        [{ label: 'Average Score', data: sections.map(function (r) { return r.average_score; }), backgroundColor: '#047857' }],
        100
    );

    document.querySelectorAll('#amsDetailMenus .ams-acc-toggle').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const item = btn.closest('.ams-acc-item');
            if (!item) return;
            const parent = item.parentElement;
            const opening = !item.classList.contains('is-open');
            Array.from(parent.children).forEach(function (child) {
                if (child.classList && child.classList.contains('ams-acc-item')) {
                    child.classList.remove('is-open');
                    const toggle = child.querySelector(':scope > .ams-acc-toggle');
                    if (toggle) toggle.setAttribute('aria-expanded', 'false');
                }
            });
            if (opening) {
                item.classList.add('is-open');
                btn.setAttribute('aria-expanded', 'true');
            }
        });
    });
})();
</script>
@endpush
