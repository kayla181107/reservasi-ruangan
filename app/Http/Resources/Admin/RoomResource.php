<?php
namespace App\Http\Resources\Admin;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Admin\ReservationResource;
use App\Http\Resources\Admin\FixedScheduleResource;

class RoomResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'              => $this->id,
            'name'            => $this->name,
            'capacity'        => $this->capacity,
            'description'     => $this->description,
            'status'          => $this->status,
            'reservations'    => ReservationResource::collection($this->whenLoaded('reservations')),
            'fixed_schedules' => FixedScheduleResource::collection($this->whenLoaded('fixedSchedules')),
        ];
    }
}
