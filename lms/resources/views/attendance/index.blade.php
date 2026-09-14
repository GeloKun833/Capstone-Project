@extends('layouts.master')
@section('content')

<div class="page-wrapper">
    <div class="content container-fluid dir-page att-page">
        <div class="page-header">
            <div class="row align-items-start">
                <div class="col">
                    <h3 class="page-title mb-1">Attendance</h3>
                    <p class="dir-subtitle">Choose a class and date, then mark who is present or absent.</p>
                </div>
                <div class="col-auto text-end">
                    <ul class="breadcrumb justify-content-end mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Attendance</li>
                    </ul>
                </div>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="att-card">
            <div class="att-card-head">
                <h5>1. Select class</h5>
                <p>Pick the section and subject you are teaching, then the date.</p>
            </div>
            <form method="GET" action="{{ route('attendance.index') }}" class="att-pick" id="attFilterForm">
                <div class="row g-2 align-items-end">
                    <div class="col-lg-6">
                        <label class="form-label" for="class">Class</label>
                        <select class="form-control" name="class" id="class" required>
                            <option value="">Select a class</option>
                            @foreach($classes as $class)
                                <option value="{{ $class['key'] }}" {{ $selectedKey === $class['key'] ? 'selected' : '' }}>
                                    {{ $class['label'] }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-3">
                        <label class="form-label" for="date">Date</label>
                        <input type="date" class="form-control" name="date" id="date" value="{{ $date }}">
                    </div>
                    <div class="col-lg-3">
                        <button type="submit" class="btn btn-primary dir-btn w-100">Show students</button>
                    </div>
                </div>
            </form>
        </div>

        @if($classes->isEmpty())
            <div class="att-card">
                <div class="dir-empty">
                    <i class="fas fa-chalkboard-teacher d-block"></i>
                    <h5 class="mt-2 mb-1">No classes assigned</h5>
                    <p class="mb-0">Ask Admin to assign you a class schedule first.</p>
                </div>
            </div>
        @elseif(!$ready)
            <div class="att-card">
                <div class="dir-empty">
                    <i class="fas fa-user-check d-block"></i>
                    <h5 class="mt-2 mb-1">Choose a class to begin</h5>
                    <p class="mb-0">Select your class and date above, then mark attendance.</p>
                </div>
            </div>
        @else
            <form method="POST" action="{{ route('attendance.store') }}" id="attendanceForm">
                @csrf
                <input type="hidden" name="section_id" value="{{ $sectionId }}">
                <input type="hidden" name="subject_id" value="{{ $subjectId }}">
                <input type="hidden" name="date" value="{{ $date }}">

                <div class="att-card">
                    <div class="att-card-head att-card-head-row">
                        <div>
                            <h5>2. Mark attendance</h5>
                            <p>{{ $selectedSection }} · {{ $selectedSubject }} · {{ \Carbon\Carbon::parse($date)->format('M j, Y') }} · {{ $students->count() }} students</p>
                        </div>
                        <div class="d-flex gap-2 flex-wrap">
                            <button type="button" class="btn btn-outline-secondary dir-btn" id="markAllPresent">Mark all present</button>
                            <button type="submit" class="btn btn-primary dir-btn">
                                <i class="fas fa-save me-1"></i> Save
                            </button>
                        </div>
                    </div>

                    @if($students->isEmpty())
                        <div class="dir-empty">
                            <i class="fas fa-user-graduate d-block"></i>
                            <h5 class="mt-2 mb-1">No students in this section</h5>
                            <p class="mb-0">There are no students assigned to {{ $selectedSection }}.</p>
                        </div>
                    @else
                        <div class="att-list">
                            @foreach($students as $student)
                                @php $status = $existing[$student->id]['status'] ?? ''; @endphp
                                <div class="att-row">
                                    <div class="att-name">
                                        <strong>{{ $student->last_name }}, {{ $student->first_name }}</strong>
                                    </div>
                                    <div class="att-choices">
                                        <label class="att-choice is-present">
                                            <input type="radio" name="attendance[{{ $student->id }}][status]" value="present" {{ $status === 'present' ? 'checked' : '' }} required>
                                            Present
                                        </label>
                                        <label class="att-choice is-absent">
                                            <input type="radio" name="attendance[{{ $student->id }}][status]" value="absent" {{ $status === 'absent' ? 'checked' : '' }} required>
                                            Absent
                                        </label>
                                    </div>
                                    <input type="text" class="form-control att-note" name="attendance[{{ $student->id }}][remarks]"
                                           value="{{ $existing[$student->id]['remarks'] ?? '' }}" placeholder="Note (optional)">
                                </div>
                            @endforeach
                        </div>
                        <div class="att-footer">
                            <button type="submit" class="btn btn-primary dir-btn">
                                <i class="fas fa-save me-1"></i> Save attendance
                            </button>
                        </div>
                    @endif
                </div>
            </form>

            @if($students->isNotEmpty())
                <div class="att-card">
                    <div class="att-card-head">
                        <h5>This month</h5>
                        <p>{{ \Carbon\Carbon::parse($date)->format('F Y') }} totals for this class.</p>
                    </div>
                    <div class="table-responsive">
                        <table class="table dir-table mb-0">
                            <thead>
                                <tr>
                                    <th>Student</th>
                                    <th class="text-center">Present</th>
                                    <th class="text-center">Absent</th>
                                    <th class="text-center">Rate</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($students as $student)
                                    @php $row = $summary[$student->id] ?? ['present'=>0,'absent'=>0,'percentage'=>0]; @endphp
                                    <tr>
                                        <td>{{ $student->last_name }}, {{ $student->first_name }}</td>
                                        <td class="text-center">{{ $row['present'] }}</td>
                                        <td class="text-center">{{ $row['absent'] }}</td>
                                        <td class="text-center">
                                            <span class="dir-badge {{ ($row['percentage'] ?? 0) >= 90 ? 'dir-badge--active' : (($row['percentage'] ?? 0) >= 75 ? 'dir-badge--inactive' : 'dir-badge--disabled') }}">
                                                {{ $row['percentage'] }}%
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        @endif
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/directory-modern.css') }}?v=20260914m">
<style>
.att-page .page-header { margin-bottom: 0.85rem; }
.att-card {
    background: #fff;
    border: 1px solid #e8eef7;
    border-radius: 18px;
    box-shadow: 0 14px 32px rgba(79, 114, 205, 0.05);
    margin-bottom: 0.9rem;
    overflow: hidden;
}
.att-card-head { padding: 1rem 1.15rem 0.35rem; }
.att-card-head h5 { margin: 0; font-size: 1.02rem; font-weight: 750; color: #1e293b; }
.att-card-head p { margin: 0.2rem 0 0; font-size: 0.8rem; color: #94a3b8; }
.att-card-head-row {
    display: flex;
    justify-content: space-between;
    gap: 0.75rem;
    flex-wrap: wrap;
    align-items: flex-start;
    padding-bottom: 0.75rem;
}
.att-pick { padding: 0 1.15rem 1.1rem; }
.att-pick .form-label {
    font-size: 0.72rem;
    font-weight: 700;
    letter-spacing: 0.04em;
    text-transform: uppercase;
    color: #94a3b8;
}
.att-pick .form-control {
    min-height: 42px;
    border-radius: 10px;
}
.att-list { padding: 0.35rem 0.7rem 0.7rem; }
.att-row {
    display: grid;
    grid-template-columns: minmax(140px, 1.2fr) auto minmax(140px, 1fr);
    gap: 0.75rem;
    align-items: center;
    padding: 0.7rem 0.75rem;
    border: 1px solid #edf1f7;
    border-radius: 14px;
    margin-bottom: 0.5rem;
}
.att-name strong { color: #1e293b; }
.att-choices { display: flex; gap: 0.4rem; }
.att-choice {
    min-width: 96px;
    text-align: center;
    padding: 0.45rem 0.7rem;
    border-radius: 999px;
    border: 1px solid #e2e8f0;
    background: #fff;
    font-size: 0.8rem;
    font-weight: 700;
    color: #64748b;
    cursor: pointer;
    margin: 0;
}
.att-choice input { display: none; }
.att-choice.is-present:has(input:checked) {
    background: #dcfce7;
    border-color: #86efac;
    color: #166534;
}
.att-choice.is-absent:has(input:checked) {
    background: #fee2e2;
    border-color: #fca5a5;
    color: #991b1b;
}
.att-note { border-radius: 10px; min-height: 40px; }
.att-footer {
    display: flex;
    justify-content: flex-end;
    padding: 0.85rem 1.15rem;
    border-top: 1px solid #eef2f7;
}
@media (max-width: 767px) {
    .att-row { grid-template-columns: 1fr; }
}
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function () {
    $('#class, #date').on('change', function () {
        if ($('#class').val()) {
            $('#attFilterForm').submit();
        }
    });

    $('#markAllPresent').on('click', function () {
        $('input[name$="[status]"][value="present"]').prop('checked', true);
    });

    $('#attendanceForm').on('submit', function (e) {
        const total = $('.att-row').length;
        const marked = $('input[name$="[status]"]:checked').length;
        if (total > 0 && marked < total) {
            e.preventDefault();
            alert('Mark Present or Absent for every student, or use Mark all present.');
            return false;
        }
    });
});
</script>
@endpush
