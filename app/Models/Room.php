<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Room extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'capacity',
        'description',
        'status', 
    ];

    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }

    public function fixedSchedules()
    {
        return $this->hasMany(FixedSchedule::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'reservations')
                    ->withPivot(['date', 'start_time', 'end_time', 'status'])
                    ->withTimestamps();
    }

    /**
     * Accessor status_aktual (real-time).
     * Akan bernilai "active" jika ada reservasi approved
     * di tanggal & jam sekarang.
     */
    public function getStatusAktualAttribute()
    {
        $now = Carbon::now();
        $today = $now->toDateString();
        $timeNow = $now->format('H:i:s');

        $adaReservasi = $this->reservations()
            ->where('date', $today)
            ->where('status', 'approved')
            ->where('start_time', '<=', $timeNow)
            ->where('end_time', '>=', $timeNow)
            ->exists();

        return $adaReservasi ? 'active' : 'inactive';
    }
}
