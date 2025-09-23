<?php

namespace App\Http\Resources\Karyawan;

use Illuminate\Http\Resources\Json\JsonResource;

class ReservationResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'         => $this->id,
            'user_id'    => $this->user_id,
            'room'       => [
                'id'   => $this->room->id,
                'name' => $this->room->nama_ruangan, 
            ],
            'room_id'    => $this->room_id,
            'date'       => $this->date ? $this->date->format('Y-m-d') : null,
            'start_time' => $this->start_time,
            'end_time'   => $this->end_time,
            'status'     => $this->status,
            'reason'     => $this->reason,
            'created_at' => $this->created_at->toDateTimeString(),
        ];
    }
}
