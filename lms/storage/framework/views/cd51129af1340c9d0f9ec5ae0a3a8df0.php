<?php $__env->startSection('content'); ?>
    <div class="page-wrapper">
    <div class="content container-fluid ams-years">
            <div class="page-header">
                <div class="row align-items-start">
                    <div class="col">
                        <div>
                            <h3 class="page-title mb-1">Academic Years</h3>
                            <p class="text-muted mb-0">Create and edit school years. A finished year is completed or archived, and it is not deleted. Classes, subjects, and curriculum stay for every year.</p>
                        </div>
                    </div>
                <div class="col-auto text-end">
                    <ul class="breadcrumb justify-content-end mb-2">
                        <li class="breadcrumb-item"><a href="<?php echo e(route('dashboard')); ?>">Dashboard</a></li>
                            <li class="breadcrumb-item active">Academic Years</li>
                        </ul>
                    <div class="d-flex flex-wrap gap-2 justify-content-end">
                    <a href="<?php echo e(route('semesters.index')); ?>" class="btn btn-outline-secondary">
                        <i class="fas fa-calendar-week me-1"></i> Semesters
                    </a>
                    <button type="button" class="btn btn-primary" id="btnAddYear"
                        data-bs-toggle="modal" data-bs-target="#yearFormModal">
                        <i class="fas fa-plus me-1"></i> Add Academic Year
                    </button>
                    </div>
                </div>
            </div>
        </div>

        <?php if(session('success')): ?>
            <div class="alert alert-success"><?php echo e(session('success')); ?></div>
        <?php endif; ?>
        <?php if(session('error')): ?>
            <div class="alert alert-danger"><?php echo e(session('error')); ?></div>
        <?php endif; ?>

        <div class="row g-3 mb-4">
            <div class="col-6 col-md-3">
                <div class="ams-stat"><span class="ams-stat-val"><?php echo e($stats['total']); ?></span><span class="ams-stat-lbl">Total</span></div>
            </div>
            <div class="col-6 col-md-3">
                <div class="ams-stat ams-stat--ok"><span class="ams-stat-val"><?php echo e($stats['current']); ?></span><span class="ams-stat-lbl">Current</span></div>
            </div>
            <div class="col-6 col-md-3">
                <div class="ams-stat ams-stat--info"><span class="ams-stat-val"><?php echo e($stats['upcoming']); ?></span><span class="ams-stat-lbl">Upcoming</span></div>
            </div>
            <div class="col-6 col-md-3">
                <div class="ams-stat ams-stat--muted"><span class="ams-stat-val"><?php echo e($stats['completed']); ?></span><span class="ams-stat-lbl">Completed</span></div>
            </div>
        </div>

        <div class="ams-toolbar mb-3">
            <div class="ams-search">
                <i class="fas fa-search ams-search-icon"></i>
                <input type="search" id="yearSearch" class="form-control ams-search-input"
                    placeholder="Search academic year..." autocomplete="off">
            </div>
            <div class="ams-filter-bar mt-2">
                <button type="button" class="ams-filter-chip is-active" data-status="all">All</button>
                <button type="button" class="ams-filter-chip" data-status="current">Current</button>
                <button type="button" class="ams-filter-chip" data-status="upcoming">Upcoming</button>
                <button type="button" class="ams-filter-chip" data-status="completed">Completed</button>
                <button type="button" class="ams-filter-chip" data-status="archived">Archive</button>
                </div>
            </div>

        <div class="row g-3" id="yearsGrid">
            <?php $__empty_1 = true; $__currentLoopData = $academicYears; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $year): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <?php
                    $status = $year->statusLabel();
                    $classStartLabel = $year->class_starts_at ? $year->class_starts_at->format('M d, Y') : '';
                    $classEndLabel = $year->class_ends_at ? $year->class_ends_at->format('M d, Y') : '';
                    $suggestedClassStart = $year->suggestedClassStart();
                    $suggestedClassEnd = $year->suggestedClassEnd();
                ?>
                <div class="col-6 col-md-4 col-xl-3 year-grid-item"
                    data-status="<?php echo e($status); ?>"
                    data-search="<?php echo e(strtolower($year->name)); ?>"
                    data-id="<?php echo e($year->id); ?>">
                    <button type="button"
                        class="ams-year-card w-100 text-start"
                        data-bs-toggle="modal"
                        data-bs-target="#yearDetailModal"
                        data-id="<?php echo e($year->id); ?>"
                        data-name="<?php echo e($year->name); ?>"
                        data-start="<?php echo e($year->start_date->format('Y-m-d')); ?>"
                        data-end="<?php echo e($year->end_date->format('Y-m-d')); ?>"
                        data-start-label="<?php echo e($year->start_date->format('M d, Y')); ?>"
                        data-end-label="<?php echo e($year->end_date->format('M d, Y')); ?>"
                        data-class-start="<?php echo e(optional($year->class_starts_at)->format('Y-m-d')); ?>"
                        data-class-end="<?php echo e(optional($year->class_ends_at)->format('Y-m-d')); ?>"
                        data-class-start-label="<?php echo e($classStartLabel); ?>"
                        data-class-end-label="<?php echo e($classEndLabel); ?>"
                        data-class-suggest="<?php echo e(optional($suggestedClassStart)->format('Y-m-d')); ?>"
                        data-class-suggest-label="<?php echo e(optional($suggestedClassStart)->format('M d, Y')); ?>"
                        data-class-suggest-note="<?php echo e($year->suggestedClassStartNote()); ?>"
                        data-class-end-suggest="<?php echo e(optional($suggestedClassEnd)->format('Y-m-d')); ?>"
                        data-class-end-suggest-label="<?php echo e(optional($suggestedClassEnd)->format('M d, Y')); ?>"
                        data-class-start-url="<?php echo e(route('academic_years.class-start', $year)); ?>"
                        data-status="<?php echo e($status); ?>"
                        data-enrollment-open="<?php echo e($year->enrollment_open ? '1' : '0'); ?>"
                        data-enrollment-start="<?php echo e(optional($year->enrollment_starts_at)->format('Y-m-d')); ?>"
                        data-enrollment-end="<?php echo e(optional($year->enrollment_ends_at)->format('Y-m-d')); ?>"
                        data-enrollment-settings-url="<?php echo e(route('academic_years.enrollment-settings', $year)); ?>"
                        data-enrollment-url="<?php echo e(route('academic_years.enrollment', $year)); ?>"
                        data-semesters="<?php echo e($year->semesters_count); ?>"
                        data-update-url="<?php echo e(route('academic_years.update', $year)); ?>"
                        data-destroy-url="<?php echo e(route('academic_years.destroy', $year)); ?>"
                        data-force-delete-url="<?php echo e(route('academic_years.force-delete', $year)); ?>"
                        data-unarchive-url="<?php echo e(route('academic_years.unarchive', $year)); ?>">
                        <div class="d-flex justify-content-between align-items-start gap-2">
                            <span class="ams-year-title"><?php echo e($year->name); ?></span>
                            <span class="ams-status-pill ams-status-pill--<?php echo e($status); ?>"><?php echo e(ucfirst($status)); ?></span>
                        </div>
                        <?php if($classStartLabel && $classEndLabel): ?>
                            <div class="ams-year-dates mt-2">
                                Class <?php echo e($classStartLabel); ?> → <?php echo e($classEndLabel); ?>

                            </div>
                        <?php endif; ?>
                        <div class="ams-year-meta mt-2">
                            <i class="fas fa-door-open me-1"></i>Enrollment: <?php echo e($year->enrollment_open ? 'Open' : 'Closed'); ?>

                        </div>
                        <div class="ams-year-meta mt-1">
                            <i class="fas fa-calendar-week me-1"></i><?php echo e(ucfirst($status)); ?> · <?php echo e($year->semesters_count); ?> semester(s)
                        </div>
                        <div class="ams-year-hint mt-3">Click to manage <i class="fas fa-arrow-right ms-1"></i></div>
                    </button>
                    </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <div class="col-12" id="yearsEmptyState">
                    <div class="alert alert-warning mb-0">No academic years yet. Add one to start enrollment periods.</div>
                </div>
            <?php endif; ?>
            </div>
        <div id="yearsFilterEmpty" class="alert alert-light border text-center d-none mt-3">
            No academic years match your search or filter.
                                    </div>
                                </div>
                            </div>


