@extends('layouts.master')
@section('content')
<div class="page-wrapper">
    <div class="content container-fluid ams-semesters">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">Semesters</h3>
                    <p class="text-muted mb-0">Semesters belong to an academic year and power enrollment periods.</p>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('academic_years.index') }}">Academic Years</a></li>
                        <li class="breadcrumb-item active">Semesters</li>
                    </ul>
                </div>
                <div class="col-auto d-flex flex-wrap gap-2">
                    <a href="{{ route('academic_years.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-calendar-alt me-1"></i> Academic Years
                    </a>
                    <button type="button" class="btn btn-primary" id="btnAddSemester"
                        data-bs-toggle="modal" data-bs-target="#semesterFormModal"
                        @if($academicYears->isEmpty()) disabled @endif>
                        <i class="fas fa-plus me-1"></i> Add Semester
                    </button>
                </div>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if($academicYears->isEmpty())
            <div class="alert alert-warning">
                Create an <a href="{{ route('academic_years.index') }}">Academic Year</a> first, then add semesters.
            </div>
        @endif

        <div class="ams-toolbar mb-3">
            <div class="ams-search">
                <i class="fas fa-search ams-search-icon"></i>
                <input type="search" id="semesterSearch" class="form-control ams-search-input"
                    placeholder="Search semester or year..." autocomplete="off">
            </div>
            <div class="ams-filter-bar mt-2">
                <button type="button" class="ams-filter-chip is-active" data-year="all">All Years</button>
                @foreach($academicYears as $year)
                    <button type="button" class="ams-filter-chip" data-year="{{ $year->id }}">
                        {{ $year->name }}
                    </button>
                @endforeach
            </div>
        </div>

        <div class="row g-3" id="semestersGrid">
            @forelse($semesters as $semester)
                <div class="col-6 col-md-4 col-xl-3 semester-grid-item"
                    data-year="{{ $semester->academic_year_id }}"
                    data-search="{{ strtolower($semester->name.' '.optional($semester->academicYear)->name) }}"
                    data-id="{{ $semester->id }}">
                    <button type="button"
                        class="ams-sem-card w-100 text-start"
                        data-bs-toggle="modal"
                        data-bs-target="#semesterDetailModal"
                        data-id="{{ $semester->id }}"
                        data-name="{{ $semester->name }}"
                        data-year-id="{{ $semester->academic_year_id }}"
                        data-year-name="{{ optional($semester->academicYear)->name ?? '—' }}"
                        data-update-url="{{ route('semesters.update', $semester) }}"
                        data-destroy-url="{{ route('semesters.destroy', $semester) }}">
                        <div class="ams-sem-title">{{ $semester->name }}</div>
                        <div class="ams-sem-year mt-2">{{ optional($semester->academicYear)->name ?? 'No year' }}</div>
                        <div class="ams-sem-hint mt-3">Click to manage <i class="fas fa-arrow-right ms-1"></i></div>
                    </button>
                </div>
            @empty
                <div class="col-12">
                    <div class="alert alert-light border mb-0">No semesters yet. Add 1st Semester / 2nd Semester for an academic year.</div>
                </div>
            @endforelse
        </div>
        <div id="semestersFilterEmpty" class="alert alert-light border text-center d-none mt-3">
            No semesters match your search or year filter.
        </div>
    </div>
</div>

{{-- Detail --}}
<div class="modal fade" id="semesterDetailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content ams-float-modal">
            <div class="modal-header border-0 pb-0">
                <div>
                    <p class="ams-modal-eyebrow mb-1">Semester</p>
                    <h4 class="modal-title mb-0" id="semDetailTitle">Semester</h4>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-3">
                <div class="ams-detail-item">
                    <span class="ams-detail-label">Academic Year</span>
                    <span class="ams-detail-value" id="semDetailYear">—</span>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0 flex-wrap gap-2">
                <button type="button" class="btn btn-warning" id="semEditBtn"><i class="fas fa-edit me-1"></i> Edit</button>
                <button type="button" class="btn btn-danger" id="semDeleteBtn"
                    data-bs-toggle="modal" data-bs-target="#semesterDeleteModal"><i class="fas fa-trash me-1"></i> Delete</button>
                <button type="button" class="btn btn-light ms-auto" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

