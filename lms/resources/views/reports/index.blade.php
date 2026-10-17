@extends('layouts.master')
@section('content')

<div class="page-wrapper">
    <div class="content container-fluid dir-page">
        <div class="page-header">
            <div class="row align-items-start">
                <div class="col">
                    <h3 class="page-title mb-1">Reports &amp; Documents</h3>
                    <p class="dir-subtitle">
                        @if(in_array(auth()->user()->role_name, ['Admin', 'Teacher'], true))
                            View a report, or generate and download a document.
                        @else
                            View school reports for the current academic year. You can download each report.
                        @endif
                    </p>
                </div>
                <div class="col-auto text-end">
                    <ul class="breadcrumb justify-content-end mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Reports</li>
                    </ul>
                </div>
            </div>
        </div>

        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <label class="dir-report-finder">
            <i class="fas fa-search" aria-hidden="true"></i>
            <input type="search" id="reportFinder" placeholder="Find a report by name" autocomplete="off">
        </label>

        @if(in_array(auth()->user()->role_name, ['Admin', 'Teacher'], true))
        <section class="dir-report-group" data-report-group>
            <div class="dir-report-group-head">
                <h4>Documents</h4>
                <span class="dir-report-pill">Generate/Download</span>
            </div>
            <div class="dir-tip mb-3">
                <strong>Tip:</strong> Pick a <em>Grade Level</em> first (then Section / Student).
                Use <strong>Entire section</strong> to download one PDF of every student enrolled in that section.
                The report follows the grade you select: Nursery and Kindergarten use descriptive letters, and Grade 4 through Grade 10 use numerical scores.
            </div>
            <div class="row g-3">
                <div class="col-md-6 col-xl-3">
                    <div class="dir-report-card" data-report-card="Student Transcript Academic history">
                        <span class="dir-report-icon is-blue"><i class="fas fa-file-alt"></i></span>
                        <h5>Student Transcript</h5>
                        <p class="dir-subtitle mb-3">Academic history — one student or all enrolled students in a section</p>
                        <button type="button" class="btn btn-primary dir-btn" data-bs-toggle="modal" data-bs-target="#transcriptModal">
                            <i class="fas fa-download me-1"></i> Generate/Download
                        </button>
                    </div>
                </div>
                <div class="col-md-6 col-xl-3">
                    <div class="dir-report-card" data-report-card="Class List Section roster">
                        <span class="dir-report-icon is-green"><i class="fas fa-users"></i></span>
                        <h5>Class List</h5>
                        <p class="dir-subtitle mb-3">Section roster — filter by grade, then section</p>
                        <button type="button" class="btn btn-primary dir-btn" data-bs-toggle="modal" data-bs-target="#classListModal">
                            <i class="fas fa-download me-1"></i> Generate/Download
                        </button>
                    </div>
                </div>
                <div class="col-md-6 col-xl-3">
                    <div class="dir-report-card" data-report-card="Grade Slip Period grades">
                        <span class="dir-report-icon is-amber"><i class="fas fa-clipboard-list"></i></span>
                        <h5>Grade Slip</h5>
                        <p class="dir-subtitle mb-3">Period grades — one student or all enrolled students in a section</p>
                        <button type="button" class="btn btn-primary dir-btn" data-bs-toggle="modal" data-bs-target="#gradeSlipModal">
                            <i class="fas fa-download me-1"></i> Generate/Download
                        </button>
                    </div>
                </div>
                <div class="col-md-6 col-xl-3">
                    <div class="dir-report-card" data-report-card="Progress Report Performance summary">
                        <span class="dir-report-icon is-sky"><i class="fas fa-chart-line"></i></span>
                        <h5>Progress Report</h5>
                        <p class="dir-subtitle mb-3">Performance summary — one student or all enrolled students in a section</p>
                        <button type="button" class="btn btn-primary dir-btn" data-bs-toggle="modal" data-bs-target="#progressSummaryModal">
                            <i class="fas fa-download me-1"></i> Generate/Download
                        </button>
                    </div>
                </div>
            </div>
        </section>
        @endif

        @php
            $reportIcons = [
                'Enrollment and Student Reports' => ['fas fa-user-graduate', 'is-blue'],
                'Academic and Grading Reports' => ['fas fa-clipboard-list', 'is-amber'],
                'Attendance Reports' => ['fas fa-user-check', 'is-green'],
                'Teacher, Subject, and Scheduling Reports' => ['fas fa-chalkboard-teacher', 'is-sky'],
                'Lessons, Activities, and Learning Reports' => ['fas fa-book-open', 'is-blue'],
                'Communication and Notification Reports' => ['fas fa-bullhorn', 'is-amber'],
                'Student Documents and Records' => ['fas fa-folder-open', 'is-green'],
                'Principal and Administrative Reports' => ['fas fa-chart-bar', 'is-sky'],
                'System and User Activity Reports' => ['fas fa-user-shield', 'is-blue'],
            ];
        @endphp

        @foreach($schoolReports as $category => $items)
            <section class="dir-report-group" data-report-group>
                <div class="dir-report-group-head">
                    <h4>{{ $category }}</h4>
                    <span class="dir-report-pill">{{ $items->count() }} {{ $items->count() === 1 ? 'report' : 'reports' }}</span>
                </div>
                <div class="row g-3">
                    @foreach($items as $item)
                        @php
                            $icon = $reportIcons[$category] ?? ['fas fa-file-alt', 'is-blue'];
                        @endphp
                        <div class="col-md-6 col-xl-3">
                            <div class="dir-report-card" data-report-card="{{ $item['title'] }} {{ $item['description'] }}">
                                <span class="dir-report-icon {{ $icon[1] }}"><i class="{{ $icon[0] }}"></i></span>
                                <h5>{{ $item['title'] }}</h5>
                                <p class="dir-subtitle mb-3">{{ $item['description'] }}</p>
                                <div class="dir-report-actions">
                                    <a href="{{ route('reports.school.show', $item['key']) }}" class="btn btn-primary dir-btn">
                                        <i class="fas fa-eye me-1"></i> View Report
                                    </a>
                                    <a href="{{ route('reports.school.download', $item['key']) }}" class="btn btn-outline-primary dir-btn">
                                        <i class="fas fa-download me-1"></i> Download
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
        @endforeach

        <div id="reportFinderEmpty" class="dir-empty" hidden>
            <i class="fas fa-search d-block"></i>
            <p class="mb-0">No report matches that name.</p>
        </div>
    </div>