<div class="modal fade" id="yearDetailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content ams-float-modal">
            <div class="modal-header border-0 pb-0">
                                                <div>
                    <p class="ams-modal-eyebrow mb-1">Academic Year</p>
                    <h4 class="modal-title mb-0" id="yearDetailTitle">Year</h4>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
            <div class="modal-body pt-3">
                <div class="ams-detail-grid">
                    <div class="ams-detail-item">
                        <span class="ams-detail-label">Status</span>
                        <span class="ams-detail-value" id="yearDetailStatus">—</span>
                    </div>
                    <div class="ams-detail-item">
                        <span class="ams-detail-label">Enrollment</span>
                        <span class="ams-detail-value" id="yearDetailEnrollment">—</span>
                    </div>
                    <div class="ams-detail-item">
                        <span class="ams-detail-label">Semesters</span>
                        <span class="ams-detail-value" id="yearDetailSemesters">—</span>
                                            </div>
                    <div class="ams-detail-item">
                        <span class="ams-detail-label">School year starts</span>
                        <span class="ams-detail-value" id="yearDetailSchoolStart">—</span>
                    </div>
                    <div class="ams-detail-item">
                        <span class="ams-detail-label">School year ends</span>
                        <span class="ams-detail-value" id="yearDetailSchoolEnd">—</span>
                    </div>
                    <div class="ams-detail-item ams-detail-item--full" id="yearEnrollmentDatesWrap">
                        <span class="ams-detail-label">Enrollment Period</span>
                        <form id="yearEnrollmentDatesForm" method="POST" class="mt-2">
                            <?php echo csrf_field(); ?>
                            <input type="hidden" name="enrollment_open" id="yearEnrollmentDatesOpen" value="0">
                            <div class="row g-2">
                                <div class="col-sm-6">
                                    <label class="form-label small mb-1" for="yearEnrollmentStart">Start Date</label>
                                    <input type="date" class="form-control" name="enrollment_starts_at" id="yearEnrollmentStart" required>
                                </div>
                                <div class="col-sm-6">
                                    <label class="form-label small mb-1" for="yearEnrollmentEnd">End Date</label>
                                    <input type="date" class="form-control" name="enrollment_ends_at" id="yearEnrollmentEnd" required>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary btn-sm mt-2">Save Enrollment Dates</button>
                            <small class="text-muted d-block mt-2" id="yearEnrollmentBounds">Enrollment dates must fall inside the school year shown above.</small>
                            <small class="text-muted d-block mt-1 d-none" id="yearClassStartLater">After this enrollment period is saved, Start of class and End of class appear, with a suggested date for each.</small>
                        </form>
                    </div>
                    <div class="ams-detail-item ams-detail-item--full d-none" id="yearClassStartWrap">
                        <span class="ams-detail-label">Start of class and end of class</span>
                        <p class="mb-2 fw-semibold" id="yearDetailDates">Not set yet</p>
                        <p class="mb-2" id="yearClassStartAsk">Enrollment is saved. When do classes start and end?</p>
                        <form id="yearClassStartForm" method="POST">
                            <?php echo csrf_field(); ?>
                            <div class="row g-2">
                                <div class="col-sm-6">
                                    <label class="form-label small mb-1" for="yearClassStart">Start of class</label>
                                    <input type="date" class="form-control" name="class_starts_at" id="yearClassStart" required>
                                </div>
                                <div class="col-sm-6">
                                    <label class="form-label small mb-1" for="yearClassEnd">End of class</label>
                                    <input type="date" class="form-control" name="class_ends_at" id="yearClassEnd" required>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary btn-sm mt-2">Save class dates</button>
                            <small class="text-muted d-block mt-2" id="yearClassStartHint"></small>
                        </form>
                    </div>
                    <div class="ams-detail-item ams-detail-item--full">
                        <span class="ams-detail-label">Kept records</span>
                        <span class="ams-detail-value">Classes, subjects, and curriculum stay in place for this year and for any new upcoming year.</span>
                                        </div>
                                    </div>
                                </div>
            <div class="modal-footer border-0 pt-0 flex-wrap gap-2">
                <button type="button" class="btn btn-warning" id="yearEditBtn"><i class="fas fa-edit me-1"></i> Edit</button>
                <form id="yearEnrollmentForm" method="POST" class="d-inline">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="enrollment_open" id="yearEnrollmentOpen" value="1">
                    <button type="submit" class="btn btn-success" id="yearEnrollmentBtn">Open Enrollment</button>
                </form>
                <button type="button" class="btn btn-outline-secondary" id="yearDeleteBtn"
                    data-bs-toggle="modal" data-bs-target="#yearDeleteModal"><i class="fas fa-box-archive me-1"></i> Archive</button>
                <button type="button" class="btn btn-danger" id="yearForceDeleteBtn"
                    data-bs-toggle="modal" data-bs-target="#yearForceDeleteModal"><i class="fas fa-trash me-1"></i> Delete</button>
                <button type="button" class="btn btn-outline-primary d-none" id="yearUnarchiveBtn"
                    data-bs-toggle="modal" data-bs-target="#yearDeleteModal"><i class="fas fa-box-open me-1"></i> Unarchive</button>
                <a href="<?php echo e(route('semesters.index')); ?>" class="btn btn-outline-primary">Manage Semesters</a>
                <button type="button" class="btn btn-light ms-auto" data-bs-dismiss="modal">Close</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>


