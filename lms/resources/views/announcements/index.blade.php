@extends('layouts.master')
@section('content')
@php $me = Auth::user(); @endphp
<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="gc-wrap">
            <div class="page-header">
                <h3 class="page-title mb-1">Announcements</h3>
                <ul class="breadcrumb mb-3">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Announcements</li>
                </ul>
            </div>

            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            @if(in_array($me->role_name, ['Admin', 'Teacher'], true))
                <a class="gc-prompt" href="{{ route('announcements.create') }}">
                    <img class="gc-avatar" src="{{ $me->photoUrl() }}" alt="">
                    <span>Announce something to your class</span>
                </a>
            @endif

            <div id="announcementStream">
                @forelse($announcements as $announcement)
                    @php
                        $canManage = $me->role_name === 'Admin'
                            || ($me->role_name === 'Teacher' && (int) $announcement->created_by === (int) $me->id);
                        $posted = $announcement->created_at->copy()->timezone(config('app.school_timezone', 'Asia/Manila'));
                        $creator = $announcement->creator;
                        $photo = $creator ? $creator->photoUrl() : asset('assets/img/profiles/avatar-01.jpg');
                        $files = $announcement->attachments ?? [];
                    @endphp
                    <article class="gc-card gc-post" data-expires-at="{{ $announcement->expires_at ? $announcement->expires_at->timestamp * 1000 : '' }}">
                        <div class="gc-card-body">
                            <div class="gc-head">
                                <img class="gc-avatar" src="{{ $photo }}" alt="">
                                <div class="gc-head-text">
                                    <div class="gc-name">{{ $creator->name ?? 'School' }}</div>
                                    <div class="gc-meta">
                                        {{ $posted->format('M j, Y g:i A') }}
                                        · {{ $announcement->audienceText() }}
                                        @if($announcement->is_pinned)
                                            · Pinned
                                        @endif
                                        @if($announcement->priority && $announcement->priority !== 'normal')
                                            · {{ ucfirst($announcement->priority) }}
                                        @endif
                                    </div>
                                </div>
                                <div class="dropdown">
                                    <button type="button" class="gc-more" data-bs-toggle="dropdown" aria-label="Announcement actions">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li><a class="dropdown-item" href="{{ route('announcements.show', $announcement->id) }}">View</a></li>
                                        @if($canManage)
                                            <li><a class="dropdown-item" href="{{ route('announcements.edit', $announcement->id) }}">Edit</a></li>
                                            <li><button type="button" class="dropdown-item" onclick="deleteAnnouncement({{ $announcement->id }})">Delete</button></li>
                                        @endif
                                        @if($me->role_name === 'Admin')
                                            <li><button type="button" class="dropdown-item" onclick="togglePin({{ $announcement->id }})">{{ $announcement->is_pinned ? 'Unpin' : 'Pin' }}</button></li>
                                        @endif
                                    </ul>
                                </div>
                            </div>
                            <h2 class="gc-title"><a href="{{ route('announcements.show', $announcement->id) }}">{{ $announcement->title }}</a></h2>
                            <p class="gc-body">{{ $announcement->content }}</p>
                            @if(!empty($files))
                                <div class="gc-files">
                                    @foreach($files as $file)
                                        @php
                                            $path = $file['path'] ?? '';
                                            $name = $file['original_name'] ?? basename($path ?: 'file');
                                        @endphp
                                        <a class="gc-file" target="_blank" rel="noopener" href="{{ $path ? asset('storage/'.$path) : '#' }}">
                                            <i class="fas fa-paperclip"></i> {{ $name }}
                                        </a>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </article>
                @empty
                    <div class="gc-empty" id="announcementsEmpty">
                        <i class="fas fa-bullhorn d-block mb-2"></i>
                        No announcements yet.
                    </div>
                @endforelse
            </div>
            <div class="gc-empty d-none" id="announcementsExpiredEmpty">Announcements on this page have expired.</div>
            @if($announcements->hasPages())
                <div class="mb-4">{{ $announcements->links() }}</div>
            @endif
        </div>
    </div>
</div>

<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title">Delete announcement?</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-2">
                <p class="text-muted mb-0">This will permanently remove the announcement.</p>
            </div>
            <div class="modal-footer border-0">
                <form id="deleteForm" method="POST" class="d-flex gap-2 w-100 justify-content-end">
                    @csrf
                    @method('DELETE')
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
@include('announcements.partials.classroom-styles')
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
        }
    }

    function togglePin(id) {
        fetch('/announcements/' + id + '/toggle-pin', {
            method: 'PATCH',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json',
            },
        }).then(function (response) {
            if (!response.ok) throw new Error('Pin failed');
            location.reload();
        }).catch(function () {
            alert('Could not update pin status.');
        });
    }

    (function expireVisibleAnnouncements() {
        document.querySelectorAll('.gc-post[data-expires-at]').forEach(function (post) {
            const expiresAt = Number(post.dataset.expiresAt);
            if (!expiresAt) return;
            const delay = expiresAt - Date.now();
            const remove = function () {
                post.remove();
                if (!document.querySelector('.gc-post')) {
                    document.getElementById('announcementsExpiredEmpty')?.classList.remove('d-none');
                }
            };
            if (delay <= 0) remove();
            else setTimeout(remove, Math.min(delay, 2147483647));
        });
    })();
</script>
@endpush
