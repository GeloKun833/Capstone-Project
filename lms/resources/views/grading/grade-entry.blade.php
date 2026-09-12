@extends('layouts.master')
@section('content')

@php
    $quarterLabels = [1 => '1st Quarter', 2 => '2nd Quarter', 3 => '3rd Quarter', 4 => '4th Quarter'];
    $hasFilters = $selectedSectionId && in_array((int) $selectedQuarter, [1, 2, 3, 4], true) && $currentAcademicYear;
    $sectionName = $sections->where('id', $selectedSectionId)->first()->name ?? 'N/A';
@endphp

<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">Quarterly Grade Entry</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Grade Entry</li>
                    </ul>
                </div>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-filter me-2"></i>Select Section, Quarter & Academic Year</h5>
            </div>
            <div class="card-body">
                @if($subjects->isEmpty() || $sections->isEmpty())
                    <div class="alert alert-warning mb-3">
                        You have no subject/section assignment yet. Ask Admin to assign you under
                        <strong>Classes &amp; Subjects</strong>.
                    </div>
                @endif
                <form method="GET" action="{{ route('teacher.grading.grade-entry') }}" id="filterForm">
                    <input type="hidden" name="step" value="grades">
                    <div class="row">
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Section *</label>
                            <select class="form-control form-select" name="section_id" id="selected_section" required @if($sections->isEmpty()) disabled @endif>
                                <option value="">-- Select Section --</option>
                                @foreach($sections as $section)
                                    <option value="{{ $section->id }}" {{ (string) ($selectedSectionId ?? '') === (string) $section->id ? 'selected' : '' }}>
                                        {{ $section->name }} ({{ $section->grade_level ?? 'N/A' }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Quarter *</label>
                            <select class="form-control form-select" name="quarter" id="selected_quarter" required>
                                <option value="">-- Select Quarter --</option>
                                @foreach($quarterLabels as $num => $label)
                                    <option value="{{ $num }}" {{ (int) ($selectedQuarter ?? 0) === $num ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Academic Year *</label>
                            <select class="form-control form-select" name="academic_year_id" id="selected_academic_year" required>
                                <option value="">-- Select Academic Year --</option>
                                @foreach($academicYears as $year)
                                    <option value="{{ $year->id }}" {{ $currentAcademicYear && $currentAcademicYear->id == $year->id ? 'selected' : '' }}>
                                        {{ $year->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="mt-3">
                        <button type="submit" class="btn btn-primary btn-lg" @if($sections->isEmpty() || $subjects->isEmpty()) disabled @endif>
                            <i class="fas fa-search me-2"></i>Load Grade Sheet
                        </button>
                    </div>
                </form>
            </div>
        </div>

        @if($hasFilters && $sectionSubjects->isNotEmpty() && $students->count() > 0)
            <div class="card mt-3">
                <div class="card-body py-2">
                    <div class="d-flex flex-wrap gap-2 align-items-center">
                        <span class="badge {{ $step === 'grades' ? 'ge-badge-active' : 'ge-badge-idle' }}">1. Grade Entry</span>
                        <i class="fas fa-chevron-right text-muted"></i>
                        <span class="badge {{ $step === 'observed' ? 'ge-badge-active' : 'ge-badge-idle' }}">2. Observed Values</span>
                        <i class="fas fa-chevron-right text-muted"></i>
                        <span class="badge {{ $step === 'summary' ? 'ge-badge-active' : 'ge-badge-idle' }}">3. Quarter Summary &amp; Print</span>
                    </div>
                </div>
            </div>

            {{-- STEP 1: Grades --}}
            @if($step === 'grades')
            <div class="card mt-3" id="gradesStepCard">
                <div class="card-header ge-header text-white">
                    <h5 class="mb-0">
                        Step 1 — {{ $quarterLabels[(int) $selectedQuarter] }} Grades —
                        {{ $sectionName }} ({{ $currentAcademicYear->name }})
                    </h5>
                </div>
                <div class="card-body">
                    <div class="alert ge-alert d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div>Enter grades for all learning areas, then <strong>Save Grades</strong>. Observed Values will open next.</div>
                        @if($hasQuarterGrades)
                            <a class="btn btn-outline-success btn-sm"
                               href="{{ route('teacher.grading.grade-entry', ['section_id'=>$selectedSectionId,'quarter'=>$selectedQuarter,'academic_year_id'=>$currentAcademicYear->id,'step'=>'observed']) }}">
                                Continue to Observed Values →
                            </a>
                        @endif
                    </div>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover" id="gradesTable">
                            <thead class="ge-thead">
                                <tr>
                                    <th>#</th>
                                    <th>Student Name</th>
                                    @foreach($sectionSubjects as $subject)
                                        <th class="text-center">{{ $subject->subject_name }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($students as $index => $student)
                                    <tr>
                                        <td class="text-center">{{ $index + 1 }}</td>
                                        <td><strong>{{ $student->last_name }}, {{ $student->first_name }}</strong></td>
                                        @foreach($sectionSubjects as $subject)
                                            @php $key = $student->id . '_' . $subject->id; @endphp
                                            <td>
                                                <input type="number" class="form-control quarter-subject-input"
                                                       data-student-id="{{ $student->id }}"
                                                       data-subject-id="{{ $subject->id }}"
                                                       value="{{ $gradeMap[$key] ?? '' }}"
                                                       min="0" max="100" step="0.01" placeholder="—">
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="text-end mt-3">
                        <button type="button" class="btn btn-success btn-lg" id="saveGradesBtn">
                            <i class="fas fa-save me-2"></i>Save Grades &amp; Continue
                        </button>
                    </div>
                </div>
            </div>
            @endif

            {{-- STEP 2: Observed Values (only after grades exist) --}}
            @if($step === 'observed' && $hasQuarterGrades)
            <div class="card mt-3">
                <div class="card-header ge-header text-white">
                    <h5 class="mb-0 text-uppercase">
                        Step 2 — Report on Learner&rsquo;s Observed Values
                        ({{ $quarterLabels[(int) $selectedQuarter] }})
                    </h5>
                </div>
                <div class="card-body">
                    <p class="text-muted small">
                        Marking: <strong>AO</strong> Always Observed |
                        <strong>SO</strong> Sometimes Observed |
                        <strong>RO</strong> Rarely Observed
                        — for <strong>{{ $quarterLabels[(int) $selectedQuarter] }}</strong> only.
                    </p>

                    <form method="POST" action="{{ route('teacher.grading.observed-values.store') }}" id="observedForm">
                        @csrf
                        <input type="hidden" name="section_id" value="{{ $selectedSectionId }}">
                        <input type="hidden" name="academic_year_id" value="{{ $currentAcademicYear->id }}">
                        <input type="hidden" name="quarter" value="{{ $selectedQuarter }}">

                        @php $rIdx = 0; @endphp
                        @foreach($students as $student)
                            <div class="border rounded mb-3 p-3">
                                <h6 class="fw-bold mb-2">{{ $student->last_name }}, {{ $student->first_name }}</h6>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-sm mb-0 observed-inline-table">
                                        <thead>
                                            <tr>
                                                <th style="width:18%">Core Values</th>
                                                <th>Behavior Statements</th>
                                                <th class="text-center" style="width:14%">Q{{ $selectedQuarter }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($observedIndicators as $core => $items)
                                                @foreach($items as $i => $indicator)
                                                    @php
                                                        $key = $student->id . '_' . $indicator->id;
                                                        $mark = $observedMap[$key] ?? '';
                                                    @endphp
                                                    <tr>
                                                        @if($i === 0)
                                                            <td rowspan="{{ $items->count() }}" class="fw-bold align-middle">{{ $core }}</td>
                                                        @endif
                                                        <td>
                                                            {{ $indicator->statement }}
                                                            <input type="hidden" name="ratings[{{ $rIdx }}][student_id]" value="{{ $student->id }}">
                                                            <input type="hidden" name="ratings[{{ $rIdx }}][indicator_id]" value="{{ $indicator->id }}">
                                                        </td>
                                                        <td>
                                                            <select class="form-select form-select-sm" name="ratings[{{ $rIdx }}][mark]">
                                                                <option value="">—</option>
                                                                @foreach(['AO','SO','RO'] as $opt)
                                                                    <option value="{{ $opt }}" {{ $mark === $opt ? 'selected' : '' }}>{{ $opt }}</option>
                                                                @endforeach
                                                            </select>
                                                        </td>
                                                    </tr>
                                                    @php $rIdx++; @endphp
                                                @endforeach
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endforeach

                        <div class="d-flex justify-content-between">
                            <a class="btn btn-secondary"
                               href="{{ route('teacher.grading.grade-entry', ['section_id'=>$selectedSectionId,'quarter'=>$selectedQuarter,'academic_year_id'=>$currentAcademicYear->id,'step'=>'grades']) }}">
                                Back to Grades
                            </a>
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="fas fa-save me-2"></i>Save Observed Values &amp; Show Summary
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            @endif

            {{-- STEP 3: Summary + Print --}}
            @if($step === 'summary' && $hasQuarterGrades)
            <div class="card mt-3">
                <div class="card-header ge-header text-white d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <h5 class="mb-0">
                        Step 3 — {{ $quarterLabels[(int) $selectedQuarter] }} Average &amp; Remarks
                        ({{ $sectionName }})
                    </h5>
                    <a class="btn btn-light btn-sm ge-print-btn"
                       href="{{ route('teacher.grading.quarter-report', ['section_id'=>$selectedSectionId,'quarter'=>$selectedQuarter,'academic_year_id'=>$currentAcademicYear->id]) }}"
                       target="_blank">
                        <i class="fas fa-print me-1"></i> Open Printable Form
                    </a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="ge-thead">
                                <tr>
                                    <th>#</th>
                                    <th>Student</th>
                                    <th class="text-center">Quarter Average</th>
                                    <th class="text-center">Auto Remarks</th>
                                    <th class="text-center">Print</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($students as $index => $student)
                                    @php $sum = $studentQuarterSummaries[$student->id] ?? ['average'=>null,'remark'=>'—']; @endphp
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $student->last_name }}, {{ $student->first_name }}</td>
                                        <td class="text-center fw-bold">
                                            {{ $sum['average'] !== null ? number_format($sum['average'], 2) : '—' }}
                                        </td>
                                        <td class="text-center">
                                            @if(($sum['remark'] ?? '—') === 'Passed')
                                                <span class="badge bg-success">Passed</span>
                                            @elseif(($sum['remark'] ?? '—') === 'Failed')
                                                <span class="badge bg-danger">Failed</span>
                                            @else
                                                —
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <a class="btn btn-sm btn-outline-success"
                                               target="_blank"
                                               href="{{ route('teacher.grading.student-report', ['student'=>$student->id,'academic_year_id'=>$currentAcademicYear->id,'quarter'=>$selectedQuarter]) }}">
                                                <i class="fas fa-print"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="d-flex gap-2">
                        <a class="btn btn-outline-secondary"
                           href="{{ route('teacher.grading.grade-entry', ['section_id'=>$selectedSectionId,'quarter'=>$selectedQuarter,'academic_year_id'=>$currentAcademicYear->id,'step'=>'observed']) }}">
                            Back to Observed Values
                        </a>
                        <a class="btn btn-success"
                           href="{{ route('teacher.grading.grade-entry', ['section_id'=>$selectedSectionId,'quarter'=>$selectedQuarter,'academic_year_id'=>$currentAcademicYear->id,'step'=>'grades']) }}">
                            Edit Grades
                        </a>
                    </div>
                </div>
            </div>
            @endif

        @elseif($hasFilters && $sectionSubjects->isEmpty())
            <div class="alert alert-warning mt-3">No subjects for this section.</div>
        @elseif($hasFilters && $students->isEmpty())
            <div class="alert alert-warning mt-3">No students in this section.</div>
        @else
            <div class="card mt-4">
                <div class="card-body text-center py-5">
                    <h4>Ready to Enter Grades</h4>
                    <p class="text-muted mb-0">Select Section, Quarter, and Academic Year to begin.</p>
                </div>
            </div>
        @endif
    </div>
</div>

@push('styles')
<style>
:root {
    --ge-accent: #2f6f4e;
    --ge-accent-soft: #e8f3ec;
    --ge-accent-mid: #3d8b63;
}
.quarter-subject-input { text-align: center; font-weight: 600; }
.observed-inline-table th, .observed-inline-table td { border: 1px solid #c5d5cb; vertical-align: middle; }
.ge-header {
    background: linear-gradient(135deg, #2f6f4e 0%, #3d8b63 100%) !important;
    border-bottom: none;
}
.ge-thead th {
    background: #e8f3ec !important;
    color: #1f4d35 !important;
    border-color: #c5d5cb !important;
    font-weight: 700;
}
.ge-badge-active {
    background: var(--ge-accent) !important;
    color: #fff !important;
}
.ge-badge-idle {
    background: #e9ecef !important;
    color: #495057 !important;
}
.ge-alert {
    background: var(--ge-accent-soft);
    border: 1px solid #c5d5cb;
    color: #1f4d35;
    border-radius: .375rem;
    padding: .75rem 1rem;
}
.ge-print-btn {
    color: var(--ge-accent) !important;
    border: 1px solid #fff;
    font-weight: 600;
}
.ge-print-btn:hover {
    background: #fff !important;
    color: #1f4d35 !important;
}
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    $('#saveGradesBtn').on('click', function(e) {
        e.preventDefault();
        const sectionId = $('#selected_section').val();
        const quarter = $('#selected_quarter').val();
        const academicYearId = $('#selected_academic_year').val();
        if (!sectionId || !quarter || !academicYearId) {
            toastr.error('Please select Section, Quarter, and Academic Year.');
            return;
        }

        const grades = [];
        $('.quarter-subject-input').each(function() {
            const value = $(this).val();
            if (value === '' || value === null) return;
            const num = parseFloat(value);
            if (isNaN(num) || num < 0 || num > 100) return;
            grades.push({
                student_id: parseInt($(this).data('student-id'), 10),
                subject_id: parseInt($(this).data('subject-id'), 10),
                score: num
            });
        });

        if (!grades.length) {
            toastr.warning('Enter at least one grade before saving.');
            return;
        }

        const $btn = $(this);
        const original = $btn.html();
        $btn.html('<i class="fas fa-spinner fa-spin me-2"></i>Saving...').prop('disabled', true);

        $.ajax({
            url: '{{ route("teacher.grading.store-quarterly-grades") }}',
            type: 'POST',
            dataType: 'json',
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
            data: {
                _token: '{{ csrf_token() }}',
                section_id: sectionId,
                quarter: quarter,
                academic_year_id: academicYearId,
                grades: grades
            },
            success: function(response) {
                if (response && response.success) {
                    toastr.success(response.message || 'Grades saved.');
                    const url = new URL('{{ route("teacher.grading.grade-entry") }}', window.location.origin);
                    url.searchParams.set('section_id', sectionId);
                    url.searchParams.set('quarter', quarter);
                    url.searchParams.set('academic_year_id', academicYearId);
                    url.searchParams.set('step', 'observed');
                    setTimeout(function() { window.location.href = url.toString(); }, 800);
                } else {
                    toastr.warning(response.message || 'Saved with warnings.');
                    $btn.html(original).prop('disabled', false);
                }
            },
            error: function(xhr) {
                toastr.error((xhr.responseJSON && xhr.responseJSON.message) || 'Failed to save grades.');
                $btn.html(original).prop('disabled', false);
            }
        });
    });
});
</script>
@endpush

@endsection
