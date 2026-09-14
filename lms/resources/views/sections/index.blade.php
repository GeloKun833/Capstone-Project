@extends('layouts.master')
@section('content')
<div class="page-wrapper">
    <div class="content container-fluid ams-sections">
        <div class="page-header">
            <div class="row align-items-start">
                <div class="col">
                    <div>
                        <h3 class="page-title mb-1">Block Sections</h3>
                        <p class="text-muted mb-0">Click a section card to view details, edit, or delete.</p>
                    </div>
                </div>
                <div class="col-auto text-end">
                    <ul class="breadcrumb justify-content-end mb-2">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('class-subject.unified-management') }}">Classes &amp; Subjects</a></li>
                        <li class="breadcrumb-item active">Sections</li>
                    </ul>
                    <a href="{{ route('sections.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add Section
                    </a>
                    <a href="{{ route('class-subject.unified-management') }}" class="btn btn-outline-secondary">
                        Back
                    </a>
                </div>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        {{-- Search + grade filter --}}
        <div class="ams-toolbar mb-3">
            <div class="ams-search">
                <i class="fas fa-search ams-search-icon"></i>
                <input type="search"
                    id="sectionSearch"
                    class="form-control ams-search-input"
                    placeholder="Search section name, grade, or adviser..."
                    autocomplete="off">
                <button type="button" id="sectionSearchClear" class="ams-search-clear d-none" title="Clear search" aria-label="Clear search">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
        <div class="ams-filter-bar mb-4">
            <button type="button" class="ams-filter-chip is-active" data-filter="all">All</button>
            @foreach($gradeLevels as $grade)
                @php $count = ($sectionsByGrade->get($grade) ?? collect())->count(); @endphp
                <button type="button" class="ams-filter-chip" data-filter="{{ $grade }}">
                    {{ $grade }}
                    <span class="ams-filter-count">{{ $count }}</span>
                </button>
            @endforeach
        </div>

        @if($sections->isEmpty())
            <div class="alert alert-warning mb-0">
                No sections yet. Add one to show Block Sections on enrollment.
            </div>
        @else
            <div class="row g-3" id="sectionsGrid">
                @foreach($sections as $section)
                    @php
                        $adviserName = $section->adviser ? $section->adviser->full_name : 'To be assigned';
                        $description = $section->description ?: '';
                    @endphp
                    <div class="col-6 col-md-4 col-xl-3 section-grid-item"
                        data-grade="{{ $section->grade_level }}"
                        data-search="{{ strtolower($section->name.' '.$section->grade_level.' '.$adviserName.' '.($section->description ?: '')) }}">
                        <button type="button"
                            class="ams-section-card w-100 text-start"
                            data-bs-toggle="modal"
                            data-bs-target="#sectionDetailModal"
                            data-id="{{ $section->id }}"
                            data-name="{{ $section->name }}"
                            data-grade="{{ $section->grade_level }}"
                            data-adviser="{{ $adviserName }}"
                            data-adviser-id="{{ $section->adviser_id }}"
                            data-teachers='@json($section->teachers->map(fn ($t) => ["id" => $t->id, "name" => ($t->full_name ?: "Teacher")])->values())'
                            data-capacity="{{ $section->capacity ?? 25 }}"
                            data-description="{{ $description }}"
                            data-edit-url="{{ route('sections.edit', $section->id) }}">
                            <div class="d-flex justify-content-between align-items-start gap-2">
                                <span class="ams-section-card-title">{{ $section->name }}</span>
                                <span class="ams-section-cap">{{ $section->capacity ?? 25 }}</span>
                            </div>
                            <div class="ams-section-grade mt-2">{{ $section->grade_level }}</div>
                            <div class="ams-section-meta mt-2">
                                <i class="fas fa-user-tie me-1"></i>
                                {{ $adviserName }}
                            </div>
                            <div class="ams-section-hint mt-3">
                                Click for details
                                <i class="fas fa-arrow-right ms-1"></i>
                            </div>
                        </button>
                    </div>
                @endforeach
            </div>
            <div id="sectionsEmptyFilter" class="alert alert-light border text-center d-none mt-3">
                No sections match your search or grade filter.
            </div>
        @endif
    </div>
