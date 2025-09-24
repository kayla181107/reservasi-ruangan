<?php

namespace App\Http\Resources\Karyawan;

use Illuminate\Http\Resources\Json\JsonResource;

class RoomResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'            => $this->id,
            'name'          => $this->name,        
            'capacity'      => $this->capacity,    
            'description'   => $this->description, 
            'status'        => $this->status,
        ];
    }
}
