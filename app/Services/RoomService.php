<?php

namespace App\Services;

use App\Models\Room;
use Illuminate\Database\Eloquent\Collection;

class RoomService
{
    /**
     * Get all rooms
     */
    public function getAll(): Collection
    {
        return Room::all();
    }

    /**
     * Find room by id
     */
    public function findById(int $id): ?Room
    {
        return Room::find($id);
    }

    /**
     * Create new room
     */
    public function create(array $data): Room
    {
        return Room::create($data);
    }

    /**
     * Update room
     */
    public function update(Room $room, array $data): Room
    {
        $room->update($data);
        return $room;
    }

    /**
     * Delete room
     */
    public function delete(Room $room): bool
    {
        return $room->delete();
    }
}
