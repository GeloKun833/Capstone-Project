<?php

namespace App\Models;

use App\Support\SchoolQuarter;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Semester extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'academic_year_id', 'status'];

    public function academicYear() { return $this->belongsTo(AcademicYear::class); }

    public static function current(): ?self
    {
        $year = AcademicYear::active();
        $period = $year ? SchoolQuarter::current($year) : null;
        if (! $period) {
            return null;
        }

        return static::query()->find($period['semester_id']);
    }

    public function statusLabel(): string
    {
        $yearStatus = $this->academicYear?->statusLabel() ?? 'upcoming';
        if ($yearStatus !== 'current') {
            return in_array($yearStatus, ['upcoming', 'completed', 'archived'], true) ? $yearStatus : 'upcoming';
        }

        $period = collect(SchoolQuarter::periods($this->academicYear))->firstWhere('semester_id', $this->id);
        if (! $period) {
            return 'upcoming';
        }

        $today = now('Asia/Manila')->startOfDay();
        if ($today->lt($period['start'])) {
            return 'upcoming';
        }
        if ($today->gt($period['end'])) {
            return 'completed';
        }

        return 'current';
    }
    public function enrollments() { return $this->hasMany(Enrollment::class); }
}
