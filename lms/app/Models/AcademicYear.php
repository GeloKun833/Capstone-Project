<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AcademicYear extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'start_date', 'end_date'];

    public function semesters() { return $this->hasMany(Semester::class); }
    public function enrollments() { return $this->hasMany(Enrollment::class); }
}
