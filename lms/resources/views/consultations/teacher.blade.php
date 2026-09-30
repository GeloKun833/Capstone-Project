@extends('layouts.master')
@section('content')
<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <h3 class="page-title">Consultation Requests</h3>
            <p class="text-muted mb-0">Review student requests and schedule or reschedule appointments.</p>
        </div>

        @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
        @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif

        @forelse($requests as $consultation)
            <div class="card mb-3">
                <div class="card-header d-flex justify-content-between align-items-center gap-2">
                    <div>
                        <h5 class="mb-0">{{ $consultation->student->full_name }} · {{ $consultation->subject->subject_name }}</h5>
                        <small class="text-muted">Requested {{ $consultation->requested_start_at->format('M j, Y g:i A') }}–{{ $consultation->requested_end_at->format('g:i A') }}</small>
                    </div>
                    <span class="badge bg-{{ $consultation->status === 'approved' ? 'success' : ($consultation->status === 'pending' ? 'warning' : 'secondary') }}">{{ ucfirst($consultation->status) }}</span>
                </div>
                <div class="card-body">
                    @if($consultation->student_message)<p><strong>Student note:</strong> {{ $consultation->student_message }}</p>@endif
                    @if($consultation->teacher_response)<p><strong>Your response:</strong> {{ $consultation->teacher_response }}</p>@endif

                    @if(in_array($consultation->status, ['pending', 'approved'], true))
                        @php
                            $appointmentStart = $consultation->scheduled_start_at ?: $consultation->requested_start_at;
                            $appointmentEnd = $consultation->scheduled_end_at ?: $consultation->requested_end_at;
                        @endphp
                        <form method="POST" action="{{ route('teacher.consultations.respond', $consultation) }}">
                            @csrf
                            @method('PUT')
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label" for="scheduled_start_{{ $consultation->id }}">Appointment start</label>
                                    <input type="datetime-local" class="form-control @error('scheduled_start_at') is-invalid @enderror"
                                           id="scheduled_start_{{ $consultation->id }}" name="scheduled_start_at"
                                           value="{{ old('scheduled_start_at', $appointmentStart->format('Y-m-d\TH:i')) }}" required>
                                    @error('scheduled_start_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label" for="scheduled_end_{{ $consultation->id }}">Appointment end</label>
                                    <input type="datetime-local" class="form-control @error('scheduled_end_at') is-invalid @enderror"
                                           id="scheduled_end_{{ $consultation->id }}" name="scheduled_end_at"
                                           value="{{ old('scheduled_end_at', $appointmentEnd->format('Y-m-d\TH:i')) }}" required>
                                    @error('scheduled_end_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label" for="teacher_response_{{ $consultation->id }}">Response or reschedule note</label>
                                    <input type="text" class="form-control" id="teacher_response_{{ $consultation->id }}"
                                           name="teacher_response" maxlength="1500" value="{{ old('teacher_response', $consultation->teacher_response) }}">
                                </div>
                                <div class="col-12 d-flex flex-wrap gap-2">
                                    <button class="btn btn-success" type="submit" name="action" value="approve">
                                        <i class="fas fa-check me-1"></i>{{ $consultation->status === 'approved' ? 'Save / Reschedule' : 'Approve Appointment' }}
                                    </button>
                                    <button class="btn btn-outline-danger" type="submit" name="action" value="decline"
                                            formnovalidate onclick="return confirm('Decline this consultation request?')">
                                        Decline
                                    </button>
                                </div>
                            </div>
                        </form>
                    @elseif($consultation->status === 'approved')
                        <p class="mb-0"><strong>Scheduled:</strong> {{ $consultation->scheduled_start_at->format('M j, Y g:i A') }}–{{ $consultation->scheduled_end_at->format('g:i A') }}</p>
                    @endif
                </div>
            </div>
        @empty
            <div class="card"><div class="card-body text-center text-muted">No consultation requests yet.</div></div>
        @endforelse
    </div>
</div>
@endsection