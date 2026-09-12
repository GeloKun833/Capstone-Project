<?php

namespace App\Notifications;

use App\Models\Message;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewMessageNotification extends Notification
{
    use Queueable;

    public Message $message;

    public function __construct(Message $message)
    {
        $this->message = $message;
    }

    public function via(object $notifiable): array
    {
        // Database only so chat alerts always reach the bell even without a mail queue
        return ['database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('New chat message from ' . ($this->message->sender->name ?? 'someone'))
            ->greeting('Hello ' . ($notifiable->name ?? 'there') . '!')
            ->line('You have a new chat message.')
            ->line(\Illuminate\Support\Str::limit(strip_tags($this->message->content), 200))
            ->action('Open Chat', url('/chat?receiver_id=' . $this->message->sender_id))
            ->line('Thank you for using our LMS system!');
    }

    public function toArray(object $notifiable): array
    {
        $senderName = optional($this->message->sender)->name ?? 'Someone';
        $excerpt = \Illuminate\Support\Str::limit(trim(strip_tags($this->message->content)), 140);

        return [
            'message_id' => $this->message->id,
            'title' => 'New message from ' . $senderName,
            'message' => $excerpt,
            'content' => $this->message->content,
            'sender_name' => $senderName,
            'sender_id' => $this->message->sender_id,
            'type' => 'chat',
            'priority' => $this->message->priority ?? 'normal',
            'icon' => 'fas fa-comments',
            'url' => url('/chat?receiver_id=' . $this->message->sender_id),
            'created_at' => optional($this->message->created_at)?->format('M d, Y H:i'),
        ];
    }
}