{{-- Create / Edit --}}
<div class="modal fade" id="semesterFormModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content ams-float-modal">
            <div class="modal-header border-0 pb-0">
                <div>
                    <p class="ams-modal-eyebrow mb-1" id="semFormEyebrow">New Semester</p>
                    <h4 class="modal-title mb-0" id="semFormTitle">Add Semester</h4>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="semesterForm">
                <div class="modal-body pt-3">
                    <div id="semFormMsg" class="mb-2"></div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold" for="semName">Semester Name</label>
                        <input type="text" class="form-control" id="semName" required
                            placeholder="e.g. 1st Semester, 2nd Semester">
                    </div>
                    <div class="mb-0">
                        <label class="form-label fw-semibold" for="semYear">Academic Year</label>
                        <select class="form-control" id="semYear" required>
                            <option value="">Select academic year</option>
                            @foreach($academicYears as $year)
                                <option value="{{ $year->id }}">{{ $year->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="semFormSave"><i class="fas fa-save me-1"></i> Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Delete --}}
<div class="modal custom-modal fade" id="semesterDeleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body">
                <div class="form-header">
                    <h3>Delete Semester</h3>
                    <p class="mb-0">Delete <strong id="semDeleteName">this semester</strong>?</p>
                </div>
                <div class="modal-btn delete-action">
                    <div class="row">
                        <div class="col-6">
                            <button type="button" class="btn btn-primary paid-continue-btn w-100" id="semDeleteConfirm">Delete</button>
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

<div id="semestersPageConfig" class="d-none"
    data-store-url="{{ route('semesters.store') }}"
    data-csrf="{{ csrf_token() }}"
    aria-hidden="true"></div>
@endsection

