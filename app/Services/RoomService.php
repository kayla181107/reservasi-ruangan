<?php

namespace App\Services;

use App\Models\Room;
use Illuminate\Validation\ValidationException;

class RoomService
{
    public function getAll(array $filters = [], array $pagination = [])
    {
        $query = Room::with(['reservations', 'fixedSchedules']);

        if (!empty($filters['name'])) {
            $query->where('name', 'like', '%' . $filters['name'] . '%');
        }

        if (!empty($filters['capacity'])) {
            $query->where('capacity', '>=', $filters['capacity']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        $page = isset($pagination['page']) ? (int) $pagination['page'] : 1;
        $perPage = isset($pagination['per_page']) ? (int) $pagination['per_page'] : 10;

        if ($perPage < 1) {
            $perPage = 10;
        }

        return $query->orderBy('id', 'asc')->paginate($perPage, ['*'], 'page', $page);
    }

    public function find($id)
    {
        return Room::with(['reservations.user', 'fixedSchedules'])->find($id);
    }

    public function create(array $data)
    {
        return Room::create($data);
    }

    public function update($id, array $data)
    {
        $room = Room::find($id);

        if (!$room) {
            return null;
        }

        $room->update($data);
        return $room;
    }

    public function delete($id)
    {
        $room = Room::find($id);

        if (!$room) {
            return false;
        }

        $activeReservation = $room->reservations()
            ->whereIn('status', ['pending', 'approved'])
            ->exists();

        $hasFixedSchedule = $room->fixedSchedules()->exists();

        if ($activeReservation || $hasFixedSchedule) {
            throw ValidationException::withMessages([
                'room' => 'Ruangan tidak bisa dihapus karena masih memiliki reservasi aktif atau jadwal tetap.'
            ]);
        }

        return $room->delete();
    }
}
