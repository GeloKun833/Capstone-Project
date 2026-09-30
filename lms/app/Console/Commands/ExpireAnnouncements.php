<?php

namespace App\Console\Commands;

use App\Models\Announcement;
use App\Models\User;
use App\Notifications\AnnouncementNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class ExpireAnnouncements extends Command
{
    protected $signature = 'announcements:expire';

    protected $description = 'Release scheduled announcements and deactivate expired announcements.';

    public function handle(): int
    {
        $now = now();
        $notifiedRecipients = 0;
        $dueAnnouncements = Announcement::with('creator')
            ->where('is_active', true)
            ->where('is_scheduled', true)
            ->whereNotNull('scheduled_at')
            ->where('scheduled_at', '<=', $now)
            ->where(function ($query) use ($now) {
                $query->whereNull('expires_at')->orWhere('expires_at', '>', $now);
            })
            ->get();

        foreach ($dueAnnouncements as $announcement) {
            $roleNames = $announcement->recipientRoleNames();
            if ($roleNames === []) {
                continue;
            }

            $recipients = User::query()->whereIn('role_name', $roleNames)->activeAccounts()->get();
            foreach ($recipients as $recipient) {
                $alreadyNotified = $recipient->notifications()
                    ->where('type', AnnouncementNotification::class)
                    ->where('data->announcement_id', $announcement->id)
                    ->exists();
                if ($alreadyNotified) {
                    continue;
                }

                $recipient->notify(new AnnouncementNotification($announcement));
                Cache::forget('header.notifs.'.$recipient->id);
                $notifiedRecipients++;
            }
        }

        $expired = Announcement::query()
            ->where('is_active', true)
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', $now)
            ->update(['is_active' => false]);

        $this->info("Notified {$notifiedRecipients} recipient(s) for scheduled announcements and expired {$expired} announcement(s).");

        return self::SUCCESS;
    }
}