</div>

@php
    $ayOptions = $academicYears;
    $semOptions = $semesters;
@endphp

{{-- Shared filter partials via repeated markup for clarity --}}

<!-- Transcript Modal -->
<div class="modal fade dir-modal" id="transcriptModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Download Student Transcript</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form class="report-form" data-type="transcript" data-single-base="{{ url('/reports/transcript') }}" data-bulk-url="{{ route('reports.bulk', ['type' => 'transcript']) }}">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Grade Level <span class="text-danger">*</span></label>
                            <select class="form-select grade-select" required>
                                <option value="">Select grade</option>
                                @foreach($gradeLevels as $grade)
                                    <option value="{{ $grade }}">{{ $grade }}</option>
                            @endforeach
                        </select>
                        <p class="grade-method-note text-muted small mb-0 mt-2"></p>
                    </div>
                        <div class="col-md-4">
                            <label class="form-label">Section</label>
                            <select class="form-select section-select">
                                <option value="">All sections in grade</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Scope</label>
                            <select class="form-select scope-select" name="scope">
                                <option value="single">One student</option>
                                <option value="section">Entire section</option>
                                <option value="grade">Entire grade</option>
                            </select>
                        </div>
                        <div class="col-md-12 student-wrap">
                            <label class="form-label">Student <span class="text-danger">*</span></label>
                            <select class="form-select student-select" name="student_id">
                                <option value="">Select student</option>
                            </select>
                            <small class="text-muted student-count"></small>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Academic Year</label>
                            <select class="form-select" name="academic_year_id">
                                <option value="">All years</option>
                                @foreach($ayOptions as $year)
                                <option value="{{ $year->id }}" @selected((string) request('academic_year_id', $currentSchoolYear->id ?? '') === (string) $year->id)>{{ $year->name }}</option>
                            @endforeach
                        </select>
                        <p class="grade-method-note text-muted small mb-0 mt-2"></p>
                    </div>
                        <div class="col-md-4">
                            <label class="form-label">Term</label>
                            <select class="form-select" name="semester_id">
                                <option value="">All terms</option>
                                @foreach($semOptions as $semester)
                                <option value="{{ $semester->id }}">{{ $semester->name }}@if($semester->academicYear) — {{ $semester->academicYear->name }}@endif</option>
                            @endforeach
                        </select>
                        <p class="grade-method-note text-muted small mb-0 mt-2"></p>
                    </div>
                        <div class="col-md-4">
                        <label class="form-label">Format</label>
                            <select class="form-select" name="format" disabled>
                                <option value="pdf">PDF (download)</option>
                            </select>
                            <small class="text-muted">PDF is the only available format for report downloads.</small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary dir-btn" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary dir-btn"><i class="fas fa-download me-1"></i> Download</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Class List Modal -->
