@extends('layouts.master')
@section('content')
    <div class="page-wrapper">
        <div class="content container-fluid dir-page">
            <div class="page-header">
                <div class="row align-items-start">
                    <div class="col">
                        <h3 class="page-title mb-1">Teachers</h3>
                        <p class="dir-subtitle">Browse teacher profiles in a card layout.</p>
                    </div>
                    <div class="col-auto text-end">
                        <ul class="breadcrumb justify-content-end mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active">All Teachers</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="dir-card">
                <div class="dir-toolbar">
                    <div>
                        <h5 class="dir-toolbar-title">Teacher grid</h5>
                        <span class="dir-count mt-1">{{ method_exists($teacherGrid, 'total') ? $teacherGrid->total() : $teacherGrid->count() }} records</span>
                    </div>
                    <div class="dir-actions">
                        <div class="dir-toggle" role="group" aria-label="View">
                            <a href="{{ route('teacher/list/page') }}" title="List view"><i class="fa fa-list"></i></a>
                            <a href="{{ route('teacher/grid/page') }}" class="is-active" title="Grid view"><i class="fa fa-th"></i></a>
                        </div>
                    </div>
                </div>

                <div class="dir-grid">
                    <div class="row g-3">
                        @forelse ($teacherGrid as $list)
                            @php
                                $tName = $list->full_name ?: (optional($list->user)->name ?? 'Teacher');
                                $tPhoto = \App\Support\AvatarUploader::url(optional($list->user)->avatar ?? $list->avatar ?? null);
                            @endphp
                            <div class="col-xl-3 col-lg-4 col-md-6">
                                <div class="dir-grid-card">
                                    <a href="{{ url('teacher/sis/'.$list->user_id) }}">
                                        <img src="{{ $tPhoto }}" alt="{{ $tName }}" onerror="this.onerror=null;this.src='{{ asset('images/photo_defaults.jpg') }}';">
                                    </a>
                                    <h5><a href="{{ url('teacher/sis/'.$list->user_id) }}">{{ $tName }}</a></h5>
                                    <div class="dir-person-meta">Teacher</div>
                                </div>
                            </div>
                        @empty
                            <div class="col-12">
                                <div class="dir-empty">
                                    <div><i class="fas fa-chalkboard-teacher"></i></div>
                                    <strong>No teachers found</strong>
                                </div>
                            </div>
                        @endforelse
                    </div>
                    @if(method_exists($teacherGrid, 'links'))
                        <div class="d-flex justify-content-center mt-3">{{ $teacherGrid->links() }}</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/directory-modern.css') }}?v=20260914b">
@endpush
