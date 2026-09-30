<?php

namespace App\Notifications;

use App\Models\Assignment;
use App\Models\Student;
use App\Models\User;
use Illuminate\Notifications\Notification;

class AssignmentDeadlineReminderNotification extends Notification
{
    public function __construct(
        public Assignment $assignment,
        public Student $student,
        public string $reminderKey
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $isParent = $notifiable instanceof User && $notifiable->role_name === User::ROLE_PARENT;
        $studentName = $this->student->full_name ?: 'your student';
        $due = $this->assignment->dueDateTime->format('M j, Y g:i A');
        $message = $isParent
            ? "{$studentName}'s assignment {$this->assignment->title} is due {$due}."
            : "Your assignment {$this->assignment->title} is due {$due}.";
        $url = $isParent
            ? route('parent.child.hub', ['childId' => $this->student->id, 'tab' => 'assignments'])
            : route('student.assignments.show', $this->assignment);

        return [
            'title' => 'Assignment due soon',
            'message' => $message,
            'type' => 'assignment_deadline_reminder',
            'icon' => 'fas fa-clock',
            'url' => $url,
            'assignment_id' => $this->assignment->id,
            'assignment_title' => $this->assignment->title,
            'student_id' => $this->student->id,
            'student_name' => $studentName,
            'subject_name' => $this->assignment->subject?->subject_name,
            'due_at' => $this->assignment->dueDateTime->toIso8601String(),
            'reminder_key' => $this->reminderKey,
        ];
    }
}