@extends('layouts.master')
@section('content')
    {{-- message --}}
    {!! Toastr::message() !!}
    <div class="page-wrapper">
        <div class="content container-fluid">

            <div class="page-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="page-title">Messages</h3>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active">Inbox</li>
                        </ul>
                    </div>
                    <div class="col-auto text-end float-end ms-auto">
                        <a href="{{ route('messages.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus"></i> New Message
                        </a>
                    </div>
                </div>
            </div>

            <!-- Message Navigation -->
            <div class="student-group-form">
                <div class="row">
                    <div class="col-lg-3 col-md-6">
                        <div class="form-group">
                            <a href="{{ route('messages.index') }}" class="btn btn-primary w-100">
                                <i class="fas fa-inbox me-2"></i>Inbox
                                @if($unreadCount > 0)
                                    <span class="badge bg-danger ms-2">{{ $unreadCount }}</span>
                                @endif
                            </a>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="form-group">
                            <a href="{{ route('messages.sent') }}" class="btn btn-outline-primary w-100">
                                <i class="fas fa-paper-plane me-2"></i>Sent
                            </a>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="form-group">
                            <a href="{{ route('messages.archived') }}" class="btn btn-outline-secondary w-100">
                                <i class="fas fa-archive me-2"></i>Archived
                            </a>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="form-group">
                            <select class="form-control" id="type_filter">
                                <option value="">All Types</option>
                                <option value="general">General</option>
                                <option value="academic">Academic</option>
                                <option value="behavioral">Behavioral</option>
                                <option value="attendance">Attendance</option>
                                <option value="grade">Grade</option>
                            </select>
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
                                        <h3 class="page-title">Inbox Messages</h3>
                                    </div>
                                    <div class="col-auto text-end float-end ms-auto download-grp">
                                        <button type="button" class="btn btn-outline-primary me-2" id="markAllRead">
                                            <i class="fas fa-check-double"></i> Mark All Read
                                        </button>
                                        <a href="#" class="btn btn-outline-secondary" id="exportMessages">
                                            <i class="fas fa-download"></i> Export
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <!-- Empty State -->
                            @if($messages->count() == 0)
                                <div class="text-center py-5">
                                    <div class="mb-4">
                                        <i class="fas fa-inbox text-primary" style="font-size: 4rem; opacity: 0.6;"></i>
                                    </div>
                                    <h4 class="fw-bold text-dark mb-3">No Messages Found</h4>
                                    <p class="text-muted mb-4">Your inbox is empty. Start a conversation by sending a message.</p>
                                    <a href="{{ route('messages.create') }}" class="btn btn-primary">
                                        <i class="fas fa-plus"></i> Compose Message
                                    </a>
                                </div>
                            @else
                                <!-- Messages List -->
                                <div class="table-responsive">
                                    <table class="table table-hover table-center mb-0">
                                        <thead>
                                            <tr>
                                                <th>Sender</th>
                                                <th>Subject</th>
                                                <th>Type</th>
                                                <th>Priority</th>
                                                <th>Student</th>
                                                <th>Date</th>
                                                <th>Status</th>
                                                <th class="text-end">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($messages as $message)
                                                <tr class="{{ !$message->is_read ? 'table-primary' : '' }}">
                                                    <td>
                                                        <h2 class="table-avatar">
                                                            <a href="#" class="avatar avatar-sm me-2">
                                                                <img class="avatar-img rounded-circle" src="{{ $message->sender->avatar ?? URL::to('assets/img/profiles/avatar-01.jpg') }}" alt="User Image">
                                                            </a>
                                                            <a href="#" class="text-dark fw-bold">
                                                                {{ $message->sender->name }}
                                                                @if(!$message->is_read)
                                                                    <i class="fas fa-circle text-primary ms-1" style="font-size: 0.5rem;"></i>
                                                                @endif
                                                            </a>
                                                        </h2>
                                                    </td>
                                                    <td>
                                                        <div>
                                                            <a href="{{ route('messages.show', $message->id) }}" class="text-dark fw-medium">
                                                                {{ $message->subject }}
                                                            </a>
                                                            <small class="text-muted d-block">
                                                                {{ Str::limit($message->content, 80) }}
                                                            </small>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-{{ $message->priority_color }} bg-opacity-10 text-{{ $message->priority_color }}">
                                                            <i class="{{ $message->type_icon }} me-1"></i>
                                                            {{ ucfirst($message->type) }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-{{ $message->priority_color }}">
                                                            {{ ucfirst($message->priority) }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        @if($message->student)
                                                            <span class="badge bg-info bg-opacity-10 text-info">
                                                                {{ $message->student->full_name }}
                                                            </span>
                                                        @else
                                                            <span class="text-muted">-</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <div>
                                                            <div class="text-dark fw-medium">{{ $message->created_at->format('M d, Y') }}</div>
                                                            <small class="text-muted">{{ $message->created_at->format('h:i A') }}</small>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        @if($message->is_read)
                                                            <span class="badge bg-success">Read</span>
                                                        @else
                                                            <span class="badge bg-warning">Unread</span>
                                                        @endif
                                                    </td>
                                                    <td class="text-end">
                                                        <div class="actions">
                                                            <a href="{{ route('messages.show', $message->id) }}" class="btn btn-sm bg-primary-light me-2">
                                                                <i class="fas fa-eye"></i>
                                                            </a>
                                                            <a href="{{ route('messages.conversation', $message->sender_id) }}" class="btn btn-sm bg-success-light me-2">
                                                                <i class="fas fa-comments"></i>
                                                            </a>
                                                            <a href="javascript:void(0);" class="btn btn-sm bg-warning-light me-2" onclick="toggleRead({{ $message->id }})">
                                                                <i class="fas fa-{{ $message->is_read ? 'envelope' : 'envelope-open' }}"></i>
                                                            </a>
                                                            <a href="javascript:void(0);" class="btn btn-sm bg-info-light me-2" onclick="archiveMessage({{ $message->id }})">
                                                                <i class="fas fa-archive"></i>
                                                            </a>
                                                            <a href="javascript:void(0);" class="btn btn-sm bg-danger-light" onclick="deleteMessage({{ $message->id }})">
                                                                <i class="fas fa-trash"></i>
                                                            </a>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>

                                <!-- Pagination -->
                                <div class="d-flex justify-content-center mt-4">
                                    {{ $messages->links() }}
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
                    Are you sure you want to delete this message? This action cannot be undone.
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
    function deleteMessage(id) {
        if (confirm('Are you sure you want to delete this message?')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/messages/${id}`;
            form.innerHTML = `
                @csrf
                @method('DELETE')
            `;
            document.body.appendChild(form);
            form.submit();
        }
    }

    function toggleRead(id) {
        fetch(`/messages/${id}/read`, {
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

    function archiveMessage(id) {
        if (confirm('Are you sure you want to archive this message?')) {
            fetch(`/messages/${id}/archive`, {
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
    }

    // Mark all as read
    document.getElementById('markAllRead').addEventListener('click', function() {
        if (confirm('Mark all messages as read?')) {
            fetch('/notifications/mark-all-read', {
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
    });

    // Filter functionality
    document.getElementById('type_filter').addEventListener('change', function() {
        const typeFilter = this.value;
        if (typeFilter) {
            let url = new URL(window.location);
            url.searchParams.set('type', typeFilter);
            window.location.href = url.toString();
        } else {
            window.location.href = window.location.pathname;
        }
    });

    // Export functionality
    document.getElementById('exportMessages').addEventListener('click', function() {
        alert('Export functionality will be implemented here');
    });
</script>
@endsection 