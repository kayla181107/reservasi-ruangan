<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ReservationLogResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'            => $this->id,
            'reservation_id'=> $this->reservation_id,
            'user'          => $this->user ? $this->user->name : null,
            'action'        => $this->action,
            'description'   => $this->description,
            'created_at'    => $this->created_at->format('Y-m-d H:i:s'),
        ];
    }
}
