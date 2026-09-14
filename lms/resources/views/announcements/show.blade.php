@extends('layouts.master')
@section('content')

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
@endphp

<div class="page-wrapper">
    <div class="content container-fluid dir-page">
        <div class="page-header">
            <div class="row align-items-start">
                <div class="col">
                    <h3 class="page-title mb-1">Announcement Details</h3>
                    <p class="dir-subtitle">Full notice, attachments, and targeting.</p>
                </div>
                <div class="col-auto text-end">
                    <ul class="breadcrumb justify-content-end mb-2">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('announcements.index') }}">Announcements</a></li>
                        <li class="breadcrumb-item active">View Announcement</li>
                    </ul>
                    @if($canManage)
                        <a href="{{ route('announcements.edit', $announcement->id) }}" class="btn btn-primary dir-btn me-1">
                            <i class="fas fa-pen me-1"></i> Edit
                        </a>
                    @endif
                    <a href="{{ route('announcements.index') }}" class="btn btn-outline-secondary dir-btn">
                        <i class="fas fa-arrow-left me-1"></i> Back
                    </a>
                </div>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-lg-8">
                <div class="dir-card">
                    <div class="dir-toolbar">
                        <div class="d-flex align-items-start gap-3">
                            <span class="dir-title-icon"><i class="{{ $announcement->type_icon }}"></i></span>
                            <div>
                                <h5 class="dir-toolbar-title mb-1">
                                    @if($announcement->is_pinned)
                                        <i class="fas fa-thumbtack text-warning me-1"></i>
                                    @endif
                                    {{ $announcement->title }}
                                </h5>
                                <div class="d-flex flex-wrap gap-1 mt-1">
                                    <span class="dir-chip">{{ ucfirst($announcement->type) }}</span>
                                    <span class="dir-badge {{ $priorityClass }}">{{ ucfirst($announcement->priority) }} priority</span>
                                    <span class="dir-chip dir-chip--soft">{{ ucfirst($announcement->target_audience) }}</span>
                                    <span class="dir-badge {{ $statusClass }}">{{ $statusLabel }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="p-4">
                        <div class="mb-4" style="white-space: pre-wrap; line-height: 1.65;">{{ $announcement->content }}</div>

                        @php $files = $announcement->attachments ?? []; @endphp
                        @if(!empty($files))
                            <h6 class="dir-toolbar-title mb-3"><i class="fas fa-paperclip me-1"></i> Attachments</h6>
                            @foreach($files as $file)
                                @php
                                    $path = $file['path'] ?? '';
                                    $name = $file['original_name'] ?? basename($path);
                                    $ext = strtolower($file['ext'] ?? pathinfo($name, PATHINFO_EXTENSION));
                                    $url = $path ? asset('storage/' . $path) : '#';
                                    $isImage = in_array($ext, ['jpg','jpeg','png','gif','webp'], true);
                                @endphp
                                <div class="dir-file-row flex-column align-items-stretch">
                                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                                        <div>
                                            <i class="fas fa-file me-2 text-muted"></i>
                                            <strong>{{ $name }}</strong>
                                            @if(!empty($file['size']))
                                                <small class="text-muted">({{ number_format(($file['size'] ?? 0) / 1024, 1) }} KB)</small>
                                            @endif
                                        </div>
                                        <a href="{{ $url }}" class="btn btn-sm btn-outline-secondary dir-btn" target="_blank" rel="noopener">
                                            {{ $isImage ? 'Open image' : 'Download / Open' }}
                                        </a>
                                    </div>
                                    @if($isImage && $path)
                                        <div class="mt-2">
                                            <img src="{{ $url }}" alt="{{ $name }}" class="img-fluid rounded" style="max-height:220px;">
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="dir-card mb-3">
                    <div class="dir-toolbar">
                        <h5 class="dir-toolbar-title mb-0">Details</h5>
                    </div>
                    <div class="p-4">
                        <div class="mb-3">
                            <div class="dir-person-meta">Created by</div>
                            <div>{{ $announcement->creator->name ?? 'N/A' }}</div>
                        </div>
                        <div class="mb-3">
                            <div class="dir-person-meta">Created on</div>
                            <div>{{ $announcement->created_at->format('M d, Y') }} · {{ $announcement->created_at->format('h:i A') }}</div>
                        </div>
                        @if($announcement->scheduled_at)
                            <div class="mb-3">
                                <div class="dir-person-meta">Scheduled for</div>
                                <div>{{ $announcement->scheduled_at->format('M d, Y') }} · {{ $announcement->scheduled_at->format('h:i A') }}</div>
                            </div>
                        @endif
                        @if($announcement->expires_at)
                            <div class="mb-3">
                                <div class="dir-person-meta">Expires on</div>
                                <div>{{ $announcement->expires_at->format('M d, Y') }} · {{ $announcement->expires_at->format('h:i A') }}</div>
                            </div>
                        @endif
                        <div>
                            <div class="dir-person-meta">Last updated</div>
                            <div>{{ $announcement->updated_at->format('M d, Y') }} · {{ $announcement->updated_at->format('h:i A') }}</div>
                        </div>
                    </div>
                </div>

                <div class="dir-card mb-3">
                    <div class="dir-toolbar">
                        <h5 class="dir-toolbar-title mb-0">Targeting</h5>
                    </div>
                    <div class="p-4">
                        <div class="mb-3">
                            <div class="dir-person-meta">Audience</div>
                            <span class="dir-chip dir-chip--soft">{{ ucfirst($announcement->target_audience) }}</span>
                        </div>
                        @if($announcement->target_roles)
                            <div class="mb-3">
                                <div class="dir-person-meta">Specific roles</div>
                                <div>{{ implode(', ', array_map('ucfirst', $announcement->target_roles)) }}</div>
                            </div>
                        @endif
                        @if($announcement->target_sections)
                            <div>
                                <div class="dir-person-meta">Specific sections</div>
                                <div>{{ implode(', ', $announcement->target_sections) }}</div>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="dir-card">
                    <div class="dir-toolbar">
                        <h5 class="dir-toolbar-title mb-0">Actions</h5>
                    </div>
                    <div class="p-3 d-grid gap-2">
                        @if($canManage)
                            <a href="{{ route('announcements.edit', $announcement->id) }}" class="btn btn-primary dir-btn">
                                <i class="fas fa-pen me-1"></i> Edit
                            </a>
                            <button type="button" class="btn btn-outline-danger dir-btn" onclick="deleteAnnouncement({{ $announcement->id }})">
                                <i class="fas fa-trash me-1"></i> Delete
                            </button>
                        @endif
                        @if(Auth::user()->role_name === 'Admin')
                            <button type="button" class="btn btn-outline-secondary dir-btn" onclick="togglePin({{ $announcement->id }})">
                                <i class="fas fa-thumbtack me-1"></i>
                                {{ $announcement->is_pinned ? 'Unpin' : 'Pin' }}
                            </button>
                        @endif
                        <a href="{{ route('announcements.index') }}" class="btn btn-outline-secondary dir-btn">
                            <i class="fas fa-arrow-left me-1"></i> Back to list
                        </a>
                    </div>
                </div>
            </div>
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
                <form id="deleteForm" method="POST" action="{{ route('announcements.destroy', $announcement->id) }}" class="d-flex gap-2 w-100 justify-content-end">
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
    function deleteAnnouncement() {
        const modalEl = document.getElementById('deleteModal');
        if (window.bootstrap && modalEl) {
            bootstrap.Modal.getOrCreateInstance(modalEl).show();
        } else if (confirm('Delete this announcement?')) {
            document.getElementById('deleteForm').submit();
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
</script>
@endpush
