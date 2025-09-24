<?php

namespace App\Services\Karyawan;

use App\Models\Reservation;
use App\Models\FixedSchedule;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

class ReservationService
{
    public function create(array $data)
    {
        $data['status'] = 'pending';

        // Parse date & time
        $date  = Carbon::parse($data['date'])->format('Y-m-d');
        $start = Carbon::parse($date . ' ' . $data['start_time']);
        $end   = Carbon::parse($date . ' ' . $data['end_time']);

        // Validasi waktu
        if ($start >= $end) {
            throw ValidationException::withMessages([
                'time' => 'Start time must be before end time.'
            ]);
        }

        // Simpan tanggal & waktu
        $data['date']        = $date;
        $data['start_time']  = $start->format('H:i');
        $data['end_time']    = $end->format('H:i');

        //  Simpan day_of_week otomatis dari date (English: Monday, Tuesday, dst)
        $data['day_of_week'] = Carbon::parse($date)->format('l');

        // Validasi bentrok dengan FixedSchedule (HARUS ditolak)
        $conflictFixed = FixedSchedule::where('room_id', $data['room_id'])
            ->where('day_of_week', $data['day_of_week'])
            ->where(function ($q) use ($start, $end) {
                $q->whereBetween('start_time', [$start, $end])
                  ->orWhereBetween('end_time', [$start, $end])
                  ->orWhere(function ($q2) use ($start, $end) {
                      $q2->where('start_time', '<=', $start)
                         ->where('end_time', '>=', $end);
                  });
            })
            ->exists();

        if ($conflictFixed) {
            throw ValidationException::withMessages([
                'reservation' => 'Conflicts with fixed schedule.'
            ]);
        }

        // Cek bentrok dengan reservasi lain/
        $conflictReservation = Reservation::overlapping(
            $data['room_id'], $start, $end
        )->whereDate('date', $date)
         ->whereIn('status', ['pending', 'approved'])
         ->exists();

        if ($conflictReservation) {
            $data['reason'] = ($data['reason'] ?? '') . ' (Conflict, waiting for admin approval)';
        }

        // Simpan reservasi
        return Reservation::create($data);
    }

    public function getUserReservations($userId)
    {
        return Reservation::with('room')
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();
    }
}
