@extends('layouts.enrollment-portal')

@section('title', 'Old Student Enrollment Dashboard')

@section('content')
<div class="ep-card mb-4" style="background:linear-gradient(135deg,#059669,#22C55E);border:none;color:#fff;">
    <div class="ep-card-body py-4">
        <h1 class="ep-page-title" style="color:#fff!important;">Welcome back, {{ $student->first_name }}!</h1>
        <p style="color:rgba(255,255,255,.9)!important;margin:0;">Review your subjects and select your section for {{ $academicYear->name ?? 'this school year' }}.</p>
    </div>
</div>

<div class="ep-card mb-4">
    <div class="ep-card-header">
        <h3><i class="fas fa-user-circle me-2"></i>Student Information</h3>
    </div>
    <div class="ep-card-body">
        <div class="row g-3">
            <div class="col-md-6">
                <p class="mb-2"><span class="text-muted">Name</span><br><strong>{{ $student->first_name }} {{ $student->middle_name }} {{ $student->last_name }}</strong></p>
                <p class="mb-2"><span class="text-muted">Grade Level</span><br><strong>{{ $student->year_level }}</strong></p>
                <p class="mb-0"><span class="text-muted">Email</span><br><strong>{{ $student->email }}</strong></p>
            </div>
            <div class="col-md-6">
                <p class="mb-2"><span class="text-muted">Student ID</span><br><strong>{{ $student->user_id ?? 'N/A' }}</strong></p>
                <p class="mb-2"><span class="text-muted">Academic Year</span><br><strong>{{ $academicYear->name ?? 'Not Set' }}</strong></p>
                <p class="mb-0"><span class="text-muted">Semester</span><br><strong>{{ $semester->name ?? 'Not Set' }}</strong></p>
            </div>
        </div>
    </div>
</div>

<div class="ep-card mb-4">
    <div class="ep-card-header">
        <h3><i class="fas fa-book me-2"></i>Upcoming Subjects — {{ $student->year_level }}</h3>
    </div>
    <div class="ep-card-body">
        @if($subjects->count() > 0)
            <div class="row g-3">
                @foreach($subjects as $subject)
                    @php $subjectSchedules = $schedules->flatten()->where('subject_id', $subject->id); @endphp
                    <div class="col-md-6">
                        <div class="ep-subject-card h-100">
                            <div class="ep-subject-card-title">
                                <i class="fas fa-book-open me-2"></i>{{ $subject->subject_name }}
                            </div>
                            <p class="text-muted small mb-2">ID: {{ $subject->subject_id }}</p>
                            @if($subjectSchedules->count() > 0)
                                <div class="ep-schedule-list">
                                    @foreach($subjectSchedules->take(3) as $schedule)
                                        <div class="ep-schedule-item">
                                            <i class="fas fa-calendar-day me-1"></i>
                                            <strong>{{ ucfirst($schedule->day_of_week) }}</strong>
                                            {{ \Carbon\Carbon::parse($schedule->start_time)->format('g:i A') }} –
                                            {{ \Carbon\Carbon::parse($schedule->end_time)->format('g:i A') }}
                                            @if($schedule->room)
                                                <br><small class="text-muted ms-3"><i class="fas fa-door-open me-1"></i>{{ $schedule->room->room_name }}</small>
                                            @endif
                                            @if($schedule->teacher)
                                                <br><small class="text-muted ms-3"><i class="fas fa-chalkboard-teacher me-1"></i>{{ $schedule->teacher->full_name }}</small>
                                            @endif
                                        </div>
                                    @endforeach
                                    @if($subjectSchedules->count() > 3)
                                        <small class="text-muted">+{{ $subjectSchedules->count() - 3 }} more schedules</small>
                                    @endif
                                </div>
                            @else
                                <p class="text-muted small mb-0">Schedule to be announced</p>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="ep-alert ep-alert-info mb-0">
                <i class="fas fa-info-circle me-2"></i>No subjects found for {{ $student->year_level }}. Please contact the registrar.
            </div>
        @endif
    </div>
