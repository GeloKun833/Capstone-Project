<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    use HasFactory;

    protected $fillable = [
        'room_name',
        'room_number',
        'capacity',
        'location',
        'description',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'capacity' => 'integer'
    ];

    /**
     * Get the calendar events for this room
     */
    public function calendarEvents()
    {
        return $this->hasMany(CalendarEvent::class);
    }

    /**
     * Scope to get only active rooms
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get the full room name with number
     */
    public function getFullNameAttribute()
    {
        return $this->room_name . ' (' . $this->room_number . ')';
    }

    /**
     * Check if room is available for a given time period
     */
    public function isAvailable($startTime, $endTime, $excludeEventId = null)
    {
        $query = $this->calendarEvents()
            ->where(function ($q) use ($startTime, $endTime) {
                $q->where(function ($q) use ($startTime, $endTime) {
                    $q->where('start_time', '<', $endTime)
                      ->where('end_time', '>', $startTime);
                });
            });

        if ($excludeEventId) {
            $query->where('id', '!=', $excludeEventId);
        }

        return $query->count() === 0;
    }
}