<div class="modal fade dir-modal" id="classListModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Download Class List</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form class="report-form" data-type="class-list" data-single-base="{{ url('/reports/class-list') }}" data-bulk-url="{{ route('reports.bulk', ['type' => 'class-list']) }}">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Grade Level <span class="text-danger">*</span></label>
                            <select class="form-select grade-select" required>
                                <option value="">Select grade</option>
                                @foreach($gradeLevels as $grade)
                                    <option value="{{ $grade }}">{{ $grade }}</option>
                            @endforeach
                        </select>
                        <p class="grade-method-note text-muted small mb-0 mt-2"></p>
                    </div>
                        <div class="col-md-6">
                            <label class="form-label">Scope</label>
                            <select class="form-select scope-select" name="scope">
                                <option value="single">One section</option>
                                <option value="grade">All sections in grade</option>
                            </select>
                        </div>
                        <div class="col-md-12 section-wrap">
                            <label class="form-label">Section <span class="text-danger">*</span></label>
                            <select class="form-select section-select" name="section_id">
                                <option value="">Select section</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Academic Year</label>
                            <select class="form-select" name="academic_year_id">
                                <option value="">Current</option>
                                @foreach($ayOptions as $year)
                                <option value="{{ $year->id }}" @selected((string) request('academic_year_id', $currentSchoolYear->id ?? '') === (string) $year->id)>{{ $year->name }}</option>
                            @endforeach
                        </select>
                        <p class="grade-method-note text-muted small mb-0 mt-2"></p>
                    </div>
                        <div class="col-md-4">
                            <label class="form-label">Term</label>
                            <select class="form-select" name="semester_id">
                                <option value="">Current</option>
                                @foreach($semOptions as $semester)
                                <option value="{{ $semester->id }}">{{ $semester->name }}@if($semester->academicYear) — {{ $semester->academicYear->name }}@endif</option>
                            @endforeach
                        </select>
                        <p class="grade-method-note text-muted small mb-0 mt-2"></p>
                    </div>
                        <div class="col-md-4">
                        <label class="form-label">Format</label>
                            <select class="form-select" name="format" disabled>
                                <option value="pdf">PDF (download)</option>
                            </select>
                            <small class="text-muted">PDF is the only available format for report downloads.</small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary dir-btn" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary dir-btn"><i class="fas fa-download me-1"></i> Download</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Grade Slip Modal -->