<div class="modal fade" id="yearFormModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content ams-float-modal">
            <div class="modal-header border-0 pb-0">
                                                <div>
                    <p class="ams-modal-eyebrow mb-1" id="yearFormEyebrow">New Academic Year</p>
                    <h4 class="modal-title mb-0" id="yearFormTitle">Add Academic Year</h4>
                                                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
            <form id="yearForm">
                <div class="modal-body pt-3">
                    <div id="yearFormMsg" class="mb-2"></div>
                    <div class="mb-3" data-mdp-academic-year>
                        <label class="form-label fw-semibold">Academic Year</label>
                        <div class="mdp-year-range">
                            <div>
                                <label class="form-label small text-muted mb-1" for="yearStartSelect">Start year</label>
                                <input class="form-control" id="yearStartSelect" data-mdp-year-start type="number" inputmode="numeric" min="1000" max="9999" step="1" required>
                                            </div>
                            <div class="mdp-year-sep">–</div>
                            <div>
                                <label class="form-label small text-muted mb-1" for="yearEndSelect">End year</label>
                                <input class="form-control" id="yearEndSelect" data-mdp-year-end type="number" inputmode="numeric" min="1000" max="9999" step="1" required>
                                        </div>
                                    </div>
                        <input type="hidden" id="yearName" name="name" data-mdp-year-name required value="">
                        <div class="mdp-year-preview" data-mdp-year-preview>Academic Year: —</div>
                        <small class="text-muted">Type the two years, for example 2028–2029.</small>
                                </div>
                    <div class="mb-3" id="yearDateRow">
                        <label class="form-label fw-semibold mb-1">School year dates</label>
                        <p class="text-muted small mb-2">These are the first day and the last day of this school year. Enrollment, Start of class, and End of class are set later, after the year is saved.</p>
                        <div class="row g-2">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" for="yearStart">School year starts</label>
                            <input type="date" class="form-control" id="yearStart" name="start_date" required>
                                                </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" for="yearEnd">School year ends</label>
                            <input type="date" class="form-control" id="yearEnd" name="end_date" required>
                                                </div>
                        </div>
                                            </div>
                    <div class="mb-0" id="yearStatusRow">
                        <label class="form-label fw-semibold" for="yearStatus">Status</label>
                        <select class="form-control" id="yearStatus" name="status">
                            <option value="upcoming">Upcoming</option>
                            <option value="current">Current (Active)</option>
                            <option value="completed">Completed</option>
                        </select>
                        <small class="text-muted">Current is the one active academic year. Enrollment stays closed until you open it. Classes, subjects, and curriculum stay available.</small>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="yearFormSave"><i class="fas fa-save me-1"></i> Save</button>
                                        </div>
            </form>
                                    </div>
                                </div>
                            </div>


