@extends('layouts.master')
@section('content')
<div class="page-wrapper">
    <div class="content container-fluid ams-curriculum">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">Curriculum</h3>
                    <p class="text-muted mb-0">
                        One curriculum per grade — subjects come from
                        <a href="{{ route('class-subject.unified-management') }}">Classes &amp; Subjects</a>
                        (same source as Enrollment).
                    </p>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Curriculum</li>
                    </ul>
                </div>
                <div class="col-auto d-flex flex-wrap gap-2">
                    <form action="{{ route('curriculum.syncAll') }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-outline-primary">
                            <i class="fas fa-sync-alt me-1"></i> Sync All from Catalog
                        </button>
                    </form>
                    <a href="{{ route('class-subject.unified-management') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-book me-1"></i> Manage Subjects
                    </a>
                </div>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="ams-toolbar mb-3">
            <div class="ams-search">
                <i class="fas fa-search ams-search-icon"></i>
                <input type="search" id="curriculumSearch" class="form-control ams-search-input"
                    placeholder="Search grade or subject..." autocomplete="off">
                <button type="button" id="curriculumSearchClear" class="ams-search-clear d-none" aria-label="Clear">
                    <i class="fas fa-times"></i>
                </button>
                    </div>
                </div>

        <div class="row g-3" id="curriculumGrid">
            @foreach($curricula as $curriculum)
                @php
                    $catalogCount = ($subjectsByGrade->get($curriculum->grade_level) ?? collect())->count();
                    $linkedCount = $curriculum->subjects->count();
                    $inSync = $linkedCount === $catalogCount && $catalogCount > 0;
                    $names = $curriculum->subjects->pluck('subject_name')->implode(', ');
                    $preview = $curriculum->subjects->take(3)->pluck('subject_name')->implode(', ');
                @endphp
                <div class="col-6 col-md-4 col-xl-3 curriculum-grid-item"
                    data-search="{{ strtolower($curriculum->grade_level.' '.$names.' '.($curriculum->description ?: '')) }}">
                    <button type="button"
                        class="ams-curr-card w-100 text-start"
                        data-bs-toggle="modal"
                        data-bs-target="#curriculumModal"
                        data-id="{{ $curriculum->id }}"
                        data-grade="{{ $curriculum->grade_level }}"
                        data-description="{{ $curriculum->description ?: '' }}"
                        data-linked="{{ $linkedCount }}"
                        data-catalog="{{ $catalogCount }}"
                        data-assign-url="{{ route('curriculum.assignSubjectsForm', $curriculum) }}"
                        data-sync-url="{{ route('curriculum.syncFromCatalog', $curriculum) }}"
                        data-update-url="{{ route('curriculum.update', $curriculum) }}"
                        data-destroy-url="{{ route('curriculum.destroy', $curriculum) }}">
                        <div class="d-flex justify-content-between align-items-start gap-2">
                            <span class="ams-curr-title">{{ $curriculum->grade_level }}</span>
                            <span class="ams-curr-count {{ $linkedCount ? '' : 'is-empty' }}">{{ $linkedCount }}</span>
                        </div>
                        <div class="ams-curr-meta mt-2">
                            @if($linkedCount === 0)
                                <span class="text-muted">No subjects linked — click to sync</span>
                            @else
                                <span class="text-muted">{{ $preview }}@if($linkedCount > 3)…@endif</span>
                            @endif
                        </div>
                        <div class="ams-curr-status mt-2">
                            @if($inSync)
                                <span class="ams-pill ams-pill--ok"><i class="fas fa-link me-1"></i>In sync with catalog</span>
                            @elseif($catalogCount === 0)
                                <span class="ams-pill ams-pill--warn"><i class="fas fa-exclamation me-1"></i>Add subjects in catalog</span>
                            @else
                                <span class="ams-pill ams-pill--info"><i class="fas fa-sync me-1"></i>Catalog has {{ $catalogCount }}</span>
                            @endif
                        </div>
                        <div class="ams-curr-hint mt-3">
                            Click to manage <i class="fas fa-arrow-right ms-1"></i>
                    </div>
                    </button>
                </div>
            @endforeach
        </div>
        <div id="curriculumEmpty" class="alert alert-light border text-center d-none mt-3">
            No curriculum matches your search.
                    </div>
                </div>
                    </div>

