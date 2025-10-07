<?php

namespace App\Services;

use App\Models\Room;
use Illuminate\Validation\ValidationException;

class RoomService
{
    public function getAll(array $filters = [], array $pagination = [])
    {
        $query = Room::with(['reservations', 'fixedSchedules']);

        // filter name (pencarian parsial)
        if (!empty($filters['name'])) {
            $query->where('name', 'like', '%' . $filters['name'] . '%');
        }

        // filter capacity (>=)
        if (!empty($filters['capacity'])) {
            $query->where('capacity', '>=', $filters['capacity']);
        }

        // filter status (exact match)
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // pagination default: page 1, per_page 10
        $perPage = isset($pagination['per_page']) ? (int) $pagination['per_page'] : 10;
        $page = isset($pagination['page']) ? (int) $pagination['page'] : 1;

        return $query->paginate($perPage, ['*'], 'page', $page);
    }

    public function find($id)
    {
        return Room::with(['reservations.user', 'fixedSchedules'])->findOrFail($id);
    }

    public function create(array $data)
    {
        return Room::create($data);
    }

    public function update($id, array $data)
    {
        $room = Room::findOrFail($id);
        $room->update($data);
        return $room;
    }

    public function delete($id)
    {
        $room = Room::findOrFail($id);

        // cek reservasi aktif (pending/approved)
        $activeReservation = $room->reservations()
            ->whereIn('status', ['pending', 'approved'])
            ->exists();

        // cek fixed schedule aktif
        $hasFixedSchedule = $room->fixedSchedules()->exists();

        if ($activeReservation || $hasFixedSchedule) {
            throw ValidationException::withMessages([
                'room' => 'Ruangan tidak bisa dihapus karena masih memiliki reservasi aktif atau jadwal tetap.'
            ]);
        }

        return $room->delete();
    }
}