<div class="modal custom-modal fade" id="yearDeleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body">
                <div class="form-header">
                    <h3 id="yearDeleteTitle">Archive Academic Year</h3>
                    <p class="mb-0" id="yearDeleteText">Archive <strong id="yearDeleteName">this year</strong>? It stays on record with its semesters and class schedules. Classes, subjects, and curriculum are not removed.</p>
                                                </div>
                <div class="modal-btn delete-action">
                    <div class="row">
                        <div class="col-6">
                            <button type="button" class="btn btn-primary paid-continue-btn w-100" id="yearDeleteConfirm">Archive</button>
                                                    </div>
                        <div class="col-6">
                            <button type="button" class="btn btn-primary paid-cancel-btn w-100" data-bs-dismiss="modal">Cancel</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal custom-modal fade" id="yearForceDeleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body">
                <div class="form-header">
                    <h3>Delete Academic Year</h3>
                    <p class="mb-0" id="yearForceDeleteText">Delete this academic year? It is removed. This is not Archive.</p>
                </div>
                <div class="modal-btn delete-action">
                    <div class="row">
                        <div class="col-6">
                            <button type="button" class="btn btn-danger w-100" id="yearForceDeleteConfirm">Delete</button>
                        </div>
                        <div class="col-6">
                            <button type="button" class="btn btn-primary paid-cancel-btn w-100" data-bs-dismiss="modal">Cancel</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="yearsPageConfig" class="d-none"
    data-store-url="<?php echo e(route('academic_years.store')); ?>"
    data-csrf="<?php echo e(csrf_token()); ?>"
    data-ask-class-start="<?php echo e(session('ask_class_start')); ?>"
    aria-hidden="true"></div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
