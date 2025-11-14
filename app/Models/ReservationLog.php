<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Reservation;
use App\Models\User;

class ReservationLog extends Model
{
    use HasFactory;

    protected $table = 'reservation_logs';

    protected $fillable = [
        'reservation_id',
        'user_id',
        'action',
        'description',
    ];

    public $timestamps = true;

    /**
     * Relasi ke reservasi utama
     */
    public function reservation()
    {
        return $this->belongsTo(Reservation::class, 'reservation_id');
    }

    /**
     * Relasi ke user yang melakukan aksi
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
