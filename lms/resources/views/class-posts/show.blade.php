@extends('layouts.master')
@section('content')


<div class="page-wrapper">
    <div class="content container-fluid">
        
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">{{ $classPost->title }}</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('class-posts.index') }}">Class Posts</a></li>
                        <li class="breadcrumb-item active">{{ $classPost->title }}</li>
                    </ul>
                </div>
                <div class="col-auto text-end">
                    @if(auth()->user()->role_name === 'Teacher' && auth()->user()->teacher && auth()->user()->teacher->id === $classPost->teacher_id)
                        <a href="{{ route('class-posts.edit', $classPost) }}" class="btn btn-primary">
                            <i class="fas fa-edit"></i> Edit Post
                        </a>
                        <form action="{{ route('class-posts.destroy', $classPost) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this post?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-body">
                        {{-- Post Header --}}
                        <div class="post-header mb-4">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <h2 class="mb-2">{{ $classPost->title }}</h2>
                                    <div class="post-meta">
                                        <span class="badge bg-{{ $classPost->type_color }} me-2">
                                            <i class="{{ $classPost->type_icon }}"></i> {{ ucfirst($classPost->type) }}
                                        </span>
                                        <span class="badge bg-{{ $classPost->priority_color }} me-2">
                                            <i class="fas fa-flag"></i> {{ ucfirst($classPost->priority) }} Priority
                                        </span>
                                        @if($classPost->is_pinned)
                                            <span class="badge bg-warning">
                                                <i class="fas fa-thumbtack"></i> Pinned
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="post-info bg-light p-3 rounded">
                                <div class="row">
                                    <div class="col-md-6">
                                        <p class="mb-2"><i class="fas fa-user text-primary"></i> <strong>Posted by:</strong> {{ $classPost->teacher->full_name ?? 'Teacher' }}</p>
                                        <p class="mb-2"><i class="fas fa-book text-success"></i> <strong>Subject:</strong> {{ $classPost->subject->subject_name }}</p>
                                        <p class="mb-0"><i class="fas fa-users text-info"></i> <strong>Section:</strong> {{ $classPost->section->name }}</p>
                                    </div>
                                    <div class="col-md-6">
                                        <p class="mb-2"><i class="fas fa-calendar-alt text-warning"></i> <strong>Posted:</strong> {{ $classPost->created_at->format('M d, Y g:i A') }}</p>
                                        <p class="mb-2"><i class="fas fa-calendar text-secondary"></i> <strong>Academic Year:</strong> {{ $classPost->academicYear->name ?? 'N/A' }}</p>
                                        <p class="mb-0"><i class="fas fa-calendar-week text-secondary"></i> <strong>Semester:</strong> {{ $classPost->semester->name ?? 'N/A' }}</p>
                                    </div>
                                </div>
                                @if($classPost->expires_at)
                                    <div class="mt-2">
                                        <p class="mb-0"><i class="fas fa-clock text-danger"></i> <strong>Expires:</strong> {{ $classPost->expires_at->format('M d, Y') }}</p>
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- Post Content --}}
                        <div class="post-content mb-4">
                            <h5 class="mb-3">Content</h5>
                            <div class="content-box p-4 bg-white border rounded">
                                {!! $classPost->formatted_content !!}
                            </div>
                        </div>

                        {{-- Attachment --}}
                        @if($classPost->file_path)
                            <div class="post-attachment mb-4">
                                <h5 class="mb-3">Attachment</h5>
                                <div class="attachment-box p-3 bg-light border rounded">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-file-{{ $classPost->file_type }} fa-2x text-primary me-3"></i>
                                            <div>
                                                <p class="mb-0 fw-bold">{{ $classPost->file_name }}</p>
                                                <small class="text-muted">{{ strtoupper($classPost->file_type) }} File</small>
                                            </div>
                                        </div>
                                        <a href="{{ $classPost->file_url }}" class="btn btn-primary btn-sm" target="_blank">
                                            <i class="fas fa-download"></i> Download
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endif

                        {{-- Comments Section --}}
                        @if($classPost->allows_comments)
                            <div class="comments-section">
                                <h5 class="mb-3">
                                    <i class="fas fa-comments"></i> Comments 
                                    <span class="badge bg-secondary">{{ $classPost->comment_count }}</span>
                                </h5>
                                
                                {{-- Add Comment Form --}}
                                <div class="card mb-4">
                                    <div class="card-body">
                                        <form action="{{ route('class-posts.store-comment', $classPost) }}" method="POST" enctype="multipart/form-data">
                                            @csrf
                                            <div class="form-group mb-3">
                                                <label class="form-label">Add a comment</label>
                                                <textarea name="content" rows="3" class="form-control @error('content') is-invalid @enderror" 
                                                          placeholder="Write your comment..." required>{{ old('content') }}</textarea>
                                                @error('content')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="form-group mb-3">
                                                <label class="form-label">Attachment (Optional)</label>
                                                <input type="file" name="comment_file" class="form-control" 
                                                       accept=".pdf,.docx,.pptx,.txt,.jpg,.jpeg,.png">
                                                <small class="form-text text-muted">Max size: 5MB</small>
                                            </div>
                                            <button type="submit" class="btn btn-primary btn-sm">
                                                <i class="fas fa-paper-plane"></i> Post Comment
                                            </button>
                                        </form>
                                    </div>
                                </div>

                                {{-- Display Comments --}}
                                @if($classPost->comments->count() > 0)
                                    <div class="comments-list">
                                        @foreach($classPost->approvedComments as $comment)
                                            <div class="card mb-3">
                                                <div class="card-body">
                                                    <div class="d-flex justify-content-between align-items-start">
                                                        <div class="d-flex align-items-start">
                                                            <div class="avatar me-3">
                                                                <i class="fas fa-user-circle fa-2x text-primary"></i>
                                                            </div>
                                                            <div>
                                                                <h6 class="mb-1">{{ $comment->user->name ?? 'User' }}</h6>
                                                                <small class="text-muted">{{ $comment->created_at->diffForHumans() }}</small>
                                                                <p class="mt-2 mb-0">{{ $comment->content }}</p>
                                                                @if($comment->file_path)
                                                                    <div class="mt-2">
                                                                        <a href="{{ asset('storage/' . $comment->file_path) }}" class="btn btn-sm btn-outline-primary" target="_blank">
                                                                            <i class="fas fa-paperclip"></i> {{ $comment->file_name }}
                                                                        </a>
                                                                    </div>
                                                                @endif
                                                            </div>
                                                        </div>
                                                        @if(auth()->id() === $comment->user_id || auth()->user()->role_name === 'Teacher')
                                                            <form action="{{ route('class-posts.delete-comment', $comment) }}" method="POST" onsubmit="return confirm('Delete this comment?');">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="btn btn-sm btn-danger">
                                                                    <i class="fas fa-trash"></i>
                                                                </button>
                                                            </form>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="text-center py-4">
                                        <i class="fas fa-comments fa-3x text-muted mb-3"></i>
                                        <p class="text-muted">No comments yet. Be the first to comment!</p>
                                    </div>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Sidebar --}}
            <div class="col-lg-4">
                <div class="card mb-3">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-info-circle"></i> Post Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="info-item mb-3">
                            <small class="text-muted d-block">Status</small>
                            <span class="badge bg-{{ $classPost->is_active ? 'success' : 'secondary' }} fs-6">
                                {{ $classPost->is_active ? 'Published' : 'Draft' }}
                            </span>
                        </div>
                        <div class="info-item mb-3">
                            <small class="text-muted d-block">Post ID</small>
                            <strong>#{{ $classPost->id }}</strong>
                        </div>
                        <div class="info-item mb-3">
                            <small class="text-muted d-block">Comments</small>
                            <strong>{{ $classPost->allows_comments ? 'Enabled' : 'Disabled' }}</strong>
                        </div>
                        <div class="info-item mb-3">
                            <small class="text-muted d-block">Confirmation Required</small>
                            <strong>{{ $classPost->requires_confirmation ? 'Yes' : 'No' }}</strong>
                        </div>
                        <div class="info-item mb-3">
                            <small class="text-muted d-block">Published</small>
                            <strong>{{ $classPost->published_at ? $classPost->published_at->format('M d, Y g:i A') : 'Not published' }}</strong>
                        </div>
                        @if($classPost->expires_at)
                            <div class="info-item mb-3">
                                <small class="text-muted d-block">Expires</small>
                                <strong class="text-danger">{{ $classPost->expires_at->format('M d, Y') }}</strong>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Quick Actions --}}
                @if(auth()->user()->role_name === 'Teacher' && auth()->user()->teacher && auth()->user()->teacher->id === $classPost->teacher_id)
                    <div class="card">
                        <div class="card-header bg-secondary text-white">
                            <h5 class="mb-0"><i class="fas fa-bolt"></i> Quick Actions</h5>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('class-posts.toggle-pin', $classPost) }}" method="POST" class="mb-2">
                                @csrf
                                <button type="submit" class="btn btn-{{ $classPost->is_pinned ? 'warning' : 'outline-warning' }} btn-sm w-100">
                                    <i class="fas fa-thumbtack"></i> {{ $classPost->is_pinned ? 'Unpin Post' : 'Pin Post' }}
                                </button>
                            </form>
                            @if($classPost->is_active)
                                <form action="{{ route('class-posts.unpublish', $classPost) }}" method="POST" class="mb-2">
                                    @csrf
                                    <button type="submit" class="btn btn-outline-secondary btn-sm w-100">
                                        <i class="fas fa-eye-slash"></i> Unpublish
                                    </button>
                                </form>
                            @else
                                <form action="{{ route('class-posts.publish', $classPost) }}" method="POST" class="mb-2">
                                    @csrf
                                    <button type="submit" class="btn btn-outline-success btn-sm w-100">
                                        <i class="fas fa-eye"></i> Publish
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        </div>

    </div>
</div>

@push('styles')
<style>
.post-content {
    font-size: 1rem;
    line-height: 1.8;
}

.content-box {
    min-height: 200px;
}

.post-meta .badge {
    font-size: 0.85rem;
    padding: 6px 12px;
}

.attachment-box {
    transition: all 0.3s ease;
}

.attachment-box:hover {
    background: #e9ecef !important;
}

.info-item {
    padding-bottom: 10px;
    border-bottom: 1px solid #e9ecef;
}

.info-item:last-child {
    border-bottom: none;
    padding-bottom: 0;
}

.comments-section {
    border-top: 2px solid #e9ecef;
    padding-top: 30px;
    margin-top: 30px;
}

.avatar {
    width: 40px;
    height: 40px;
}
</style>
@endpush

@endsection

