@extends('layouts.master')
@section('content')
    {{-- message --}}
    {!! Toastr::message() !!}
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
                            <button type="button" class="btn btn-secondary" onclick="window.print()">
                                <i class="fas fa-print me-2"></i>Print
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

            <!-- Quarterly Grades Table -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-table me-2"></i>
                                Quarterly Grades
                                @if($currentAcademicYear)
                                    - {{ $currentAcademicYear->name }}
                                @endif
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover" id="gradesTable">
                                    <thead class="table-dark">
                                        <tr>
                                            <th style="width: 5%;">#</th>
                                            <th style="width: 35%;">Learning Area</th>
                                            <th style="width: 12%;" class="text-center">1st Quarter</th>
                                            <th style="width: 12%;" class="text-center">2nd Quarter</th>
                                            <th style="width: 12%;" class="text-center">3rd Quarter</th>
                                            <th style="width: 12%;" class="text-center">4th Quarter</th>
                                            <th style="width: 12%;" class="text-center">Final Grade</th>
                                            <th style="width: 12%;" class="text-center">Remarks</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @if($quarterlyGrades->count() > 0)
                                            @foreach($quarterlyGrades as $index => $quarterlyGrade)
                                                <tr>
                                                    <td class="text-center">{{ $index + 1 }}</td>
                                                    <td>
                                                        <strong>{{ $quarterlyGrade->subject->subject_name ?? 'N/A' }}</strong>
                                                    </td>
                                                    <td class="text-center">
                                                        @if($quarterlyGrade->quarter_1 !== null)
                                                            <span class="badge {{ $quarterlyGrade->quarter_1 >= 75 ? 'bg-success' : 'bg-danger' }}">
                                                                {{ number_format($quarterlyGrade->quarter_1, 2) }}
                                                            </span>
                                                        @else
                                                            <span class="text-muted">--</span>
                                                        @endif
                                                    </td>
                                                    <td class="text-center">
                                                        @if($quarterlyGrade->quarter_2 !== null)
                                                            <span class="badge {{ $quarterlyGrade->quarter_2 >= 75 ? 'bg-success' : 'bg-danger' }}">
                                                                {{ number_format($quarterlyGrade->quarter_2, 2) }}
                                                            </span>
                                                        @else
                                                            <span class="text-muted">--</span>
                                                        @endif
                                                    </td>
                                                    <td class="text-center">
                                                        @if($quarterlyGrade->quarter_3 !== null)
                                                            <span class="badge {{ $quarterlyGrade->quarter_3 >= 75 ? 'bg-success' : 'bg-danger' }}">
                                                                {{ number_format($quarterlyGrade->quarter_3, 2) }}
                                                            </span>
                                                        @else
                                                            <span class="text-muted">--</span>
                                                        @endif
                                                    </td>
                                                    <td class="text-center">
                                                        @if($quarterlyGrade->quarter_4 !== null)
                                                            <span class="badge {{ $quarterlyGrade->quarter_4 >= 75 ? 'bg-success' : 'bg-danger' }}">
                                                                {{ number_format($quarterlyGrade->quarter_4, 2) }}
                                                            </span>
                                                        @else
                                                            <span class="text-muted">--</span>
                                                        @endif
                                                    </td>
                                                    <td class="text-center">
                                                        @if($quarterlyGrade->final_grade !== null)
                                                            <span class="badge bg-primary" style="font-size: 14px; padding: 8px 12px;">
                                                                <strong>{{ number_format($quarterlyGrade->final_grade, 2) }}</strong>
                                                            </span>
                                                        @else
                                                            <span class="text-muted">--</span>
                                                        @endif
                                                    </td>
                                                    <td class="text-center">
                                                        @if($quarterlyGrade->remarks)
                                                            <span class="badge {{ $quarterlyGrade->id ? $quarterlyGrade->remarks_class : 'bg-secondary' }}" style="font-size: 12px;">
                                                                {{ $quarterlyGrade->remarks }}
                                                            </span>
                                                        @else
                                                            <span class="text-muted">--</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @else
                                            <tr>
                                                <td colspan="8" class="text-center py-5">
                                                    <div class="empty-state">
                                                        <i class="fas fa-clipboard-list fa-3x text-muted mb-3"></i>
                                                        <h5>No Grades Available</h5>
                                                        @if($currentAcademicYear)
                                                            <p class="text-muted">You don't have any quarterly grades recorded for {{ $currentAcademicYear->name }}.</p>
                                                        @else
                                                            <p class="text-muted">Please select an Academic Year to view your grades.</p>
                                                        @endif
                                                        <p class="text-muted">Grades will appear here once your teachers have entered them.</p>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endif
                                    </tbody>
                                </table>
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
.gpa-card {
    background: linear-gradient(135deg, #f8f9fa, #e9ecef);
    transition: all 0.3s ease;
}

.gpa-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.empty-state {
    padding: 40px 20px;
}

.empty-state i {
    color: #dee2e6;
}

.table th {
    background-color: #343a40;
    color: white;
    font-weight: 600;
    text-align: center;
    vertical-align: middle;
}

.table td {
    vertical-align: middle;
}

.badge {
    font-size: 0.875em;
    padding: 6px 10px;
}

/* DepEd Grading Scale Colors */
.remarks-outstanding { background-color: #28a745 !important; color: white; }
.remarks-very-satisfactory { background-color: #17a2b8 !important; color: white; }
.remarks-satisfactory { background-color: #007bff !important; color: white; }
.remarks-fairly-satisfactory { background-color: #ffc107 !important; color: #212529; }
.remarks-did-not-meet { background-color: #dc3545 !important; color: white; }

@media print {
    .page-header,
    .card-header:first-child,
    .btn,
    .modal {
        display: none !important;
    }
    
    .card {
        border: none;
        box-shadow: none;
    }
    
    .table {
        font-size: 12px;
    }
}
</style>
@endpush

@endsection
