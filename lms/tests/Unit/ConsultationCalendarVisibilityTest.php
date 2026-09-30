<?php

namespace Tests\Unit;

use App\Models\CalendarEvent;
use App\Models\Student;
use Tests\TestCase;

class ConsultationCalendarVisibilityTest extends TestCase
{
    public function test_private_consultation_meeting_is_visible_only_to_its_student(): void
    {
        $event = new CalendarEvent(['event_type' => 'meeting', 'student_id' => 19]);
        $student = new Student();
        $student->id = 19;
        $otherStudent = new Student();
        $otherStudent->id = 20;

        $this->assertTrue($event->isVisibleToStudent($student));
        $this->assertFalse($event->isVisibleToStudent($otherStudent));
    }

    public function test_school_wide_event_without_student_link_remains_visible(): void
    {
        $event = new CalendarEvent(['event_type' => 'meeting']);
        $student = new Student();
        $student->id = 19;

        $this->assertTrue($event->isVisibleToStudent($student));
    }
}