<div class="modal fade dir-modal" id="gradeSlipModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Download Grade Slip</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form class="report-form" data-type="grade-slip" data-single-base="{{ url('/reports/grade-slip') }}" data-bulk-url="{{ route('reports.bulk', ['type' => 'grade-slip']) }}">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Grade Level <span class="text-danger">*</span></label>
                            <select class="form-select grade-select" required>
                                <option value="">Select grade</option>
                                @foreach($gradeLevels as $grade)
                                    <option value="{{ $grade }}">{{ $grade }}</option>
                            @endforeach
                        </select>
                        <p class="grade-method-note text-muted small mb-0 mt-2"></p>
                    </div>
                        <div class="col-md-4">
                            <label class="form-label">Section</label>
                            <select class="form-select section-select">
                                <option value="">All sections in grade</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Scope</label>
                            <select class="form-select scope-select" name="scope">
                                <option value="single">One student</option>
                                <option value="section">Entire section</option>
                                <option value="grade">Entire grade</option>
                            </select>
                        </div>
                        <div class="col-md-12 student-wrap">
                            <label class="form-label">Student <span class="text-danger">*</span></label>
                            <select class="form-select student-select" name="student_id">
                                <option value="">Select student</option>
                            </select>
                            <small class="text-muted student-count"></small>
                        </div>
                        <div class="col-md-4">
                        <label class="form-label">Academic Year</label>
                            <select class="form-select" name="academic_year_id">
                                <option value="">Current</option>
                                @foreach($ayOptions as $year)
                                <option value="{{ $year->id }}" @selected((string) request('academic_year_id', $currentSchoolYear->id ?? '') === (string) $year->id)>{{ $year->name }}</option>
                            @endforeach
                        </select>
                        <p class="grade-method-note text-muted small mb-0 mt-2"></p>
                    </div>
                        <div class="col-md-4">
                        <label class="form-label">Term</label>
                            <select class="form-select" name="semester_id">
                                <option value="">Current</option>
                                @foreach($semOptions as $semester)
                                <option value="{{ $semester->id }}">{{ $semester->name }}@if($semester->academicYear) — {{ $semester->academicYear->name }}@endif</option>
                            @endforeach
                        </select>
                        <p class="grade-method-note text-muted small mb-0 mt-2"></p>
                    </div>
                        <div class="col-md-4">
                        <label class="form-label">Format</label>
                            <select class="form-select" name="format" disabled>
                                <option value="pdf">PDF (download)</option>
                            </select>
                            <small class="text-muted">PDF is the only available format for report downloads.</small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary dir-btn" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary dir-btn"><i class="fas fa-download me-1"></i> Download</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Progress Report Modal -->
<div class="modal fade dir-modal" id="progressSummaryModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Download Progress Report</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form class="report-form" data-type="progress-summary" data-single-base="{{ url('/reports/progress-summary') }}" data-bulk-url="{{ route('reports.bulk', ['type' => 'progress-summary']) }}">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Grade Level <span class="text-danger">*</span></label>
                            <select class="form-select grade-select" required>
                                <option value="">Select grade</option>
                                @foreach($gradeLevels as $grade)
                                    <option value="{{ $grade }}">{{ $grade }}</option>
                            @endforeach
                        </select>
                        <p class="grade-method-note text-muted small mb-0 mt-2"></p>
                    </div>
                        <div class="col-md-4">
                            <label class="form-label">Section</label>
                            <select class="form-select section-select">
                                <option value="">All sections in grade</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Scope</label>
                            <select class="form-select scope-select" name="scope">
                                <option value="single">One student</option>
                                <option value="section">Entire section</option>
                                <option value="grade">Entire grade</option>
                            </select>
                        </div>
                        <div class="col-md-12 student-wrap">
                            <label class="form-label">Student <span class="text-danger">*</span></label>
                            <select class="form-select student-select" name="student_id">
                                <option value="">Select student</option>
                            </select>
                            <small class="text-muted student-count"></small>
                        </div>
                        <div class="col-md-4">
                        <label class="form-label">Academic Year</label>
                            <select class="form-select" name="academic_year_id">
                                <option value="">Current</option>
                                @foreach($ayOptions as $year)
                                <option value="{{ $year->id }}" @selected((string) request('academic_year_id', $currentSchoolYear->id ?? '') === (string) $year->id)>{{ $year->name }}</option>
                            @endforeach
                        </select>
                        <p class="grade-method-note text-muted small mb-0 mt-2"></p>
                    </div>
                        <div class="col-md-4">
                        <label class="form-label">Term</label>
                            <select class="form-select" name="semester_id">
                                <option value="">Current</option>
                                @foreach($semOptions as $semester)
                                <option value="{{ $semester->id }}">{{ $semester->name }}@if($semester->academicYear) — {{ $semester->academicYear->name }}@endif</option>
                            @endforeach
                        </select>
                        <p class="grade-method-note text-muted small mb-0 mt-2"></p>
                    </div>
                        <div class="col-md-4">
                        <label class="form-label">Format</label>
                            <select class="form-select" name="format" disabled>
                                <option value="pdf">PDF (download)</option>
                            </select>
                            <small class="text-muted">PDF is the only available format for report downloads.</small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary dir-btn" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary dir-btn"><i class="fas fa-download me-1"></i> Download</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/directory-modern.css') }}?v=20261018a">
