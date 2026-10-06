<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Semester extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'academic_year_id', 'status'];

    public function academicYear() { return $this->belongsTo(AcademicYear::class); }

    public static function current(): ?self
    {
        $year = AcademicYear::current();
        if ($year) {
            return static::query()
                ->where('academic_year_id', $year->id)
                ->orderBy('id')
                ->first();
        }

        return static::query()->latest('id')->first();
    }

    public function statusLabel(): string
    {
        if (in_array($this->status, ['current', 'upcoming', 'completed', 'archived'], true)) {
            return $this->status;
        }

        return $this->academicYear?->statusLabel() ?? 'upcoming';
    }
    public function enrollments() { return $this->hasMany(Enrollment::class); }
}
