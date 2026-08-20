@extends('layouts.master')
@section('content')
    {{-- message --}}
    {!! Toastr::message() !!}
    <div class="page-wrapper">
        <div class="content container-fluid">

            <div class="page-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="page-title">Announcement Details</h3>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('announcements.index') }}">Announcements</a></li>
                            <li class="breadcrumb-item active">View Announcement</li>
                        </ul>
                    </div>
                    <div class="col-auto text-end float-end ms-auto">
                        @if(Auth::user()->role_name === 'Admin' || (Auth::user()->role_name === 'Teacher' && $announcement->created_by === Auth::id()))
                            <a href="{{ route('announcements.edit', $announcement->id) }}" class="btn btn-primary me-2">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                        @endif
                        <a href="{{ route('announcements.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back
                        </a>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-body">
                            <!-- Announcement Header -->
                            <div class="announcement-header mb-4">
                                <div class="d-flex align-items-center mb-3">
                                    @if($announcement->is_pinned)
                                        <i class="fas fa-thumbtack text-warning me-3" style="font-size: 1.5rem;" title="Pinned Announcement"></i>
                                    @endif
                                    <div class="flex-grow-1">
                                        <h2 class="mb-2">{{ $announcement->title }}</h2>
                                        <div class="d-flex align-items-center flex-wrap">
                                            <span class="badge bg-{{ $announcement->priority_color }} me-2">
                                                <i class="{{ $announcement->type_icon }} me-1"></i>
                                                {{ ucfirst($announcement->type) }}
                                            </span>
                                            <span class="badge bg-{{ $announcement->priority_color }} me-2">
                                                {{ ucfirst($announcement->priority) }} Priority
                                            </span>
                                            <span class="badge bg-info bg-opacity-10 text-info me-2">
                                                {{ ucfirst($announcement->target_audience) }}
                                            </span>
                                            @if($announcement->is_scheduled && $announcement->scheduled_at > now())
                                                <span class="badge bg-warning">Scheduled</span>
                                            @elseif($announcement->expires_at && $announcement->expires_at < now())
                                                <span class="badge bg-secondary">Expired</span>
                                            @elseif($announcement->is_active)
                                                <span class="badge bg-success">Active</span>
                                            @else
                                                <span class="badge bg-danger">Inactive</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Announcement Content -->
                            <div class="announcement-content mb-4">
                                <div class="content-body">
                                    {!! nl2br(e($announcement->content)) !!}
                                </div>
                            </div>

                            <!-- Announcement Metadata -->
                            <div class="announcement-meta">
                                <hr>
                                <div class="row">
                                    <div class="col-md-6">
                                        <h6 class="text-muted mb-3">Announcement Details</h6>
                                        <ul class="list-unstyled">
                                            <li class="mb-2">
                                                <strong>Created by:</strong> 
                                                <span>{{ $announcement->creator->name }}</span>
                                            </li>
                                            <li class="mb-2">
                                                <strong>Created on:</strong> 
                                                <span>{{ $announcement->created_at->format('F d, Y \a\t h:i A') }}</span>
                                            </li>
                                            @if($announcement->scheduled_at)
                                                <li class="mb-2">
                                                    <strong>Scheduled for:</strong> 
                                                    <span>{{ $announcement->scheduled_at->format('F d, Y \a\t h:i A') }}</span>
                                                </li>
                                            @endif
                                            @if($announcement->expires_at)
                                                <li class="mb-2">
                                                    <strong>Expires on:</strong> 
                                                    <span>{{ $announcement->expires_at->format('F d, Y \a\t h:i A') }}</span>
                                                </li>
                                            @endif
                                            <li class="mb-2">
                                                <strong>Last updated:</strong> 
                                                <span>{{ $announcement->updated_at->format('F d, Y \a\t h:i A') }}</span>
                                            </li>
                                        </ul>
                                    </div>
                                    <div class="col-md-6">
                                        <h6 class="text-muted mb-3">Targeting Information</h6>
                                        <ul class="list-unstyled">
                                            <li class="mb-2">
                                                <strong>Target Audience:</strong> 
                                                <span>{{ ucfirst($announcement->target_audience) }}</span>
                                            </li>
                                            @if($announcement->target_roles)
                                                <li class="mb-2">
                                                    <strong>Specific Roles:</strong> 
                                                    <span>{{ implode(', ', array_map('ucfirst', $announcement->target_roles)) }}</span>
                                                </li>
                                            @endif
                                            @if($announcement->target_sections)
                                                <li class="mb-2">
                                                    <strong>Specific Sections:</strong> 
                                                    <span>{{ implode(', ', $announcement->target_sections) }}</span>
                                                </li>
                                            @endif
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <!-- Quick Actions -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">Quick Actions</h5>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                @if(Auth::user()->role_name === 'Admin' || (Auth::user()->role_name === 'Teacher' && $announcement->created_by === Auth::id()))
                                    <a href="{{ route('announcements.edit', $announcement->id) }}" class="btn btn-primary">
                                        <i class="fas fa-edit me-2"></i>Edit Announcement
                                    </a>
                                    <button type="button" class="btn btn-danger" onclick="deleteAnnouncement({{ $announcement->id }})">
                                        <i class="fas fa-trash me-2"></i>Delete Announcement
                                    </button>
                                @endif
                                @if(Auth::user()->role_name === 'Admin')
                                    <button type="button" class="btn btn-warning" onclick="togglePin({{ $announcement->id }})">
                                        <i class="fas fa-thumbtack me-2"></i>
                                        {{ $announcement->is_pinned ? 'Unpin' : 'Pin' }} Announcement
                                    </button>
                                @endif
                                <a href="{{ route('announcements.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left me-2"></i>Back to List
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Status Information -->
                    <div class="card mt-3">
                        <div class="card-header">
                            <h5 class="card-title">Status Information</h5>
                        </div>
                        <div class="card-body">
                            <div class="status-item mb-3">
                                <strong>Visibility:</strong>
                                @if($announcement->isVisibleTo(Auth::user()))
                                    <span class="badge bg-success">Visible to you</span>
                                @else
                                    <span class="badge bg-danger">Not visible to you</span>
                                @endif
                            </div>
                            
                            <div class="status-item mb-3">
                                <strong>Pinned Status:</strong>
                                @if($announcement->is_pinned)
                                    <span class="badge bg-warning">Pinned</span>
                                @else
                                    <span class="badge bg-secondary">Not Pinned</span>
                                @endif
                            </div>
                            
                            <div class="status-item mb-3">
                                <strong>Active Status:</strong>
                                @if($announcement->is_active)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-danger">Inactive</span>
                                @endif
                            </div>
                            
                            @if($announcement->is_scheduled)
                                <div class="status-item mb-3">
                                    <strong>Scheduled:</strong>
                                    @if($announcement->scheduled_at > now())
                                        <span class="badge bg-warning">Will be published on {{ $announcement->scheduled_at->format('M d, Y') }}</span>
                                    @else
                                        <span class="badge bg-success">Published</span>
                                    @endif
                                </div>
                            @endif
                            
                            @if($announcement->expires_at)
                                <div class="status-item">
                                    <strong>Expiration:</strong>
                                    @if($announcement->expires_at < now())
                                        <span class="badge bg-secondary">Expired on {{ $announcement->expires_at->format('M d, Y') }}</span>
                                    @else
                                        <span class="badge bg-info">Expires on {{ $announcement->expires_at->format('M d, Y') }}</span>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to delete this announcement? This action cannot be undone.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form id="deleteForm" method="POST" style="display: inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script>
    function deleteAnnouncement(id) {
        if (confirm('Are you sure you want to delete this announcement?')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/announcements/${id}`;
            form.innerHTML = `
                @csrf
                @method('DELETE')
            `;
            document.body.appendChild(form);
            form.submit();
        }
    }

    function togglePin(id) {
        fetch(`/announcements/${id}/toggle-pin`, {
            method: 'PATCH',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
            },
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
    }
</script>
@endsection 