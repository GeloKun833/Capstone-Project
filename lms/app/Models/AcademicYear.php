<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class AcademicYear extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'start_date', 'end_date', 'status', 'enrollment_open', 'enrollment_starts_at', 'enrollment_ends_at'];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
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
}
