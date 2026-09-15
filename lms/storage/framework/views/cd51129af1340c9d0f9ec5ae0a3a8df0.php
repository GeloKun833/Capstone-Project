<?php $__env->startSection('content'); ?>
    <div class="page-wrapper">
    <div class="content container-fluid ams-years">
            <div class="page-header">
                <div class="row align-items-start">
                    <div class="col">
                        <div>
                            <h3 class="page-title mb-1">Academic Years</h3>
                            <p class="text-muted mb-0">Manage school years used by enrollment, grading, and semesters.</p>
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
                </div>
            </div>

        <div class="row g-3" id="yearsGrid">
            <?php $__empty_1 = true; $__currentLoopData = $academicYears; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $year): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <?php $status = $year->statusLabel(); ?>
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
                        data-status="<?php echo e($status); ?>"
                        data-semesters="<?php echo e($year->semesters_count); ?>"
                        data-update-url="<?php echo e(route('academic_years.update', $year)); ?>"
                        data-destroy-url="<?php echo e(route('academic_years.destroy', $year)); ?>">
                        <div class="d-flex justify-content-between align-items-start gap-2">
                            <span class="ams-year-title"><?php echo e($year->name); ?></span>
                            <span class="ams-status-pill ams-status-pill--<?php echo e($status); ?>"><?php echo e(ucfirst($status)); ?></span>
                        </div>
                        <div class="ams-year-dates mt-2">
                            <?php echo e($year->start_date->format('M d, Y')); ?> → <?php echo e($year->end_date->format('M d, Y')); ?>

                        </div>
                        <div class="ams-year-meta mt-2">
                            <i class="fas fa-calendar-week me-1"></i><?php echo e($year->semesters_count); ?> semester(s)
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
                        <span class="ams-detail-label">Semesters</span>
                        <span class="ams-detail-value" id="yearDetailSemesters">—</span>
                                            </div>
                    <div class="ams-detail-item ams-detail-item--full">
                        <span class="ams-detail-label">Date Range</span>
                        <span class="ams-detail-value" id="yearDetailDates">—</span>
                                        </div>
                                    </div>
                                </div>
            <div class="modal-footer border-0 pt-0 flex-wrap gap-2">
                <button type="button" class="btn btn-warning" id="yearEditBtn"><i class="fas fa-edit me-1"></i> Edit</button>
                <button type="button" class="btn btn-danger" id="yearDeleteBtn"
                    data-bs-toggle="modal" data-bs-target="#yearDeleteModal"><i class="fas fa-trash me-1"></i> Delete</button>
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
                                <select class="form-control form-select" id="yearStartSelect" data-mdp-year-start></select>
                                            </div>
                            <div class="mdp-year-sep">–</div>
                            <div>
                                <label class="form-label small text-muted mb-1" for="yearEndSelect">End year</label>
                                <select class="form-control form-select" id="yearEndSelect" data-mdp-year-end></select>
                                        </div>
                                    </div>
                        <input type="hidden" id="yearName" name="name" data-mdp-year-name required value="">
                        <div class="mdp-year-preview" data-mdp-year-preview>Academic Year: —</div>
                        <small class="text-muted">Select years only (example: 2026–2027). Start/end dates are set automatically.</small>
                                </div>
                    <div class="row g-2 d-none" id="yearDateRow">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" for="yearStart">Start Date</label>
                            <input type="date" class="form-control" id="yearStart" name="start_date">
                                                </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" for="yearEnd">End Date</label>
                            <input type="date" class="form-control" id="yearEnd" name="end_date">
                                                </div>
                                            </div>
                    <p class="text-muted small mt-2 mb-0">School year status is derived from the year label.</p>
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
                    <h3>Delete Academic Year</h3>
                    <p class="mb-0">Delete <strong id="yearDeleteName">this year</strong>? Linked semesters will also be removed.</p>
                                                </div>
                <div class="modal-btn delete-action">
                    <div class="row">
                        <div class="col-6">
                            <button type="button" class="btn btn-primary paid-continue-btn w-100" id="yearDeleteConfirm">Delete</button>
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
            status: btn.getAttribute('data-status') || '',
            semesters: btn.getAttribute('data-semesters') || '0',
            updateUrl: btn.getAttribute('data-update-url') || '',
            destroyUrl: btn.getAttribute('data-destroy-url') || '',
            cardBtn: btn
        };
        document.getElementById('yearDetailTitle').textContent = active.name;
        document.getElementById('yearDetailStatus').textContent = statusLabel(active.status);
        document.getElementById('yearDetailSemesters').textContent = active.semesters;
        document.getElementById('yearDetailDates').textContent = active.startLabel + ' → ' + active.endLabel;
    });

    document.getElementById('btnAddYear')?.addEventListener('click', function () {
        mode = 'create';
        active = null;
        document.getElementById('yearFormEyebrow').textContent = 'New Academic Year';
        document.getElementById('yearFormTitle').textContent = 'Add Academic Year';
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
        document.getElementById('yearName').value = active.name;
        document.getElementById('yearStart').value = active.start;
        document.getElementById('yearEnd').value = active.end;
        document.getElementById('yearFormMsg').innerHTML = '';
        detailModal?.hide();
        setTimeout(function () {
            formModal?.show();
            if (window.ModernDatepicker) window.ModernDatepicker.refresh();
        }, 200);
    });

    document.getElementById('yearForm')?.addEventListener('submit', function (e) {
        e.preventDefault();
        const payload = {
            name: document.getElementById('yearName').value.trim(),
            start_date: '',
            end_date: '',
            _token: csrf
        };
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
        document.getElementById('yearDeleteName').textContent = active.name;
    });

    document.getElementById('yearDeleteConfirm')?.addEventListener('click', function () {
        if (!active) return;
        fetch(active.destroyUrl, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrf,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ _token: csrf, _method: 'DELETE' })
        }).then(function () { window.location.reload(); });
    });
})();
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Laravel\Capstone-Project\lms\resources\views/academic_years/index.blade.php ENDPATH**/ ?>