<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Room;
use App\Models\User;
use App\Models\Reservation;

class FixedSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'room_id',
        'day_of_week',
        'start_time',
        'end_time',
        'description',
        'status',
    ];

    protected $casts = [
        'day_of_week' => 'date:Y-m-d', // ✅ cast to date
        'start_time'  => 'string',     // ✅ save as string (format H:i)
        'end_time'    => 'string',     // ✅ save as string (format H:i)
    ];

    // Relation to Room (fixed schedule belongs to one room)
    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope to find overlapping reservations
     */
    public function scopeOverlapping($query, $roomId, $start, $end)
    {
        return $query->where('room_id', $roomId)
            ->whereIn('status', ['pending','approved'])
            ->where(function ($q) use ($start, $end) {
                $q->whereBetween('start_time', [$start, $end])
                  ->orWhereBetween('end_time', [$start, $end])
                  ->orWhere(function ($q2) use ($start, $end) {
                      $q2->where('start_time', '<=', $start)
                         ->where('end_time', '>=', $end);
                  });
            });
    }
}
