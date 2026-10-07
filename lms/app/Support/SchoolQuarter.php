<?php

namespace App\Support;

use App\Models\AcademicYear;
use Illuminate\Support\Carbon;

class SchoolQuarter
{
    private static array $cache = [];

    /**
     * Grading periods for a year are the terms created for that year,
     * in the order they were added, spread across the year's start and end dates.
     */
    public static function periods(?AcademicYear $year): array
    {
        if (! $year) {
            return [];
        }

        if (isset(self::$cache[$year->id])) {
            return self::$cache[$year->id];
        }

        $start = $year->start_date?->copy()->startOfDay();
        $end = $year->end_date?->copy()->startOfDay();
        if (! $start || ! $end || $end->lt($start)) {
            return self::$cache[$year->id] = [];
        }

        $terms = $year->relationLoaded('semesters')
            ? $year->semesters->sortBy('id')->values()
            : $year->semesters()->orderBy('id')->get();

        $terms = $terms->take(4)->values();
        if ($terms->isEmpty()) {
            return self::$cache[$year->id] = [];
        }

        $totalDays = (int) $start->diffInDays($end) + 1;
        $count = $terms->count();
        $base = intdiv($totalDays, $count);
        $extra = $totalDays % $count;
        $cursor = $start->copy();
        $periods = [];

        foreach ($terms as $index => $term) {
            $number = $index + 1;
            $length = $base + ($number <= $extra ? 1 : 0);
            $periodEnd = $cursor->copy()->addDays(max(0, $length - 1));
            $periods[] = [
                'number' => $number,
                'label' => $term->name,
                'semester_id' => (int) $term->id,
                'start' => $cursor->copy(),
                'end' => $periodEnd->copy(),
            ];
            $cursor = $periodEnd->copy()->addDay();
        }

        return self::$cache[$year->id] = $periods;
    }

    public static function current(?AcademicYear $year, ?Carbon $today = null): ?array
    {
        $today = ($today ?? now('Asia/Manila'))->copy()->startOfDay();
        foreach (self::periods($year) as $period) {
            if ($today->betweenIncluded($period['start'], $period['end'])) {
                return $period;
            }
        }

        return null;
    }
}
