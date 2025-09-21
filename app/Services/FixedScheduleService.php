<?php

namespace App\Services;

use App\Models\FixedSchedule;
use Illuminate\Database\Eloquent\Collection;

class FixedScheduleService
{
    /**
     * Get all fixed schedules
     */
    public function getAll(): Collection
    {
        return FixedSchedule::with('room')->get();
    }

    /**
     * Find fixed schedule by id
     */
    public function findById(int $id): ?FixedSchedule
    {
        return FixedSchedule::with('room')->find($id);
    }

    /**
     * Create new fixed schedule
     */
    public function create(array $data): FixedSchedule
    {
        return FixedSchedule::create($data);
    }

    /**
     * Update fixed schedule
     */
    public function update(FixedSchedule $schedule, array $data): FixedSchedule
    {
        $schedule->update($data);
        return $schedule;
    }

    /**
     * Delete fixed schedule
     */
    public function delete(FixedSchedule $schedule): bool
    {
        return $schedule->delete();
    }
}
