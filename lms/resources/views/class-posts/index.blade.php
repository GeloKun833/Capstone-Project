@extends('layouts.master')
@section('content')
{{-- message --}}
{!! Toastr::message() !!}

<div class="page-wrapper">
    <div class="content container-fluid">
        
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">Class Posts</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Class Posts</li>
                    </ul>
                </div>
                <div class="col-auto text-end float-end ms-auto download-grp">
                    <a href="{{ route('class-posts.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Create Post
                    </a>
                </div>
            </div>
        </div>

        {{-- Filter Section --}}
        <div class="card">
            <div class="card-body">
                <form method="GET" action="{{ route('class-posts.index') }}">
                    <div class="row">
                        <div class="col-lg-3 col-md-6">
                            <div class="form-group">
                                <input type="text" name="search" class="form-control" placeholder="Search by title..." value="{{ request('search') }}">
                            </div>
                        </div>
                        <div class="col-lg-2 col-md-6">
                            <div class="form-group">
                                <select name="subject_id" class="form-control">
                                    <option value="">All Subjects</option>
                                    @foreach($subjects as $subject)
                                        <option value="{{ $subject->id }}" {{ request('subject_id') == $subject->id ? 'selected' : '' }}>
                                            {{ $subject->subject_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-lg-2 col-md-6">
                            <div class="form-group">
                                <select name="section_id" class="form-control">
                                    <option value="">All Sections</option>
                                    @foreach($sections as $section)
                                        <option value="{{ $section->id }}" {{ request('section_id') == $section->id ? 'selected' : '' }}>
                                            {{ $section->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-lg-2 col-md-6">
                            <div class="form-group">
                                <select name="type" class="form-control">
                                    <option value="">All Types</option>
                                    <option value="announcement" {{ request('type') == 'announcement' ? 'selected' : '' }}>Announcement</option>
                                    <option value="resource" {{ request('type') == 'resource' ? 'selected' : '' }}>Resource</option>
                                    <option value="discussion" {{ request('type') == 'discussion' ? 'selected' : '' }}>Discussion</option>
                                    <option value="reminder" {{ request('type') == 'reminder' ? 'selected' : '' }}>Reminder</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-lg-1 col-md-3">
                            <button type="submit" class="btn btn-primary btn-block w-100">
                                <i class="fas fa-search"></i> Filter
                            </button>
                        </div>
                        <div class="col-lg-1 col-md-3">
                            <a href="{{ route('class-posts.index') }}" class="btn btn-secondary btn-block w-100">
                                <i class="fas fa-redo"></i> Clear
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- Posts List --}}
        <div class="row">
            <div class="col-md-12">
                @forelse($posts as $post)
                    <div class="card mb-3 {{ $post->is_pinned ? 'border-primary' : '' }}">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="flex-grow-1">
                                    <div class="d-flex align-items-center mb-2">
                                        @if($post->is_pinned)
                                            <span class="badge bg-primary me-2">
                                                <i class="fas fa-thumbtack"></i> Pinned
                                            </span>
                                        @endif
                                        <span class="badge bg-{{ $post->type == 'announcement' ? 'info' : ($post->type == 'resource' ? 'success' : ($post->type == 'reminder' ? 'warning' : 'secondary')) }}">
                                            {{ ucfirst($post->type) }}
                                        </span>
                                        <span class="badge bg-{{ $post->priority == 'urgent' ? 'danger' : ($post->priority == 'high' ? 'warning' : 'secondary') }} ms-2">
                                            {{ ucfirst($post->priority) }}
                                        </span>
                                    </div>
                                    <h5 class="card-title">
                                        <a href="{{ route('class-posts.show', $post->id) }}">{{ $post->title }}</a>
                                    </h5>
                                    <p class="card-text text-muted mb-2">
                                        {{ Str::limit(strip_tags($post->content), 200) }}
                                    </p>
                                    <div class="text-muted small">
                                        <i class="fas fa-user"></i> {{ $post->teacher->name ?? 'Unknown' }}
                                        <i class="fas fa-book ms-3"></i> {{ $post->subject->subject_name ?? 'N/A' }}
                                        <i class="fas fa-users ms-3"></i> {{ $post->section->name ?? 'N/A' }}
                                        <i class="fas fa-clock ms-3"></i> {{ $post->created_at->diffForHumans() }}
                                        @if($post->file_path)
                                            <i class="fas fa-paperclip ms-3"></i> Attachment
                                        @endif
                                    </div>
                                </div>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-light" type="button" data-bs-toggle="dropdown">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="{{ route('class-posts.show', $post->id) }}">
                                            <i class="fas fa-eye"></i> View
                                        </a></li>
                                        @if(auth()->user()->role_name == 'Teacher' && $post->teacher_id == auth()->user()->teacher->id)
                                            <li><a class="dropdown-item" href="{{ route('class-posts.edit', $post->id) }}">
                                                <i class="fas fa-edit"></i> Edit
                                            </a></li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <form action="{{ route('class-posts.destroy', $post->id) }}" method="POST" onsubmit="return confirm('Are you sure?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="dropdown-item text-danger">
                                                        <i class="fas fa-trash"></i> Delete
                                                    </button>
                                                </form>
                                            </li>
                                        @endif
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="card">
                        <div class="card-body text-center py-5">
                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                            <h5>No Class Posts Found</h5>
                            <p class="text-muted">Start by creating your first class post</p>
                            <a href="{{ route('class-posts.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Create Post
                            </a>
                        </div>
                    </div>
                @endforelse

                {{-- Pagination --}}
                @if($posts->hasPages())
                    <div class="d-flex justify-content-center mt-4">
                        {{ $posts->links() }}
                    </div>
                @endif
            </div>
        </div>

    </div>
</div>
@endsection

