@extends('layouts.master')
@section('content')
    {{-- message --}}
    {!! Toastr::message() !!}
    <div class="page-wrapper">
        <div class="content container-fluid">

            <div class="page-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="page-title">Sent Messages</h3>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active">Sent Messages</li>
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
                            <a href="{{ route('messages.index') }}" class="btn btn-outline-primary w-100">
                                <i class="fas fa-inbox me-2"></i>Inbox
                            </a>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="form-group">
                            <a href="{{ route('messages.sent') }}" class="btn btn-primary w-100">
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
                                        <h3 class="page-title">Sent Messages</h3>
                                    </div>
                                    <div class="col-auto text-end float-end ms-auto download-grp">
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
                                        <i class="fas fa-paper-plane text-primary" style="font-size: 4rem; opacity: 0.6;"></i>
                                    </div>
                                    <h4 class="fw-bold text-dark mb-3">No Sent Messages</h4>
                                    <p class="text-muted mb-4">You haven't sent any messages yet. Start a conversation by sending a message.</p>
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
                                                <th>Recipient</th>
                                                <th>Subject</th>
                                                <th>Type</th>
                                                <th>Student</th>
                                                <th>Date</th>
                                                <th>Status</th>
                                                <th class="text-end">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($messages as $message)
                                                <tr>
                                                    <td>
                                                        <h2 class="table-avatar">
                                                            <a href="#" class="avatar avatar-sm me-2">
                                                                <img class="avatar-img rounded-circle" src="{{ asset('assets/img/profiles/avatar-01.jpg') }}" alt="User Image">
                                                            </a>
                                                            <a href="#">{{ $message->recipient->name ?? 'Unknown User' }}</a>
                                                        </h2>
                                                    </td>
                                                    <td>
                                                        <a href="{{ route('messages.show', $message->id) }}" class="text-decoration-none">
                                                            {{ $message->subject }}
                                                        </a>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-{{ $message->type_color }}">
                                                            {{ ucfirst($message->type) }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        @if($message->student)
                                                            {{ $message->student->full_name }}
                                                        @else
                                                            <span class="text-muted">-</span>
                                                        @endif
                                                    </td>
                                                    <td>{{ $message->created_at->format('M j, Y g:i A') }}</td>
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
                                                            <button type="button" class="btn btn-sm bg-danger-light" onclick="deleteMessage({{ $message->id }})">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
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
@endsection

@push('scripts')
<script>
function deleteMessage(messageId) {
    if (confirm('Are you sure you want to delete this message?')) {
        fetch(`/messages/${messageId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error deleting message');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error deleting message');
        });
    }
}

// Type filter functionality
document.getElementById('type_filter').addEventListener('change', function() {
    const type = this.value;
    const rows = document.querySelectorAll('tbody tr');
    
    rows.forEach(row => {
        const typeCell = row.querySelector('td:nth-child(3)');
        if (type === '' || typeCell.textContent.trim().toLowerCase().includes(type.toLowerCase())) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
});
</script>
@endpush 