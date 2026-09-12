@extends('layouts.master')
@section('content')

<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">Learner&rsquo;s Observed Values</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Observed Values</li>
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

        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-filter me-2"></i>Select Section, Academic Year &amp; Student</h5>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('teacher.grading.observed-values') }}" class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Section *</label>
                        <select name="section_id" class="form-control form-select" required onchange="this.form.submit()">
                            <option value="">-- Select Section --</option>
                            @foreach($sections as $section)
                                <option value="{{ $section->id }}" {{ (string) $sectionId === (string) $section->id ? 'selected' : '' }}>
                                    {{ $section->name }} ({{ $section->grade_level ?? 'N/A' }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Academic Year *</label>
                        <select name="academic_year_id" class="form-control form-select" required onchange="this.form.submit()">
                            @foreach($academicYears as $year)
                                <option value="{{ $year->id }}" {{ (string) $academicYearId === (string) $year->id ? 'selected' : '' }}>
                                    {{ $year->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Student *</label>
                        <select name="student_id" class="form-control form-select" @if($students->isEmpty()) disabled @endif onchange="this.form.submit()">
                            <option value="">-- Select Student --</option>
                            @foreach($students as $student)
                                <option value="{{ $student->id }}" {{ (string) $studentId === (string) $student->id ? 'selected' : '' }}>
                                    {{ $student->last_name }}, {{ $student->first_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </form>
            </div>
        </div>

        @if($studentId && $indicators->isNotEmpty())
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0 text-uppercase">Report on Learner&rsquo;s Observed Values</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-3">
                        Marking: <strong>AO</strong> = Always Observed &nbsp;|&nbsp;
                        <strong>SO</strong> = Sometimes Observed &nbsp;|&nbsp;
                        <strong>RO</strong> = Rarely Observed
                    </p>

                    <form method="POST" action="{{ route('teacher.grading.observed-values.store') }}">
                        @csrf
                        <input type="hidden" name="section_id" value="{{ $sectionId }}">
                        <input type="hidden" name="academic_year_id" value="{{ $academicYearId }}">
                        <input type="hidden" name="student_id" value="{{ $studentId }}">

                        <div class="table-responsive">
                            <table class="table table-bordered observed-values-table">
                                <thead>
                                    <tr>
                                        <th style="width: 18%;">Core Values</th>
                                        <th>Behavior Statements</th>
                                        <th class="text-center" style="width: 10%;">1</th>
                                        <th class="text-center" style="width: 10%;">2</th>
                                        <th class="text-center" style="width: 10%;">3</th>
                                        <th class="text-center" style="width: 10%;">4</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php $rowIndex = 0; @endphp
                                    @foreach($indicators as $coreValue => $items)
                                        @foreach($items as $i => $indicator)
                                            @php
                                                $rating = $ratings->get($indicator->id);
                                                $rowspan = $items->count();
                                            @endphp
                                            <tr>
                                                @if($i === 0)
                                                    <td rowspan="{{ $rowspan }}" class="fw-bold align-middle">{{ $coreValue }}</td>
                                                @endif
                                                <td>
                                                    {{ $indicator->statement }}
                                                    <input type="hidden" name="ratings[{{ $rowIndex }}][indicator_id]" value="{{ $indicator->id }}">
                                                </td>
                                                @foreach([1, 2, 3, 4] as $q)
                                                    @php $field = 'quarter_' . $q; @endphp
                                                    <td>
                                                        <select class="form-control form-select form-select-sm" name="ratings[{{ $rowIndex }}][{{ $field }}]">
                                                            <option value="">—</option>
                                                            @foreach(['AO', 'SO', 'RO'] as $mark)
                                                                <option value="{{ $mark }}" {{ optional($rating)->{$field} === $mark ? 'selected' : '' }}>{{ $mark }}</option>
                                                            @endforeach
                                                        </select>
                                                    </td>
                                                @endforeach
                                            </tr>
                                            @php $rowIndex++; @endphp
                                        @endforeach
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="text-end mt-3">
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="fas fa-save me-2"></i>Save Observed Values
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @elseif($sectionId && $students->isEmpty())
            <div class="alert alert-warning">No students found in this section.</div>
        @elseif($sectionId && ! $studentId)
            <div class="alert alert-info">Select a student to enter observed values.</div>
        @endif
    </div>
</div>

@push('styles')
<style>
.observed-values-table th,
.observed-values-table td {
    border: 1px solid #212529;
    vertical-align: middle;
}
.observed-values-table thead th {
    background: #f8f9fa;
    text-align: center;
}
</style>
@endpush

@endsection