@endpush

@push('scripts')
<script>
(function () {
    const students = @json($studentsPayload);
    const sections = @json($sectionsPayload);
    const earlyLevels = @json($earlyLevels ?? []);
    const descriptiveLevels = @json($descriptiveLevels ?? []);

    function gradeReportNote(grade) {
        if (!grade) return '';
        if (earlyLevels.indexOf(grade) !== -1) {
            return 'This level uses descriptive letters A–E on the progress report. Numerical averages are not used.';
        }
        if (descriptiveLevels.indexOf(grade) !== -1) {
            return 'This level is encoded with descriptive letters A–E (Advancing through Emerging).';
        }
        return 'This level uses numerical scores from 0 to 100, with Passed or Failed.';
    }

    function fillSections(form, grade) {
        const select = form.querySelector('.section-select');
        if (!select) return;
        const keepAll = form.dataset.type !== 'class-list';
        const current = select.value;
        select.innerHTML = keepAll
            ? '<option value="">All sections in grade</option>'
            : '<option value="">Select section</option>';

        sections
            .filter(function (s) { return !grade || s.grade === grade; })
            .forEach(function (s) {
                const opt = document.createElement('option');
                opt.value = s.id;
                opt.textContent = s.name + (s.grade ? ' (' + s.grade + ')' : '');
                select.appendChild(opt);
            });

        if ([...select.options].some(function (o) { return o.value === current; })) {
            select.value = current;
        }
    }

    function fillStudents(form) {
        const studentSelect = form.querySelector('.student-select');
        const countEl = form.querySelector('.student-count');
        if (!studentSelect) return;

        const grade = (form.querySelector('.grade-select')?.value || '').trim();
        const sectionId = (form.querySelector('.section-select')?.value || '').trim();

        let list = students.filter(function (st) {
            const stGrade = (st.grade || '').trim();
            const ids = (st.section_ids || []).map(String);

            // If a section is chosen, prefer students assigned to that section
            if (sectionId) {
                if (ids.includes(String(sectionId))) {
                    return true;
                }
                // fallback: denormalized section name + grade match
                const sectionMeta = sections.find(function (s) { return String(s.id) === String(sectionId); });
                const sectionName = sectionMeta ? sectionMeta.name : '';
                if (sectionName && st.section && st.section === sectionName) {
                    return !grade || stGrade === grade || stGrade === (sectionMeta.grade || '');
                }
                return false;
            }

            // Grade only: match year_level/class OR any section under that grade
            if (grade) {
                if (stGrade === grade) return true;
                const gradeSectionIds = sections
                    .filter(function (s) { return s.grade === grade; })
                    .map(function (s) { return String(s.id); });
                return ids.some(function (id) { return gradeSectionIds.includes(id); });
            }

            return true;
        });

        // Stable sort by name
        list = list.slice().sort(function (a, b) {
            return String(a.name).localeCompare(String(b.name));
        });

        studentSelect.innerHTML = '<option value="">Select student</option>';
        list.forEach(function (st) {
            const opt = document.createElement('option');
            opt.value = st.id;
            opt.textContent = st.name + (st.grade ? ' — ' + st.grade : '');
            studentSelect.appendChild(opt);
        });

        if (countEl) {
            countEl.textContent = list.length
                ? (list.length + ' student(s) available')
                : 'No enrolled students found for this grade/section';
        }
    }

    function syncScopeUI(form) {
        const scope = form.querySelector('.scope-select')?.value || 'single';
        const studentWrap = form.querySelector('.student-wrap');
        const sectionWrap = form.querySelector('.section-wrap');
        const sectionSelect = form.querySelector('.section-select');
        const formatNote = form.querySelector('.bulk-format-note');
        const formatSelect = form.querySelector('[name="format"]');
        const type = form.dataset.type;

        if (studentWrap) {
            studentWrap.classList.toggle('d-none', scope !== 'single');
            const studentSelect = form.querySelector('.student-select');
            if (studentSelect) studentSelect.required = scope === 'single';
        }

        if (type === 'class-list') {
            if (sectionWrap) sectionWrap.classList.toggle('d-none', scope === 'grade');
            if (sectionSelect) sectionSelect.required = scope === 'single';
        } else if (sectionSelect) {
            // for student reports, section required only when scope=section
            sectionSelect.required = scope === 'section';
        }

        const isBulk = scope !== 'single';
        if (formatNote) formatNote.classList.toggle('d-none', !isBulk);
        if (formatSelect && isBulk) {
            formatSelect.value = 'pdf';
            formatSelect.disabled = true;
        } else if (formatSelect) {
            formatSelect.disabled = false;
        }
    }

    function buildQuery(form) {
        const params = new URLSearchParams();
        ['academic_year_id', 'semester_id', 'format'].forEach(function (name) {
            const el = form.querySelector('[name="' + name + '"]');
            if (el && el.value) params.set(name, el.value);
        });
        return params;
    }

    document.querySelectorAll('.report-form').forEach(function (form) {
        form.querySelector('.grade-select')?.addEventListener('change', function () {
            fillSections(form, this.value);
            fillStudents(form);
            syncScopeUI(form);
            const note = form.querySelector('.grade-method-note');
            if (note) note.textContent = gradeReportNote(this.value);
        });
        form.querySelector('.section-select')?.addEventListener('change', function () {
            fillStudents(form);
            syncScopeUI(form);
        });
        form.querySelector('.scope-select')?.addEventListener('change', function () {
            syncScopeUI(form);
        });

        form.addEventListener('submit', function (e) {
        e.preventDefault();
            const type = form.dataset.type;
            const scope = form.querySelector('.scope-select')?.value || 'single';
            const grade = form.querySelector('.grade-select')?.value || '';
            const sectionId = form.querySelector('.section-select')?.value || '';
            const studentId = form.querySelector('.student-select')?.value || '';
            const params = buildQuery(form);

            if (!grade) {
                alert('Please select a grade level.');
                return;
            }

            if (scope === 'single') {
                if (type === 'class-list') {
                    if (!sectionId) {
                        alert('Please select a section.');
                        return;
                    }
                    window.location.href = form.dataset.singleBase + '/' + sectionId + '?' + params.toString();
                    return;
                }
        if (!studentId) {
                    alert('Please select a student.');
                    return;
                }
                window.location.href = form.dataset.singleBase + '/' + studentId + '?' + params.toString();
                return;
            }

            // Combined PDF of enrolled students
            params.set('grade_level', grade);
            if (scope === 'section') {
                if (!sectionId) {
                    alert('Please select a section for entire-section download.');
            return;
        }
                params.set('section_id', sectionId);
            }
            params.set('format', 'pdf');
            window.location.href = form.dataset.bulkUrl + '?' + params.toString();
        });

        syncScopeUI(form);
    });
})();

(function () {
    var finder = document.getElementById('reportFinder');
    var empty = document.getElementById('reportFinderEmpty');
    if (!finder) return;
    finder.addEventListener('input', function () {
        var query = finder.value.trim().toLowerCase();
        var any = false;
        document.querySelectorAll('[data-report-group]').forEach(function (group) {
            var visible = false;
            group.querySelectorAll('[data-report-card]').forEach(function (card) {
                var haystack = (card.getAttribute('data-report-card') || '').toLowerCase();
                var show = query === '' || haystack.indexOf(query) !== -1;
                card.parentElement.style.display = show ? '' : 'none';
                if (show) visible = true;
            });
            group.style.display = visible ? '' : 'none';
            if (visible) any = true;
        });
        if (empty) empty.hidden = any;
    });
})();
</script>
@endpush
@endsection