<style>
.ams-years { --ams-line:#e5e7eb; --ams-ink:#111827; --ams-muted:#6b7280; --ams-blue:#1e3a8a; --ams-soft:#f8fafc; }
.ams-stat { background:#fff; border:1px solid var(--ams-line); border-radius:14px; padding:1rem; }
.ams-stat-val { display:block; font-size:1.5rem; font-weight:800; color:var(--ams-ink); }
.ams-stat-lbl { font-size:.8rem; color:var(--ams-muted); font-weight:600; }
.ams-stat--ok .ams-stat-val { color:#065f46; }
.ams-stat--info .ams-stat-val { color:#1d4ed8; }
.ams-stat--muted .ams-stat-val { color:#64748b; }
.ams-toolbar { max-width:520px; }
.ams-search { position:relative; }
.ams-search-icon { position:absolute; left:.9rem; top:50%; transform:translateY(-50%); color:#94a3b8; }
.ams-search-input { height:44px; padding-left:2.4rem; border-radius:12px; border:1px solid var(--ams-line); }
.ams-filter-bar { display:flex; flex-wrap:wrap; gap:.5rem; }
.ams-filter-chip { border:1px solid var(--ams-line); background:#fff; border-radius:999px; padding:.35rem .8rem; font-size:.85rem; font-weight:600; }
.ams-filter-chip.is-active { background:var(--ams-blue); border-color:var(--ams-blue); color:#fff; }
.ams-year-card { border:1px solid var(--ams-line); background:#fff; border-radius:14px; padding:1rem; min-height:148px; transition:transform .18s ease, box-shadow .18s ease; }
.ams-year-card:hover { transform:translateY(-3px); border-color:#93c5fd; box-shadow:0 12px 24px rgba(30,58,138,.12); }
.ams-year-title { font-weight:700; font-size:1.05rem; color:var(--ams-ink); }
.ams-year-dates, .ams-year-meta { font-size:.82rem; color:var(--ams-muted); }
.ams-year-hint { font-size:.75rem; font-weight:600; color:#2563eb; }
.ams-status-pill { font-size:.72rem; font-weight:700; border-radius:999px; padding:.2rem .55rem; white-space:nowrap; }
.ams-status-pill--current { background:#d1fae5; color:#065f46; }
.ams-status-pill--upcoming { background:#dbeafe; color:#1e40af; }
.ams-status-pill--completed { background:#e2e8f0; color:#475569; }
.ams-status-pill--archived { background:#fef3c7; color:#92400e; }
.ams-float-modal { border:0; border-radius:18px; box-shadow:0 24px 48px rgba(15,23,42,.18); }
.ams-modal-eyebrow { font-size:.75rem; font-weight:700; letter-spacing:.04em; text-transform:uppercase; color:var(--ams-muted); margin:0; }
.ams-detail-grid { display:grid; grid-template-columns:1fr 1fr; gap:.85rem; }
.ams-detail-item { background:var(--ams-soft); border:1px solid var(--ams-line); border-radius:12px; padding:.75rem .9rem; }
.ams-detail-item--full { grid-column:1 / -1; }
.ams-detail-label { display:block; font-size:.72rem; font-weight:700; text-transform:uppercase; color:var(--ams-muted); margin-bottom:.25rem; }
.ams-detail-value { font-weight:600; color:var(--ams-ink); }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
(function () {
    const cfg = document.getElementById('yearsPageConfig');
    const storeUrl = cfg?.dataset.storeUrl || '';
    const csrf = cfg?.dataset.csrf || '';
    let active = null;
    let mode = 'create';
    let statusFilter = 'all';

    const detailEl = document.getElementById('yearDetailModal');
    const formEl = document.getElementById('yearFormModal');
    const deleteEl = document.getElementById('yearDeleteModal');
    const detailModal = detailEl ? bootstrap.Modal.getOrCreateInstance(detailEl) : null;
    const formModal = formEl ? bootstrap.Modal.getOrCreateInstance(formEl) : null;
    const deleteModal = deleteEl ? bootstrap.Modal.getOrCreateInstance(deleteEl) : null;

    function applyFilters() {
        const q = (document.getElementById('yearSearch')?.value || '').trim().toLowerCase();
        let visible = 0;
        document.querySelectorAll('.year-grid-item').forEach(function (item) {
            const statusOk = statusFilter === 'all' || item.getAttribute('data-status') === statusFilter;
            const searchOk = !q || (item.getAttribute('data-search') || '').indexOf(q) !== -1;
            const show = statusOk && searchOk;
            item.classList.toggle('d-none', !show);
            if (show) visible += 1;
        });
        const empty = document.getElementById('yearsFilterEmpty');
        if (empty) empty.classList.toggle('d-none', visible > 0 || document.querySelectorAll('.year-grid-item').length === 0);
    }

    document.getElementById('yearSearch')?.addEventListener('input', applyFilters);
    document.querySelectorAll('.ams-filter-chip').forEach(function (chip) {
        chip.addEventListener('click', function () {
            document.querySelectorAll('.ams-filter-chip').forEach(function (c) { c.classList.remove('is-active'); });
            chip.classList.add('is-active');
            statusFilter = chip.getAttribute('data-status') || 'all';
            applyFilters();
        });
    });

    function statusLabel(s) {
        return (s || '').charAt(0).toUpperCase() + (s || '').slice(1);
    }

    detailEl?.addEventListener('show.bs.modal', function (e) {
        const btn = e.relatedTarget;
        if (!btn || !btn.classList.contains('ams-year-card')) return;
        active = {
            id: btn.getAttribute('data-id'),
            name: btn.getAttribute('data-name') || '',
            start: btn.getAttribute('data-start') || '',
            end: btn.getAttribute('data-end') || '',
            startLabel: btn.getAttribute('data-start-label') || '',
            endLabel: btn.getAttribute('data-end-label') || '',
            classStart: btn.getAttribute('data-class-start') || '',
            classEnd: btn.getAttribute('data-class-end') || '',
            classStartLabel: btn.getAttribute('data-class-start-label') || '',
            classEndLabel: btn.getAttribute('data-class-end-label') || '',
            classSuggest: btn.getAttribute('data-class-suggest') || '',
            classSuggestLabel: btn.getAttribute('data-class-suggest-label') || '',
            classSuggestNote: btn.getAttribute('data-class-suggest-note') || '',
            classEndSuggest: btn.getAttribute('data-class-end-suggest') || '',
            classEndSuggestLabel: btn.getAttribute('data-class-end-suggest-label') || '',
            classStartUrl: btn.getAttribute('data-class-start-url') || '',
            status: btn.getAttribute('data-status') || '',
            enrollmentOpen: btn.getAttribute('data-enrollment-open') || '0',
            enrollmentStart: btn.getAttribute('data-enrollment-start') || '',
            enrollmentEnd: btn.getAttribute('data-enrollment-end') || '',
            enrollmentSettingsUrl: btn.getAttribute('data-enrollment-settings-url') || '',
            enrollmentUrl: btn.getAttribute('data-enrollment-url') || '',
            semesters: btn.getAttribute('data-semesters') || '0',
            updateUrl: btn.getAttribute('data-update-url') || '',
            destroyUrl: btn.getAttribute('data-destroy-url') || '',
            forceDeleteUrl: btn.getAttribute('data-force-delete-url') || '',
            unarchiveUrl: btn.getAttribute('data-unarchive-url') || '',
            cardBtn: btn
        };
        document.getElementById('yearDeleteBtn')?.classList.toggle('d-none', active.status === 'current' || active.status === 'archived');
        document.getElementById('yearUnarchiveBtn')?.classList.toggle('d-none', active.status !== 'archived');
        document.getElementById('yearDetailTitle').textContent = active.name;
        document.getElementById('yearDetailStatus').textContent = statusLabel(active.status);
        document.getElementById('yearDetailEnrollment').textContent = active.enrollmentOpen === '1' ? 'Open' : 'Closed';
        const enrollmentForm = document.getElementById('yearEnrollmentForm');
        const enrollmentBtn = document.getElementById('yearEnrollmentBtn');
        if (enrollmentForm && enrollmentBtn) {
            enrollmentForm.action = active.enrollmentUrl;
            const isOpen = active.enrollmentOpen === '1';
            document.getElementById('yearEnrollmentOpen').value = isOpen ? '0' : '1';
            enrollmentBtn.textContent = isOpen ? 'Close Enrollment' : 'Open Enrollment';
            enrollmentBtn.disabled = !isOpen && active.status !== 'current';
            enrollmentBtn.title = enrollmentBtn.disabled ? 'Set this year as Current before opening enrollment.' : '';
        }
        document.getElementById('yearDetailSemesters').textContent = active.semesters;
        document.getElementById('yearDetailSchoolStart').textContent = active.startLabel || '—';
        document.getElementById('yearDetailSchoolEnd').textContent = active.endLabel || '—';
        const bounds = document.getElementById('yearEnrollmentBounds');
        if (bounds) {
            bounds.textContent = (active.startLabel && active.endLabel)
                ? 'Enrollment dates must fall inside ' + active.startLabel + ' to ' + active.endLabel + '. Open Enrollment after the dates are saved.'
                : 'Enrollment dates must fall inside the school year shown above.';
        }
        const classRange = document.getElementById('yearDetailDates');
        if (classRange) {
            classRange.textContent = (active.classStartLabel && active.classEndLabel)
                ? active.classStartLabel + ' → ' + active.classEndLabel
                : 'Not set yet';
        }
        const hasEnrollment = !!(active.enrollmentStart && active.enrollmentEnd);
        const showClassStart = active.status === 'current' && hasEnrollment;
        document.getElementById('yearClassStartWrap')?.classList.toggle('d-none', !showClassStart);
        document.getElementById('yearClassStartLater')?.classList.toggle('d-none', !(active.status === 'current' && !hasEnrollment));
        const classForm = document.getElementById('yearClassStartForm');
        const classInput = document.getElementById('yearClassStart');
        const classEndInput = document.getElementById('yearClassEnd');
        const classHint = document.getElementById('yearClassStartHint');
        const classAsk = document.getElementById('yearClassStartAsk');
        if (classForm && classInput && classEndInput) {
            classForm.action = active.classStartUrl;
            classInput.value = active.classStart || active.classSuggest;
            classEndInput.value = active.classEnd || active.classEndSuggest;
            classInput.min = active.classSuggest || active.enrollmentEnd || active.start || '';
            classInput.max = active.end || '';
            classEndInput.min = classInput.value || active.classSuggest || '';
            classEndInput.max = active.end || '';
        }
        if (classAsk) {
            classAsk.textContent = (active.classStart && active.classEnd)
                ? 'These are the class dates. You can change them.'
                : 'Enrollment is saved. When do classes start and end?';
        }
        if (classHint) {
            const startNote = active.classSuggestNote || '';
            const endNote = active.classEndSuggestLabel
                ? 'Suggested end of class: ' + active.classEndSuggestLabel + '. This is the last day of the school year.'
                : '';
            classHint.textContent = [startNote, endNote].filter(Boolean).join(' ');
        }
        const datesWrap = document.getElementById('yearEnrollmentDatesWrap');
        const datesForm = document.getElementById('yearEnrollmentDatesForm');
        if (datesWrap && datesForm) {
            const isCurrent = active.status === 'current';
            datesWrap.classList.toggle('d-none', !isCurrent);
            datesForm.action = active.enrollmentSettingsUrl;
            document.getElementById('yearEnrollmentDatesOpen').value = active.enrollmentOpen === '1' ? '1' : '0';
            document.getElementById('yearEnrollmentStart').value = active.enrollmentStart;
            document.getElementById('yearEnrollmentEnd').value = active.enrollmentEnd;
        }
    });

    document.getElementById('btnAddYear')?.addEventListener('click', function () {
        mode = 'create';
        active = null;
        document.getElementById('yearFormEyebrow').textContent = 'New Academic Year';
        document.getElementById('yearFormTitle').textContent = 'Add Academic Year';
        document.getElementById('yearStatusRow').classList.add('d-none');
        document.getElementById('yearForm').reset();
        document.getElementById('yearStart').value = '';
        document.getElementById('yearEnd').value = '';
        document.getElementById('yearFormMsg').innerHTML = '';
        const yearRoot = document.querySelector('[data-mdp-academic-year]');
        if (yearRoot) yearRoot.dataset.mdpReady = '';
        setTimeout(function () {
            if (window.ModernDatepicker) window.ModernDatepicker.refresh();
        }, 100);
    });

    document.getElementById('yearEditBtn')?.addEventListener('click', function () {
        if (!active) return;
        mode = 'edit';
        document.getElementById('yearFormEyebrow').textContent = 'Edit Academic Year';
        document.getElementById('yearFormTitle').textContent = active.name;
        document.getElementById('yearStatusRow').classList.remove('d-none');
        document.getElementById('yearStatus').value = ['upcoming', 'current', 'completed'].indexOf(active.status) >= 0 ? active.status : 'completed';
        document.getElementById('yearName').value = active.name;
        document.getElementById('yearStart').value = active.start;
        document.getElementById('yearEnd').value = active.end;
        if (active.start) document.getElementById('yearEnd').min = active.start;
        document.getElementById('yearFormMsg').innerHTML = '';
        detailModal?.hide();
        setTimeout(function () {
            formModal?.show();
            if (window.ModernDatepicker) window.ModernDatepicker.refresh();
        }, 200);
    });

    document.getElementById('yearStart')?.addEventListener('change', function () {
        const end = document.getElementById('yearEnd');
        if (end && this.value) end.min = this.value;
    });

    document.getElementById('yearForm')?.addEventListener('submit', function (e) {
        e.preventDefault();

        const payload = {
            name: document.getElementById('yearName').value.trim(),
            start_date: document.getElementById('yearStart').value,
            end_date: document.getElementById('yearEnd').value,
            status: mode === 'edit' ? document.getElementById('yearStatus').value : 'upcoming',
            _token: csrf
        };
        const yearRange = payload.name.match(/^(\d{4})-(\d{4})$/);
        if (!yearRange || Number(yearRange[2]) <= Number(yearRange[1])) {
            document.getElementById('yearFormMsg').innerHTML =
                '<div class="alert alert-danger py-2 mb-0">Enter a valid range with the end year later than the start year.</div>';
            return;
        }
        if (!payload.start_date || !payload.end_date || payload.end_date <= payload.start_date) {
            document.getElementById('yearFormMsg').innerHTML =
                '<div class="alert alert-danger py-2 mb-0">Enter the day the school year starts and a later day for when it ends.</div>';
            return;
        }
        const url = mode === 'edit' && active ? active.updateUrl : storeUrl;
        if (mode === 'edit') payload._method = 'PUT';

        const btn = document.getElementById('yearFormSave');
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Saving...';

        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrf
            },
            body: JSON.stringify(payload)
        })
        .then(function (r) { return r.json().then(function (d) { if (!r.ok) throw d; return d; }); })
        .then(function () { window.location.reload(); })
        .catch(function (err) {
            let msg = 'Failed to save.';
            if (err?.message) msg = err.message;
            if (err?.errors) {
                const first = Object.values(err.errors)[0];
                if (first?.[0]) msg = first[0];
            }
            document.getElementById('yearFormMsg').innerHTML = '<div class="alert alert-danger py-2 mb-0">' + msg + '</div>';
        })
        .finally(function () {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-save me-1"></i> Save';
        });
    });

    deleteEl?.addEventListener('show.bs.modal', function () {
        if (!active) return;
        const restoring = active.status === 'archived';
        document.getElementById('yearDeleteTitle').textContent = restoring ? 'Unarchive Academic Year' : 'Archive Academic Year';
        document.getElementById('yearDeleteText').innerHTML = restoring
            ? 'Unarchive <strong>' + active.name + '</strong>? It will be labeled Completed again. Classes, subjects, and curriculum stay in place.'
            : 'Archive <strong>' + active.name + '</strong>? It stays on record with its semesters and class schedules. Classes, subjects, and curriculum are not removed.';
        document.getElementById('yearDeleteConfirm').textContent = restoring ? 'Unarchive' : 'Archive';
    });

    document.getElementById('yearDeleteConfirm')?.addEventListener('click', function () {
        if (!active) return;
        const restoring = active.status === 'archived';
        fetch(restoring ? active.unarchiveUrl : active.destroyUrl, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrf,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(restoring ? { _token: csrf } : { _token: csrf, _method: 'DELETE' })
        })
        .then(function (r) { return r.json().then(function (d) { if (!r.ok) throw d; return d; }); })
        .then(function () { window.location.reload(); })
        .catch(function (err) { window.alert(err?.message || (restoring ? 'Unable to unarchive this academic year.' : 'Unable to archive this academic year.')); });
    });

    const forceDeleteEl = document.getElementById('yearForceDeleteModal');
    forceDeleteEl?.addEventListener('show.bs.modal', function () {
        if (!active) return;
        const text = document.getElementById('yearForceDeleteText');
        if (text) {
            text.innerHTML = 'Delete <strong>' + active.name + '</strong>? The academic year is removed. This is not Archive.';
        }
    });

    document.getElementById('yearForceDeleteConfirm')?.addEventListener('click', function () {
        if (!active || !active.forceDeleteUrl) return;
        fetch(active.forceDeleteUrl, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrf,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ _token: csrf })
        })
        .then(function (r) { return r.json().then(function (d) { if (!r.ok) throw d; return d; }); })
        .then(function () { window.location.reload(); })
        .catch(function (err) { window.alert(err?.message || 'Unable to delete this academic year.'); });
    });

    const askClassStart = cfg?.dataset.askClassStart || '';
    if (askClassStart && detailEl) {
        const opener = document.querySelector('.ams-year-card[data-id="' + askClassStart + '"]');
        if (opener) {
            const event = new Event('show.bs.modal');
            Object.defineProperty(event, 'relatedTarget', { value: opener });
            detailEl.dispatchEvent(event);
            detailModal?.show();
        }
    }
})();
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Laravel\Capstone-Project\lms\resources\views/academic_years/index.blade.php ENDPATH**/ ?>