</div>

{{-- Detail / actions modal --}}
<div class="modal fade" id="sectionDetailModal" tabindex="-1" aria-labelledby="sectionDetailTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content ams-section-modal">
            <div class="modal-header border-0 pb-0">
                <div>
                    <p class="ams-modal-eyebrow mb-1">Block Section</p>
                    <h4 class="modal-title mb-0" id="sectionDetailTitle">Section</h4>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-3">
                <div class="ams-detail-grid">
                    <div class="ams-detail-item">
                        <span class="ams-detail-label">Grade Level</span>
                        <span class="ams-detail-value" id="sectionDetailGrade">—</span>
                    </div>
                    <div class="ams-detail-item">
                        <span class="ams-detail-label">Capacity</span>
                        <span class="ams-detail-value" id="sectionDetailCapacity">—</span>
                    </div>
                    <div class="ams-detail-item ams-detail-item--full">
                        <span class="ams-detail-label">Adviser</span>
                        <span class="ams-detail-value" id="sectionDetailAdviser">—</span>
                    </div>
                    <div class="ams-detail-item ams-detail-item--full">
                        <span class="ams-detail-label">Assigned teachers</span>
                        <div class="ams-detail-value" id="sectionDetailTeachers">None yet</div>
                    </div>
                    <div class="ams-detail-item ams-detail-item--full">
                        <span class="ams-detail-label">Description</span>
                        <span class="ams-detail-value" id="sectionDetailDescription">—</span>
                    </div>
                </div>

                <hr class="my-3">
                <h6 class="ams-detail-label mb-2">Assign Teacher to this Section</h6>
                <p class="text-muted small mb-2">Links the teacher to this block section. Check adviser to make them the homeroom adviser.</p>
                <form method="POST" id="sectionAssignTeacherForm" class="row g-2 align-items-end">
                    @csrf
                    <div class="col-12">
                        <label class="form-label small mb-1" for="sectionAssignTeacherId">Teacher</label>
                        <select class="form-control" name="teacher_id" id="sectionAssignTeacherId" required>
                            <option value="">Select teacher</option>
                            @forelse($teachers as $teacher)
                                <option value="{{ $teacher->id }}">{{ $teacher->full_name ?: ($teacher->user->name ?? 'Teacher') }}</option>
                            @empty
                                <option value="" disabled>No teachers available</option>
                            @endforelse
                        </select>
                    </div>
                    <div class="col-12">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="set_as_adviser" id="sectionSetAdviser" value="1" checked>
                            <label class="form-check-label" for="sectionSetAdviser">Set as section adviser</label>
                        </div>
                    </div>
                    <div class="col-12 d-flex gap-2">
                        <button type="submit" class="btn btn-primary btn-sm" @disabled($teachers->isEmpty())>
                            <i class="fas fa-user-plus me-1"></i> Assign to Section
                        </button>
                    </div>
                </form>
                <form method="POST" id="sectionUnassignTeacherForm" class="mt-2 d-none">
                    @csrf
                    <input type="hidden" name="teacher_id" id="sectionUnassignTeacherId" value="">
                    <button type="submit" class="btn btn-outline-danger btn-sm" id="sectionUnassignTeacherBtn"
                        onclick="return confirm('Unassign this teacher from the section?');">
                        <i class="fas fa-user-minus me-1"></i> Unassign selected teacher
                    </button>
                </form>
            </div>
            <div class="modal-footer border-0 pt-0 flex-wrap gap-2">
                <a href="#" id="sectionDetailEditBtn" class="btn btn-warning">
                    <i class="fas fa-edit me-1"></i> Edit
                </a>
                <button type="button"
                    class="btn btn-danger"
                    id="sectionDetailDeleteBtn"
                    data-bs-toggle="modal"
                    data-bs-target="#deleteSectionModal">
                    <i class="fas fa-trash me-1"></i> Delete
                </button>
                <button type="button" class="btn btn-outline-secondary ms-auto" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

