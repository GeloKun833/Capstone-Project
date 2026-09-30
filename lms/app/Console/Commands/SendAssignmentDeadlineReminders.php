<?php

namespace App\Console\Commands;

use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Student;
use App\Models\User;
use App\Notifications\AssignmentDeadlineReminderNotification;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class SendAssignmentDeadlineReminders extends Command
{
    protected $signature = 'assignments:send-deadline-reminders {--dry-run : Show eligible recipients without notifying anyone}';

    protected $description = 'Notify students and linked parents once when a published assignment is due within 24 hours.';

    public function handle(): int
    {
        $timezone = config('app.school_timezone', 'Asia/Manila');
        $now = Carbon::now($timezone);
        $windowEnd = $now->copy()->addDay();
        $sent = 0;
        $eligible = 0;

        $assignments = Assignment::query()
            ->with(['subject'])
            ->where('status', 'published')
            ->where('is_active', true)
            ->whereDate('due_date', '>=', $now->toDateString())
            ->whereDate('due_date', '<=', $windowEnd->toDateString())
            ->orderBy('due_date')
            ->get();

        foreach ($assignments as $assignment) {
            $deadline = $assignment->dueDateTime;
            if (! $deadline || ! $deadline->gt($now) || ! $deadline->lte($windowEnd)) {
                continue;
            }

            $students = Student::query()
                ->with('user')
                ->whereHas('enrollments', function ($enrollments) use ($assignment) {
                    $enrollments->where('subject_id', $assignment->subject_id)
                        ->where('status', 'active')
                        ->where('academic_year_id', $assignment->academic_year_id)
                        ->where('semester_id', $assignment->semester_id);
                })
                ->whereHas('sections', fn ($sections) => $sections->where('sections.id', $assignment->section_id))
                ->whereNotIn('students.id', AssignmentSubmission::query()
                    ->select('student_id')
                    ->where('assignment_id', $assignment->id))
                ->orderBy('students.id')
                ->get();

            foreach ($students as $student) {
                $reminderKey = hash('sha256', implode('|', [
                    $assignment->id,
                    $student->id,
                    $deadline->toIso8601String(),
                ]));
                $notification = new AssignmentDeadlineReminderNotification($assignment, $student, $reminderKey);
                $recipients = collect([$student->user, $student->linkedParentUser()])
                    ->filter(fn ($user) => $user && $user->isActiveAccount())
                    ->unique('id');

                foreach ($recipients as $recipient) {
                    if ($this->alreadyNotified($recipient, $reminderKey)) {
                        continue;
                    }

                    $eligible++;
                    if ($this->option('dry-run')) {
                        $this->line("Would notify {$recipient->name} about {$assignment->title} for {$student->full_name} (due {$deadline->format('M j, Y g:i A')}).");
                        continue;
                    }

                    try {
                        $recipient->notify($notification);
                        Cache::forget('header.notifs.'.$recipient->id);
                        $sent++;
                    } catch (\Throwable $e) {
                        report($e);
                        $this->warn("Could not notify user #{$recipient->id}: {$e->getMessage()}");
                    }
                }
            }
        }

        if ($this->option('dry-run')) {
            $this->info("Dry run complete. {$eligible} notification(s) would be sent; nothing was changed.");
        } else {
            $this->info("Sent {$sent} assignment deadline reminder(s).");
        }

        return self::SUCCESS;
    }

    private function alreadyNotified(User $user, string $reminderKey): bool
    {
        return $user->notifications()
            ->where('type', AssignmentDeadlineReminderNotification::class)
            ->where('data->reminder_key', $reminderKey)
            ->exists();
    }
}