{{-- Floating detail modal --}}
<div class="modal fade" id="curriculumModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content ams-curr-modal">
            <div class="modal-header border-0 pb-0">
                <div>
                    <p class="ams-modal-eyebrow mb-1">Grade Curriculum</p>
                    <h4 class="modal-title mb-0" id="currModalTitle">Curriculum</h4>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-3">
                <p class="text-muted mb-3" id="currModalDesc"></p>

                <div class="d-flex flex-wrap gap-2 mb-3">
                    <span class="ams-stat-badge ams-stat-badge--linked" id="currModalLinked">0 linked</span>
                    <span class="ams-stat-badge ams-stat-badge--catalog" id="currModalCatalog">0 in catalog</span>
        </div>

                <div class="ams-curr-subjects mb-3" id="currModalSubjects"></div>
                <div id="currModalEmpty" class="alert alert-warning d-none mb-0">
                    No subjects linked yet. Click <strong>Sync from Catalog</strong> to pull subjects for this grade.
                </div>
                                </div>
            <div class="modal-footer border-0 pt-0 flex-wrap gap-2">
                <form id="currSyncForm" method="POST" action="" class="d-inline">
                                    @csrf
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-sync-alt me-1"></i> Sync from Catalog
                    </button>
                                </form>
                <a href="#" id="currAssignBtn" class="btn btn-outline-primary">
                    <i class="fas fa-check-square me-1"></i> Choose Subjects
                </a>
                <button type="button" class="btn btn-outline-secondary" id="currEditBtn">
                    <i class="fas fa-edit me-1"></i> Edit
                </button>
                <button type="button" class="btn btn-outline-danger" id="currDeleteBtn"
                    data-bs-toggle="modal" data-bs-target="#curriculumDeleteModal">
                    <i class="fas fa-trash me-1"></i> Delete
                </button>
                <button type="button" class="btn btn-light ms-auto" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
            </div>
        </div>

