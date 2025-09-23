<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class FixedScheduleResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'          => $this->id,
            'room_id'     => $this->room_id,
            'day_of_week' => $this->day_of_week,
            'start_time'  => Carbon::parse($this->start_time)->format('H:i'),
            'end_time'    => Carbon::parse($this->end_time)->format('H:i'),
            'description' => $this->description,
        ];
    }
}
