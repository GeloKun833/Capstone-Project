<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    public const STATUSES = ['present', 'absent', 'late', 'excused'];

    protected $fillable = [
        'student_id',
        'subject_id',
        'date',
        'status',
        'teacher_id',
        'remarks',
    ];

    public static function countsAsPresent(?string $status): bool
    {
        return in_array($status, ['present', 'late'], true);
    }

    public static function summarize($attendances): array
    {
        $total = $attendances->count();
        $present = $attendances->where('status', 'present')->count();
        $late = $attendances->where('status', 'late')->count();
        $excused = $attendances->where('status', 'excused')->count();
        $absent = $attendances->where('status', 'absent')->count();
        $attended = $present + $late;
        $rateBase = max(0, $total - $excused);

        return [
            'total' => $total,
            'present' => $present,
            'late' => $late,
            'excused' => $excused,
            'absent' => $absent,
            'percentage' => $rateBase > 0 ? round(($attended / $rateBase) * 100, 2) : 0,
        ];
    }

    public function student() {
        return $this->belongsTo(Student::class);
    }
    public function subject() {
        return $this->belongsTo(Subject::class);
    }
    public function teacher() {
        return $this->belongsTo(Teacher::class);
    }
}
