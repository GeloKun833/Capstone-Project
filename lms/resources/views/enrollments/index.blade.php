@extends('layouts.master')
@section('content')

<div class="page-wrapper">
    <div class="content container-fluid dir-page">
        <div class="page-header">
            <div class="row align-items-start">
                <div class="col">
                    <h3 class="page-title mb-1">Enrollment Management</h3>
                    <p class="dir-subtitle">One row per student, with subjects grouped together.</p>
                </div>
                <div class="col-auto text-end">
                    <ul class="breadcrumb justify-content-end mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Enrollments</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="dir-card dir-filters">
            <form method="GET" action="{{ route('enrollments.index') }}">
                <div class="row g-2 align-items-end">
                    <div class="col-lg-8 col-md-8">
                        <label class="form-label">Search students</label>
                        <input type="text" name="search" class="form-control" placeholder="Name, email, or subject" value="{{ $search ?? '' }}">
                    </div>
                    <div class="col-lg-4 col-md-4 pb-3">
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary dir-btn flex-fill">Search</button>
                            <a href="{{ route('enrollments.index') }}" class="btn btn-outline-secondary dir-btn">Clear</a>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <div class="dir-card">
            <div class="dir-toolbar">
                <div>
                    <h5 class="dir-toolbar-title">Enrolled students</h5>
                    <span class="dir-count mt-1">{{ $paginatedEnrollments->total() }} student{{ $paginatedEnrollments->total() === 1 ? '' : 's' }}</span>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table dir-table mb-0">
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Grade / Section</th>
                            <th>Subjects</th>
                            <th>Academic Year</th>
                            <th>Semester</th>
                            <th>Status</th>
                            <th>Enrolled</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($paginatedEnrollments as $row)
                            @php
                                $photo = $row['student_upload']
                                    ? \Illuminate\Support\Facades\Storage::url('student-photos/'.$row['student_upload'])
                                    : asset('images/photo_defaults.jpg');
                                $status = strtolower((string) $row['status']);
                                $statusClass = match ($status) {
                                    'active' => 'dir-badge--active',
                                    'pending' => 'dir-badge--inactive',
                                    'completed' => 'dir-badge--neutral',
                                    'dropped', 'inactive' => 'dir-badge--disabled',
                                    default => 'dir-badge--neutral',
                                };
                                $subjects = $row['subjects'] ?? collect();
                                $visibleSubjects = $subjects->take(4);
                                $extraSubjects = max(0, $subjects->count() - 4);
                            @endphp
                            <tr>
                                <td>
                                    <div class="dir-person">
                                        <img src="{{ $photo }}" alt="{{ $row['student_name'] }}" onerror="this.onerror=null;this.src='{{ asset('images/photo_defaults.jpg') }}';">
                                        <span>
                                            @if(!empty($row['student_user_id']))
                                                <a href="{{ route('student.sis', $row['student_user_id']) }}" class="dir-person-name">{{ $row['student_name'] }}</a>
                                            @else
                                                <span class="dir-person-name">{{ $row['student_name'] }}</span>
                                            @endif
                                            <span class="dir-person-meta">{{ $row['student_email'] }}</span>
                                        </span>
                                    </div>
                                </td>
                                <td>
                                    @if($row['grade_level'] || ($row['sections'] ?? collect())->isNotEmpty())
                                        <div class="dir-chip-wrap">
                                            @if($row['grade_level'])
                                                <span class="dir-chip">{{ $row['grade_level'] }}</span>
                                            @endif
                                            @foreach(($row['sections'] ?? collect()) as $section)
                                                <span class="dir-chip dir-chip--soft">{{ $section }}</span>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="dir-muted">—</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="dir-chip-wrap">
                                        @forelse($visibleSubjects as $subject)
                                            <span class="dir-chip" title="{{ $subject }}">{{ $subject }}</span>
                                        @empty
                                            @if($row['has_portal'])
                                                <span class="dir-chip dir-chip--soft">Portal application</span>
                                            @else
                                                <span class="dir-muted">No subjects</span>
                                            @endif
                                        @endforelse
                                        @if($extraSubjects > 0)
                                            <span class="dir-chip dir-chip--soft" title="{{ $subjects->implode(', ') }}">+{{ $extraSubjects }} more</span>
                                        @endif
                                        @if($row['has_portal'] && $subjects->isNotEmpty())
                                            <span class="dir-chip dir-chip--soft">Portal</span>
                                        @endif
                                    </div>
                                </td>
                                <td>{{ $row['academic_year'] }}</td>
                                <td>{{ $row['semester'] }}</td>
                                <td>
                                    <span class="dir-badge {{ $statusClass }}">{{ ucfirst($status) }}</span>
                                </td>
                                <td>
                                    {{ $row['enrollment_date'] ? \Carbon\Carbon::parse($row['enrollment_date'])->format('M j, Y') : '—' }}
                                </td>
                                <td class="text-end">
                                    <div class="d-inline-flex gap-1 justify-content-end">
                                        @if(!empty($row['student_user_id']))
                                            <a href="{{ route('student.sis', $row['student_user_id']) }}" class="dir-icon-btn is-success" title="Student record">
                                                <i class="fas fa-id-card"></i>
                                            </a>
                                        @elseif(!empty($row['primary_enrollment_id']))
                                            <a href="{{ route('enrollments.show', $row['primary_enrollment_id']) }}" class="dir-icon-btn" title="View enrollment">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        @endif
                                        @if($row['has_portal'] && !empty($row['portal_id']))
                                            <a href="{{ route('enrollment.registrar.show', $row['portal_id']) }}" class="dir-icon-btn" title="Portal application">
                                                <i class="fas fa-globe"></i>
                                            </a>
                                        @endif
                                        @if(!empty($row['primary_enrollment_id']))
                                            <a href="{{ route('enrollments.edit', $row['primary_enrollment_id']) }}" class="dir-icon-btn" title="Edit enrollment">
                                                <i class="far fa-edit"></i>
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
                                        <strong>No enrollments found</strong>
                                        <div class="small mt-1">Create a user or enroll a student to see them here.</div>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($paginatedEnrollments->hasPages())
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 px-3 py-3">
                    <p class="text-muted mb-0 small">
                        Showing {{ $paginatedEnrollments->firstItem() }}–{{ $paginatedEnrollments->lastItem() }} of {{ $paginatedEnrollments->total() }} students
                    </p>
                    {{ $paginatedEnrollments->links('pagination::bootstrap-4') }}
                </div>
            @endif
        </div>
    </div>
</div>

@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/directory-modern.css') }}?v=20260914b">
@endpush
