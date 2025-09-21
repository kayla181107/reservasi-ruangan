<?php

namespace App\Services;

use App\Models\Reservation;
use Illuminate\Database\Eloquent\Collection;

class ReservationService
{
    /**
     * Get all reservations
     */
    public function getAll(): Collection
    {
        return Reservation::with(['user', 'room'])->get();
    }

    /**
     * Find reservation by id
     */
    public function findById(int $id): ?Reservation
    {
        return Reservation::with(['user', 'room'])->find($id);
    }

    /**
     * Create new reservation
     */
    public function create(array $data): Reservation
    {
        return Reservation::create($data);
    }

    /**
     * Update reservation
     */
    public function update(Reservation $reservation, array $data): Reservation
    {
        $reservation->update($data);
        return $reservation;
    }

    /**
     * Delete reservation
     */
    public function delete(Reservation $reservation): bool
    {
        return $reservation->delete();
    }
}
