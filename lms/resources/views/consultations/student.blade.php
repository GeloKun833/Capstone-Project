@extends('layouts.master')
@section('content')
<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <h3 class="page-title">Teacher Consultations</h3>
            <p class="text-muted mb-0">Request a meeting with a teacher assigned to one of your current classes.</p>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <div class="row g-3">
            <div class="col-lg-5">
                <div class="card h-100">
                    <div class="card-header"><h5 class="mb-0">Request a Consultation</h5></div>
                    <div class="card-body">
                        @if($subjects->isEmpty())
                            <p class="text-muted mb-0">No teacher is assigned to your current subject and section yet.</p>
                        @else
                            <form method="POST" action="{{ route('student.consultations.store') }}">
                                @csrf
                                <div class="mb-3">
                                    <label for="consultation_subject" class="form-label">Subject</label>
                                    <select id="consultation_subject" name="subject_id" class="form-control @error('subject_id') is-invalid @enderror" required>
                                        <option value="">Choose a subject</option>
                                        @foreach($subjects as $subject)
                                            <option value="{{ $subject->id }}" @selected((string) old('subject_id') === (string) $subject->id)>
                                                {{ $subject->subject_name }}{{ $subject->class ? ' · '.$subject->class : '' }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('subject_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="mb-3">
                                    <label for="consultation_teacher" class="form-label">Teacher</label>
                                    <select id="consultation_teacher" name="teacher_id" class="form-control @error('teacher_id') is-invalid @enderror" required disabled>
                                        <option value="">Choose a subject first</option>
                                    </select>
                                    @error('teacher_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="mb-3">
                                    <label for="requested_start_at" class="form-label">Preferred date and time</label>
                                    <input type="datetime-local" id="requested_start_at" name="requested_start_at"
                                           class="form-control @error('requested_start_at') is-invalid @enderror"
                                           min="{{ $minimumRequestTime->format('Y-m-d\TH:i') }}"
                                           value="{{ old('requested_start_at', $minimumRequestTime->format('Y-m-d\TH:i')) }}" required>
                                    @error('requested_start_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    <small class="text-muted">School time: {{ config('app.school_timezone', 'Asia/Manila') }}. Your teacher will confirm the appointment.</small>
                                </div>
                                <div class="mb-3">
                                    <label for="duration_minutes" class="form-label">Requested duration</label>
                                    <select id="duration_minutes" name="duration_minutes" class="form-control" required>
                                        @foreach([15, 30, 45, 60] as $minutes)
                                            <option value="{{ $minutes }}" @selected((int) old('duration_minutes', 30) === $minutes)>{{ $minutes }} minutes</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="student_message" class="form-label">What would you like to discuss?</label>
                                    <textarea id="student_message" name="student_message" rows="3" maxlength="1500"
                                              class="form-control @error('student_message') is-invalid @enderror"
                                              placeholder="Add a short note for your teacher">{{ old('student_message') }}</textarea>
                                    @error('student_message')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane me-1"></i>Send Request</button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-lg-7">
                <div class="card">
                    <div class="card-header"><h5 class="mb-0">My Requests and Appointments</h5></div>
                    <div class="card-body">
                        @forelse($requests as $consultation)
                            <div class="border rounded p-3 mb-3">
                                <div class="d-flex justify-content-between align-items-start gap-2">
                                    <div>
                                        <h6 class="mb-1">{{ $consultation->subject->subject_name }} · {{ $consultation->teacher->full_name }}</h6>
                                        <div class="small text-muted">Requested {{ $consultation->requested_start_at->format('M j, Y g:i A') }}–{{ $consultation->requested_end_at->format('g:i A') }}</div>
                                    </div>
                                    <span class="badge bg-{{ $consultation->status === 'approved' ? 'success' : ($consultation->status === 'pending' ? 'warning' : 'secondary') }}">{{ ucfirst($consultation->status) }}</span>
                                </div>
                                @if($consultation->status === 'approved')
                                    <p class="small text-success mt-2 mb-1"><strong>Appointment:</strong> {{ $consultation->scheduled_start_at->format('M j, Y g:i A') }}–{{ $consultation->scheduled_end_at->format('g:i A') }}</p>
                                @endif
                                @if($consultation->student_message)<p class="small mb-1">{{ $consultation->student_message }}</p>@endif
                                @if($consultation->teacher_response)<p class="small text-muted mb-1"><strong>Teacher:</strong> {{ $consultation->teacher_response }}</p>@endif
                                @if($consultation->status === 'pending')
                                    <form method="POST" action="{{ route('student.consultations.cancel', $consultation) }}" class="mt-2">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-secondary">Cancel Request</button>
                                    </form>
                                @endif
                            </div>
                        @empty
                            <p class="text-muted mb-0">You haven’t requested a consultation yet.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const subjectSelect = document.getElementById('consultation_subject');
    const teacherSelect = document.getElementById('consultation_teacher');
    if (!subjectSelect || !teacherSelect) return;

    const teachersBySubject = @json($subjects->mapWithKeys(fn ($subject) => [(string) $subject->id => $subject->consultationTeachers->map(fn ($teacher) => ['id' => $teacher->id, 'name' => $teacher->full_name])->values()]));
    const oldTeacherId = @json(old('teacher_id'));

    function updateTeachers() {
        const choices = teachersBySubject[subjectSelect.value] || [];
        teacherSelect.replaceChildren(new Option(choices.length ? 'Choose a teacher' : 'No assigned teacher', ''));
        choices.forEach(function (teacher) {
            const option = new Option(teacher.name, teacher.id, false, String(teacher.id) === String(oldTeacherId));
            teacherSelect.add(option);
        });
        teacherSelect.disabled = choices.length === 0;
    }

    subjectSelect.addEventListener('change', updateTeachers);
    updateTeachers();
});
</script>
@endsection