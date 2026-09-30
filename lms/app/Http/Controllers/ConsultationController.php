<?php

namespace App\Http\Controllers;

use App\Models\CalendarEvent;
use App\Models\ConsultationRequest;
use App\Models\Teacher;
use App\Notifications\ConsultationRequestNotification;
use App\Services\StudentConsultationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ConsultationController extends Controller
{
    public function studentIndex(StudentConsultationService $consultations)
    {
        $student = Auth::user()->student;
        abort_unless($student, 403, 'Student profile not found.');

        $subjects = $consultations->optionsFor($student);
        $requests = ConsultationRequest::with(['teacher', 'subject', 'calendarEvent'])
            ->where('student_id', $student->id)
            ->orderByRaw("CASE status WHEN 'pending' THEN 0 WHEN 'approved' THEN 1 ELSE 2 END")
            ->orderByDesc('created_at')
            ->get();
        $minimumRequestTime = now(config('app.school_timezone', 'Asia/Manila'))->addHour();

        return view('consultations.student', compact('student', 'subjects', 'requests', 'minimumRequestTime'));
    }

    public function store(Request $request, StudentConsultationService $consultations)
    {
        $student = Auth::user()->student;
        abort_unless($student, 403, 'Student profile not found.');

        $data = $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'teacher_id' => 'required|exists:teachers,id',
            'requested_start_at' => 'required|date',
            'duration_minutes' => 'required|integer|in:15,30,45,60',
            'student_message' => 'nullable|string|max:1500',
        ]);

        if (! $consultations->teacherIsAssigned($student, (int) $data['subject_id'], (int) $data['teacher_id'])) {
            return back()->withInput()->withErrors([
                'teacher_id' => 'Choose a teacher assigned to this subject and section.',
            ]);
        }

        $timezone = config('app.school_timezone', 'Asia/Manila');
        $start = Carbon::parse($data['requested_start_at'], $timezone);
        $end = $start->copy()->addMinutes((int) $data['duration_minutes']);
        if (! $start->gt(Carbon::now($timezone))) {
            return back()->withInput()->withErrors([
                'requested_start_at' => 'Choose a consultation time in the future.',
            ]);
        }

        $conflicts = CalendarEvent::checkConflicts(
            $start->format('Y-m-d H:i:s'),
            $end->format('Y-m-d H:i:s'),
            (int) $data['teacher_id'],
            null,
            null,
            (int) $data['subject_id']
        );
        if (! empty($conflicts['teacher'])) {
            return back()->withInput()->withErrors([
                'requested_start_at' => 'The teacher is already scheduled at that time. Choose another time.',
            ]);
        }

        $consultation = ConsultationRequest::create([
            'student_id' => $student->id,
            'teacher_id' => (int) $data['teacher_id'],
            'subject_id' => (int) $data['subject_id'],
            'requested_start_at' => $start->format('Y-m-d H:i:s'),
            'requested_end_at' => $end->format('Y-m-d H:i:s'),
            'student_message' => $data['student_message'] ?? null,
            'status' => ConsultationRequest::STATUS_PENDING,
        ]);

        $teacher = Teacher::with('user')->find($consultation->teacher_id);
        if ($teacher?->user) {
            $teacher->user->notify(new ConsultationRequestNotification($consultation->load(['student', 'subject'])));
            Cache::forget('header.notifs.'.$teacher->user->id);
        }

        return redirect()->route('student.consultations.index')->with('success', 'Consultation request sent to your teacher.');
    }

    public function cancel(ConsultationRequest $consultation)
    {
        $student = Auth::user()->student;
        abort_unless($student && (int) $consultation->student_id === (int) $student->id, 403);

        if ($consultation->status !== ConsultationRequest::STATUS_PENDING) {
            return back()->with('error', 'Only pending consultation requests can be cancelled.');
        }

        $consultation->update(['status' => ConsultationRequest::STATUS_CANCELLED]);

        return back()->with('success', 'Consultation request cancelled.');
    }

    public function teacherIndex()
    {
        $teacher = Auth::user()->teacher;
        abort_unless($teacher, 403, 'Teacher profile not found.');

        $requests = ConsultationRequest::with(['student', 'subject', 'calendarEvent'])
            ->where('teacher_id', $teacher->id)
            ->orderByRaw("CASE status WHEN 'pending' THEN 0 WHEN 'approved' THEN 1 ELSE 2 END")
            ->orderByDesc('created_at')
            ->get();

        return view('consultations.teacher', compact('teacher', 'requests'));
    }

    public function respond(Request $request, ConsultationRequest $consultation)
    {
        $teacher = Auth::user()->teacher;
        abort_unless($teacher && (int) $consultation->teacher_id === (int) $teacher->id, 403);

        if (! in_array($consultation->status, [ConsultationRequest::STATUS_PENDING, ConsultationRequest::STATUS_APPROVED], true)) {
            return back()->with('error', 'This consultation request is no longer active.');
        }

        $data = $request->validate([
            'action' => 'required|in:approve,decline',
            'scheduled_start_at' => 'required_if:action,approve|nullable|date',
            'scheduled_end_at' => 'required_if:action,approve|nullable|date',
            'teacher_response' => 'nullable|string|max:1500',
        ]);

        if ($data['action'] === 'decline') {
            DB::transaction(function () use ($consultation, $data) {
                if ($consultation->calendarEvent) {
                    $consultation->calendarEvent->delete();
                }
                $consultation->update([
                    'status' => ConsultationRequest::STATUS_DECLINED,
                    'calendar_event_id' => null,
                    'scheduled_start_at' => null,
                    'scheduled_end_at' => null,
                    'teacher_response' => $data['teacher_response'] ?? null,
                ]);
            });

            return back()->with('success', 'Consultation request declined.');
        }

        $timezone = config('app.school_timezone', 'Asia/Manila');
        $start = Carbon::parse($data['scheduled_start_at'], $timezone);
        $end = Carbon::parse($data['scheduled_end_at'], $timezone);
        if (! $start->gt(Carbon::now($timezone)) || ! $end->gt($start)) {
            return back()->withInput()->withErrors([
                'scheduled_start_at' => 'Choose a future appointment time with an end time after the start time.',
            ]);
        }

        $startValue = $start->format('Y-m-d H:i:s');
        $endValue = $end->format('Y-m-d H:i:s');
        $conflicts = CalendarEvent::checkConflicts(
            $startValue,
            $endValue,
            $teacher->id,
            null,
            $consultation->calendar_event_id,
            $consultation->subject_id
        );
        if (! empty($conflicts['teacher'])) {
            return back()->withInput()->withErrors([
                'scheduled_start_at' => 'You already have a calendar event at that time. Choose another slot.',
            ]);
        }

        $consultationConflict = ConsultationRequest::query()
            ->where('teacher_id', $teacher->id)
            ->where('status', ConsultationRequest::STATUS_APPROVED)
            ->where('id', '!=', $consultation->id)
            ->where('scheduled_start_at', '<', $endValue)
            ->where('scheduled_end_at', '>', $startValue)
            ->exists();
        if ($consultationConflict) {
            return back()->withInput()->withErrors([
                'scheduled_start_at' => 'Another approved consultation overlaps this time. Choose another slot.',
            ]);
        }

        $student = $consultation->student;
        DB::transaction(function () use ($consultation, $teacher, $student, $startValue, $endValue, $data) {
            $eventData = [
                'title' => 'Consultation: '.$student->full_name,
                'description' => trim(($consultation->student_message ?: 'Student consultation')."\n\n".($data['teacher_response'] ?? '')),
                'event_type' => 'meeting',
                'start_time' => $startValue,
                'end_time' => $endValue,
                'subject_id' => $consultation->subject_id,
                'teacher_id' => $teacher->id,
                'student_id' => $student->id,
                'room_id' => null,
                'created_by' => Auth::id(),
                'is_all_day' => false,
                'is_recurring' => false,
            ];

            $event = $consultation->calendarEvent;
            if ($event) {
                $event->update($eventData);
            } else {
                $event = CalendarEvent::create($eventData);
            }

            $consultation->update([
                'status' => ConsultationRequest::STATUS_APPROVED,
                'calendar_event_id' => $event->id,
                'scheduled_start_at' => $startValue,
                'scheduled_end_at' => $endValue,
                'teacher_response' => $data['teacher_response'] ?? null,
            ]);
        });

        return back()->with('success', 'Consultation approved and added to both calendars.');
    }
}