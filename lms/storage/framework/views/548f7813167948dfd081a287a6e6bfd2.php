
<?php $__env->startSection('content'); ?>

<div class="page-wrapper">
    <div class="content container-fluid dir-page">
        <div class="page-header">
            <div class="row align-items-start">
                <div class="col">
                    <h3 class="page-title mb-1">Reports &amp; Documents</h3>
                    <p class="dir-subtitle">Generate transcripts, class lists, grade slips, and progress reports.</p>
                </div>
                <div class="col-auto text-end">
                    <ul class="breadcrumb justify-content-end mb-0">
                        <li class="breadcrumb-item"><a href="<?php echo e(route('home')); ?>">Dashboard</a></li>
                        <li class="breadcrumb-item active">Reports</li>
                    </ul>
                </div>
            </div>
        </div>

        <?php if(session('error')): ?>
            <div class="alert alert-danger"><?php echo e(session('error')); ?></div>
        <?php endif; ?>

        <div class="dir-tip mb-4">
            <strong>Tip:</strong> Pick a <em>Grade Level</em> first (then Section / Student).
            Use <strong>Entire section</strong> to download one PDF of every student enrolled in that section.
        </div>

        <div class="row g-3">
            <div class="col-md-6 col-xl-3">
                <div class="dir-report-card">
                    <span class="dir-report-icon is-blue"><i class="fas fa-file-alt"></i></span>
                    <h5>Student Transcript</h5>
                    <p class="dir-subtitle mb-3">Academic history — one student or all enrolled students in a section</p>
                    <button type="button" class="btn btn-primary dir-btn" data-bs-toggle="modal" data-bs-target="#transcriptModal">
                        <i class="fas fa-download me-1"></i> Generate / Download
                    </button>
                </div>
            </div>

            <div class="col-md-6 col-xl-3">
                <div class="dir-report-card">
                    <span class="dir-report-icon is-green"><i class="fas fa-users"></i></span>
                    <h5>Class List</h5>
                    <p class="dir-subtitle mb-3">Section roster — filter by grade, then section</p>
                    <button type="button" class="btn btn-primary dir-btn" data-bs-toggle="modal" data-bs-target="#classListModal">
                        <i class="fas fa-download me-1"></i> Generate / Download
                    </button>
                </div>
            </div>

            <div class="col-md-6 col-xl-3">
                <div class="dir-report-card">
                    <span class="dir-report-icon is-amber"><i class="fas fa-clipboard-list"></i></span>
                    <h5>Grade Slip</h5>
                    <p class="dir-subtitle mb-3">Period grades — one student or all enrolled students in a section</p>
                    <button type="button" class="btn btn-primary dir-btn" data-bs-toggle="modal" data-bs-target="#gradeSlipModal">
                        <i class="fas fa-download me-1"></i> Generate / Download
                    </button>
                </div>
            </div>

            <div class="col-md-6 col-xl-3">
                <div class="dir-report-card">
                    <span class="dir-report-icon is-sky"><i class="fas fa-chart-line"></i></span>
                    <h5>Progress Report</h5>
                    <p class="dir-subtitle mb-3">Performance summary — one student or all enrolled students in a section</p>
                    <button type="button" class="btn btn-primary dir-btn" data-bs-toggle="modal" data-bs-target="#progressSummaryModal">
                        <i class="fas fa-download me-1"></i> Generate / Download
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
    $ayOptions = $academicYears;
    $semOptions = $semesters;
?>



