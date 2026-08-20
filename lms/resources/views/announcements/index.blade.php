@extends('layouts.master')
@section('content')
    {{-- message --}}
    {!! Toastr::message() !!}
    <div class="page-wrapper">
        <div class="content container-fluid">

            <div class="page-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="page-title">Announcements</h3>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active">Announcements</li>
                        </ul>
                    </div>
                    <div class="col-auto text-end float-end ms-auto">
                        @if(Auth::user()->role_name === 'Admin' || Auth::user()->role_name === 'Teacher')
                            <a href="{{ route('announcements.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus"></i> New Announcement
                            </a>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Filter Section -->
            <div class="student-group-form">
                <div class="row">
                    <div class="col-lg-3 col-md-6">
                        <div class="form-group">
                            <select class="form-control" id="type_filter">
                                <option value="">All Types</option>
                                <option value="general">General</option>
                                <option value="academic">Academic</option>
                                <option value="event">Event</option>
                                <option value="reminder">Reminder</option>
                                <option value="emergency">Emergency</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="form-group">
                            <select class="form-control" id="priority_filter">
                                <option value="">All Priorities</option>
                                <option value="low">Low</option>
                                <option value="normal">Normal</option>
                                <option value="high">High</option>
                                <option value="urgent">Urgent</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="form-group">
                            <select class="form-control" id="status_filter">
                                <option value="">All Status</option>
                                <option value="pinned">Pinned Only</option>
                                <option value="active">Active Only</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="search-student-btn">
                            <button type="button" class="btn btn-primary" id="applyFilters">
                                <i class="fas fa-filter"></i> Apply Filters
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-12">
                    <div class="card card-table">
                        <div class="card-body">
                            <div class="page-header">
                                <div class="row align-items-center">
                                    <div class="col">
                                        <h3 class="page-title">All Announcements</h3>
                                    </div>
                                    <div class="col-auto text-end float-end ms-auto download-grp">
                                        <a href="#" class="btn btn-outline-primary me-2" id="exportAnnouncements">
                                            <i class="fas fa-download"></i> Export
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <!-- Empty State -->
                            @if($announcements->count() == 0)
                                <div class="text-center py-5">
                                    <div class="mb-4">
                                        <i class="fas fa-bullhorn text-primary" style="font-size: 4rem; opacity: 0.6;"></i>
                                    </div>
                                    <h4 class="fw-bold text-dark mb-3">No Announcements Found</h4>
                                    <p class="text-muted mb-4">There are no announcements available for your role at this time.</p>
                                    
                                    @if(Auth::user()->role_name === 'Admin' || Auth::user()->role_name === 'Teacher')
                                        <a href="{{ route('announcements.create') }}" class="btn btn-primary">
                                            <i class="fas fa-plus"></i> Create First Announcement
                                        </a>
                                    @endif
                                </div>
                            @else
                                <!-- Announcements List -->
                                <div class="table-responsive">
                                    <table class="table table-hover table-center mb-0">
                                        <thead>
                                            <tr>
                                                <th>Title</th>
                                                <th>Type</th>
                                                <th>Priority</th>
                                                <th>Target Audience</th>
                                                <th>Created By</th>
                                                <th>Date</th>
                                                <th>Status</th>
                                                <th class="text-end">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($announcements as $announcement)
                                                <tr class="{{ $announcement->is_pinned ? 'table-warning' : '' }}">
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            @if($announcement->is_pinned)
                                                                <i class="fas fa-thumbtack text-warning me-2" title="Pinned"></i>
                                                            @endif
                                                            <div>
                                                                <h2 class="table-avatar">
                                                                    <a href="{{ route('announcements.show', $announcement->id) }}" class="avatar avatar-sm me-2">
                                                                        <i class="{{ $announcement->type_icon }} text-{{ $announcement->priority_color }}"></i>
                                                                    </a>
                                                                    <a href="{{ route('announcements.show', $announcement->id) }}" class="text-dark fw-bold">
                                                                        {{ $announcement->title }}
                                                                    </a>
                                                                </h2>
                                                                <small class="text-muted">
                                                                    {{ Str::limit($announcement->content, 100) }}
                                                                </small>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-{{ $announcement->priority_color }} bg-opacity-10 text-{{ $announcement->priority_color }}">
                                                            <i class="{{ $announcement->type_icon }} me-1"></i>
                                                            {{ ucfirst($announcement->type) }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-{{ $announcement->priority_color }}">
                                                            {{ ucfirst($announcement->priority) }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-info bg-opacity-10 text-info">
                                                            {{ ucfirst($announcement->target_audience) }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <h2 class="table-avatar">
                                                            <a href="#" class="avatar avatar-sm me-2">
                                                                <img class="avatar-img rounded-circle" src="{{ $announcement->creator->avatar ?? URL::to('assets/img/profiles/avatar-01.jpg') }}" alt="User Image">
                                                            </a>
                                                            <a href="#">{{ $announcement->creator->name }}</a>
                                                        </h2>
                                                    </td>
                                                    <td>
                                                        <div>
                                                            <div class="text-dark fw-medium">{{ $announcement->created_at->format('M d, Y') }}</div>
                                                            <small class="text-muted">{{ $announcement->created_at->format('h:i A') }}</small>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        @if($announcement->is_scheduled && $announcement->scheduled_at > now())
                                                            <span class="badge bg-warning">Scheduled</span>
                                                        @elseif($announcement->expires_at && $announcement->expires_at < now())
                                                            <span class="badge bg-secondary">Expired</span>
                                                        @elseif($announcement->is_active)
                                                            <span class="badge bg-success">Active</span>
                                                        @else
                                                            <span class="badge bg-danger">Inactive</span>
                                                        @endif
                                                    </td>
                                                    <td class="text-end">
                                                        <div class="actions">
                                                            <a href="{{ route('announcements.show', $announcement->id) }}" class="btn btn-sm bg-primary-light me-2">
                                                                <i class="fas fa-eye"></i>
                                                            </a>
                                                            @if(Auth::user()->role_name === 'Admin' || (Auth::user()->role_name === 'Teacher' && $announcement->created_by === Auth::id()))
                                                                <a href="{{ route('announcements.edit', $announcement->id) }}" class="btn btn-sm bg-success-light me-2">
                                                                    <i class="fas fa-edit"></i>
                                                                </a>
                                                                <a href="javascript:void(0);" class="btn btn-sm bg-danger-light" onclick="deleteAnnouncement({{ $announcement->id }})">
                                                                    <i class="fas fa-trash"></i>
                                                                </a>
                                                            @endif
                                                            @if(Auth::user()->role_name === 'Admin')
                                                                <a href="javascript:void(0);" class="btn btn-sm bg-warning-light ms-2" onclick="togglePin({{ $announcement->id }})" title="{{ $announcement->is_pinned ? 'Unpin' : 'Pin' }}">
                                                                    <i class="fas fa-thumbtack"></i>
                                                                </a>
                                                            @endif
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>

                                <!-- Pagination -->
                                <div class="d-flex justify-content-center mt-4">
                                    {{ $announcements->links() }}
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

    // Filter functionality
    document.getElementById('applyFilters').addEventListener('click', function() {
        const typeFilter = document.getElementById('type_filter').value;
        const priorityFilter = document.getElementById('priority_filter').value;
        const statusFilter = document.getElementById('status_filter').value;
        
        let url = new URL(window.location);
        if (typeFilter) url.searchParams.set('type', typeFilter);
        if (priorityFilter) url.searchParams.set('priority', priorityFilter);
        if (statusFilter) url.searchParams.set('status', statusFilter);
        
        window.location.href = url.toString();
    });

    // Export functionality
    document.getElementById('exportAnnouncements').addEventListener('click', function() {
        // Implement export functionality
        alert('Export functionality will be implemented here');
    });
</script>
@endsection 