@extends('layouts.master')
@section('content')

@php
    $canCreate = in_array(Auth::user()->role_name, ['Admin', 'Teacher'], true);
@endphp

<div class="page-wrapper">
    <div class="content container-fluid dir-page">
        <div class="page-header">
            <div class="row align-items-start">
                <div class="col">
                    <h3 class="page-title mb-1">Announcements</h3>
                    <p class="dir-subtitle">School notices, reminders, and pinned updates.</p>
                </div>
                <div class="col-auto text-end">
                    <ul class="breadcrumb justify-content-end {{ $canCreate ? 'mb-2' : 'mb-0' }}">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Announcements</li>
                    </ul>
                    @if($canCreate)
                        <a href="{{ route('announcements.create') }}" class="btn btn-primary dir-btn">
                            <i class="fas fa-plus me-1"></i> New Announcement
                        </a>
                    @endif
                </div>
            </div>
        </div>

        <div class="dir-card dir-filters">
            <form method="GET" action="{{ route('announcements.index') }}">
                <div class="row g-2 align-items-end">
                    <div class="col-lg-3 col-md-6">
                        <label class="form-label">Type</label>
                        <select class="form-control" name="type">
                            <option value="">All Types</option>
                            @foreach(['general','academic','event','reminder','emergency'] as $type)
                                <option value="{{ $type }}" {{ request('type') === $type ? 'selected' : '' }}>{{ ucfirst($type) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <label class="form-label">Priority</label>
                        <select class="form-control" name="priority">
                            <option value="">All Priorities</option>
                            @foreach(['low','normal','high','urgent'] as $priority)
                                <option value="{{ $priority }}" {{ request('priority') === $priority ? 'selected' : '' }}>{{ ucfirst($priority) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <label class="form-label">Status</label>
                        <select class="form-control" name="status">
                            <option value="">All Status</option>
                            <option value="pinned" {{ request('status') === 'pinned' ? 'selected' : '' }}>Pinned Only</option>
                            <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active Only</option>
                        </select>
                    </div>
                    <div class="col-lg-3 col-md-6 pb-3">
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary dir-btn flex-fill">
                                <i class="fas fa-filter me-1"></i> Filter
                            </button>
                            <a href="{{ route('announcements.index') }}" class="btn btn-outline-secondary dir-btn">Clear</a>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <div class="dir-card">
            <div class="dir-toolbar">
                <div>
                    <h5 class="dir-toolbar-title">All announcements</h5>
                    <span class="dir-count mt-1">{{ $announcements->total() }} notice{{ $announcements->total() === 1 ? '' : 's' }}</span>
                </div>
                @if($announcements->count() > 0)
                    <button type="button" class="btn btn-outline-secondary dir-btn" id="exportAnnouncements">
                        <i class="fas fa-download me-1"></i> Export
                    </button>
                @endif
            </div>

            @if($announcements->count() == 0)
                <div class="dir-empty">
                    <i class="fas fa-bullhorn d-block"></i>
                    <h5 class="mt-2 mb-1">No announcements found</h5>
                    <p class="mb-3">There are no announcements available for your role at this time.</p>
                    @if($canCreate)
                        <a href="{{ route('announcements.create') }}" class="btn btn-primary dir-btn">
                            <i class="fas fa-plus me-1"></i> Create first announcement
                        </a>
                    @endif
                </div>
            @else
                <div class="table-responsive">
                    <table class="table dir-table mb-0" id="announcementsTable">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Type</th>
                                <th>Priority</th>
                                <th>Audience</th>
                                <th>Created By</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($announcements as $announcement)
                                @php
                                    $canManage = Auth::user()->role_name === 'Admin'
                                        || (Auth::user()->role_name === 'Teacher' && (int) $announcement->created_by === (int) Auth::id());
                                    $priorityClass = match ($announcement->priority) {
                                        'urgent' => 'dir-badge--disabled',
                                        'high' => 'dir-badge--inactive',
                                        'normal' => 'dir-badge--active',
                                        default => 'dir-badge--neutral',
                                    };
                                    $statusClass = 'dir-badge--neutral';
                                    $statusLabel = 'Inactive';
                                    if ($announcement->is_scheduled && $announcement->scheduled_at && $announcement->scheduled_at->isFuture()) {
                                        $statusClass = 'dir-badge--inactive';
                                        $statusLabel = 'Scheduled';
                                    } elseif ($announcement->expires_at && $announcement->expires_at->isPast()) {
                                        $statusClass = 'dir-badge--neutral';
                                        $statusLabel = 'Expired';
                                    } elseif ($announcement->is_active) {
                                        $statusClass = 'dir-badge--active';
                                        $statusLabel = 'Active';
                                    } else {
                                        $statusClass = 'dir-badge--disabled';
                                        $statusLabel = 'Inactive';
                                    }
                                    $creatorPhoto = $announcement->creator->avatar ?? asset('assets/img/profiles/avatar-01.jpg');
                                @endphp
                                <tr class="{{ $announcement->is_pinned ? 'is-pinned' : '' }}">
                                    <td>
                                        <div class="dir-title">
                                            <span class="dir-title-icon" title="{{ ucfirst($announcement->type) }}">
                                                <i class="{{ $announcement->type_icon }}"></i>
                                            </span>
                                            <div>
                                                <a href="{{ route('announcements.show', $announcement->id) }}" class="dir-person-name">
                                                    @if($announcement->is_pinned)
                                                        <i class="fas fa-thumbtack text-warning me-1" title="Pinned"></i>
                                                    @endif
                                                    {{ $announcement->title }}
                                                </a>
                                                <span class="dir-person-meta">{{ Str::limit(strip_tags($announcement->content), 90) }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td><span class="dir-chip">{{ ucfirst($announcement->type) }}</span></td>
                                    <td><span class="dir-badge {{ $priorityClass }}">{{ ucfirst($announcement->priority) }}</span></td>
                                    <td><span class="dir-chip dir-chip--soft">{{ ucfirst($announcement->target_audience) }}</span></td>
                                    <td>
                                        <div class="dir-person">
                                            <img src="{{ $creatorPhoto }}" alt="">
                                            <span class="dir-person-name">{{ $announcement->creator->name ?? 'N/A' }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="dir-time">{{ $announcement->created_at->format('M d, Y') }}</div>
                                        <span class="dir-person-meta">{{ $announcement->created_at->format('h:i A') }}</span>
                                    </td>
                                    <td><span class="dir-badge {{ $statusClass }}">{{ $statusLabel }}</span></td>
                                    <td class="text-end">
                                        <div class="d-inline-flex gap-1">
                                            <a href="{{ route('announcements.show', $announcement->id) }}" class="dir-icon-btn" title="View">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            @if($canManage)
                                                <a href="{{ route('announcements.edit', $announcement->id) }}" class="dir-icon-btn" title="Edit">
                                                    <i class="fas fa-pen"></i>
                                                </a>
                                                <button type="button" class="dir-icon-btn is-danger" title="Delete" onclick="deleteAnnouncement({{ $announcement->id }})">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            @endif
                                            @if(Auth::user()->role_name === 'Admin')
                                                <button type="button" class="dir-icon-btn" title="{{ $announcement->is_pinned ? 'Unpin' : 'Pin' }}" onclick="togglePin({{ $announcement->id }})">
                                                    <i class="fas fa-thumbtack"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if($announcements->hasPages())
                    <div class="px-3 py-3">
                        {{ $announcements->links() }}
                    </div>
                @endif
            @endif
        </div>
    </div>
</div>

<div class="modal fade dir-modal" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title">Delete announcement?</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-2">
                <p class="text-muted mb-0">This will permanently remove the announcement. This action cannot be undone.</p>
            </div>
            <div class="modal-footer border-0">
                <form id="deleteForm" method="POST" class="d-flex gap-2 w-100 justify-content-end">
                    @csrf
                    @method('DELETE')
                    <button type="button" class="btn btn-light dir-btn" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger dir-btn">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/directory-modern.css') }}?v=20260914c">
@endpush

@push('scripts')
<script>
    function deleteAnnouncement(id) {
        const form = document.getElementById('deleteForm');
        if (!form) return;
        form.action = '/announcements/' + id;
        const modalEl = document.getElementById('deleteModal');
        if (window.bootstrap && modalEl) {
            bootstrap.Modal.getOrCreateInstance(modalEl).show();
        } else if (confirm('Delete this announcement?')) {
            form.submit();
        }
    }

    function togglePin(id) {
        fetch('/announcements/' + id + '/toggle-pin', {
            method: 'PATCH',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
        })
        .then(function (response) {
            if (!response.ok) throw new Error('Pin failed');
            return response.json().catch(function () { return { success: true }; });
        })
        .then(function () { location.reload(); })
        .catch(function () {
            alert('Could not update pin status.');
        });
    }

    document.getElementById('exportAnnouncements')?.addEventListener('click', function () {
        const table = document.getElementById('announcementsTable');
        if (!table) return;
        const rows = [];
        table.querySelectorAll('tr').forEach(function (tr) {
            const cells = [...tr.children].slice(0, 7).map(function (cell) {
                return '"' + cell.innerText.replace(/\s+/g, ' ').trim().replace(/"/g, '""') + '"';
            });
            if (cells.length) rows.push(cells.join(','));
        });
        const blob = new Blob([rows.join('\n')], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement('a');
        link.href = URL.createObjectURL(blob);
        link.download = 'announcements.csv';
        link.click();
        URL.revokeObjectURL(link.href);
    });
</script>
@endpush
