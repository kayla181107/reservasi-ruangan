<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FixedSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'room_id',
        'day_of_week',   
        'start_time',
        'end_time',
        'description',

    ];

    public function room()
    {
        return $this->belongsTo(Room::class);
    }
}
