@extends('layouts.master')
@section('content')
<div class="page-wrapper">
    <div class="content container-fluid dir-page">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title mb-1">Grade Encoding Access</h3>
                    <p class="dir-subtitle">Set the grade entry dates first, then allow teachers to encode grades.</p>
                </div>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif
        @if(isset($errors) && $errors->any())
            <div class="alert alert-danger">{{ $errors->first() }}</div>
        @endif

        <div class="dir-card mb-4">
            <div class="p-3 p-md-4">
                <h5 class="mb-1">Grade entry dates</h5>
                <p class="text-muted small mb-2">Set the opening date and the due date first. Teachers can encode grades only from the opening date through the due date.</p>
                <p class="mb-3"><strong>{{ $windowNote }}</strong></p>
                <form method="POST" action="{{ route('admin.grade-encoding.window') }}" id="encodingWindowForm">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label" for="encodingStartsOn">Opening date</label>
                            <input type="date" class="form-control" id="encodingStartsOn" name="encoding_starts_on" required
                                   min="{{ $startMin }}"
                                   value="{{ old('encoding_starts_on', $startsOn) }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="encodingDueOn">Due date</label>
                            <input type="date" class="form-control" id="encodingDueOn" name="encoding_due_on" required
                                   min="{{ old('encoding_starts_on', $startsOn) ?: $schoolToday }}"
                                   value="{{ old('encoding_due_on', $dueOn) }}">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary dir-btn mt-3">Save dates</button>
                </form>
            </div>
        </div>

        <div class="dir-card mb-4">
            <div class="p-3 p-md-4">
                <h5 class="mb-1">Open grade levels</h5>
                <p class="text-muted small">Teachers can encode grades only for the levels you leave checked. Nursery and Kindergarten use descriptive letters. Grade 1 through Grade 3 also use letters. Grade 4 through Grade 10 use scores from 0 to 100.</p>
                <form method="POST" action="{{ route('admin.grade-encoding.levels') }}">
                    @csrf
                    <div class="row g-2">
                        @foreach($gradeLevels as $grade)
                            @php
                                $checked = $openLevels === null || in_array($grade, $openLevels, true);
                            @endphp
                            <div class="col-6 col-md-4 col-lg-3">
                                <label class="d-flex align-items-center gap-2 mb-0">
                                    <input type="checkbox" name="grade_levels[]" value="{{ $grade }}" {{ $checked ? 'checked' : '' }}>
                                    <span>{{ $grade }}</span>
                                </label>
                            </div>
                        @endforeach
                    </div>
                    <button type="submit" class="btn btn-primary dir-btn mt-3">Save grade levels</button>
                </form>
            </div>
        </div>

        <div class="dir-card">
            <div class="p-3 p-md-4 pb-0">
                <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-3">
                    <div>
                        <h5 class="mb-1">Teachers</h5>
                        <p class="text-muted small mb-0">This list is teachers only. Students, parents, and registrars are not included.</p>
                    </div>
                    <button type="button" class="btn btn-primary dir-btn" data-bs-toggle="modal" data-bs-target="#allowAllTeachersModal" @disabled(! $hasWindow)>
                        Allow all teachers
                    </button>
                </div>
                @unless($hasWindow)
                    <p class="text-muted small">Set the opening date and the due date before allowing teachers.</p>
                @endunless
            </div>
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead>
                        <tr>
                            <th>Teacher</th>
                            <th>Account</th>
                            <th>Grade Encoding Access</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                            @php $allowed = $user->permissions->contains('name', $permission); @endphp
                            <tr>
                                <td>{{ $user->name }}</td>
                                <td>{{ $user->isActiveAccount() ? 'Active' : 'Inactive' }}</td>
                                <td>{{ $allowed ? 'Allowed' : 'Not Allowed' }}</td>
                                <td class="text-end">
                                    @if($allowed)
                                        <form method="POST" action="{{ route('admin.grade-encoding.update', $user) }}">
                                            @csrf
                                            <input type="hidden" name="allow" value="0">
                                            <button type="submit" class="btn btn-sm btn-outline-danger">Revoke</button>
                                        </form>
                                    @else
                                        <form method="POST" action="{{ route('admin.grade-encoding.update', $user) }}">
                                            @csrf
                                            <input type="hidden" name="allow" value="1">
                                            <button type="submit" class="btn btn-sm btn-primary" @disabled(! $hasWindow)>Allow</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4">No teachers are available.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade dir-modal" id="allowAllTeachersModal" tabindex="-1" aria-labelledby="allowAllTeachersTitle" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="allowAllTeachersTitle">Allow all teachers</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="mb-2">Allow every teacher to encode grades?</p>
                <p class="dir-muted mb-0">{{ $users->count() }} {{ $users->count() === 1 ? 'teacher' : 'teachers' }} will be allowed. {{ $windowNote }} Students, parents, and registrars stay off this list.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary dir-btn" data-bs-dismiss="modal">Cancel</button>
                <form method="POST" action="{{ route('admin.grade-encoding.allow-all') }}" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-primary dir-btn">Allow all teachers</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/directory-modern.css') }}?v=20261018b">
@endpush
@push('scripts')
<script>
(function () {
    var start = document.getElementById('encodingStartsOn');
    var due = document.getElementById('encodingDueOn');
    if (!start || !due) return;
    function syncDue() {
        if (!start.value) return;
        due.min = start.value;
        if (due.value && due.value < start.value) due.value = start.value;
    }
    start.addEventListener('change', syncDue);
    syncDue();
})();
</script>
@endpush