{{-- Edit floating modal --}}
<div class="modal fade" id="curriculumEditModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content ams-curr-modal">
            <div class="modal-header border-0 pb-0">
                <div>
                    <p class="ams-modal-eyebrow mb-1">Edit Curriculum</p>
                    <h4 class="modal-title mb-0" id="currEditTitle">Edit</h4>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="currEditForm" method="POST" action="">
                @csrf
                @method('PUT')
                <div class="modal-body pt-3">
                    <div id="currEditMsg" class="mb-2"></div>
                    <div class="mb-3">
                        <label for="currEditGrade" class="form-label fw-semibold">Grade Level</label>
                        <select name="grade_level" id="currEditGrade" class="form-control" required>
                            @foreach($gradeLevels as $grade)
                                <option value="{{ $grade }}">{{ $grade }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-0">
                        <label for="currEditDescription" class="form-label fw-semibold">Description</label>
                        <textarea name="description" id="currEditDescription" rows="4" class="form-control"
                            placeholder="Optional notes for this grade curriculum"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="currEditSaveBtn">
                        <i class="fas fa-save me-1"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>
                        </div>
                    </div>

{{-- Delete confirm --}}
<div class="modal custom-modal fade" id="curriculumDeleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body">
                <div class="form-header">
                    <h3>Delete Curriculum</h3>
                    <p class="mb-0">
                        Delete curriculum for <strong id="currDeleteName">this grade</strong>?
                        Subject catalog entries are kept.
                    </p>
                </div>
                <div class="modal-btn delete-action">
                    <form id="currDeleteForm" method="POST" action="">
                        @csrf
                        @method('DELETE')
                        <div class="row">
                            <div class="col-6">
                                <button type="submit" class="btn btn-primary paid-continue-btn w-100">Delete</button>
                            </div>
                            <div class="col-6">
                                <button type="button" class="btn btn-primary paid-cancel-btn w-100" data-bs-dismiss="modal">Cancel</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .ams-curriculum {
        --ams-blue: #1e3a8a;
        --ams-line: #e5e7eb;
        --ams-soft: #f8fafc;
        --ams-ink: #111827;
        --ams-muted: #6b7280;
    }
    .ams-toolbar { max-width: 420px; }
    .ams-search { position: relative; }
    .ams-search-icon {
        position: absolute; left: .9rem; top: 50%; transform: translateY(-50%);
        color: #94a3b8; pointer-events: none;
    }
    .ams-search-input {
        height: 44px; padding-left: 2.4rem; padding-right: 2.4rem;
        border-radius: 12px; border: 1px solid var(--ams-line); box-shadow: none;
    }
    .ams-search-input:focus {
        border-color: #93c5fd;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, .12);
    }
    .ams-search-clear {
        position: absolute; right: .55rem; top: 50%; transform: translateY(-50%);
        border: 0; background: #e2e8f0; color: #475569;
        width: 1.6rem; height: 1.6rem; border-radius: 999px;
        display: inline-flex; align-items: center; justify-content: center; font-size: .7rem;
    }
    .ams-curr-card {
        border: 1px solid var(--ams-line); background: #fff; border-radius: 14px;
        padding: 1rem 1.05rem; min-height: 168px; cursor: pointer;
        transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease;
    }
    .ams-curr-card:hover {
        transform: translateY(-3px); border-color: #93c5fd;
        box-shadow: 0 12px 24px rgba(30, 58, 138, .12);
    }
    .ams-curr-title { font-weight: 700; color: var(--ams-ink); font-size: 1.05rem; }
    .ams-curr-count {
        min-width: 1.75rem; height: 1.75rem; padding: 0 .4rem; border-radius: 8px;
        display: inline-flex; align-items: center; justify-content: center;
        background: #1e3a8a; color: #fff; font-size: .85rem; font-weight: 700;
    }
    .ams-curr-count.is-empty { background: #64748b; }
    .ams-curr-meta { font-size: .8rem; line-height: 1.35; }
    .ams-pill {
        display: inline-flex; align-items: center; font-size: .72rem; font-weight: 700;
        border-radius: 999px; padding: .2rem .55rem;
    }
    .ams-pill--ok { background: #d1fae5; color: #065f46; }
    .ams-pill--warn { background: #fef3c7; color: #92400e; }
    .ams-pill--info { background: #dbeafe; color: #1e40af; }
    .ams-curr-hint { font-size: .75rem; font-weight: 600; color: #2563eb; }
    .ams-curr-modal {
        border: 0; border-radius: 18px; overflow: hidden;
        box-shadow: 0 24px 48px rgba(15, 23, 42, .18);
    }
    .ams-modal-eyebrow {
        font-size: .75rem; font-weight: 700; letter-spacing: .04em;
        text-transform: uppercase; color: var(--ams-muted); margin: 0;
    }
    .ams-stat-badge {
        display: inline-flex; align-items: center;
        border-radius: 999px; padding: .3rem .7rem;
        font-size: .8rem; font-weight: 700;
    }
    .ams-stat-badge--linked { background: #1e3a8a; color: #fff; }
    .ams-stat-badge--catalog { background: #334155; color: #fff; }
    .ams-subject-chip {
        display: inline-flex; align-items: center; gap: .35rem;
        background: #eef2ff; color: #312e81; border-radius: 999px;
        padding: .35rem .75rem; font-size: .85rem; font-weight: 600;
        margin: 0 .35rem .5rem 0;
    }
    #curriculumModal.modal.fade .modal-dialog,
    #curriculumEditModal.modal.fade .modal-dialog {
        transform: translateY(18px) scale(.97); opacity: 0;
        transition: transform .25s cubic-bezier(.22,1,.36,1), opacity .25s ease;
    }
    #curriculumModal.modal.show .modal-dialog,
    #curriculumEditModal.modal.show .modal-dialog {
        transform: none; opacity: 1;
    }
</style>
@endpush

@push('scripts')
@php
    $subjectsByCurriculumJson = $curricula->mapWithKeys(function ($c) {
        return [
            (string) $c->id => $c->subjects->map(function ($s) {
                return [
                    'id' => $s->id,
                    'name' => $s->subject_name,
                ];
            })->values(),
        ];
    });
@endphp
<script>
(function () {
    const csrfToken = @json(csrf_token());
    const subjectsById = @json($subjectsByCurriculumJson);

    let active = null;
    const searchInput = document.getElementById('curriculumSearch');
    const searchClear = document.getElementById('curriculumSearchClear');
    const emptyEl = document.getElementById('curriculumEmpty');
    const detailModalEl = document.getElementById('curriculumModal');
    const editModalEl = document.getElementById('curriculumEditModal');
    const detailModal = detailModalEl ? bootstrap.Modal.getOrCreateInstance(detailModalEl) : null;
    const editModal = editModalEl ? bootstrap.Modal.getOrCreateInstance(editModalEl) : null;

    function applySearch() {
        const q = ((searchInput && searchInput.value) || '').trim().toLowerCase();
        if (searchClear) searchClear.classList.toggle('d-none', !q);
        let visible = 0;
        document.querySelectorAll('.curriculum-grid-item').forEach(function (item) {
            const hay = item.getAttribute('data-search') || '';
            const show = !q || hay.indexOf(q) !== -1;
            item.classList.toggle('d-none', !show);
            if (show) visible += 1;
        });
        if (emptyEl) emptyEl.classList.toggle('d-none', visible > 0);
    }

    if (searchInput) searchInput.addEventListener('input', applySearch);
    if (searchClear) {
        searchClear.addEventListener('click', function () {
            searchInput.value = '';
            searchInput.focus();
            applySearch();
        });
    }

    function escapeHtml(str) {
        return String(str || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function renderSubjects(subjects) {
        const box = document.getElementById('currModalSubjects');
        const empty = document.getElementById('currModalEmpty');
        if (!subjects || !subjects.length) {
            box.innerHTML = '';
            empty.classList.remove('d-none');
            return;
        }
        empty.classList.add('d-none');
        box.innerHTML = subjects.map(function (s) {
            return '<span class="ams-subject-chip"><i class="fas fa-book"></i> ' + escapeHtml(s.name || '') + '</span>';
        }).join('');
    }

    function fillDetailModal() {
        if (!active) return;
        document.getElementById('currModalTitle').textContent = active.grade;
        document.getElementById('currModalDesc').textContent = active.description || 'No description yet.';
        document.getElementById('currModalLinked').textContent = active.linked + ' linked';
        document.getElementById('currModalCatalog').textContent = active.catalog + ' in catalog';
        document.getElementById('currSyncForm').action = active.syncUrl;
        document.getElementById('currAssignBtn').href = active.assignUrl;
        renderSubjects(active.subjects || []);
    }

    if (detailModalEl) {
        detailModalEl.addEventListener('show.bs.modal', function (event) {
            const btn = event.relatedTarget;
            if (!btn || !btn.classList.contains('ams-curr-card')) return;

            const id = btn.getAttribute('data-id');
            active = {
                id: id,
                grade: btn.getAttribute('data-grade') || '',
                description: btn.getAttribute('data-description') || '',
                linked: btn.getAttribute('data-linked') || '0',
                catalog: btn.getAttribute('data-catalog') || '0',
                subjects: subjectsById[id] || subjectsById[String(id)] || [],
                assignUrl: btn.getAttribute('data-assign-url') || '#',
                syncUrl: btn.getAttribute('data-sync-url') || '#',
                updateUrl: btn.getAttribute('data-update-url') || '#',
                destroyUrl: btn.getAttribute('data-destroy-url') || '#',
                cardBtn: btn
            };
            fillDetailModal();
        });
    }

    document.getElementById('currEditBtn')?.addEventListener('click', function () {
        if (!active || !editModal) return;
        document.getElementById('currEditTitle').textContent = active.grade;
        document.getElementById('currEditForm').action = active.updateUrl;
        document.getElementById('currEditGrade').value = active.grade;
        document.getElementById('currEditDescription').value = active.description || '';
        document.getElementById('currEditMsg').innerHTML = '';
        detailModal?.hide();
        setTimeout(function () { editModal.show(); }, 200);
    });

    document.getElementById('currEditForm')?.addEventListener('submit', function (e) {
        e.preventDefault();
        if (!active) return;

        const $btn = document.getElementById('currEditSaveBtn');
        const $msg = document.getElementById('currEditMsg');
        const grade = document.getElementById('currEditGrade').value;
        const description = document.getElementById('currEditDescription').value;

        $btn.disabled = true;
        $btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Saving...';
        $msg.innerHTML = '';

        fetch(active.updateUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({
                _token: csrfToken,
                _method: 'PUT',
                grade_level: grade,
                description: description
            })
        })
        .then(function (r) {
            return r.json().then(function (data) {
                if (!r.ok) throw data;
                return data;
            });
        })
        .then(function (data) {
            const cur = data.curriculum || {};
            active.grade = cur.grade_level || grade;
            active.description = cur.description || description;
            active.linked = String(cur.linked != null ? cur.linked : active.linked);
            if (cur.subjects) {
                active.subjects = cur.subjects;
                subjectsById[String(active.id)] = cur.subjects;
            }

            // Update card on the grid
            if (active.cardBtn) {
                active.cardBtn.setAttribute('data-grade', active.grade);
                active.cardBtn.setAttribute('data-description', active.description || '');
                active.cardBtn.setAttribute('data-linked', active.linked);
                const title = active.cardBtn.querySelector('.ams-curr-title');
                if (title) title.textContent = active.grade;
                const item = active.cardBtn.closest('.curriculum-grid-item');
                if (item) {
                    const names = (active.subjects || []).map(function (s) { return s.name; }).join(' ');
                    item.setAttribute('data-search', (active.grade + ' ' + names + ' ' + (active.description || '')).toLowerCase());
                }
            }

            $msg.innerHTML = '<div class="alert alert-success py-2 mb-0">Saved.</div>';
            editModal.hide();
            fillDetailModal();
            setTimeout(function () { detailModal?.show(); }, 220);
        })
        .catch(function (err) {
            let msg = 'Failed to save.';
            if (err && err.message) msg = err.message;
            if (err && err.errors) {
                const first = Object.values(err.errors)[0];
                if (first && first[0]) msg = first[0];
            }
            $msg.innerHTML = '<div class="alert alert-danger py-2 mb-0">' + escapeHtml(msg) + '</div>';
        })
        .finally(function () {
            $btn.disabled = false;
            $btn.innerHTML = '<i class="fas fa-save me-1"></i> Save Changes';
        });
    });

    const deleteModal = document.getElementById('curriculumDeleteModal');
    if (deleteModal) {
        deleteModal.addEventListener('show.bs.modal', function () {
            if (!active) return;
            document.getElementById('currDeleteName').textContent = active.grade;
            document.getElementById('currDeleteForm').action = active.destroyUrl;
        });
    }
})();
</script>
@endpush
