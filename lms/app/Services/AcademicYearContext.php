<?php

namespace App\Services;

use App\Models\AcademicYear;

class AcademicYearContext
{
    /**
     * The year marked Current. New enrollment uses this year.
     * Choosing a year to view does not change it.
     */
    public function active(): ?AcademicYear
    {
        return AcademicYear::active();
    }

    /**
     * The year lists, schedules, grades, and reports should display.
     * Defaults to the active year until an admin picks another year to view.
     */
    public function viewing(): ?AcademicYear
    {
        $selectedId = session('academic_year_view_id');
        if ($selectedId) {
            $selected = AcademicYear::query()->find($selectedId);
            if ($selected) {
                return $selected;
            }
        }

        return $this->active();
    }

    public function view(AcademicYear $year): void
    {
        session(['academic_year_view_id' => $year->id]);
    }
}