{{-- Delete Section Modal --}}
<div class="modal custom-modal fade" id="deleteSectionModal" tabindex="-1" role="dialog" aria-labelledby="deleteSectionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body">
                <div class="form-header">
                    <h3 id="deleteSectionModalLabel">Delete Section</h3>
                    <p class="mb-0">
                        Are you sure you want to delete
                        <strong id="deleteSectionName">this section</strong>?
                    </p>
                    <p class="text-muted small mt-2 mb-0" id="deleteSectionGrade"></p>
                </div>
                <div class="modal-btn delete-action">
                    <form id="deleteSectionForm" method="POST" action="">
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

<div id="sectionsPageConfig" class="d-none" data-destroy-base="{{ url('sections') }}" aria-hidden="true"></div>
@endsection

@push('styles')
<style>
    .ams-sections {
        --ams-blue: #1e3a8a;
        --ams-line: #e5e7eb;
        --ams-soft: #f8fafc;
        --ams-ink: #111827;
        --ams-muted: #6b7280;
    }
    .ams-toolbar {
        max-width: 420px;
    }
    .ams-search {
        position: relative;
    }
    .ams-search-icon {
        position: absolute;
        left: 0.9rem;
        top: 50%;
        transform: translateY(-50%);
        color: #94a3b8;
        pointer-events: none;
    }
    .ams-search-input {
        height: 44px;
        padding-left: 2.4rem;
        padding-right: 2.4rem;
        border-radius: 12px;
        border: 1px solid var(--ams-line);
        box-shadow: none;
    }
    .ams-search-input:focus {
        border-color: #93c5fd;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
    }
    .ams-search-clear {
        position: absolute;
        right: 0.55rem;
        top: 50%;
        transform: translateY(-50%);
        border: 0;
        background: #e2e8f0;
        color: #475569;
        width: 1.6rem;
        height: 1.6rem;
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 0.7rem;
        line-height: 1;
    }
    .ams-search-clear:hover {
        background: #cbd5e1;
    }
    .ams-filter-bar {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
    }
    .ams-filter-chip {
        border: 1px solid var(--ams-line);
        background: #fff;
        color: var(--ams-ink);
        border-radius: 999px;
        padding: 0.4rem 0.85rem;
        font-size: 0.85rem;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        transition: background .15s ease, border-color .15s ease, color .15s ease;
    }
    .ams-filter-chip:hover {
        border-color: #93c5fd;
        background: #eff6ff;
    }
    .ams-filter-chip.is-active {
        background: var(--ams-blue);
        border-color: var(--ams-blue);
        color: #fff;
    }
    .ams-filter-count {
        display: inline-flex;
        min-width: 1.25rem;
        height: 1.25rem;
        padding: 0 0.35rem;
        border-radius: 999px;
        align-items: center;
        justify-content: center;
        font-size: 0.75rem;
        background: rgba(15, 23, 42, 0.08);
    }
    .ams-filter-chip.is-active .ams-filter-count {
        background: rgba(255, 255, 255, 0.22);
    }
    .ams-section-card {
        border: 1px solid var(--ams-line);
        background: #fff;
        border-radius: 14px;
        padding: 1rem 1.05rem;
        min-height: 148px;
        transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease;
        cursor: pointer;
    }
    .ams-section-card:hover {
        transform: translateY(-3px);
        border-color: #93c5fd;
        box-shadow: 0 12px 24px rgba(30, 58, 138, 0.12);
    }
    .ams-section-card-title {
        font-weight: 700;
        color: var(--ams-ink);
        font-size: 1.05rem;
        line-height: 1.3;
    }
    .ams-section-cap {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 1.75rem;
        height: 1.75rem;
        padding: 0 0.4rem;
        border-radius: 8px;
        background: #065f46;
        color: #fff;
        font-size: 0.8rem;
        font-weight: 700;
        flex-shrink: 0;
    }
    .ams-section-grade {
        display: inline-block;
        font-size: 0.78rem;
        font-weight: 700;
        color: #1e40af;
        background: #eff6ff;
        border-radius: 999px;
        padding: 0.2rem 0.65rem;
    }
    .ams-section-meta {
        font-size: 0.82rem;
        color: var(--ams-muted);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .ams-section-hint {
        font-size: 0.75rem;
        font-weight: 600;
        color: #2563eb;
    }
    .ams-section-modal {
        border: 0;
        border-radius: 18px;
        overflow: hidden;
        box-shadow: 0 24px 48px rgba(15, 23, 42, 0.18);
    }
    .ams-modal-eyebrow {
        font-size: 0.75rem;
        font-weight: 700;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        color: var(--ams-muted);
        margin: 0;
    }
    .ams-detail-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 0.85rem;
    }
    .ams-detail-item {
        background: var(--ams-soft);
        border: 1px solid var(--ams-line);
        border-radius: 12px;
        padding: 0.75rem 0.9rem;
    }
    .ams-detail-item--full { grid-column: 1 / -1; }
    .ams-detail-label {
        display: block;
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        color: var(--ams-muted);
        margin-bottom: 0.25rem;
    }
    .ams-detail-value {
        font-size: 0.95rem;
        font-weight: 600;
        color: var(--ams-ink);
        word-break: break-word;
    }
    #sectionDetailModal.modal.fade .modal-dialog {
        transform: translateY(18px) scale(.97);
        opacity: 0;
        transition: transform .25s cubic-bezier(.22,1,.36,1), opacity .25s ease;
    }
    #sectionDetailModal.modal.show .modal-dialog {
        transform: translateY(0) scale(1);
        opacity: 1;
    }