</div>

<div class="ep-card mb-4">
    <div class="ep-card-header">
        <h3><i class="fas fa-users me-2"></i>Available Sections — {{ $student->year_level }}</h3>
    </div>
    <div class="ep-card-body">
        <div class="ep-alert ep-alert-info mb-4">
            <i class="fas fa-info-circle me-2"></i>
            <strong>Choose your section</strong> to complete enrollment. Sections are assigned based on your grade level.
        </div>

        @if($availableSections->count() > 0)
            <div class="row g-3">
                @foreach($availableSections as $section)
                    <div class="col-md-6">
                        <div class="ep-section-card {{ $section->is_full ? 'is-full' : '' }}"
                             @if(!$section->is_full) role="button" tabindex="0" data-section-id="{{ $section->id }}" @endif>
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <h5 class="mb-1"><i class="fas fa-chalkboard me-2 text-primary"></i>{{ $section->name }}</h5>
                                    <small class="text-muted">Grade {{ $section->grade_level }}</small>
                                </div>
                                @if($section->is_full)
                                    <span class="ep-chip ep-chip-rejected">Full</span>
                                @else
                                    <span class="ep-chip ep-chip-approved">Available</span>
                                @endif
                            </div>

                            <p class="mb-2 small">
                                <i class="fas fa-user-tie me-1"></i>
                                <strong>Adviser:</strong> {{ $section->adviser?->full_name ?? 'To be assigned' }}
                            </p>
                            <p class="mb-3 small">
                                <i class="fas fa-users me-1"></i>
                                <strong>Capacity:</strong>
                                @if($section->capacity)
                                    {{ $section->available_spots }} / {{ $section->capacity }} spots available
                                @else
                                    {{ $section->available_spots }} spots available
                                @endif
                            </p>

                            @if($section->description)
                                <p class="small text-muted mb-3">{{ $section->description }}</p>
                            @endif

                            @if(!$section->is_full)
                                <button type="button" class="ep-btn ep-btn-success w-100">
                                    <i class="fas fa-check-circle me-2"></i>Select This Section
                                </button>
                            @else
                                <button type="button" class="ep-btn w-100" disabled style="opacity:.6;">
                                    <i class="fas fa-times-circle me-2"></i>Section is Full
                                </button>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="ep-alert ep-alert-warning mb-0">
                <i class="fas fa-exclamation-triangle me-2"></i>
                No sections are currently available for {{ $student->year_level }}. Please contact the registrar.
            </div>
        @endif
    </div>
</div>

<div class="d-flex justify-content-center gap-2 flex-wrap mb-4">
    <form action="{{ route('enrollment.old-student.login') }}" method="GET">
        <button type="submit" class="ep-btn ep-btn-outline"><i class="fas fa-sign-out-alt me-2"></i>Logout</button>
    </form>
    <a href="{{ route('enrollment.portal.index') }}" class="ep-btn ep-btn-outline"><i class="fas fa-home me-2"></i>Back to Portal</a>
</div>
@endsection

@section('scripts')
<script>
function selectSection(sectionId) {
    if (!confirm('Are you sure you want to select this section? This will complete your enrollment.')) return;
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '{{ route("enrollment.old-student.select-section") }}';
    form.innerHTML = '<input type="hidden" name="_token" value="{{ csrf_token() }}">' +
        '<input type="hidden" name="section_id" value="' + sectionId + '">';
    document.body.appendChild(form);
    form.submit();
}

document.querySelectorAll('.ep-section-card[data-section-id]').forEach(card => {
    const id = card.dataset.sectionId;
    card.addEventListener('click', e => {
        if (e.target.closest('button')) return;
        selectSection(id);
    });
    card.addEventListener('keydown', e => {
        if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); selectSection(id); }
    });
    card.querySelector('button')?.addEventListener('click', e => {
        e.stopPropagation();
        selectSection(id);
    });
});
</script>
@endsection
