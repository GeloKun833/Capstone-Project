@extends('layouts.master')
@section('content')
    <div class="page-wrapper">
        <div class="content container-fluid dir-page">
            <div class="page-header">
                <div class="row align-items-start">
                    <div class="col">
                        <h3 class="page-title mb-1">Students</h3>
                        <p class="dir-subtitle">Search active or archived students and open their records.</p>
                    </div>
                    <div class="col-auto text-end">
                        <ul class="breadcrumb justify-content-end mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active">All Students</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="dir-card dir-filters">
                <form method="GET" action="{{ route('student/list') }}">
                    @if($showingArchived)
                        <input type="hidden" name="archived" value="1">
                    @endif
                    <div class="row g-2 align-items-end">
                        <div class="col-lg-2 col-md-6">
                            <label class="form-label">Student ID</label>
                            <input type="text" name="search_id" class="form-control" placeholder="Search by ID" value="{{ request('search_id') }}">
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <label class="form-label">Name</label>
                            <input type="text" name="search_name" class="form-control" placeholder="Search by name" value="{{ request('search_name') }}">
                        </div>
                        <div class="col-lg-2 col-md-6">
                            <label class="form-label">Class</label>
                            <input type="text" name="search_class" class="form-control" placeholder="Search by class" value="{{ request('search_class') }}">
                        </div>
                        <div class="col-lg-2 col-md-6">
                            <label class="form-label">Year level</label>
                            <input type="text" name="search_year_level" class="form-control" placeholder="Year level" value="{{ request('search_year_level') }}">
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <label class="form-label">Phone</label>
                            <input type="text" name="search_phone" class="form-control" placeholder="Search by phone" value="{{ request('search_phone') }}">
                        </div>
                        <div class="col-12 pb-3">
                            <button type="submit" class="btn btn-primary dir-btn">Search</button>
                            <a href="{{ $showingArchived ? route('student/list').'?archived=1' : route('student/list') }}" class="btn btn-outline-secondary dir-btn">Clear</a>
                        </div>
                    </div>
                </form>
            </div>

            <div class="dir-card">
                <div class="dir-toolbar">
                    <div>
                        <h5 class="dir-toolbar-title">{{ $showingArchived ? 'Archived students' : 'Active students' }}</h5>
                        <span class="dir-count mt-1">{{ method_exists($studentList, 'total') ? $studentList->total() : $studentList->count() }} records</span>
                    </div>
                    <div class="dir-actions">
                        @if($showingArchived)
                            <a href="{{ route('student/list') }}" class="btn btn-outline-secondary dir-btn btn-sm">Show active</a>
                        @else
                            <a href="{{ route('student/list') }}?archived=1" class="btn btn-outline-secondary dir-btn btn-sm">Show archived</a>
                        @endif
                        <div class="dir-toggle" role="group" aria-label="View">
                            <a href="{{ route('student/list') }}{{ $showingArchived ? '?archived=1' : '' }}" class="is-active" title="List view"><i class="fa fa-list"></i></a>
                            <a href="{{ route('student/grid') }}{{ $showingArchived ? '?archived=1' : '' }}" title="Grid view"><i class="fa fa-th"></i></a>
                        </div>
                        <a href="{{ route('student/add/page') }}" class="btn btn-primary dir-btn btn-sm">
                            <i class="fas fa-plus me-1"></i> Add
                        </a>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table dir-table mb-0">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Class</th>
                                <th>DOB</th>
                                <th>Parent</th>
                                <th>Mobile</th>
                                <th>Address</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($studentList as $list)
                                @php
                                    $sName = trim(($list->first_name ?? '').' '.($list->last_name ?? ''));
                                    $sPhoto = $list->upload
                                        ? \Illuminate\Support\Facades\Storage::url('student-photos/'.$list->upload)
                                        : asset('images/photo_defaults.jpg');
                                    $sId = $list->admission_id ?: ('STD'.$list->id);
                                    $klass = trim(($list->year_level ?: $list->class).' '.($list->section ?? ''));
                                    try {
                                        $dob = $list->date_of_birth ? \Carbon\Carbon::parse($list->date_of_birth)->format('M j, Y') : '—';
                                    } catch (\Exception $e) {
                                        $dob = $list->date_of_birth ?: '—';
                                    }
                                @endphp
                                <tr>
                                    <td hidden class="id">{{ $list->id }}</td>
                                    <td hidden class="avatar">{{ $list->upload }}</td>
                                    <td class="text-muted">{{ $sId }}</td>
                                    <td>
                                        <div class="dir-person">
                                            <img src="{{ $sPhoto }}" alt="{{ $sName }}" onerror="this.onerror=null;this.src='{{ asset('images/photo_defaults.jpg') }}';">
                                            <span>
                                                @if(!empty($list->user_id))
                                                    <a href="{{ route('student.sis', $list->user_id) }}" class="dir-person-name">{{ $sName ?: 'Unnamed student' }}</a>
                                                @else
                                                    <span class="dir-person-name">{{ $sName ?: 'Unnamed student' }}</span>
                                                @endif
                                                <span class="dir-person-meta">Student</span>
                                            </span>
                                        </div>
                                    </td>
                                    <td>
                                        @if($klass)
                                            <span class="dir-chip">{{ $klass }}</span>
                                        @else
                                            <span class="dir-muted">—</span>
                                        @endif
                                    </td>
                                    <td>{{ $dob }}</td>
                                    <td>{{ $list->parent_name ?: '—' }}</td>
                                    <td>{{ $list->phone_number ?: '—' }}</td>
                                    <td><span class="dir-muted">{{ $list->address ?: '—' }}</span></td>
                                    <td class="text-end">
                                        <div class="d-inline-flex gap-1 justify-content-end">
                                            @if(!empty($list->user_id))
                                                <a href="{{ route('student.sis', $list->user_id) }}" class="dir-icon-btn is-success" title="View SIS">
                                                    <i class="fas fa-id-card"></i>
                                                </a>
                                            @endif
                                            <a href="{{ url('student/edit/'.$list->id) }}" class="dir-icon-btn" title="Edit student">
                                                <i class="far fa-edit"></i>
                                            </a>
                                            @if($showingArchived)
                                                <form action="{{ url('student/restore/'.$list->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="dir-icon-btn is-success" title="Restore">
                                                        <i class="fas fa-undo"></i>
                                                    </button>
                                                </form>
                                            @else
                                                <a class="dir-icon-btn is-danger student_delete" data-bs-toggle="modal" data-bs-target="#studentUser" title="Delete student">
                                                    <i class="far fa-trash-alt"></i>
                                                </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8">
                                        <div class="dir-empty">
                                            <div><i class="fas fa-user-graduate"></i></div>
                                            <strong>No students found</strong>
                                            <div class="small mt-1">Try a different search or add a new student.</div>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if(method_exists($studentList, 'links'))
                    <div class="d-flex justify-content-center py-3">{{ $studentList->links() }}</div>
                @endif
            </div>
        </div>
    </div>

    <div class="modal fade dir-modal" id="studentUser" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title">Delete student?</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body pt-2">
                    <p class="text-muted mb-0">This will archive or remove the student record. You can restore archived students later.</p>
                </div>
                <div class="modal-footer border-0">
                    <form action="{{ route('student/delete') }}" method="POST" class="d-flex gap-2 w-100 justify-content-end">
                        @csrf
                        <input type="hidden" name="id" class="e_id" value="">
                        <input type="hidden" name="avatar" class="e_avatar" value="">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @section('script')
    <script>
        $(document).on('click', '.student_delete', function () {
            var _this = $(this).closest('tr');
            $('.e_id').val(_this.find('.id').text());
            $('.e_avatar').val(_this.find('.avatar').text());
        });
    </script>
    @endsection

@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/directory-modern.css') }}?v=20260914b">
@endpush
