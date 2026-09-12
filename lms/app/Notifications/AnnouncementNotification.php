<?php

namespace App\Notifications;

use App\Models\Announcement;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AnnouncementNotification extends Notification
{
    use Queueable;

    public Announcement $announcement;

    public function __construct(Announcement $announcement)
    {
        $this->announcement = $announcement;
    }

    public function via(object $notifiable): array
    {
        // Database first so the bell always works even if mail is misconfigured
        return ['database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('New Announcement: ' . $this->announcement->title)
            ->greeting('Hello ' . ($notifiable->name ?? 'there') . '!')
            ->line('A new announcement has been posted.')
            ->line('**' . $this->announcement->title . '**')
            ->line(\Illuminate\Support\Str::limit(strip_tags($this->announcement->content), 200))
            ->action('View Announcement', url('/announcements/' . $this->announcement->id))
            ->line('Thank you for using our LMS system!');
    }

    public function toArray(object $notifiable): array
    {
        $excerpt = \Illuminate\Support\Str::limit(trim(strip_tags($this->announcement->content)), 140);

        return [
            'announcement_id' => $this->announcement->id,
            'title' => $this->announcement->title,
            'message' => $excerpt,
            'content' => $this->announcement->content,
            'type' => $this->announcement->type,
            'priority' => $this->announcement->priority,
            'icon' => $this->announcement->type_icon ?? 'fas fa-bullhorn',
            'url' => url('/announcements/' . $this->announcement->id),
            'created_by' => optional($this->announcement->creator)->name,
            'created_at' => optional($this->announcement->created_at)?->format('M d, Y H:i'),
        ];
    }
}
