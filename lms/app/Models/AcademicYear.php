<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class AcademicYear extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'start_date', 'end_date'];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function semesters()
    {
        return $this->hasMany(Semester::class);
    }

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }

    /**
     * Date-based status: current | upcoming | completed
     */
    public function statusLabel(): string
    {
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

    public function isCurrent(): bool
    {
        return $this->statusLabel() === 'current';
    }
}
