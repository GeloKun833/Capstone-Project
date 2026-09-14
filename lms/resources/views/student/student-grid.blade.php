@extends('layouts.master')
@section('content')
    <div class="page-wrapper">
        <div class="content container-fluid dir-page">
            <div class="page-header">
                <div class="row align-items-start">
                    <div class="col">
                        <h3 class="page-title mb-1">Students</h3>
                        <p class="dir-subtitle">Browse student profiles in a card layout.</p>
                    </div>
                    <div class="col-auto text-end">
                        <ul class="breadcrumb justify-content-end mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active">All Students</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="dir-card">
                <div class="dir-toolbar">
                    <div>
                        <h5 class="dir-toolbar-title">{{ !empty($showingArchived) ? 'Archived students' : 'Active students' }}</h5>
                        <span class="dir-count mt-1">{{ method_exists($studentList, 'total') ? $studentList->total() : $studentList->count() }} records</span>
                    </div>
                    <div class="dir-actions">
                        <div class="dir-toggle" role="group" aria-label="View">
                            <a href="{{ route('student/list') }}{{ !empty($showingArchived) ? '?archived=1' : '' }}" title="List view"><i class="fa fa-list"></i></a>
                            <a href="{{ route('student/grid') }}{{ !empty($showingArchived) ? '?archived=1' : '' }}" class="is-active" title="Grid view"><i class="fa fa-th"></i></a>
                        </div>
                    </div>
                </div>

                <div class="dir-grid">
                    <div class="row g-3">
                        @forelse ($studentList as $list)
                            @php
                                $sName = trim(($list->first_name ?? '').' '.($list->last_name ?? ''));
                                $sPhoto = $list->upload
                                    ? \Illuminate\Support\Facades\Storage::url('student-photos/'.$list->upload)
                                    : asset('images/photo_defaults.jpg');
                                $klass = trim(($list->year_level ?: $list->class).' '.($list->section ?? ''));
                            @endphp
                            <div class="col-xl-3 col-lg-4 col-md-6">
                                <div class="dir-grid-card">
                                    @if(!empty($list->user_id))
                                        <a href="{{ route('student.sis', $list->user_id) }}">
                                            <img src="{{ $sPhoto }}" alt="{{ $sName }}" onerror="this.onerror=null;this.src='{{ asset('images/photo_defaults.jpg') }}';">
                                        </a>
                                        <h5><a href="{{ route('student.sis', $list->user_id) }}">{{ $sName ?: 'Student' }}</a></h5>
                                    @else
                                        <img src="{{ $sPhoto }}" alt="{{ $sName }}" onerror="this.onerror=null;this.src='{{ asset('images/photo_defaults.jpg') }}';">
                                        <h5>{{ $sName ?: 'Student' }}</h5>
                                    @endif
                                    <div class="dir-person-meta">{{ $klass ?: 'Student' }}</div>
                                </div>
                            </div>
                        @empty
                            <div class="col-12">
                                <div class="dir-empty">
                                    <div><i class="fas fa-user-graduate"></i></div>
                                    <strong>No students found</strong>
                                </div>
                            </div>
                        @endforelse
                    </div>
                    @if(method_exists($studentList, 'links'))
                        <div class="d-flex justify-content-center mt-3">{{ $studentList->links() }}</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/directory-modern.css') }}?v=20260914b">
@endpush
