<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Grade;
use App\Models\Student;

class LowGradeAlertNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $grade;
    public $student;
    public $subject;

    /**
     * Create a new notification instance.
     */
    public function __construct(Grade $grade, Student $student, $subject = null)
    {
        $this->grade = $grade;
        $this->student = $student;
        $this->subject = $subject;
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
        $subjectName = $this->subject ? $this->subject->name : 'Unknown Subject';
        $gradeValue = $this->grade->grade_value;
        $componentName = $this->grade->component ? $this->grade->component->name : 'Assignment';

        $message = (new MailMessage)
                    ->subject('Low Grade Alert - ' . $this->student->full_name)
                    ->greeting('Hello ' . $notifiable->name . '!')
                    ->line('This is an automated alert regarding a low grade.');

        if ($notifiable->role_name === 'Student') {
            $message->line('You have received a low grade in ' . $subjectName . '.')
                    ->line('**Component:** ' . $componentName)
                    ->line('**Grade:** ' . $gradeValue)
                    ->line('Please review your performance and consider seeking help if needed.');
        } elseif ($notifiable->role_name === 'Parent') {
            $message->line('Your child ' . $this->student->full_name . ' has received a low grade in ' . $subjectName . '.')
                    ->line('**Component:** ' . $componentName)
                    ->line('**Grade:** ' . $gradeValue)
                    ->line('Please discuss this with your child and consider contacting their teacher.');
        } else {
            $message->line('Student ' . $this->student->full_name . ' has received a low grade in ' . $subjectName . '.')
                    ->line('**Component:** ' . $componentName)
                    ->line('**Grade:** ' . $gradeValue)
                    ->line('Please review and provide additional support if needed.');
        }

        return $message->action('View Grade Details', url('/grades/' . $this->grade->id))
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
            'grade_id' => $this->grade->id,
            'student_name' => $this->student->full_name,
            'student_id' => $this->student->id,
            'subject_name' => $this->subject ? $this->subject->name : 'Unknown Subject',
            'component_name' => $this->grade->component ? $this->grade->component->name : 'Assignment',
            'grade_value' => $this->grade->grade_value,
            'grade_date' => $this->grade->created_at->format('M d, Y'),
            'alert_type' => 'low_grade',
        ];
    }
}
