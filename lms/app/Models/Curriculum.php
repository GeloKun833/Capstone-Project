<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Curriculum extends Model
{
    use HasFactory;

    protected $fillable = [
        'grade_level',
        'description',
    ];

    public function subjects()
    {
        return $this->belongsToMany(Subject::class, 'curriculum_subject', 'curriculum_id', 'subject_id');
    }
}
