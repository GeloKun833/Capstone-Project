@extends('layouts.master')
@section('content')

    <div class="page-wrapper">
        <div class="content container-fluid">
            <div class="page-header">
                <div class="row">
                    <div class="col-sm-12">
                        <div class="page-sub-header">
                            <h3 class="page-title">
                                <i class="fas fa-percentage text-success me-2"></i>My Grades
                            </h3>
                            <ul class="breadcrumb">
                                <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                                <li class="breadcrumb-item active">Grades</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Academic Year Selector -->
            <div class="row mb-3">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <form method="GET" action="{{ route('student.grades') }}" class="d-flex align-items-center">
                                <label class="form-label me-3 mb-0"><strong>Select Academic Year:</strong></label>
                                <select class="form-control form-select me-3" name="academic_year_id" id="academic_year_select" style="width: auto; min-width: 250px;" onchange="this.form.submit()">
                                    <option value="">-- Select Academic Year --</option>
                                    @foreach($academicYears as $year)
                                        <option value="{{ $year->id }}" {{ $currentAcademicYear && $currentAcademicYear->id == $year->id ? 'selected' : '' }}>
                                            {{ $year->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 text-end">
                    <div class="card">
                        <div class="card-body">
                            <button type="button" class="btn btn-success me-2" data-bs-toggle="modal" data-bs-target="#gradingSystemModal">
                                <i class="fas fa-info-circle me-2"></i>Grading System
                            </button>
                            @if($currentAcademicYear)
                                <a class="btn btn-primary me-2" target="_blank"
                                   href="{{ route('student.report-card', ['academic_year_id' => $currentAcademicYear->id]) }}">
                                    <i class="fas fa-print me-2"></i>Printable Report Card
                                </a>
                            @endif
                            <button type="button" class="btn btn-secondary" onclick="window.print()">
                                <i class="fas fa-print me-2"></i>Print Page
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Grade Alerts Section -->
            @if($gradeAlerts->count() > 0)
            <div class="row mb-3">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-warning text-white">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-exclamation-triangle me-2"></i>Grade Alerts
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-warning mb-0">
                                <h6>You have {{ $gradeAlerts->count() }} grade alert(s) that need attention:</h6>
                                <ul class="mb-0">
                                    @foreach($gradeAlerts as $alert)
                                    <li>
                                        <strong>{{ $alert->subject->subject_name ?? 'Subject' }}</strong>: 
                                        {{ $alert->message }} (Current Grade: {{ $alert->current_grade }})
                                    </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            {{-- 1. Observed Values first --}}
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card report-card-panel">
                        <div class="card-header bg-success text-white">
                            <h5 class="card-title mb-0 text-uppercase">
                                Report on Learner&rsquo;s Observed Values
                                @if($currentAcademicYear)
                                    — {{ $currentAcademicYear->name }}
                                @endif
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered report-card-table mb-3">
                                    <thead>
                                        <tr>
                                            <th style="width: 18%;">Core Values</th>
                                            <th>Behavior Statements</th>
                                            <th class="text-center" style="width: 10%;">1</th>
                                            <th class="text-center" style="width: 10%;">2</th>
                                            <th class="text-center" style="width: 10%;">3</th>
                                            <th class="text-center" style="width: 10%;">4</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($observedIndicators as $coreValue => $items)
                                            @foreach($items as $i => $indicator)
                                                @php $rating = $observedRatings->get($indicator->id); @endphp
                                                <tr>
                                                    @if($i === 0)
                                                        <td rowspan="{{ $items->count() }}" class="fw-bold align-middle">{{ $coreValue }}</td>
                                                    @endif
                                                    <td>{{ $indicator->statement }}</td>
                                                    @foreach(['quarter_1', 'quarter_2', 'quarter_3', 'quarter_4'] as $qField)
                                                        <td class="text-center">{{ optional($rating)->{$qField} ?: '—' }}</td>
                                                    @endforeach
                                                </tr>
                                            @endforeach
                                        @empty
                                            <tr>
                                                <td colspan="6" class="text-center text-muted py-4">No observed value indicators yet.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            <p class="small text-muted mb-0">
                                <strong>Marking:</strong> AO = Always Observed &nbsp;|&nbsp; SO = Sometimes Observed &nbsp;|&nbsp; RO = Rarely Observed
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 2. Learning Progress second --}}
            <!-- Report on Learning Progress and Achievement -->
            <div class="row">
                <div class="col-12">
                    <div class="card report-card-panel">
                        <div class="card-header bg-primary text-white">
                            <h5 class="card-title mb-0 text-uppercase">
                                Report on Learning Progress and Achievement
                                @if($currentAcademicYear)
                                    — {{ $currentAcademicYear->name }}
                                @endif
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered report-card-table" id="gradesTable">
                                    <thead>
                                        <tr>
                                            <th rowspan="2" class="align-middle" style="width: 34%;">Learning Areas</th>
                                            <th colspan="4" class="text-center">Quarter</th>
                                            <th rowspan="2" class="align-middle text-center" style="width: 12%;">Final Grade</th>
                                            <th rowspan="2" class="align-middle text-center" style="width: 14%;">Remarks</th>
                                        </tr>
                                        <tr>
                                            <th class="text-center" style="width: 10%;">1</th>
                                            <th class="text-center" style="width: 10%;">2</th>
                                            <th class="text-center" style="width: 10%;">3</th>
                                            <th class="text-center" style="width: 10%;">4</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @if($quarterlyGrades->count() > 0)
                                            @foreach($quarterlyGrades as $quarterlyGrade)
                                                <tr>
                                                    <td>
                                                        <strong>{{ $quarterlyGrade->subject->subject_name ?? 'N/A' }}</strong>
                                                    </td>
                                                    @foreach(['quarter_1', 'quarter_2', 'quarter_3', 'quarter_4'] as $qField)
                                                        <td class="text-center">
                                                            @if($quarterlyGrade->{$qField} !== null)
                                                                {{ number_format($quarterlyGrade->{$qField}, 0) }}
                                                            @else
                                                                <span class="text-muted">—</span>
                                                            @endif
                                                        </td>
                                                    @endforeach
                                                    <td class="text-center fw-bold">
                                                        @if($quarterlyGrade->final_grade !== null)
                                                            {{ number_format($quarterlyGrade->final_grade, 0) }}
                                                        @else
                                                            <span class="text-muted">—</span>
                                                        @endif
                                                    </td>
                                                    <td class="text-center">
                                                        @php $fg = $quarterlyGrade->final_grade; @endphp
                                                        {{ $quarterlyGrade->remarks ?: ($fg !== null ? \App\Services\ReportCardService::remarkForScore((float)$fg) : '—') }}
                                                    </td>
                                                </tr>
                                            @endforeach
                                            <tr class="general-average-row">
                                                <td class="fw-bold text-end">General Average</td>
                                                @foreach(['q1', 'q2', 'q3', 'q4', 'final'] as $avgKey)
                                                    <td class="text-center fw-bold">
                                                        {{ isset($generalAverages[$avgKey]) && $generalAverages[$avgKey] !== null ? number_format($generalAverages[$avgKey], 0) : '—' }}
                                                    </td>
                                                @endforeach
                                                <td class="text-center fw-bold">
                                                    @php $ga = $generalAverages['final'] ?? null; @endphp
                                                    {{ $ga !== null ? \App\Services\ReportCardService::remarkForScore((float) $ga) : '—' }}
                                                </td>
                                            </tr>
                                        @else
                                            <tr>
                                                <td colspan="7" class="text-center py-5">
                                                    <div class="empty-state">
                                                        <i class="fas fa-clipboard-list fa-3x text-muted mb-3"></i>
                                                        <h5>No Grades Available</h5>
                                                        @if($currentAcademicYear)
                                                            <p class="text-muted">No quarterly grades yet for {{ $currentAcademicYear->name }}.</p>
                                                        @else
                                                            <p class="text-muted">Please select an Academic Year.</p>
                                                        @endif
                                                    </div>
                                                </td>
                                            </tr>
                                        @endif
                                    </tbody>
                                </table>
                            </div>

                            <div class="row mt-4">
                                <div class="col-md-7">
                                    <h6 class="text-uppercase fw-bold mb-2">Descriptors / Grading Scale</h6>
                                    <table class="table table-bordered table-sm grading-scale-table mb-0">
                                        <thead>
                                            <tr>
                                                <th>Descriptors</th>
                                                <th class="text-center">Grading Scale</th>
                                                <th class="text-center">Remarks</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr><td>Outstanding</td><td class="text-center">90 – 100</td><td class="text-center">Passed</td></tr>
                                            <tr><td>Very Satisfactory</td><td class="text-center">85 – 89</td><td class="text-center">Passed</td></tr>
                                            <tr><td>Satisfactory</td><td class="text-center">80 – 84</td><td class="text-center">Passed</td></tr>
                                            <tr><td>Fairly Satisfactory</td><td class="text-center">75 – 79</td><td class="text-center">Passed</td></tr>
                                            <tr><td>Did Not Meet Expectations</td><td class="text-center">Below 75</td><td class="text-center">Failed</td></tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- GPA Summary Section -->
            @if($gpaRecords->count() > 0)
            <div class="row mt-3">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">
                                <i class="fas fa-chart-line me-2"></i>GPA Summary
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                @foreach($gpaRecords->take(3) as $gpa)
                                <div class="col-md-4">
                                    <div class="gpa-card text-center p-3 border rounded">
                                        <h4 class="text-primary mb-2">{{ $gpa->gpa }}</h4>
                                        <p class="mb-1"><strong>{{ $gpa->academicYear->name ?? 'N/A' }}</strong></p>
                                        <p class="text-muted mb-0">{{ $gpa->semester->name ?? 'N/A' }}</p>
                                        <small class="text-muted">Total Units: {{ $gpa->total_units }}</small>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>

    <!-- Grading System Modal -->
    <div class="modal fade" id="gradingSystemModal" tabindex="-1" aria-labelledby="gradingSystemModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="gradingSystemModalLabel">
                        <i class="fas fa-info-circle me-2"></i>DepEd Grading System
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="table-dark">
                                <tr>
                                    <th>Grade Range</th>
                                    <th>Remarks</th>
                                    <th>Description</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><strong>90 - 100</strong></td>
                                    <td><span class="badge bg-success">Outstanding</span></td>
                                    <td>Performance is outstanding and exceeds expectations</td>
                                </tr>
                                <tr>
                                    <td><strong>85 - 89</strong></td>
                                    <td><span class="badge bg-info">Very Satisfactory</span></td>
                                    <td>Performance is very satisfactory and meets high standards</td>
                                </tr>
                                <tr>
                                    <td><strong>80 - 84</strong></td>
                                    <td><span class="badge bg-primary">Satisfactory</span></td>
                                    <td>Performance is satisfactory and meets expectations</td>
                                </tr>
                                <tr>
                                    <td><strong>75 - 79</strong></td>
                                    <td><span class="badge bg-warning">Fairly Satisfactory</span></td>
                                    <td>Performance is fairly satisfactory and needs improvement</td>
                                </tr>
                                <tr>
                                    <td><strong>Below 75</strong></td>
                                    <td><span class="badge bg-danger">Did Not Meet Expectations</span></td>
                                    <td>Performance did not meet the minimum expectations</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="alert alert-info mt-3">
                        <strong>Note:</strong> Final Grade is computed as the average of all four quarters (Q1, Q2, Q3, Q4).
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

@push('styles')
<style>
.report-card-table thead th {
    background: #f8f9fa;
    color: #212529;
    border: 1px solid #212529;
    font-weight: 700;
    text-align: center;
    vertical-align: middle;
}
.report-card-table td {
    border: 1px solid #212529;
    vertical-align: middle;
}
.general-average-row {
    background: #f1f3f5;
}
.grading-scale-table th,
.grading-scale-table td {
    border: 1px solid #212529;
}
.empty-state {
    padding: 40px 20px;
}
@media print {
    .page-header,
    .btn,
    .modal,
    .sidebar,
    .header {
        display: none !important;
    }
    .card {
        border: none;
        box-shadow: none;
    }
    .report-card-table {
        font-size: 12px;
    }
}
</style>
@endpush

@endsection
