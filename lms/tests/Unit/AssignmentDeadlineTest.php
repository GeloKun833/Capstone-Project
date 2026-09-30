<?php

namespace Tests\Unit;

use App\Models\Assignment;
use Carbon\Carbon;
use Tests\TestCase;

class AssignmentDeadlineTest extends TestCase
{
    protected function tearDown(): void
    {
        Carbon::setTestNow(null);
        parent::tearDown();
    }

    public function test_assignment_is_overdue_only_after_its_school_local_due_time(): void
    {
        config(['app.school_timezone' => 'Asia/Manila']);
        Carbon::setTestNow(Carbon::parse('2026-09-30 18:09:00', 'Asia/Manila'));

        $assignment = new Assignment([
            'due_date' => '2026-09-30',
            'due_time' => '18:10:00',
        ]);

        $this->assertSame('2026-09-30 18:10:00', $assignment->dueDateTime->format('Y-m-d H:i:s'));
        $this->assertFalse($assignment->is_overdue);
        $assignment->status = 'published';
        $assignment->allows_late_submission = true;
        $this->assertTrue($assignment->canSubmit());

        Carbon::setTestNow(Carbon::parse('2026-09-30 18:11:00', 'Asia/Manila'));

        $this->assertTrue($assignment->is_overdue);
        $this->assertFalse($assignment->canSubmit());
    }

    public function test_closed_assignment_rejects_submission_before_its_deadline(): void
    {
        config(['app.school_timezone' => 'Asia/Manila']);
        Carbon::setTestNow(Carbon::parse('2026-09-30 18:00:00', 'Asia/Manila'));

        $assignment = new Assignment([
            'status' => 'closed',
            'due_date' => '2026-09-30',
            'due_time' => '18:10:00',
        ]);

        $this->assertFalse($assignment->canSubmit());
    }

    public function test_assignment_without_due_time_expires_at_end_of_school_day(): void
    {
        config(['app.school_timezone' => 'Asia/Manila']);
        Carbon::setTestNow(Carbon::parse('2026-09-30 23:59:00', 'Asia/Manila'));

        $assignment = new Assignment(['due_date' => '2026-09-30']);

        $this->assertSame('2026-09-30 23:59:59', $assignment->dueDateTime->format('Y-m-d H:i:s'));
        $this->assertFalse($assignment->is_overdue);

        Carbon::setTestNow(Carbon::parse('2026-10-01 00:00:00', 'Asia/Manila'));

        $this->assertTrue($assignment->is_overdue);
    }
}