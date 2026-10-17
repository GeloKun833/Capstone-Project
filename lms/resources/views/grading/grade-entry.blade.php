@extends('layouts.master')
@section('content')

@php
    $quarterLabels = collect(\App\Support\SchoolQuarter::periods($currentAcademicYear ?? null))
        ->mapWithKeys(fn ($period) => [$period['number'] => $period['label']])
        ->all();
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

        <div class="alert {{ \App\Support\GradeEncoding::isWindowOpen() ? 'alert-info' : 'alert-warning' }}">
            {{ \App\Support\GradeEncoding::windowNote() }}
        </div>

        <div class="ge-card ge-filters">
            <div class="ge-card-head">
                <h5>Grade sheet</h5>
                <p>Choose a term and a section. The list opens for the current academic year.</p>
            </div>
            @if($sections->isEmpty())
                <div class="alert alert-warning mx-3 mt-0">
                    No teaching assignments are available for the current Academic Year.
                </div>
            @endif
            @if($currentAcademicYear && ! $currentPeriod)
                <div class="alert alert-warning mx-3 mt-0">
                    Today is outside the quarters for {{ $currentAcademicYear->name }}.
                </div>
            @endif
            <form method="GET" action="{{ route('teacher.grading.grade-entry') }}" id="filterForm" class="px-3 pb-3">
                <input type="hidden" name="step" value="{{ $step }}">
                <input type="hidden" id="selected_academic_year" value="{{ $currentAcademicYear->id ?? '' }}">
                <div class="row g-2 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label" for="academic_year_label">Academic Year</label>
                        <input type="text" class="form-control ge-locked" id="academic_year_label" value="{{ $currentAcademicYear->name ?? 'No current academic year' }}" readonly tabindex="-1">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="selected_quarter">Term</label>
                        <select class="form-control form-select" name="quarter" id="selected_quarter" @if(empty($gradingPeriods)) disabled @endif>
                            @forelse($gradingPeriods as $period)
                                <option value="{{ $period['number'] }}" {{ (int) $selectedQuarter === (int) $period['number'] ? 'selected' : '' }}>
                                    {{ $period['label'] }} · {{ $period['start']->format('M j, Y') }} – {{ $period['end']->format('M j, Y') }}
                                </option>
                            @empty
                                <option value="">No terms for this year</option>
                            @endforelse
                        </select>
                    </div>
                    <div class="col-md-5">
                        <label class="form-label" for="selected_section">Section</label>
                        <select class="form-control form-select" name="section_id" id="selected_section" required @if($sections->isEmpty() || empty($gradingPeriods)) disabled @endif>
                            <option value="">Select section</option>
                            @foreach($sections as $section)
                                <option value="{{ $section->id }}" {{ (string) ($selectedSectionId ?? '') === (string) $section->id ? 'selected' : '' }}>
                                    {{ $section->grade_level ? $section->grade_level.' · ' : '' }}{{ $section->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </form>
        </div>

        @if($hasFilters && ! $encodingOpen && \App\Support\GradeEncoding::isWindowOpen())
            <div class="alert alert-warning">
                {{ \App\Support\GradeEncoding::closedMessage(optional($sections->firstWhere('id', (int) $selectedSectionId))->grade_level) }}
            </div>
        @endif

        @if($hasFilters && $encodingOpen && $sectionSubjects->isNotEmpty() && $students->count() > 0)
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
                        <h5>{{ $descriptive ? 'Descriptive Method' : 'Numerical Method' }} — {{ $quarterLabels[(int) $selectedQuarter] ?? 'Current term' }}</h5>
                        <p>
                            {{ $sectionName }} uses the {{ $descriptive ? 'Descriptive' : 'Numerical' }} Method
                            · {{ $currentAcademicYear->name }}
                            · Report status: {{ \App\Services\ReportCardService::reportStatus($gradedCount, $expectedCount) }}
                            · {{ $gradedCount }} / {{ $expectedCount }} {{ $descriptive ? 'assessed' : 'graded' }}
                            @if($expectedCount > $gradedCount)
                                · {{ $expectedCount - $gradedCount }} missing
                            @endif
                        </p>
                    </div>
                    @if($hasQuarterGrades)
                        <a class="btn btn-outline-primary dir-btn"
                           href="{{ route('teacher.grading.grade-entry', $stepQuery('observed')) }}">
                            Continue to Observed Values
                        </a>
                    @endif
                </div>
                @if($descriptive)
                    <div class="ge-note">
                        Choose a subject, then click a letter once. Letters under All subjects set every subject for that student. Letters under the subject title set every student. Fill blank cells covers only the empty ones. Remarks is where you write outstanding comments for this subject.
                    </div>
                    <div class="ge-bulk">
                        <span>Fill blank cells</span>
                        @foreach($descriptiveScale as $letter => $meta)
                            <button type="button" class="ge-letter ge-fill-blank" data-letter="{{ $letter }}" title="{{ $meta['english'] }} — empty cells only">{{ $letter }}</button>
                        @endforeach
                        <button type="button" class="ge-clear" id="clearAllLetters">Clear all</button>
                    </div>
                @else
                    <div class="ge-note">Open a subject tab, type each student’s score from 0 to 100, and write a remark if you want. Use “Fill blank scores” only for students who do not have a score yet.</div>
                @endif
                <div id="gradesTable" data-method="{{ $descriptive ? 'descriptive' : 'numerical' }}">
                    <div class="ge-switch" role="tablist">
                        @foreach($sectionSubjects as $subject)
                            @php
                                $subjectDone = 0;
                                foreach ($students as $student) {
                                    $mark = $gradeMap[$student->id.'_'.$subject->id] ?? '';
                                    if ($mark !== '' && $mark !== null) {
                                        $subjectDone++;
                                    }
                                }
                            @endphp
                            <button type="button" class="ge-switch-btn {{ $loop->first ? 'is-active' : '' }}" data-subject-tab="{{ $subject->id }}">
                                {{ $subject->subject_name }}
                                <span class="ge-switch-count">{{ $subjectDone }}/{{ $students->count() }}</span>
                            </button>
                        @endforeach
                    </div>
                    @if($descriptive)
                        <div class="ge-legend">
                            @foreach($descriptiveScale as $letter => $meta)
                                <p><strong>{{ $letter }} — {{ $meta['english'] }} ({{ $meta['filipino'] }}).</strong> {{ $meta['description'] }}</p>
                            @endforeach
                        </div>
                    @endif
                    @foreach($sectionSubjects as $subject)
                        <section class="ge-sheet {{ $loop->first ? 'is-active' : '' }}" data-subject-panel="{{ $subject->id }}">
                            <div class="ge-sheet-head">
                                <div>
                                    <strong>{{ $subject->subject_name }}</strong>
                                    @if($descriptive)
                                        <span>Set every student in this subject</span>
                                    @else
                                        <span>Score for this subject, from 0 to 100.</span>
                                    @endif
                                </div>
                                @if($descriptive)
                                    <div class="ge-quick">
                                        @foreach($descriptiveScale as $letter => $meta)
                                            <button type="button" class="ge-letter ge-fill-col" data-subject-id="{{ $subject->id }}" data-letter="{{ $letter }}" title="Set every student to {{ $letter }} — {{ $meta['english'] }} ({{ $meta['filipino'] }}). {{ $meta['description'] }}">{{ $letter }}</button>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="ge-fill-empty">
                                        <span>Students with no score yet</span>
                                        <input type="number" class="form-control form-control-sm ge-empty-score" min="0" max="100" step="0.01" placeholder="0–100" aria-label="Score for students with no score in {{ $subject->subject_name }}">
                                        <button type="button" class="btn btn-outline-secondary dir-btn ge-fill-empty-btn" data-subject-id="{{ $subject->id }}">Fill blank scores</button>
                                    </div>
                                @endif
                            </div>
                            <ul class="ge-roster">
                                @foreach($students as $index => $student)
                                    @php $key = $student->id . '_' . $subject->id; @endphp
                                    <li class="ge-roster-row {{ $descriptive ? '' : 'is-score' }}">
                                        <div class="ge-who">
                                            <span class="ge-num">{{ $index + 1 }}</span>
                                            <span class="dir-person-name">{{ $student->last_name }}, {{ $student->first_name }}</span>
                                        </div>
                                        @if($descriptive)
                                        <div class="ge-side">
                                            <span class="ge-side-label">All subjects</span>
                                            <div class="ge-quick">
                                                @foreach($descriptiveScale as $letter => $meta)
                                                    <button type="button" class="ge-letter ge-fill-row" data-student-id="{{ $student->id }}" data-letter="{{ $letter }}" title="Set every subject to {{ $letter }} — {{ $meta['english'] }} ({{ $meta['filipino'] }}). {{ $meta['description'] }}">{{ $letter }}</button>
                                                @endforeach
                                            </div>
                                        </div>
                                        @endif
                                        <div class="ge-cell">
                                            <span class="ge-side-label">{{ $descriptive ? 'This subject' : 'Score' }}</span>
                                            @if($descriptive)
                                                <input type="hidden" class="quarter-subject-input"
                                                       data-student-id="{{ $student->id }}"
                                                       data-subject-id="{{ $subject->id }}"
                                                       value="{{ $gradeMap[$key] ?? '' }}">
                                                <div class="ge-quick">
                                                    @foreach($descriptiveScale as $letter => $meta)
                                                        <button type="button" class="ge-letter {{ ($gradeMap[$key] ?? '') === $letter ? 'is-on' : '' }}" data-letter="{{ $letter }}" title="{{ $letter }} — {{ $meta['english'] }} ({{ $meta['filipino'] }}). {{ $meta['description'] }}">{{ $letter }}</button>
                                                    @endforeach
                                                </div>
                                                <p class="ge-letter-desc">
                                                    @if(!empty($gradeMap[$key]) && isset($descriptiveScale[$gradeMap[$key]]))
                                                        {{ $gradeMap[$key] }} — {{ $descriptiveScale[$gradeMap[$key]]['english'] }} ({{ $descriptiveScale[$gradeMap[$key]]['filipino'] }}). {{ $descriptiveScale[$gradeMap[$key]]['description'] }}
                                                    @endif
                                                </p>
                                            @else
                                                <input type="number" class="form-control quarter-subject-input"
                                                       data-student-id="{{ $student->id }}"
                                                       data-subject-id="{{ $subject->id }}"
                                                       value="{{ $gradeMap[$key] ?? '' }}"
                                                       min="0" max="100" step="0.01" placeholder="—">
                                            @endif
                                        </div>
                                        <div class="ge-remark">
                                            <span class="ge-side-label">Remarks</span>
                                            <textarea class="form-control ge-comment" rows="2" maxlength="500"
                                                      data-student-id="{{ $student->id }}"
                                                      data-subject-id="{{ $subject->id }}"
                                                      placeholder="Outstanding comments">{{ $commentMap[$key] ?? '' }}</textarea>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        </section>
                    @endforeach
                </div>
                <div class="ge-footer">
                    <button type="button" class="btn btn-primary dir-btn" id="saveGradesBtn">
                        <i class="fas fa-save me-1"></i> Save Grades &amp; Continue
                    </button>
                </div>
            </div>
            @endif

            @if($step === 'observed' && $hasQuarterGrades)
            @php
                $observedMarks = [
                    'AO' => 'Always Observed',
                    'SO' => 'Sometimes Observed',
                    'RO' => 'Rarely Observed',
                ];
                $observedFlat = collect($observedIndicators)->flatten(1);
            @endphp
            <div class="ge-card">
                <div class="ge-card-head">
                    <h5>Step 2 — Observed values ({{ $quarterLabels[(int) $selectedQuarter] }})</h5>
                    <p>Choose one behavior. Click AO, SO, or RO once. All behaviors sets every behavior for that student. The letters under the behavior set every student. Fill blank cells covers only the empty ones.</p>
                </div>
                <form method="POST" action="{{ route('teacher.grading.observed-values.store') }}" id="observedForm">
                    @csrf
                    <input type="hidden" name="section_id" value="{{ $selectedSectionId }}">
                    <input type="hidden" name="academic_year_id" value="{{ $currentAcademicYear->id }}">
                    <input type="hidden" name="quarter" value="{{ $selectedQuarter }}">

                    <div class="ge-bulk">
                        <span>Fill blank cells</span>
                        @foreach($observedMarks as $code => $label)
                            <button type="button" class="ge-letter ov-mark-btn ov-fill-blank" data-mark="{{ $code }}" title="{{ $label }} — empty cells only">{{ $code }}</button>
                        @endforeach
                        <button type="button" class="ge-clear" id="clearAllObserved">Clear all</button>
                    </div>

                    <div id="observedTable">
                        <div class="ge-legend">
                            @foreach($observedMarks as $code => $label)
                                <p><strong>{{ $code }} — {{ $label }}.</strong></p>
                            @endforeach
                        </div>
                        <div class="ge-switch">
                            @foreach($observedIndicators as $core => $items)
                                <div class="ge-switch-group">
                                    <span>{{ $core }}</span>
                                    @foreach($items as $indicator)
                                        @php
                                            $behaviorDone = 0;
                                            foreach ($students as $student) {
                                                $saved = $observedMap[$student->id.'_'.$indicator->id] ?? '';
                                                if ($saved !== '' && $saved !== null) {
                                                    $behaviorDone++;
                                                }
                                            }
                                        @endphp
                                        <button type="button" class="ge-switch-btn {{ $observedFlat->first() && (int) $observedFlat->first()->id === (int) $indicator->id ? 'is-active' : '' }}" data-indicator-tab="{{ $indicator->id }}" title="{{ $indicator->statement }}">
                                            {{ \Illuminate\Support\Str::limit($indicator->statement, 36) }}
                                            <span class="ge-switch-count">{{ $behaviorDone }}/{{ $students->count() }}</span>
                                        </button>
                                    @endforeach
                                </div>
                            @endforeach
                        </div>
                        @php $rIdx = 0; $firstIndicator = true; @endphp
                        @foreach($observedFlat as $indicator)
                            <section class="ge-sheet {{ $firstIndicator ? 'is-active' : '' }}" data-indicator-panel="{{ $indicator->id }}">
                                <div class="ge-sheet-head">
                                    <div>
                                        <strong>{{ $indicator->core_value }}</strong>
                                        <span>{{ $indicator->statement }}</span>
                                        <span>Set every student for this behavior</span>
                                    </div>
                                    <div class="ge-quick">
                                        @foreach($observedMarks as $code => $label)
                                            <button type="button" class="ge-letter ov-mark-btn ov-fill-col" data-indicator-id="{{ $indicator->id }}" data-mark="{{ $code }}" title="Set every student to {{ $code }} — {{ $label }}">{{ $code }}</button>
                                        @endforeach
                                    </div>
                                </div>
                                <ul class="ge-roster">
                                    @foreach($students as $index => $student)
                                        @php
                                            $key = $student->id . '_' . $indicator->id;
                                            $mark = $observedMap[$key] ?? '';
                                        @endphp
                                        <li class="ge-roster-row">
                                            <div class="ge-who">
                                                <span class="ge-num">{{ $index + 1 }}</span>
                                                <span class="dir-person-name">{{ $student->last_name }}, {{ $student->first_name }}</span>
                                            </div>
                                            <div class="ge-side">
                                                <span class="ge-side-label">All behaviors</span>
                                                <div class="ge-quick">
                                                    @foreach($observedMarks as $code => $label)
                                                        <button type="button" class="ge-letter ov-mark-btn ov-fill-row" data-student-id="{{ $student->id }}" data-mark="{{ $code }}" title="Set every behavior to {{ $code }} — {{ $label }}">{{ $code }}</button>
                                                    @endforeach
                                                </div>
                                            </div>
                                            <div class="ov-cell">
                                                <span class="ge-side-label">This behavior</span>
                                                <input type="hidden" name="ratings[{{ $rIdx }}][student_id]" value="{{ $student->id }}">
                                                <input type="hidden" name="ratings[{{ $rIdx }}][indicator_id]" value="{{ $indicator->id }}">
                                                <input type="hidden" class="ov-mark" name="ratings[{{ $rIdx }}][mark]" value="{{ $mark }}" data-student-id="{{ $student->id }}" data-indicator-id="{{ $indicator->id }}">
                                                <div class="ge-quick">
                                                    @foreach($observedMarks as $code => $label)
                                                        <button type="button" class="ge-letter ov-mark-btn ov-pick {{ $mark === $code ? 'is-on' : '' }}" data-mark="{{ $code }}" title="{{ $code }} — {{ $label }}">{{ $code }}</button>
                                                    @endforeach
                                                </div>
                                                <p class="ge-letter-desc">@if($mark !== '' && isset($observedMarks[$mark])){{ $mark }} — {{ $observedMarks[$mark] }}@endif</p>
                                            </div>
                                        </li>
                                        @php $rIdx++; @endphp
                                    @endforeach
                                </ul>
                            </section>
                            @php $firstIndicator = false; @endphp
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
                        <h5>Step 3 — {{ $quarterLabels[(int) $selectedQuarter] }} {{ $descriptive ? 'assessments' : 'average & remarks' }}</h5>
                        <p>{{ $sectionName }} · {{ $currentAcademicYear->name }} · Report status: {{ \App\Services\ReportCardService::reportStatus($gradedCount, $expectedCount) }}</p>
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
                                <th class="text-center">{{ $descriptive ? 'Assessed' : 'Quarter average' }}</th>
                                <th class="text-center">Report status</th>
                                <th class="text-center">{{ $descriptive ? 'Missing' : 'Remarks' }}</th>
                                <th class="text-end">Print</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($students as $index => $student)
                                @php $sum = $studentQuarterSummaries[$student->id] ?? ['average'=>null,'remark'=>'—']; @endphp
                                <tr>
                                    <td class="text-center dir-muted">{{ $index + 1 }}</td>
                                    <td><span class="dir-person-name">{{ $student->last_name }}, {{ $student->first_name }}</span></td>
                                    <td class="text-center">
                                        @if($descriptive)
                                            {{ $sum['filled'] ?? 0 }} / {{ $sectionSubjects->count() }}
                                        @else
                                            {{ $sum['average'] !== null ? number_format($sum['average'], 2) : '—' }}
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @php $studentStatus = \App\Services\ReportCardService::reportStatus((int) ($sum['filled'] ?? 0), $sectionSubjects->count()); @endphp
                                        @if($studentStatus === 'Complete')
                                            <span class="dir-badge dir-badge--active">Complete</span>
                                        @elseif($studentStatus === 'Incomplete')
                                            <span class="dir-badge dir-badge--disabled">Incomplete</span>
                                        @else
                                            <span class="dir-muted">Not started</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($descriptive)
                                            {{ $sum['missing'] ?? 0 }}
                                        @elseif(($sum['remark'] ?? '—') === 'Passed')
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
.ge-locked { background: #fafaf9; color: #1c1917; pointer-events: none; }
.ge-quick {
    display: flex;
    flex-wrap: wrap;
    gap: 0.25rem;
    margin-top: 0.35rem;
}
.ge-letter {
    width: 28px;
    height: 28px;
    padding: 0;
    border-radius: 8px;
    border: 1px solid #e5e7eb;
    background: #fff;
    color: #334155;
    font-size: 0.78rem;
    font-weight: 750;
    line-height: 1;
    cursor: pointer;
}
.ge-letter.is-on,
.ge-cell .ge-letter.is-on {
    background: #3d5ee1;
    border-color: #3d5ee1;
    color: #fff;
}
.ge-bulk {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 0.35rem;
    margin: 0 1.15rem 0.85rem;
}
.ge-bulk > span {
    font-size: 0.78rem;
    font-weight: 700;
    color: #64748b;
    margin-right: 0.15rem;
}
.ge-bulk .form-control { width: 110px; min-height: 36px; }
.ge-clear, .ge-apply {
    border: 0;
    background: transparent;
    color: #64748b;
    font-size: 0.78rem;
    font-weight: 700;
    padding: 0.2rem 0.35rem;
}
.ge-quick-score { align-items: center; }
.ge-quick-score .form-control { width: 88px; min-height: 32px; text-align: center; }
.ge-cell { min-width: 0; }
.ge-switch { display: flex; flex-wrap: wrap; gap: 0.45rem; padding: 0 1.15rem 0.85rem; }
.ge-switch-group { display: flex; flex-wrap: wrap; gap: 0.4rem; align-items: center; width: 100%; }
.ge-switch-group > span {
    font-size: 0.68rem;
    font-weight: 750;
    letter-spacing: 0.04em;
    text-transform: uppercase;
    color: #3d5ee1;
    margin-right: 0.15rem;
}
.ge-switch-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    border: 1px solid #e5e7eb;
    background: #fff;
    color: #334155;
    border-radius: 999px;
    padding: 0.35rem 0.7rem;
    font-size: 0.8rem;
    font-weight: 700;
}
.ge-switch-btn.is-active { background: #eef2ff; border-color: #c7d2fe; color: #312e81; }
.ge-switch-count { font-size: 0.72rem; color: #64748b; font-weight: 700; }
.ge-switch-btn.is-active .ge-switch-count { color: #4338ca; }
.ge-sheet { display: none; margin: 0 1.15rem 1rem; border: 1px solid #eef2f7; border-radius: 14px; overflow: hidden; }
.ge-sheet.is-active { display: block; }
.ge-sheet-head {
    display: flex;
    justify-content: space-between;
    gap: 0.75rem;
    align-items: center;
    flex-wrap: wrap;
    padding: 0.85rem 1rem;
    background: #f8fafc;
    border-bottom: 1px solid #eef2f7;
}
.ge-sheet-head strong { display: block; color: #111827; }
.ge-sheet-head span { display: block; color: #64748b; font-size: 0.8rem; }
.ge-sheet-head .ge-quick { margin-top: 0; }
.ge-roster { list-style: none; margin: 0; padding: 0; }
.ge-roster-row {
    display: grid;
    gap: 0.75rem;
    align-items: center;
    padding: 0.75rem 1rem;
    border-top: 1px solid #f1f5f9;
}
#gradesTable .ge-roster-row:not(.is-score) {
    grid-template-columns: minmax(160px, 1.1fr) minmax(140px, 0.8fr) minmax(150px, 0.9fr) minmax(180px, 1.1fr);
}
#gradesTable .ge-roster-row.is-score {
    grid-template-columns: minmax(180px, 1.4fr) 150px minmax(220px, 1.5fr);
}
.ge-fill-empty {
    display: flex;
    align-items: center;
    gap: 0.45rem;
    flex-wrap: wrap;
}
.ge-fill-empty span { display: inline; color: #64748b; font-size: 0.8rem; }
.ge-fill-empty .form-control { width: 96px; min-height: 36px; text-align: center; }
#gradesTable .is-score .quarter-subject-input {
    max-width: 120px;
    text-align: center;
    font-weight: 700;
}
#observedTable .ge-roster-row {
    grid-template-columns: minmax(180px, 1.2fr) minmax(160px, 0.9fr) minmax(200px, 1.1fr);
}
.ge-roster-row:first-child { border-top: 0; }
.ge-who { display: flex; align-items: center; gap: 0.55rem; min-width: 0; }
.ge-num { color: #94a3b8; font-weight: 700; width: 1.4rem; }
.ge-side-label {
    display: block;
    font-size: 0.68rem;
    font-weight: 750;
    letter-spacing: 0.03em;
    text-transform: uppercase;
    color: #94a3b8;
    margin-bottom: 0.2rem;
}
.ge-letter-desc {
    margin: 0.35rem 0 0;
    color: #475569;
    font-size: 0.75rem;
    line-height: 1.35;
}
.ge-letter-desc:empty { display: none; }
.ge-legend {
    margin: 0 1.15rem 0.85rem;
    display: grid;
    gap: 0.25rem;
}
.ge-legend p { margin: 0; color: #475569; font-size: 0.78rem; line-height: 1.4; }
.ge-remark textarea {
    min-height: 64px;
    border-radius: 10px;
    font-size: 0.85rem;
}
@media (max-width: 860px) {
    .ge-roster-row { grid-template-columns: 1fr; }
}
.ov-mark-btn { width: auto; min-width: 36px; padding: 0 0.3rem; }
.ov-col { min-width: 148px; }
.ov-core {
    font-size: 0.68rem;
    font-weight: 750;
    letter-spacing: 0.03em;
    text-transform: uppercase;
    color: #3d5ee1;
}
.ov-statement {
    font-size: 0.78rem;
    font-weight: 650;
    color: #1e293b;
    white-space: normal;
    line-height: 1.3;
}
.ov-pick.is-on[data-mark="AO"] { background: #166534; border-color: #166534; color: #fff; }
.ov-pick.is-on[data-mark="SO"] { background: #b45309; border-color: #b45309; color: #fff; }
.ov-pick.is-on[data-mark="RO"] { background: #b91c1c; border-color: #b91c1c; color: #fff; }
.quarter-subject-input {
    text-align: center;
    font-weight: 650;
    min-width: 72px;
    border-radius: 10px;
}
.ge-levels {
    display: flex;
    flex-wrap: wrap;
    gap: 0.4rem;
    padding: 0 1rem 0.75rem;
    color: #57534e;
    font-size: 0.78rem;
}
.ge-levels span {
    background: #fafaf9;
    border: 1px solid #e7e5e4;
    border-radius: 999px;
    padding: 0.2rem 0.55rem;
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
    const letterGuide = @json($descriptiveScale ?? []);

    function letterCaption(letter) {
        const meta = letterGuide[letter];
        if (!meta) return '';
        return letter + ' — ' + meta.english + ' (' + meta.filipino + '). ' + meta.description;
    }

    function paintCell($input) {
        const value = String($input.val() || '');
        const $cell = $input.closest('.ge-cell');
        $cell.find('.ge-letter').each(function () {
            $(this).toggleClass('is-on', String($(this).data('letter')) === value);
        });
        $cell.find('.ge-letter-desc').text(value ? letterCaption(value) : '');
    }

    function commentFor($input) {
        const studentId = String($input.data('student-id'));
        const subjectId = String($input.data('subject-id'));
        const $comment = $('.ge-comment').filter(function () {
            return String($(this).data('student-id')) === studentId && String($(this).data('subject-id')) === subjectId;
        });
        return String($comment.val() || '').trim();
    }

    function inputsFor(filter) {
        return $('.quarter-subject-input').filter(filter);
    }

    $('#gradesTable').on('click', '[data-subject-tab]', function () {
        const id = String($(this).data('subject-tab'));
        $('#gradesTable [data-subject-tab]').removeClass('is-active');
        $(this).addClass('is-active');
        $('#gradesTable [data-subject-panel]').removeClass('is-active');
        $('#gradesTable [data-subject-panel="' + id + '"]').addClass('is-active');
    });

    function refreshSubjectCounts() {
        $('#gradesTable [data-subject-tab]').each(function () {
            const id = String($(this).data('subject-tab'));
            const inputs = inputsFor(function () {
                return String($(this).data('subject-id')) === id;
            });
            const filled = inputs.filter(function () {
                return String($(this).val() || '') !== '';
            }).length;
            $(this).find('.ge-switch-count').text(filled + '/' + inputs.length);
        });
    }

    $('#gradesTable').on('click', '.ge-cell .ge-letter', function () {
        const letter = String($(this).data('letter'));
        const $input = $(this).closest('.ge-cell').find('.quarter-subject-input');
        $input.val(String($input.val() || '') === letter ? '' : letter);
        paintCell($input);
        refreshSubjectCounts();
    });

    $('#gradesTable').on('click', '.ge-fill-col', function () {
        const subjectId = String($(this).data('subject-id'));
        const letter = String($(this).data('letter'));
        inputsFor(function () {
            return String($(this).data('subject-id')) === subjectId;
        }).each(function () {
            $(this).val(letter);
            paintCell($(this));
        });
        refreshSubjectCounts();
    });

    $('#gradesTable').on('click', '.ge-fill-row', function () {
        const studentId = String($(this).data('student-id'));
        const letter = String($(this).data('letter'));
        inputsFor(function () {
            return String($(this).data('student-id')) === studentId;
        }).each(function () {
            $(this).val(letter);
            paintCell($(this));
        });
        refreshSubjectCounts();
    });

    $('.ge-fill-blank').on('click', function () {
        const letter = String($(this).data('letter'));
        inputsFor(function () {
            return String($(this).val() || '') === '';
        }).each(function () {
            $(this).val(letter);
            paintCell($(this));
        });
        refreshSubjectCounts();
    });

    $('#clearAllLetters').on('click', function () {
        $('.quarter-subject-input').each(function () {
            $(this).val('');
            paintCell($(this));
        });
        refreshSubjectCounts();
    });

    function applyScore($inputs, score) {
        const num = parseFloat(score);
        if (isNaN(num) || num < 0 || num > 100) {
            toastr.warning('Enter a score from 0 to 100.');
            return;
        }
        if (!$inputs.length) {
            toastr.info('Every student in this subject already has a score.');
            return;
        }
        $inputs.val(num);
        refreshSubjectCounts();
    }

    $('#gradesTable').on('click', '.ge-fill-empty-btn', function () {
        const subjectId = String($(this).data('subject-id'));
        const score = $(this).closest('.ge-fill-empty').find('.ge-empty-score').val();
        applyScore(inputsFor(function () {
            return String($(this).data('subject-id')) === subjectId && String($(this).val() || '') === '';
        }), score);
    });

    const observedGuide = @json($observedMarks ?? []);

    function paintObserved($input) {
        const value = String($input.val() || '');
        const $cell = $input.closest('.ov-cell');
        $cell.find('.ov-pick').each(function () {
            $(this).toggleClass('is-on', String($(this).data('mark')) === value);
        });
        $cell.find('.ge-letter-desc').text(value && observedGuide[value] ? value + ' — ' + observedGuide[value] : '');
    }

    function observedMarks(filter) {
        return $('.ov-mark').filter(filter);
    }

    $('#observedTable').on('click', '[data-indicator-tab]', function () {
        const id = String($(this).data('indicator-tab'));
        $('#observedTable [data-indicator-tab]').removeClass('is-active');
        $(this).addClass('is-active');
        $('#observedTable [data-indicator-panel]').removeClass('is-active');
        $('#observedTable [data-indicator-panel="' + id + '"]').addClass('is-active');
    });

    function refreshBehaviorCounts() {
        $('#observedTable [data-indicator-tab]').each(function () {
            const id = String($(this).data('indicator-tab'));
            const marks = observedMarks(function () {
                return String($(this).data('indicator-id')) === id;
            });
            const filled = marks.filter(function () {
                return String($(this).val() || '') !== '';
            }).length;
            $(this).find('.ge-switch-count').text(filled + '/' + marks.length);
        });
    }

    $('#observedTable').on('click', '.ov-pick', function () {
        const mark = String($(this).data('mark'));
        const $input = $(this).closest('.ov-cell').find('.ov-mark');
        $input.val(String($input.val() || '') === mark ? '' : mark);
        paintObserved($input);
        refreshBehaviorCounts();
    });

    $('#observedTable').on('click', '.ov-fill-col', function () {
        const indicatorId = String($(this).data('indicator-id'));
        const mark = String($(this).data('mark'));
        observedMarks(function () {
            return String($(this).data('indicator-id')) === indicatorId;
        }).each(function () {
            $(this).val(mark);
            paintObserved($(this));
        });
        refreshBehaviorCounts();
    });

    $('#observedTable').on('click', '.ov-fill-row', function () {
        const studentId = String($(this).data('student-id'));
        const mark = String($(this).data('mark'));
        observedMarks(function () {
            return String($(this).data('student-id')) === studentId;
        }).each(function () {
            $(this).val(mark);
            paintObserved($(this));
        });
        refreshBehaviorCounts();
    });

    $('.ov-fill-blank').on('click', function () {
        const mark = String($(this).data('mark'));
        observedMarks(function () {
            return String($(this).val() || '') === '';
        }).each(function () {
            $(this).val(mark);
            paintObserved($(this));
        });
        refreshBehaviorCounts();
    });

    $('#clearAllObserved').on('click', function () {
        $('.ov-mark').each(function () {
            $(this).val('');
            paintObserved($(this));
        });
        refreshBehaviorCounts();
    });

    $('#observedForm').on('submit', function (e) {
        e.preventDefault();
        const ratings = [];
        $('.ov-mark').each(function () {
            const studentId = parseInt($(this).data('student-id'), 10);
            const indicatorId = parseInt($(this).data('indicator-id'), 10);
            if (!studentId || !indicatorId) return;
            const mark = String($(this).val() || '');
            ratings.push({
                student_id: studentId,
                indicator_id: indicatorId,
                mark: mark === '' ? null : mark
            });
        });
        if (!ratings.length) {
            toastr.warning('There are no observed values to save.');
            return;
        }

        const $form = $(this);
        const $btn = $form.find('[type="submit"]');
        const original = $btn.html();
        $btn.html('<i class="fas fa-spinner fa-spin me-1"></i> Saving...').prop('disabled', true);

        $.ajax({
            url: $form.attr('action'),
            type: 'POST',
            dataType: 'json',
            contentType: 'application/json',
            processData: false,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            data: JSON.stringify({
                section_id: $form.find('[name="section_id"]').val(),
                quarter: $form.find('[name="quarter"]').val(),
                academic_year_id: $form.find('[name="academic_year_id"]').val(),
                ratings: ratings
            }),
            success: function (response) {
                if (response && response.redirect) {
                    window.location.href = response.redirect;
                    return;
                }
                toastr.success((response && response.message) || 'Observed values saved.');
                $btn.html(original).prop('disabled', false);
            },
            error: function (xhr) {
                const body = xhr.responseJSON || {};
                let message = body.message || 'Failed to save observed values.';
                if (body.errors) {
                    const first = Object.values(body.errors)[0];
                    if (first && first[0]) message = first[0];
                }
                toastr.error(message);
                $btn.html(original).prop('disabled', false);
            }
        });
    });

    $('#selected_section, #selected_quarter').on('change', function () {
        if ($('#selected_section').val() && $('#selected_quarter').val()) {
            $('#filterForm').trigger('submit');
        }
    });
    $('#saveGradesBtn').on('click', function(e) {
        e.preventDefault();
        const sectionId = $('#selected_section').val();
        const quarter = $('#selected_quarter').val();
        const academicYearId = $('#selected_academic_year').val();
        if (!sectionId || !quarter || !academicYearId) {
            toastr.error('Select a section and a term from the current academic year.');
            return;
        }

        const grades = [];
        const descriptive = $('#gradesTable').data('method') === 'descriptive';
        $('.quarter-subject-input').each(function() {
            const value = $(this).val();
            const comment = commentFor($(this));
            const emptyGrade = value === '' || value === null;
            if (emptyGrade && comment === '') return;
            const row = {
                student_id: parseInt($(this).data('student-id'), 10),
                subject_id: parseInt($(this).data('subject-id'), 10),
                comment: comment
            };
            if (!row.student_id || !row.subject_id) return;
            if (!emptyGrade) {
                if (descriptive) {
                    row.level = String(value).toUpperCase();
                } else {
                    const num = parseFloat(value);
                    if (isNaN(num) || num < 0 || num > 100) return;
                    row.score = num;
                }
            }
            grades.push(row);
        });

        if (!grades.length) {
            toastr.warning('Enter at least one grade or remark before saving.');
            return;
        }

        const $btn = $(this);
        const original = $btn.html();
        $btn.html('<i class="fas fa-spinner fa-spin me-2"></i>Saving...').prop('disabled', true);

        $.ajax({
            url: '{{ route("teacher.grading.store-quarterly-grades") }}',
            type: 'POST',
            dataType: 'json',
            contentType: 'application/json',
            processData: false,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            data: JSON.stringify({
                section_id: sectionId,
                quarter: quarter,
                academic_year_id: academicYearId,
                grades: grades
            }),
            success: function(response) {
                if (response && response.success) {
                    toastr.success(response.message || 'Grades saved.');
                    const url = new URL('{{ route("teacher.grading.grade-entry") }}', window.location.origin);
                    url.searchParams.set('section_id', sectionId);
                    url.searchParams.set('quarter', quarter);
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
