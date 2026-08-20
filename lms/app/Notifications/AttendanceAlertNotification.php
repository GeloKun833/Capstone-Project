<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Attendance;
use App\Models\Student;

class AttendanceAlertNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $attendance;
    public $student;
    public $subject;
    public $missedDays;

    /**
     * Create a new notification instance.
     */
    public function __construct(Attendance $attendance, Student $student, $subject = null, $missedDays = 0)
    {
        $this->attendance = $attendance;
        $this->student = $student;
        $this->subject = $subject;
        $this->missedDays = $missedDays;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $subjectName = $this->subject ? ($this->subject->subject_name ?? $this->subject->name ?? 'Unknown Subject') : 'Unknown Subject';
        $date = $this->attendance->date->format('M d, Y');

        $message = (new MailMessage)
                    ->subject('Attendance Alert - ' . $this->student->full_name)
                    ->greeting('Hello ' . $notifiable->name . '!')
                    ->line('This is an automated alert regarding attendance.');

        if ($notifiable->role_name === 'Student') {
            $message->line('You were marked absent in ' . $subjectName . ' on ' . $date . '.')
                    ->line('**Status:** ' . ucfirst($this->attendance->status))
                    ->line('**Total Missed Days:** ' . $this->missedDays)
                    ->line('Please ensure regular attendance to maintain academic progress.');
        } elseif ($notifiable->role_name === 'Parent') {
            $message->line('Your child ' . $this->student->full_name . ' was marked absent in ' . $subjectName . ' on ' . $date . '.')
                    ->line('**Status:** ' . ucfirst($this->attendance->status))
                    ->line('**Total Missed Days:** ' . $this->missedDays)
                    ->line('Please discuss this with your child and ensure regular attendance.');
        } else {
            $message->line('Student ' . $this->student->full_name . ' was marked absent in ' . $subjectName . ' on ' . $date . '.')
                    ->line('**Status:** ' . ucfirst($this->attendance->status))
                    ->line('**Total Missed Days:** ' . $this->missedDays)
                    ->line('Please review and provide support if needed.');
        }

        if ($this->attendance->remarks) {
            $message->line('**Remarks:** ' . $this->attendance->remarks);
        }

        return $message->action('View Attendance Details', url('/attendance/' . $this->attendance->id))
                    ->line('Thank you for using our LMS system!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'attendance_id' => $this->attendance->id,
            'student_name' => $this->student->full_name,
            'student_id' => $this->student->id,
            'subject_name' => $this->subject ? ($this->subject->subject_name ?? $this->subject->name ?? 'Unknown Subject') : 'Unknown Subject',
            'date' => $this->attendance->date->format('M d, Y'),
            'status' => $this->attendance->status,
            'missed_days' => $this->missedDays,
            'remarks' => $this->attendance->remarks,
            'alert_type' => 'attendance',
        ];
    }
}
