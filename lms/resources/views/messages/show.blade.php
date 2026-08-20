@extends('layouts.master')
@section('content')
    {{-- message --}}
    {!! Toastr::message() !!}
    <div class="page-wrapper">
        <div class="content container-fluid">

            <div class="page-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="page-title">Message Details</h3>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('messages.index') }}">Messages</a></li>
                            <li class="breadcrumb-item active">View Message</li>
                        </ul>
                    </div>
                    <div class="col-auto text-end float-end ms-auto">
                        <a href="{{ route('messages.conversation', $message->sender_id) }}" class="btn btn-primary me-2">
                            <i class="fas fa-comments"></i> View Conversation
                        </a>
                        <a href="{{ route('messages.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back
                        </a>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-body">
                            <!-- Message Header -->
                            <div class="message-header mb-4">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="flex-grow-1">
                                        <h2 class="mb-2">{{ $message->subject }}</h2>
                                        <div class="d-flex align-items-center flex-wrap">
                                            <span class="badge bg-{{ $message->priority_color }} me-2">
                                                <i class="{{ $message->type_icon }} me-1"></i>
                                                {{ ucfirst($message->type) }}
                                            </span>
                                            <span class="badge bg-{{ $message->priority_color }} me-2">
                                                {{ ucfirst($message->priority) }} Priority
                                            </span>
                                            @if($message->student)
                                                <span class="badge bg-info bg-opacity-10 text-info me-2">
                                                    Related to: {{ $message->student->full_name }}
                                                </span>
                                            @endif
                                            @if($message->is_read)
                                                <span class="badge bg-success">Read</span>
                                            @else
                                                <span class="badge bg-warning">Unread</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Message Content -->
                            <div class="message-content mb-4">
                                <div class="content-body">
                                    {!! nl2br(e($message->content)) !!}
                                </div>
                            </div>

                            <!-- Message Metadata -->
                            <div class="message-meta">
                                <hr>
                                <div class="row">
                                    <div class="col-md-6">
                                        <h6 class="text-muted mb-3">Message Details</h6>
                                        <ul class="list-unstyled">
                                            <li class="mb-2">
                                                <strong>From:</strong> 
                                                <span>{{ $message->sender->name }} ({{ ucfirst($message->sender->role_name) }})</span>
                                            </li>
                                            <li class="mb-2">
                                                <strong>To:</strong> 
                                                <span>{{ $message->recipient->name }} ({{ ucfirst($message->recipient->role_name) }})</span>
                                            </li>
                                            <li class="mb-2">
                                                <strong>Sent on:</strong> 
                                                <span>{{ $message->created_at->format('F d, Y \a\t h:i A') }}</span>
                                            </li>
                                            @if($message->read_at)
                                                <li class="mb-2">
                                                    <strong>Read on:</strong> 
                                                    <span>{{ $message->read_at->format('F d, Y \a\t h:i A') }}</span>
                                                </li>
                                            @endif
                                        </ul>
                                    </div>
                                    <div class="col-md-6">
                                        <h6 class="text-muted mb-3">Additional Information</h6>
                                        <ul class="list-unstyled">
                                            @if($message->student)
                                                <li class="mb-2">
                                                    <strong>Related Student:</strong> 
                                                    <span>{{ $message->student->full_name }}</span>
                                                </li>
                                                <li class="mb-2">
                                                    <strong>Student ID:</strong> 
                                                    <span>{{ $message->student->student_id }}</span>
                                                </li>
                                                <li class="mb-2">
                                                    <strong>Section:</strong> 
                                                    <span>{{ $message->student->section->name ?? 'N/A' }}</span>
                                                </li>
                                            @else
                                                <li class="mb-2">
                                                    <strong>Related Student:</strong> 
                                                    <span class="text-muted">None</span>
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
                                <a href="{{ route('messages.conversation', $message->sender_id) }}" class="btn btn-primary">
                                    <i class="fas fa-comments me-2"></i>View Conversation
                                </a>
                                <a href="{{ route('messages.create') }}?reply_to={{ $message->sender_id }}" class="btn btn-success">
                                    <i class="fas fa-reply me-2"></i>Reply
                                </a>
                                @if($message->recipient_id === Auth::id())
                                    @if($message->is_read)
                                        <button type="button" class="btn btn-warning" onclick="toggleRead({{ $message->id }})">
                                            <i class="fas fa-envelope me-2"></i>Mark as Unread
                                        </button>
                                    @else
                                        <button type="button" class="btn btn-info" onclick="toggleRead({{ $message->id }})">
                                            <i class="fas fa-envelope-open me-2"></i>Mark as Read
                                        </button>
                                    @endif
                                @endif
                                <button type="button" class="btn btn-secondary" onclick="archiveMessage({{ $message->id }})">
                                    <i class="fas fa-archive me-2"></i>Archive Message
                                </button>
                                <button type="button" class="btn btn-danger" onclick="deleteMessage({{ $message->id }})">
                                    <i class="fas fa-trash me-2"></i>Delete Message
                                </button>
                                <a href="{{ route('messages.index') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-arrow-left me-2"></i>Back to Inbox
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Sender Information -->
                    <div class="card mt-3">
                        <div class="card-header">
                            <h5 class="card-title">Sender Information</h5>
                        </div>
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                <img class="avatar avatar-lg me-3" src="{{ $message->sender->avatar ?? URL::to('assets/img/profiles/avatar-01.jpg') }}" alt="User Image">
                                <div>
                                    <h6 class="mb-1">{{ $message->sender->name }}</h6>
                                    <span class="badge bg-primary">{{ ucfirst($message->sender->role_name) }}</span>
                                </div>
                            </div>
                            <ul class="list-unstyled">
                                <li class="mb-2">
                                    <strong>Email:</strong> {{ $message->sender->email }}
                                </li>
                                <li class="mb-2">
                                    <strong>Phone:</strong> {{ $message->sender->phone_number ?? 'N/A' }}
                                </li>
                                <li class="mb-2">
                                    <strong>Department:</strong> {{ $message->sender->department ?? 'N/A' }}
                                </li>
                            </ul>
                        </div>
                    </div>

                    <!-- Message Status -->
                    <div class="card mt-3">
                        <div class="card-header">
                            <h5 class="card-title">Message Status</h5>
                        </div>
                        <div class="card-body">
                            <div class="status-item mb-3">
                                <strong>Read Status:</strong>
                                @if($message->is_read)
                                    <span class="badge bg-success">Read</span>
                                @else
                                    <span class="badge bg-warning">Unread</span>
                                @endif
                            </div>
                            
                            <div class="status-item mb-3">
                                <strong>Archive Status:</strong>
                                @if($message->is_archived)
                                    <span class="badge bg-secondary">Archived</span>
                                @else
                                    <span class="badge bg-success">Active</span>
                                @endif
                            </div>
                            
                            <div class="status-item mb-3">
                                <strong>Message Type:</strong>
                                <span class="badge bg-{{ $message->priority_color }} bg-opacity-10 text-{{ $message->priority_color }}">
                                    {{ ucfirst($message->type) }}
                                </span>
                            </div>
                            
                            <div class="status-item">
                                <strong>Priority:</strong>
                                <span class="badge bg-{{ $message->priority_color }}">
                                    {{ ucfirst($message->priority) }}
                                </span>
                            </div>
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
            form.innerHTML = '@csrf @method("DELETE")';
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
</script>
@endsection 