</style>
@endpush

@push('scripts')
<script>
(function () {
    const destroyBase = document.getElementById('sectionsPageConfig')?.dataset.destroyBase || '';
    let activeSection = null;
    let activeGrade = 'all';

    const searchInput = document.getElementById('sectionSearch');
    const searchClear = document.getElementById('sectionSearchClear');
    const emptyMsg = document.getElementById('sectionsEmptyFilter');

    function applyFilters() {
        const q = ((searchInput && searchInput.value) || '').trim().toLowerCase();
        if (searchClear) {
            searchClear.classList.toggle('d-none', !q);
        }

        let visible = 0;
        document.querySelectorAll('.section-grid-item').forEach(function (item) {
            const grade = item.getAttribute('data-grade') || '';
            const hay = item.getAttribute('data-search') || '';
            const gradeOk = activeGrade === 'all' || grade === activeGrade;
            const searchOk = !q || hay.indexOf(q) !== -1;
            const show = gradeOk && searchOk;
            item.classList.toggle('d-none', !show);
            if (show) visible += 1;
        });

        if (emptyMsg) {
            const hasItems = document.querySelectorAll('.section-grid-item').length > 0;
            emptyMsg.classList.toggle('d-none', !(hasItems && visible === 0));
        }
    }

    // Grade filter
    document.querySelectorAll('.ams-filter-chip').forEach(function (chip) {
        chip.addEventListener('click', function () {
            document.querySelectorAll('.ams-filter-chip').forEach(function (c) {
                c.classList.remove('is-active');
            });
            chip.classList.add('is-active');
            activeGrade = chip.getAttribute('data-filter') || 'all';
            applyFilters();
        });
    });

    if (searchInput) {
        searchInput.addEventListener('input', applyFilters);
    }
    if (searchClear) {
        searchClear.addEventListener('click', function () {
            searchInput.value = '';
            searchInput.focus();
            applyFilters();
        });
    }

    // Detail modal populate
    const detailModal = document.getElementById('sectionDetailModal');
    if (detailModal) {
        detailModal.addEventListener('show.bs.modal', function (event) {
            const btn = event.relatedTarget;
            if (!btn || !btn.classList.contains('ams-section-card')) return;

            activeSection = {
                id: btn.getAttribute('data-id'),
                name: btn.getAttribute('data-name') || 'Section',
                grade: btn.getAttribute('data-grade') || '',
                adviser: btn.getAttribute('data-adviser') || 'To be assigned',
                adviserId: btn.getAttribute('data-adviser-id') || '',
                teachers: [],
                capacity: btn.getAttribute('data-capacity') || '25',
                description: btn.getAttribute('data-description') || '',
                editUrl: btn.getAttribute('data-edit-url') || '#'
            };
            try {
                activeSection.teachers = JSON.parse(btn.getAttribute('data-teachers') || '[]');
            } catch (e) {
                activeSection.teachers = [];
            }

            document.getElementById('sectionDetailTitle').textContent = activeSection.name;
            document.getElementById('sectionDetailGrade').textContent = activeSection.grade || '—';
            document.getElementById('sectionDetailCapacity').textContent = activeSection.capacity + ' seats';
            document.getElementById('sectionDetailAdviser').textContent = activeSection.adviser;
            document.getElementById('sectionDetailDescription').textContent = activeSection.description || 'No description';
            document.getElementById('sectionDetailEditBtn').href = activeSection.editUrl;

            const teachersEl = document.getElementById('sectionDetailTeachers');
            if (teachersEl) {
                if (!activeSection.teachers.length) {
                    teachersEl.textContent = 'None yet';
                } else {
                    teachersEl.innerHTML = activeSection.teachers.map(function (t) {
                        const isAdv = String(t.id) === String(activeSection.adviserId);
                        return '<span class="d-block">' + (t.name || 'Teacher') + (isAdv ? ' <small class="text-muted">(adviser)</small>' : '') + '</span>';
                    }).join('');
                }
            }

            const assignForm = document.getElementById('sectionAssignTeacherForm');
            if (assignForm) {
                assignForm.action = destroyBase + '/' + encodeURIComponent(activeSection.id) + '/assign-teacher';
            }
            const unassignForm = document.getElementById('sectionUnassignTeacherForm');
            const unassignId = document.getElementById('sectionUnassignTeacherId');
            if (unassignForm && unassignId) {
                unassignForm.action = destroyBase + '/' + encodeURIComponent(activeSection.id) + '/unassign-teacher';
                if (activeSection.teachers.length) {
                    unassignForm.classList.remove('d-none');
                    unassignId.value = String(activeSection.teachers[0].id);
                    const firstAssigned = activeSection.teachers[0].id;
                    const sel = document.getElementById('sectionAssignTeacherId');
                    if (sel) sel.value = firstAssigned;
                    unassignId.value = sel && sel.value ? sel.value : String(firstAssigned);
                } else {
                    unassignForm.classList.add('d-none');
                    unassignId.value = '';
                }
            }
        });
    }

    document.getElementById('sectionAssignTeacherId')?.addEventListener('change', function () {
        const unassignId = document.getElementById('sectionUnassignTeacherId');
        const unassignForm = document.getElementById('sectionUnassignTeacherForm');
        if (!unassignId || !unassignForm || !activeSection) return;
        unassignId.value = this.value || '';
        const assignedIds = (activeSection.teachers || []).map(function (t) { return String(t.id); });
        unassignForm.classList.toggle('d-none', !assignedIds.includes(String(this.value)));
    });

    // When opening delete from detail modal, pass current section data
    const deleteModal = document.getElementById('deleteSectionModal');
    const deleteForm = document.getElementById('deleteSectionForm');
    const deleteName = document.getElementById('deleteSectionName');
    const deleteGrade = document.getElementById('deleteSectionGrade');
    const deleteTrigger = document.getElementById('sectionDetailDeleteBtn');

    if (deleteTrigger && deleteModal) {
        deleteTrigger.addEventListener('click', function () {
            if (!activeSection) return;
            deleteName.textContent = activeSection.name;
            deleteGrade.textContent = activeSection.grade ? ('Grade level: ' + activeSection.grade) : '';
            deleteForm.action = destroyBase + '/' + encodeURIComponent(activeSection.id);
        });
    }

    if (deleteModal) {
        deleteModal.addEventListener('show.bs.modal', function (event) {
            const btn = event.relatedTarget;
            if (btn && btn.getAttribute('data-id')) {
                const id = btn.getAttribute('data-id');
                const name = btn.getAttribute('data-name') || 'this section';
                const grade = btn.getAttribute('data-grade') || '';
                deleteName.textContent = name;
                deleteGrade.textContent = grade ? ('Grade level: ' + grade) : '';
                deleteForm.action = destroyBase + '/' + encodeURIComponent(id);
            } else if (activeSection) {
                deleteName.textContent = activeSection.name;
                deleteGrade.textContent = activeSection.grade ? ('Grade level: ' + activeSection.grade) : '';
                deleteForm.action = destroyBase + '/' + encodeURIComponent(activeSection.id);
            }
        });
    }
})();
</script>
@endpush
