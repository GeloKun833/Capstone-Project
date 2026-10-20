<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class AcademicYear extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'start_date', 'end_date', 'class_starts_at', 'class_ends_at', 'status', 'enrollment_open', 'enrollment_starts_at', 'enrollment_ends_at'];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'class_starts_at' => 'date',
        'class_ends_at' => 'date',
        'enrollment_open' => 'boolean',
        'enrollment_starts_at' => 'date',
        'enrollment_ends_at' => 'date',
    ];

    public function getYearAttribute(): ?string
    {
        return $this->name;
    }

    public function semesters()
    {
        return $this->hasMany(Semester::class);
    }

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }

    public function displayName(): string
    {
        return str_replace('-', '–', (string) $this->name);
    }

    /**
     * The year an admin has explicitly set as current. No date or latest-row fallback.
     */
    public static function active(): ?self
    {
        return static::query()->where('status', 'current')->orderByDesc('start_date')->first();
    }

    public static function current(): ?self
    {
        return static::active()
            ?? static::query()->latest('id')->first();
    }

    public function enrollmentIsOpen(): bool
    {
        return app(\App\Services\EnrollmentReadiness::class)->isAvailableFor($this);
    }

    /**
     * Stored status: current | upcoming | completed | archived
     */
    public function statusLabel(): string
    {
        if (in_array($this->status, ['current', 'upcoming', 'completed', 'archived'], true)) {
            return $this->status;
        }

        $today = Carbon::today();
        if ($this->start_date && $this->end_date) {
            if ($this->start_date->lte($today) && $this->end_date->gte($today)) {
                return 'current';
            }
            if ($this->start_date->gt($today)) {
                return 'upcoming';
            }
        }

        return 'completed';
    }

    public function syncTermRecords(): void
    {
        $this->semesters()->update(['status' => $this->statusLabel()]);
    }

    public function isCurrent(): bool
    {
        return $this->statusLabel() === 'current';
    }

    public static function nameKey(string $name): ?string
    {
        if (! preg_match('/^(\d{4})\s*[-–]\s*(\d{4})$/u', trim($name), $matches)) {
            return null;
        }

        return $matches[1].'-'.$matches[2];
    }

    public static function findByNameKey(string $name, ?int $ignoreId = null): ?self
    {
        $key = static::nameKey($name);
        if (! $key) {
            return null;
        }

        return static::query()
            ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
            ->get()
            ->first(fn (self $year) => static::nameKey((string) $year->name) === $key);
    }

    /**
     * The day after enrollment ends, kept inside this school year.
     */
    public function suggestedClassStart(): ?Carbon
    {
        if (! $this->enrollment_starts_at || ! $this->enrollment_ends_at) {
            return null;
        }
        if ($this->enrollment_ends_at->lt($this->enrollment_starts_at)) {
            $fallback = $this->start_date?->copy()->startOfDay();

            return $fallback ? $this->nearestWeekday($fallback, 'forward', $this->end_date, null) : null;
        }

        $suggested = $this->enrollment_ends_at->copy()->addDay()->startOfDay();
        if ($this->start_date && $suggested->lt($this->start_date->copy()->startOfDay())) {
            $suggested = $this->start_date->copy()->startOfDay();
        }
        if ($this->end_date && $suggested->gt($this->end_date->copy()->startOfDay())) {
            $suggested = $this->end_date->copy()->startOfDay();
        }

        return $this->nearestWeekday($suggested, 'forward', $this->end_date, $this->enrollment_ends_at);
    }

    public function suggestedClassStartNote(): ?string
    {
        $suggested = $this->suggestedClassStart();
        if (! $suggested || ! $this->enrollment_ends_at) {
            return null;
        }

        $label = $suggested->format('M d, Y');
        $dayAfter = $this->enrollment_ends_at->copy()->addDay()->startOfDay();
        if ($dayAfter->isWeekend() && $suggested->gt($dayAfter)) {
            return 'Suggested date: '.$label.'. The day after enrollment ends falls on a weekend, so this is the next weekday.';
        }
        if ($suggested->equalTo($dayAfter)) {
            return 'Suggested date: '.$label.'. This is the day after enrollment ends.';
        }
        if ($this->start_date && $suggested->equalTo($this->start_date->copy()->startOfDay())) {
            return 'Suggested date: '.$label.'. This is the first day of the school year.';
        }

        return 'Suggested date: '.$label.'. This stays on or before the end of the school year.';
    }

    public function suggestedClassEnd(): ?Carbon
    {
        if (! $this->enrollment_starts_at || ! $this->enrollment_ends_at || ! $this->end_date) {
            return null;
        }

        $end = $this->nearestWeekday($this->end_date->copy()->startOfDay(), 'back', $this->end_date, $this->suggestedClassStart());
        $start = $this->suggestedClassStart();
        if ($start && $end->lt($start)) {
            return $start;
        }

        return $end;
    }

    public function suggestedClassEndNote(): ?string
    {
        $suggested = $this->suggestedClassEnd();
        if (! $suggested || ! $this->end_date) {
            return null;
        }

        $label = $suggested->format('M d, Y');
        $schoolEnd = $this->end_date->copy()->startOfDay();
        if ($schoolEnd->isWeekend() && $suggested->lt($schoolEnd)) {
            return 'Suggested end of class: '.$label.'. The last day of the school year falls on a weekend, so this is the weekday before.';
        }

        return 'Suggested end of class: '.$label.'. This is the last day of the school year.';
    }

    /**
     * Move a suggested date off Saturday or Sunday without leaving the school year
     * or landing on the enrollment period.
     */
    private function nearestWeekday(Carbon $date, string $direction, ?Carbon $latest, ?Carbon $earliest): Carbon
    {
        $date = $date->copy()->startOfDay();
        $latestDay = $latest?->copy()->startOfDay();
        $earliestDay = $earliest?->copy()->startOfDay();

        if (! $date->isWeekend()) {
            return $date;
        }

        $forward = $date->copy();
        while ($forward->isWeekend()) {
            $forward->addDay();
        }
        $back = $date->copy();
        while ($back->isWeekend()) {
            $back->subDay();
        }

        $forwardOk = (! $latestDay || $forward->lte($latestDay))
            && (! $earliestDay || $forward->gt($earliestDay));
        $backOk = (! $latestDay || $back->lte($latestDay))
            && (! $earliestDay || $back->gt($earliestDay));

        if ($direction === 'back') {
            return $backOk ? $back : ($forwardOk ? $forward : $date);
        }

        return $forwardOk ? $forward : ($backOk ? $back : $date);
    }
}
