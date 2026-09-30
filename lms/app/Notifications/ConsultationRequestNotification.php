<?php

namespace App\Notifications;

use App\Models\ConsultationRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ConsultationRequestNotification extends Notification
{
    use Queueable;

    public function __construct(public ConsultationRequest $consultation) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('New student consultation request')
            ->line($this->studentName().' requested a consultation for '.$this->consultation->subject->subject_name.'.')
            ->action('Review Request', route('teacher.consultations.index'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'New consultation request',
            'message' => $this->studentName().' requested a consultation for '.$this->consultation->subject->subject_name.'.',
            'consultation_request_id' => $this->consultation->id,
            'student_id' => $this->consultation->student_id,
            'student_name' => $this->studentName(),
            'subject_id' => $this->consultation->subject_id,
            'subject_name' => $this->consultation->subject->subject_name,
            'requested_start_at' => $this->consultation->requested_start_at->toIso8601String(),
            'type' => 'consultation_request',
            'icon' => 'fas fa-comments',
            'url' => route('teacher.consultations.index'),
        ];
    }

    private function studentName(): string
    {
        return $this->consultation->student->full_name ?: 'A student';
    }
}