@push('styles')
<style>
.ams-semesters { --ams-line:#e5e7eb; --ams-ink:#111827; --ams-muted:#6b7280; --ams-blue:#1e3a8a; --ams-soft:#f8fafc; }
.ams-toolbar { max-width:640px; }
.ams-search { position:relative; }
.ams-search-icon { position:absolute; left:.9rem; top:50%; transform:translateY(-50%); color:#94a3b8; }
.ams-search-input { height:44px; padding-left:2.4rem; border-radius:12px; border:1px solid var(--ams-line); }
.ams-filter-bar { display:flex; flex-wrap:wrap; gap:.5rem; }
.ams-filter-chip { border:1px solid var(--ams-line); background:#fff; border-radius:999px; padding:.35rem .8rem; font-size:.85rem; font-weight:600; }
.ams-filter-chip.is-active { background:var(--ams-blue); border-color:var(--ams-blue); color:#fff; }
.ams-sem-card { border:1px solid var(--ams-line); background:#fff; border-radius:14px; padding:1rem; min-height:128px; transition:transform .18s ease, box-shadow .18s ease; }
.ams-sem-card:hover { transform:translateY(-3px); border-color:#93c5fd; box-shadow:0 12px 24px rgba(30,58,138,.12); }
.ams-sem-title { font-weight:700; font-size:1.05rem; color:var(--ams-ink); }
.ams-sem-year { display:inline-block; font-size:.78rem; font-weight:700; color:#1e40af; background:#eff6ff; border-radius:999px; padding:.2rem .65rem; }
.ams-sem-hint { font-size:.75rem; font-weight:600; color:#2563eb; }
.ams-float-modal { border:0; border-radius:18px; box-shadow:0 24px 48px rgba(15,23,42,.18); }
.ams-modal-eyebrow { font-size:.75rem; font-weight:700; letter-spacing:.04em; text-transform:uppercase; color:var(--ams-muted); margin:0; }
.ams-detail-item { background:var(--ams-soft); border:1px solid var(--ams-line); border-radius:12px; padding:.75rem .9rem; }
.ams-detail-label { display:block; font-size:.72rem; font-weight:700; text-transform:uppercase; color:var(--ams-muted); margin-bottom:.25rem; }
.ams-detail-value { font-weight:600; color:var(--ams-ink); }
</style>
@endpush

@push('scripts')
<script>
(function () {
    const cfg = document.getElementById('semestersPageConfig');
    const storeUrl = cfg?.dataset.storeUrl || '';
    const csrf = cfg?.dataset.csrf || '';
    let active = null;
    let mode = 'create';
    let yearFilter = 'all';

    const detailEl = document.getElementById('semesterDetailModal');
    const formEl = document.getElementById('semesterFormModal');
    const deleteEl = document.getElementById('semesterDeleteModal');
    const detailModal = detailEl ? bootstrap.Modal.getOrCreateInstance(detailEl) : null;
    const formModal = formEl ? bootstrap.Modal.getOrCreateInstance(formEl) : null;

    function applyFilters() {
        const q = (document.getElementById('semesterSearch')?.value || '').trim().toLowerCase();
        let visible = 0;
        document.querySelectorAll('.semester-grid-item').forEach(function (item) {
            const yearOk = yearFilter === 'all' || String(item.getAttribute('data-year')) === String(yearFilter);
            const searchOk = !q || (item.getAttribute('data-search') || '').indexOf(q) !== -1;
            const show = yearOk && searchOk;
            item.classList.toggle('d-none', !show);
            if (show) visible += 1;
        });
        const empty = document.getElementById('semestersFilterEmpty');
        if (empty) empty.classList.toggle('d-none', visible > 0 || document.querySelectorAll('.semester-grid-item').length === 0);
    }

    document.getElementById('semesterSearch')?.addEventListener('input', applyFilters);
    document.querySelectorAll('.ams-filter-chip').forEach(function (chip) {
        chip.addEventListener('click', function () {
            document.querySelectorAll('.ams-filter-chip').forEach(function (c) { c.classList.remove('is-active'); });
            chip.classList.add('is-active');
            yearFilter = chip.getAttribute('data-year') || 'all';
            applyFilters();
        });
    });

    detailEl?.addEventListener('show.bs.modal', function (e) {
        const btn = e.relatedTarget;
        if (!btn || !btn.classList.contains('ams-sem-card')) return;
        active = {
            id: btn.getAttribute('data-id'),
            name: btn.getAttribute('data-name') || '',
            yearId: btn.getAttribute('data-year-id') || '',
            yearName: btn.getAttribute('data-year-name') || '',
            updateUrl: btn.getAttribute('data-update-url') || '',
            destroyUrl: btn.getAttribute('data-destroy-url') || ''
        };
        document.getElementById('semDetailTitle').textContent = active.name;
        document.getElementById('semDetailYear').textContent = active.yearName;
    });

    document.getElementById('btnAddSemester')?.addEventListener('click', function () {
        mode = 'create';
        active = null;
        document.getElementById('semFormEyebrow').textContent = 'New Semester';
        document.getElementById('semFormTitle').textContent = 'Add Semester';
        document.getElementById('semesterForm').reset();
        document.getElementById('semFormMsg').innerHTML = '';
    });

    document.getElementById('semEditBtn')?.addEventListener('click', function () {
        if (!active) return;
        mode = 'edit';
        document.getElementById('semFormEyebrow').textContent = 'Edit Semester';
        document.getElementById('semFormTitle').textContent = active.name;
        document.getElementById('semName').value = active.name;
        document.getElementById('semYear').value = active.yearId;
        document.getElementById('semFormMsg').innerHTML = '';
        detailModal?.hide();
        setTimeout(function () { formModal?.show(); }, 200);
    });

    document.getElementById('semesterForm')?.addEventListener('submit', function (e) {
        e.preventDefault();
        const payload = {
            name: document.getElementById('semName').value.trim(),
            academic_year_id: document.getElementById('semYear').value,
            _token: csrf
        };
        const url = mode === 'edit' && active ? active.updateUrl : storeUrl;
        if (mode === 'edit') payload._method = 'PUT';

        const btn = document.getElementById('semFormSave');
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
            document.getElementById('semFormMsg').innerHTML = '<div class="alert alert-danger py-2 mb-0">' + msg + '</div>';
        })
        .finally(function () {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-save me-1"></i> Save';
        });
    });

    deleteEl?.addEventListener('show.bs.modal', function () {
        if (!active) return;
        document.getElementById('semDeleteName').textContent = active.name;
    });

    document.getElementById('semDeleteConfirm')?.addEventListener('click', function () {
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
@endpush
