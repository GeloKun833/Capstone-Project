<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Message;

class NewMessageNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $message;

    /**
     * Create a new notification instance.
     */
    public function __construct(Message $message)
    {
        $this->message = $message;
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
        $studentInfo = '';
        if ($this->message->student) {
            $studentInfo = ' regarding student: ' . $this->message->student->full_name;
        }

        return (new MailMessage)
                    ->subject('New Message: ' . $this->message->subject)
                    ->greeting('Hello ' . $notifiable->name . '!')
                    ->line('You have received a new message from ' . $this->message->sender->name . $studentInfo . '.')
                    ->line('**Subject:** ' . $this->message->subject)
                    ->line('**Message:** ' . substr($this->message->content, 0, 100) . '...')
                    ->line('**Type:** ' . ucfirst($this->message->type))
                    ->line('**Priority:** ' . ucfirst($this->message->priority))
                    ->action('View Message', url('/messages/' . $this->message->id))
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
            'message_id' => $this->message->id,
            'subject' => $this->message->subject,
            'content' => substr($this->message->content, 0, 100) . '...',
            'sender_name' => $this->message->sender->name,
            'sender_id' => $this->message->sender_id,
            'type' => $this->message->type,
            'priority' => $this->message->priority,
            'student_name' => $this->message->student ? $this->message->student->full_name : null,
            'created_at' => $this->message->created_at->format('M d, Y H:i'),
        ];
    }
}
