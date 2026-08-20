<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Announcement;

class AnnouncementNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $announcement;

    /**
     * Create a new notification instance.
     */
    public function __construct(Announcement $announcement)
    {
        $this->announcement = $announcement;
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
        $priorityColor = match($this->announcement->priority) {
            'urgent' => '#dc3545',
            'high' => '#ffc107',
            'normal' => '#17a2b8',
            'low' => '#6c757d',
            default => '#17a2b8'
        };

        return (new MailMessage)
                    ->subject('New Announcement: ' . $this->announcement->title)
                    ->greeting('Hello ' . $notifiable->name . '!')
                    ->line('A new announcement has been posted.')
                    ->line('**' . $this->announcement->title . '**')
                    ->line($this->announcement->content)
                    ->line('Priority: ' . ucfirst($this->announcement->priority))
                    ->line('Type: ' . ucfirst($this->announcement->type))
                    ->action('View Announcement', url('/announcements/' . $this->announcement->id))
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
            'announcement_id' => $this->announcement->id,
            'title' => $this->announcement->title,
            'content' => $this->announcement->content,
            'type' => $this->announcement->type,
            'priority' => $this->announcement->priority,
            'created_by' => $this->announcement->creator->name,
            'created_at' => $this->announcement->created_at->format('M d, Y H:i'),
        ];
    }
}
