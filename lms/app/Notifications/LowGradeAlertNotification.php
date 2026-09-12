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

    public function __construct(Grade $grade, Student $student, $subject = null)
    {
        $this->grade = $grade;
        $this->student = $student;
        $this->subject = $subject;
    }

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    protected function subjectName(): string
    {
        if (! $this->subject) {
            return 'Unknown Subject';
        }

        return $this->subject->subject_name
            ?? $this->subject->name
            ?? 'Unknown Subject';
    }

    protected function gradeValue(): string
    {
        if ($this->grade->percentage !== null) {
            return number_format((float) $this->grade->percentage, 2).'%';
        }
        if ($this->grade->score !== null) {
            return (string) $this->grade->score;
        }

        return 'N/A';
    }

    protected function studentDisplayName(): string
    {
        return $this->student->full_name
            ?? trim(($this->student->first_name ?? '').' '.($this->student->last_name ?? ''))
            ?: 'Student';
    }

    public function toMail(object $notifiable): MailMessage
    {
        $subjectName = $this->subjectName();
        $gradeValue = $this->gradeValue();
        $studentName = $this->studentDisplayName();

        $message = (new MailMessage)
            ->subject('Low Grade Alert - '.$studentName)
            ->greeting('Hello '.$notifiable->name.'!');

        if ($notifiable->role_name === 'Student') {
            $message->line('You have a low grade in '.$subjectName.'.')
                ->line('**Grade:** '.$gradeValue)
                ->line('Please review your performance and consider seeking help if needed.');
        } elseif ($notifiable->role_name === 'Parent') {
            $message->line('Your child '.$studentName.' has a low grade in '.$subjectName.'.')
                ->line('**Grade:** '.$gradeValue)
                ->line('Please discuss this with your child and consider contacting their teacher.');
        } else {
            $message->line('Student '.$studentName.' has a low grade in '.$subjectName.'.')
                ->line('**Grade:** '.$gradeValue)
                ->line('Please review and provide additional support if needed.');
        }

        return $message
            ->action('Open LMS', url('/'))
            ->line('Thank you for using the Panorama Montessori School LMS.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'grade_id' => $this->grade->id,
            'student_name' => $this->studentDisplayName(),
            'student_id' => $this->student->id,
            'subject_name' => $this->subjectName(),
            'grade_value' => $this->gradeValue(),
            'grade_date' => optional($this->grade->created_at)->format('M d, Y'),
            'alert_type' => 'low_grade',
        ];
    }
}
