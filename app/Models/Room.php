<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

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
}
