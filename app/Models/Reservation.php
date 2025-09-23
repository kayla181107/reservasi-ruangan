<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;
use App\Models\Room;

class Reservation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'room_id',
        'date',
        'start_time',
        'end_time',
        'status',
        'reason',
    ];

    protected $casts = [
        'date'       => 'date:Y-m-d', 
        'start_time' => 'string',     
        'end_time'   => 'string',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    /**
     * Scope untuk cek overlapping reservation
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
