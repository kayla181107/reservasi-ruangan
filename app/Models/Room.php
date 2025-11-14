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

    /**
     * Relasi ke tabel reservations
     */
    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }

    /**
     * Relasi ke tabel fixed_schedules
     */
    public function fixedSchedules()
    {
        return $this->hasMany(FixedSchedule::class);
    }

    /**
     * Relasi ke user melalui reservations (many to many)
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'reservations')
                    ->withPivot(['date', 'start_time', 'end_time', 'status'])
                    ->withTimestamps();
    }

    /**
     * Accessor status_aktual (real-time)
     * Bernilai "active" jika ada reservasi approved pada jam & tanggal sekarang.
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

    /**
     * Scope untuk filter ruangan berdasarkan status aktual
     */
    public function scopeAktif($query)
    {
        return $query->whereHas('reservations', function ($q) {
            $now = Carbon::now();
            $today = $now->toDateString();
            $timeNow = $now->format('H:i:s');

            $q->where('date', $today)
              ->where('status', 'approved')
              ->where('start_time', '<=', $timeNow)
              ->where('end_time', '>=', $timeNow);
        });
    }
}
