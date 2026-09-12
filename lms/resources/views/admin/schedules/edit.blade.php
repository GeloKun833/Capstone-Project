@extends('layouts.master')
@section('content')

<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">Edit Class Schedule</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.schedules.index') }}">Schedules</a></li>
                        <li class="breadcrumb-item active">Edit Schedule</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-body py-3">
                <div class="d-flex flex-wrap gap-2 align-items-center schedule-steps">
                    <span class="badge rounded-pill bg-primary step-badge" data-step="1">1. Teacher</span>
                    <i class="fas fa-chevron-right text-muted"></i>
                    <span class="badge rounded-pill bg-primary step-badge" data-step="2">2. Class Information</span>
                    <i class="fas fa-chevron-right text-muted"></i>
                    <span class="badge rounded-pill bg-primary step-badge" data-step="3">3. Schedule Details</span>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <form action="{{ route('admin.schedules.update', $schedule) }}" method="POST" id="scheduleForm">
                    @csrf
                    @method('PUT')

                    <div class="row" id="step-teacher">
                        <div class="col-12">
                            <h5 class="form-title"><span>Step 1 — Select Teacher</span></h5>
                        </div>
                        <div class="col-12 col-md-8 col-lg-6">
                            <div class="form-group local-forms">
                                <label>Teacher <span class="login-danger">*</span></label>
                                <select class="form-control" name="teacher_id" id="teacher_id" required>
                                    <option value="">Select Teacher</option>
                                    @foreach($teachers as $teacher)
                                        <option value="{{ $teacher->id }}" {{ (int) old('teacher_id', $schedule->teacher_id) === (int) $teacher->id ? 'selected' : '' }}>
                                            {{ $teacher->full_name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('teacher_id')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                                <div id="teacher-assign-hint" class="small text-muted mt-1"></div>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-2" id="step-class">
                        <div class="col-12">
                            <h5 class="form-title"><span>Step 2 — Class Information</span></h5>
                            <p class="text-muted small">Updated from the teacher’s current section/subject assignments.</p>
                        </div>

                        <div class="col-12 col-sm-6">
                            <div class="form-group local-forms">
                                <label>Section <span class="login-danger">*</span></label>
                                <select class="form-control" name="section_id" id="section_id" required>
                                    <option value="">Select Section</option>
                                </select>
                                @error('section_id')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="col-12 col-sm-6">
                            <div class="form-group local-forms">
                                <label>Subject <span class="login-danger">*</span></label>
                                <select class="form-control" name="subject_id" id="subject_id" required>
                                    <option value="">Select Subject</option>
                                </select>
                                @error('subject_id')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                                <small class="text-muted" id="subject-filter-hint"></small>
                            </div>
                        </div>

                        <div class="col-12 col-sm-6">
                            <div class="form-group local-forms">
                                <label>Room</label>
                                <select class="form-control" name="room_id" id="room_id">
                                    <option value="">Select Room (Optional)</option>
                                    @foreach($rooms as $room)
                                        <option value="{{ $room->id }}" {{ (int) old('room_id', $schedule->room_id) === (int) $room->id ? 'selected' : '' }}>
                                            {{ $room->room_name }}@if($room->room_type) — {{ $room->room_type }}@endif
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="alert alert-light border py-2 mb-0" id="class-info-summary"></div>
                        </div>
                    </div>

                    <div class="row mt-3" id="step-schedule">
                        <div class="col-12">
                            <h5 class="form-title"><span>Step 3 — Schedule Details &amp; Notes</span></h5>
                        </div>

                        <div class="col-12 col-sm-4">
                            <div class="form-group local-forms">
                                <label>Day of Week <span class="login-danger">*</span></label>
                                <select class="form-control" name="day_of_week" required>
                                    <option value="">Select Day</option>
                                    @foreach(['monday','tuesday','wednesday','thursday','friday','saturday','sunday'] as $day)
                                        <option value="{{ $day }}" {{ old('day_of_week', $schedule->day_of_week) == $day ? 'selected' : '' }}>{{ ucfirst($day) }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-12 col-sm-4">
                            <div class="form-group local-forms">
                                <label>Start Time <span class="login-danger">*</span></label>
                                <input type="time" class="form-control" name="start_time"
                                       value="{{ old('start_time', \Carbon\Carbon::parse($schedule->start_time)->format('H:i')) }}" required>
                            </div>
                        </div>

                        <div class="col-12 col-sm-4">
                            <div class="form-group local-forms">
                                <label>End Time <span class="login-danger">*</span></label>
                                <input type="time" class="form-control" name="end_time"
                                       value="{{ old('end_time', \Carbon\Carbon::parse($schedule->end_time)->format('H:i')) }}" required>
                            </div>
                        </div>

                        <div class="col-12 col-sm-4">
                            <div class="form-group local-forms">
                                <label>Class Type <span class="login-danger">*</span></label>
                                <select class="form-control" name="class_type" required>
                                    <option value="lecture" {{ old('class_type', $schedule->class_type) == 'lecture' ? 'selected' : '' }}>Regular Class</option>
                                    <option value="laboratory" {{ old('class_type', $schedule->class_type) == 'laboratory' ? 'selected' : '' }}>Laboratory</option>
                                    <option value="tutorial" {{ old('class_type', $schedule->class_type) == 'tutorial' ? 'selected' : '' }}>Activity / Tutorial</option>
                                    <option value="exam" {{ old('class_type', $schedule->class_type) == 'exam' ? 'selected' : '' }}>Exam</option>
                                    <option value="other" {{ old('class_type', $schedule->class_type) == 'other' ? 'selected' : '' }}>Other</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-12 col-sm-4">
                            <div class="form-group local-forms">
                                <label>Color</label>
                                <input type="color" class="form-control" name="color" value="{{ old('color', $schedule->color ?: '#3d5ee1') }}" style="height: 45px;">
                            </div>
                        </div>

                        <div class="col-12 col-sm-4">
                            <div class="form-group local-forms">
                                <label>Status</label>
                                <select class="form-control" name="is_active">
                                    <option value="1" {{ old('is_active', $schedule->is_active) == 1 ? 'selected' : '' }}>Active</option>
                                    <option value="0" {{ old('is_active', $schedule->is_active) == 0 ? 'selected' : '' }}>Inactive</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="form-group local-forms">
                                <label>Notes</label>
                                <textarea class="form-control" name="notes" rows="3">{{ old('notes', $schedule->notes) }}</textarea>
                            </div>
                        </div>

                        <div class="col-12">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Update Schedule
                            </button>
                            <a href="{{ route('admin.schedules.index') }}" class="btn btn-secondary">Cancel</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function () {
    const assignmentsUrl = @json(url('/admin/schedules/teacher'));
    const initialTeacher = @json((int) old('teacher_id', $schedule->teacher_id));
    const initialSection = @json((int) old('section_id', $schedule->section_id));
    const initialSubject = @json((int) old('subject_id', $schedule->subject_id));

    let allSubjects = [];
    let allSections = [];

    const teacherSelect = document.getElementById('teacher_id');
    const sectionSelect = document.getElementById('section_id');
    const subjectSelect = document.getElementById('subject_id');
    const hint = document.getElementById('teacher-assign-hint');
    const subjectHint = document.getElementById('subject-filter-hint');
    const summary = document.getElementById('class-info-summary');

    function fillSections(selectedId) {
        sectionSelect.innerHTML = '<option value="">Select Section</option>';
        allSections.forEach(function (sec) {
            const opt = document.createElement('option');
            opt.value = sec.id;
            opt.textContent = sec.label;
            if (String(selectedId) === String(sec.id)) opt.selected = true;
            sectionSelect.appendChild(opt);
        });
        // Keep current schedule section visible even if unassigned later
        if (selectedId && ![...sectionSelect.options].some(function (o) { return String(o.value) === String(selectedId); })) {
            const opt = document.createElement('option');
            opt.value = selectedId;
            opt.textContent = @json(($schedule->section->name ?? 'Current') . ' (' . ($schedule->section->grade_level ?? 'N/A') . ')') + ' — current';
            opt.selected = true;
            sectionSelect.appendChild(opt);
        }
    }

    function subjectsForSection(sectionId) {
        if (!sectionId) return allSubjects;
        const sec = allSections.find(function (s) { return String(s.id) === String(sectionId); });
        const grade = sec ? (sec.grade_level || '') : '';
        if (!grade) return allSubjects;
        const matched = allSubjects.filter(function (sub) {
            return !sub.class || String(sub.class) === String(grade);
        });
        return matched.length ? matched : allSubjects;
    }

    function fillSubjects(sectionId, selectedId) {
        const list = subjectsForSection(sectionId).slice();
        subjectSelect.innerHTML = '<option value="">Select Subject</option>';
        list.forEach(function (sub) {
            const opt = document.createElement('option');
            opt.value = sub.id;
            opt.textContent = sub.label;
            if (String(selectedId) === String(sub.id)) opt.selected = true;
            subjectSelect.appendChild(opt);
        });
        if (selectedId && ![...subjectSelect.options].some(function (o) { return String(o.value) === String(selectedId); })) {
            const opt = document.createElement('option');
            opt.value = selectedId;
            opt.textContent = @json(($schedule->subject->subject_name ?? 'Current') . ' (' . ($schedule->subject->class ?? 'N/A') . ')') + ' — current';
            opt.selected = true;
            subjectSelect.appendChild(opt);
        }
        if (subjectHint) {
            subjectHint.textContent = list.length + ' subject(s) available for this teacher/section';
        }
    }

    function updateSummary() {
        const teacherName = teacherSelect.options[teacherSelect.selectedIndex]?.text || '';
        const sectionName = sectionSelect.options[sectionSelect.selectedIndex]?.text || '';
        const subjectName = subjectSelect.options[subjectSelect.selectedIndex]?.text || '';
        summary.innerHTML = '<strong>Class:</strong> ' + subjectName +
            ' &nbsp;|&nbsp; <strong>Section:</strong> ' + sectionName +
            ' &nbsp;|&nbsp; <strong>Teacher:</strong> ' + teacherName;
    }

    function loadTeacher(teacherId, preselectSection, preselectSubject) {
        if (!teacherId) return;
        hint.textContent = 'Loading assignments…';
        fetch(assignmentsUrl + '/' + teacherId + '/assignments', {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            allSections = data.sections || [];
            allSubjects = data.subjects || [];
            hint.textContent = (allSections.length + ' section(s), ' + allSubjects.length + ' subject(s) assigned');
            fillSections(preselectSection || '');
            fillSubjects(preselectSection || '', preselectSubject || '');
            updateSummary();
        })
        .catch(function () {
            hint.innerHTML = '<span class="text-danger">Failed to load teacher assignments.</span>';
        });
    }

    teacherSelect.addEventListener('change', function () {
        loadTeacher(this.value);
    });
    sectionSelect.addEventListener('change', function () {
        fillSubjects(this.value, '');
        updateSummary();
    });
    subjectSelect.addEventListener('change', updateSummary);

    loadTeacher(initialTeacher, initialSection, initialSubject);
})();
</script>
@endpush
@endsection
