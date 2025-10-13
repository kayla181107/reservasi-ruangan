<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Room;
use App\Models\User;
use App\Models\Reservation;
use Carbon\Carbon;

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
        'start_time' => 'string',     
        'end_time'   => 'string',     
    ];

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }

    /**
     * Scope untuk mencari overlapping reservation
     */
    public function scopeOverlapping($query, $roomId, $start, $end)
    {
        $start = Carbon::parse($start)->format('H:i');
        $end   = Carbon::parse($end)->format('H:i');

        return $query->where('room_id', $roomId)
            ->whereIn('status', ['pending', 'approved', 'rejected', 'canceled'])
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
