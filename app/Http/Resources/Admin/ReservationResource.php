<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Resources\Json\JsonResource;

class ReservationResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'         => $this->id,

            // relasi user
            'user'       => [
                'id'    => $this->user->id,
                'name'  => $this->user->name,
                'email' => $this->user->email,
            ],

            
            'room'       => [
                'id'   => $this->room->id,
                'name' => $this->room->name,
            ],

            // field utama sesuai tabel
            'date'       => $this->date,
            'start_time' => $this->start_time,
            'end_time'   => $this->end_time,
            'status'     => $this->status,
            'reason'     => $this->reason,

            // timestamp
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
        ];
    }
}
