<?php

namespace App\Support;

/**
 * Single official passing mark for analytics, alerts, report cards, and remarks.
 * Letter-grade bands (e.g. D at 60) are descriptors, not pass/fail.
 */
class AcademicThresholds
{
    public const PASSING_PERCENTAGE = 75.0;

    /** Attendance below this percentage is flagged in the Performance Hub. */
    public const ATTENDANCE_CONCERN_PERCENTAGE = 80.0;

    /** This many absences in the academic year is treated as recurring. */
    public const RECURRING_ABSENCE_COUNT = 5;
}