<!-- Transcript Modal -->
<div class="modal fade dir-modal" id="transcriptModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Download Student Transcript</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form class="report-form" data-type="transcript" data-single-base="<?php echo e(url('/reports/transcript')); ?>" data-bulk-url="<?php echo e(route('reports.bulk', ['type' => 'transcript'])); ?>">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Grade Level <span class="text-danger">*</span></label>
                            <select class="form-select grade-select" required>
                                <option value="">Select grade</option>
                                <?php $__currentLoopData = $gradeLevels; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $grade): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($grade); ?>"><?php echo e($grade); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
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
                                <?php $__currentLoopData = $ayOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $year): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($year->id); ?>"><?php echo e($year->name); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Semester</label>
                            <select class="form-select" name="semester_id">
                                <option value="">All semesters</option>
                                <?php $__currentLoopData = $semOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $semester): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($semester->id); ?>"><?php echo e($semester->name); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Format</label>
                            <select class="form-select" name="format">
                                <option value="pdf">PDF (download)</option>
                                <option value="excel">Excel (download)</option>
                            </select>
                            <small class="text-muted bulk-format-note d-none">Entire section/grade downloads one PDF of all enrolled students.</small>
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
            <form class="report-form" data-type="class-list" data-single-base="<?php echo e(url('/reports/class-list')); ?>" data-bulk-url="<?php echo e(route('reports.bulk', ['type' => 'class-list'])); ?>">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Grade Level <span class="text-danger">*</span></label>
                            <select class="form-select grade-select" required>
                                <option value="">Select grade</option>
                                <?php $__currentLoopData = $gradeLevels; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $grade): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($grade); ?>"><?php echo e($grade); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
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
                                <?php $__currentLoopData = $ayOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $year): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($year->id); ?>"><?php echo e($year->name); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Semester</label>
                            <select class="form-select" name="semester_id">
                                <option value="">Current</option>
                                <?php $__currentLoopData = $semOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $semester): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($semester->id); ?>"><?php echo e($semester->name); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Format</label>
                            <select class="form-select" name="format">
                                <option value="pdf">PDF (download)</option>
                                <option value="excel">Excel (download)</option>
                            </select>
                            <small class="text-muted bulk-format-note d-none">Entire section/grade downloads one PDF of all enrolled students.</small>
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
            <form class="report-form" data-type="grade-slip" data-single-base="<?php echo e(url('/reports/grade-slip')); ?>" data-bulk-url="<?php echo e(route('reports.bulk', ['type' => 'grade-slip'])); ?>">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Grade Level <span class="text-danger">*</span></label>
                            <select class="form-select grade-select" required>
                                <option value="">Select grade</option>
                                <?php $__currentLoopData = $gradeLevels; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $grade): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($grade); ?>"><?php echo e($grade); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
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
                                <?php $__currentLoopData = $ayOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $year): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($year->id); ?>"><?php echo e($year->name); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Semester</label>
                            <select class="form-select" name="semester_id">
                                <option value="">Current</option>
                                <?php $__currentLoopData = $semOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $semester): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($semester->id); ?>"><?php echo e($semester->name); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Format</label>
                            <select class="form-select" name="format">
                                <option value="pdf">PDF (download)</option>
                                <option value="excel">Excel (download)</option>
                            </select>
                            <small class="text-muted bulk-format-note d-none">Entire section/grade downloads one PDF of all enrolled students.</small>
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
            <form class="report-form" data-type="progress-summary" data-single-base="<?php echo e(url('/reports/progress-summary')); ?>" data-bulk-url="<?php echo e(route('reports.bulk', ['type' => 'progress-summary'])); ?>">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Grade Level <span class="text-danger">*</span></label>
                            <select class="form-select grade-select" required>
                                <option value="">Select grade</option>
                                <?php $__currentLoopData = $gradeLevels; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $grade): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($grade); ?>"><?php echo e($grade); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
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
                                <?php $__currentLoopData = $ayOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $year): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($year->id); ?>"><?php echo e($year->name); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Semester</label>
                            <select class="form-select" name="semester_id">
                                <option value="">Current</option>
                                <?php $__currentLoopData = $semOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $semester): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($semester->id); ?>"><?php echo e($semester->name); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Format</label>
                            <select class="form-select" name="format">
                                <option value="pdf">PDF (download)</option>
                                <option value="excel">Excel (download)</option>
                            </select>
                            <small class="text-muted bulk-format-note d-none">Entire section/grade downloads one PDF of all enrolled students.</small>
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

<?php $__env->startPush('styles'); ?>
<link rel="stylesheet" href="<?php echo e(asset('assets/css/directory-modern.css')); ?>?v=20260914c">
<?php $__env->stopPush(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
(function () {
    const students = <?php echo json_encode($studentsPayload, 15, 512) ?>;
    const sections = <?php echo json_encode($sectionsPayload, 15, 512) ?>;

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
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Laravel\Capstone-Project\lms\resources\views/reports/index.blade.php ENDPATH**/ ?>