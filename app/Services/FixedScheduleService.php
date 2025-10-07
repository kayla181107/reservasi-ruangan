<?php

namespace App\Services;

use App\Models\FixedSchedule;

class FixedScheduleService
{
    public function getAllFiltered(array $filters = [], $perPage = 10)
    {
        $query = FixedSchedule::with('room');

        // 🔍 Filter Search
        if (!empty($filters['search'])) {
            $searchTerm = strtolower($filters['search']);
            $query->whereHas('room', function ($q) use ($searchTerm) {
                $q->whereRaw('LOWER(name) LIKE ?', ["%{$searchTerm}%"]);
            });
        }

        // 🗓 Filter Hari
        if (!empty($filters['day_of_week'])) {
            $query->where('day_of_week', $filters['day_of_week']);
        }

        // 🕒 Filter Jam Mulai
        if (!empty($filters['start_time'])) {
            $query->where('start_time', '>=', $filters['start_time']);
        }

        // 🕒 Filter Jam Selesai
        if (!empty($filters['end_time'])) {
            $query->where('end_time', '<=', $filters['end_time']);
        }

        // 🏠 Filter Berdasarkan Room ID
        if (!empty($filters['room_id'])) {
            $query->where('room_id', $filters['room_id']);
        }

        // 📑 Pagination
        return $query->orderBy('day_of_week', 'asc')->paginate($perPage);
    }

    public function find($id)
    {
        return FixedSchedule::with('room')->findOrFail($id);
    }

    public function create(array $data)
    {
        return FixedSchedule::create($data);
    }

    public function update($id, array $data)
    {
        $schedule = FixedSchedule::findOrFail($id);
        $schedule->update($data);
        return $schedule;
    }

    public function delete($id)
    {
        $schedule = FixedSchedule::findOrFail($id);
        return $schedule->delete();
    }
}
