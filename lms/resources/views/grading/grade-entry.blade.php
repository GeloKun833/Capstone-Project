@extends('layouts.master')
@section('content')

@php
    $quarterLabels = [1 => '1st Quarter', 2 => '2nd Quarter', 3 => '3rd Quarter', 4 => '4th Quarter'];
    $hasFilters = $selectedSectionId && in_array((int) $selectedQuarter, [1, 2, 3, 4], true) && $currentAcademicYear;
    $sectionName = $sections->where('id', $selectedSectionId)->first()->name ?? 'N/A';
    $stepQuery = fn ($step) => [
        'section_id' => $selectedSectionId,
        'quarter' => $selectedQuarter,
        'academic_year_id' => $currentAcademicYear->id ?? null,
        'step' => $step,
    ];
@endphp

<div class="page-wrapper">
    <div class="content container-fluid dir-page ge-page">
        <div class="page-header">
            <div class="row align-items-start">
                <div class="col">
                    <h3 class="page-title mb-1">Grade Entry</h3>
                    <p class="dir-subtitle">Enter quarterly grades, observed values, then print the summary.</p>
                </div>
                <div class="col-auto text-end">
                    <ul class="breadcrumb justify-content-end mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Grade Entry</li>
                    </ul>
                </div>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="ge-card ge-filters">
            <div class="ge-card-head">
                <h5>Load grade sheet</h5>
                <p>Choose the class, quarter, and school year first.</p>
            </div>
            @if($subjects->isEmpty() || $sections->isEmpty())
                <div class="alert alert-warning mx-3 mt-0">
                    You have no subject/section assignment yet. Ask Admin to assign you under
                    <strong>Classes &amp; Subjects</strong>.
                </div>
            @endif
            <form method="GET" action="{{ route('teacher.grading.grade-entry') }}" id="filterForm" class="px-3 pb-3">
                <input type="hidden" name="step" value="grades">
                <div class="row g-2 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label" for="selected_section">Section</label>
                        <select class="form-control form-select" name="section_id" id="selected_section" required @if($sections->isEmpty()) disabled @endif>
                            <option value="">Select section</option>
                            @foreach($sections as $section)
                                <option value="{{ $section->id }}" {{ (string) ($selectedSectionId ?? '') === (string) $section->id ? 'selected' : '' }}>
                                    {{ $section->name }} ({{ $section->grade_level ?? 'N/A' }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label" for="selected_quarter">Quarter</label>
                        <select class="form-control form-select" name="quarter" id="selected_quarter" required>
                            <option value="">Select quarter</option>
                            @foreach($quarterLabels as $num => $label)
                                <option value="{{ $num }}" {{ (int) ($selectedQuarter ?? 0) === $num ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label" for="selected_academic_year">Academic year</label>
                        <select class="form-control form-select" name="academic_year_id" id="selected_academic_year" required>
                            <option value="">Select year</option>
                            @foreach($academicYears as $year)
                                <option value="{{ $year->id }}" {{ $currentAcademicYear && $currentAcademicYear->id == $year->id ? 'selected' : '' }}>
                                    {{ $year->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary dir-btn w-100" @if($sections->isEmpty() || $subjects->isEmpty()) disabled @endif>
                            <i class="fas fa-search me-1"></i> Load
                        </button>
                    </div>
                </div>
            </form>
        </div>

        @if($hasFilters && $sectionSubjects->isNotEmpty() && $students->count() > 0)
            <div class="ge-steps">
                <span class="ge-step {{ $step === 'grades' ? 'is-active' : '' }}">1. Grade Entry</span>
                <span class="ge-step-line"></span>
                <span class="ge-step {{ $step === 'observed' ? 'is-active' : '' }}">2. Observed Values</span>
                <span class="ge-step-line"></span>
                <span class="ge-step {{ $step === 'summary' ? 'is-active' : '' }}">3. Summary &amp; Print</span>
            </div>

            @if($step === 'grades')
            <div class="ge-card" id="gradesStepCard">
                <div class="ge-card-head ge-card-head-row">
                    <div>
                        <h5>Step 1 — {{ $quarterLabels[(int) $selectedQuarter] }} grades</h5>
                        <p>{{ $sectionName }} · {{ $currentAcademicYear->name }} · {{ $students->count() }} students</p>
                    </div>
                    @if($hasQuarterGrades)
                        <a class="btn btn-outline-primary dir-btn"
                           href="{{ route('teacher.grading.grade-entry', $stepQuery('observed')) }}">
                            Continue to Observed Values
                        </a>
                    @endif
                </div>
                <div class="ge-note">Enter scores from 0–100 for each learning area, then save to continue.</div>
                <div class="table-responsive">
                    <table class="table dir-table mb-0" id="gradesTable">
                        <thead>
                            <tr>
                                <th style="width:48px">#</th>
                                <th>Student</th>
                                @foreach($sectionSubjects as $subject)
                                    <th class="text-center">{{ $subject->subject_name }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($students as $index => $student)
                                <tr>
                                    <td class="text-center dir-muted">{{ $index + 1 }}</td>
                                    <td>
                                        <span class="dir-person-name">{{ $student->last_name }}, {{ $student->first_name }}</span>
                                    </td>
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
                <div class="ge-footer">
                    <button type="button" class="btn btn-primary dir-btn" id="saveGradesBtn">
                        <i class="fas fa-save me-1"></i> Save Grades &amp; Continue
                    </button>
                </div>
            </div>
            @endif

            @if($step === 'observed' && $hasQuarterGrades)
            <div class="ge-card">
                <div class="ge-card-head">
                    <h5>Step 2 — Observed values ({{ $quarterLabels[(int) $selectedQuarter] }})</h5>
                    <p>AO Always Observed · SO Sometimes Observed · RO Rarely Observed</p>
                </div>
                <form method="POST" action="{{ route('teacher.grading.observed-values.store') }}" id="observedForm">
                    @csrf
                    <input type="hidden" name="section_id" value="{{ $selectedSectionId }}">
                    <input type="hidden" name="academic_year_id" value="{{ $currentAcademicYear->id }}">
                    <input type="hidden" name="quarter" value="{{ $selectedQuarter }}">

                    <div class="p-3">
                        @php $rIdx = 0; @endphp
                        @foreach($students as $student)
                            <div class="ge-student-block">
                                <h6>{{ $student->last_name }}, {{ $student->first_name }}</h6>
                                <div class="table-responsive">
                                    <table class="table dir-table observed-inline-table mb-0">
                                        <thead>
                                            <tr>
                                                <th style="width:18%">Core values</th>
                                                <th>Behavior statements</th>
                                                <th class="text-center" style="width:120px">Q{{ $selectedQuarter }}</th>
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
                                                            <td rowspan="{{ $items->count() }}" class="align-middle fw-bold">{{ $core }}</td>
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
                    </div>

                    <div class="ge-footer ge-footer-split">
                        <a class="btn btn-outline-secondary dir-btn" href="{{ route('teacher.grading.grade-entry', $stepQuery('grades')) }}">Back to grades</a>
                        <button type="submit" class="btn btn-primary dir-btn">
                            <i class="fas fa-save me-1"></i> Save Observed Values
                        </button>
                    </div>
                </form>
            </div>
            @endif

            @if($step === 'summary' && $hasQuarterGrades)
            <div class="ge-card">
                <div class="ge-card-head ge-card-head-row">
                    <div>
                        <h5>Step 3 — {{ $quarterLabels[(int) $selectedQuarter] }} average &amp; remarks</h5>
                        <p>{{ $sectionName }} · {{ $currentAcademicYear->name }}</p>
                    </div>
                    <a class="btn btn-primary dir-btn"
                       href="{{ route('teacher.grading.quarter-report', ['section_id'=>$selectedSectionId,'quarter'=>$selectedQuarter,'academic_year_id'=>$currentAcademicYear->id]) }}"
                       target="_blank">
                        <i class="fas fa-print me-1"></i> Printable form
                    </a>
                </div>
                <div class="table-responsive">
                    <table class="table dir-table mb-0">
                        <thead>
                            <tr>
                                <th style="width:48px">#</th>
                                <th>Student</th>
                                <th class="text-center">Quarter average</th>
                                <th class="text-center">Remarks</th>
                                <th class="text-end">Print</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($students as $index => $student)
                                @php $sum = $studentQuarterSummaries[$student->id] ?? ['average'=>null,'remark'=>'—']; @endphp
                                <tr>
                                    <td class="text-center dir-muted">{{ $index + 1 }}</td>
                                    <td><span class="dir-person-name">{{ $student->last_name }}, {{ $student->first_name }}</span></td>
                                    <td class="text-center">{{ $sum['average'] !== null ? number_format($sum['average'], 2) : '—' }}</td>
                                    <td class="text-center">
                                        @if(($sum['remark'] ?? '—') === 'Passed')
                                            <span class="dir-badge dir-badge--active">Passed</span>
                                        @elseif(($sum['remark'] ?? '—') === 'Failed')
                                            <span class="dir-badge dir-badge--disabled">Failed</span>
                                        @else
                                            <span class="dir-muted">—</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <a class="dir-icon-btn"
                                           target="_blank"
                                           title="Print"
                                           href="{{ route('teacher.grading.student-report', ['student'=>$student->id,'academic_year_id'=>$currentAcademicYear->id,'quarter'=>$selectedQuarter]) }}">
                                            <i class="fas fa-print"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="ge-footer ge-footer-split">
                    <a class="btn btn-outline-secondary dir-btn" href="{{ route('teacher.grading.grade-entry', $stepQuery('observed')) }}">Back to observed values</a>
                    <a class="btn btn-primary dir-btn" href="{{ route('teacher.grading.grade-entry', $stepQuery('grades')) }}">Edit grades</a>
                </div>
            </div>
            @endif

        @elseif($hasFilters && $sectionSubjects->isEmpty())
            <div class="ge-card">
                <div class="dir-empty">
                    <i class="fas fa-book d-block"></i>
                    <h5 class="mt-2 mb-1">No subjects for this section</h5>
                    <p class="mb-0">Ask Admin to assign subjects under Classes &amp; Subjects.</p>
                </div>
            </div>
        @elseif($hasFilters && $students->isEmpty())
            <div class="ge-card">
                <div class="dir-empty">
                    <i class="fas fa-user-graduate d-block"></i>
                    <h5 class="mt-2 mb-1">No students in this section</h5>
                    <p class="mb-0">There are no enrolled students to grade yet.</p>
                </div>
            </div>
        @else
            <div class="ge-card">
                <div class="dir-empty">
                    <i class="fas fa-edit d-block"></i>
                    <h5 class="mt-2 mb-1">Ready to enter grades</h5>
                    <p class="mb-0">Select section, quarter, and academic year, then load the grade sheet.</p>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/directory-modern.css') }}?v=20260914j">
<style>
.ge-page .page-header { margin-bottom: 0.85rem; }
.ge-card {
    background: #fff;
    border: 1px solid #e8eef7;
    border-radius: 18px;
    box-shadow: 0 14px 32px rgba(79, 114, 205, 0.05);
    overflow: hidden;
}
.ge-card + .ge-card, .ge-steps + .ge-card { margin-top: 0.9rem; }
.ge-filters { margin-bottom: 0.9rem; }
.ge-card-head { padding: 1rem 1.15rem 0.35rem; }
.ge-card-head h5 {
    margin: 0;
    font-size: 1.02rem;
    font-weight: 750;
    color: #1e293b;
}
.ge-card-head p {
    margin: 0.2rem 0 0;
    font-size: 0.8rem;
    color: #94a3b8;
}
.ge-card-head-row {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 0.75rem;
    flex-wrap: wrap;
    padding-bottom: 0.75rem;
}
.ge-filters .form-label {
    font-size: 0.72rem;
    font-weight: 700;
    letter-spacing: 0.04em;
    text-transform: uppercase;
    color: #94a3b8;
}
.ge-filters .form-control,
.ge-filters .form-select {
    border-radius: 10px;
    min-height: 42px;
    border-color: #e5e7eb;
}
.quarter-subject-input {
    text-align: center;
    font-weight: 650;
    min-width: 72px;
    border-radius: 10px;
}
.ge-steps {
    display: flex;
    align-items: center;
    gap: 0.55rem;
    flex-wrap: wrap;
    margin-bottom: 0.9rem;
}
.ge-step {
    display: inline-flex;
    align-items: center;
    min-height: 34px;
    padding: 0 0.8rem;
    border-radius: 999px;
    background: #f1f5f9;
    color: #64748b;
    font-size: 0.78rem;
    font-weight: 700;
}
.ge-step.is-active {
    background: #eef2ff;
    color: #4338ca;
}
.ge-step-line {
    width: 18px;
    height: 1px;
    background: #e2e8f0;
}
.ge-note {
    margin: 0 1.15rem 0.85rem;
    padding: 0.7rem 0.85rem;
    border-radius: 12px;
    background: #f8faff;
    color: #475569;
    font-size: 0.85rem;
}
.ge-footer {
    display: flex;
    justify-content: flex-end;
    gap: 0.5rem;
    padding: 0.9rem 1.15rem;
    border-top: 1px solid #eef2f7;
}
.ge-footer-split { justify-content: space-between; }
.ge-student-block {
    border: 1px solid #edf1f7;
    border-radius: 14px;
    padding: 0.85rem;
    margin-bottom: 0.75rem;
}
.ge-student-block h6 {
    font-weight: 750;
    margin-bottom: 0.65rem;
    color: #1e293b;
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
