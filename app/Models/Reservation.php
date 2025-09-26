<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;
use App\Models\Room;
use Carbon\Carbon;

class Reservation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'room_id',
        'date',
        'day_of_week',   // ✅ ganti dari hari → day_of_week
        'start_time',
        'end_time',
        'reason',
        'status',
    ];

    protected $casts = [
        'date'       => 'date:Y-m-d', // ✅ cast jadi tanggal saja
        'start_time' => 'string',     // ✅ simpan sebagai string (format H:i)
        'end_time'   => 'string',     // ✅ simpan sebagai string (format H:i)
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function setDateAttribute($value)
    {
        $this->attributes['date'] = $value;

        $carbon = Carbon::parse($value)->locale('en'); 
        $this->attributes['day_of_week'] = ucfirst($carbon->dayName); // Example: Monday
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
