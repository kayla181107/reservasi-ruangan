<?php

namespace App\Http\Resources\Karyawan;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Karyawan\RoomResource;

class FixedScheduleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'room_id'     => $this->room_id,
            'room'        => new RoomResource($this->whenLoaded('room')),
            'day_of_week' => $this->day_of_week,
            'start_time'  => $this->start_time,
            'end_time'    => $this->end_time,
            'description' => $this->description,
        ];
    }
}
