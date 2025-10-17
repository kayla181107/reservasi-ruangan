<?php

namespace App\Services\Karyawan;

use App\Models\Reservation;
use App\Models\FixedSchedule;
use App\Models\ReservationLog; 
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;
use App\Services\Traits\ReservationCommonTrait;
use Illuminate\Support\Facades\Mail;
use App\Mail\ReservationRejectedByFixedScheduleMail;
use App\Mail\ReservationRejectedMail;
use Illuminate\Support\Facades\Auth; 

class ReservationService
{
    use ReservationCommonTrait;

    /**
     * Membuat reservasi baru oleh karyawan.
     */
    public function create(array $data)
    {
        $data['status'] = 'pending';

        $date     = Carbon::parse($data['date'])->format('Y-m-d');
        $start    = Carbon::parse($date . ' ' . $data['start_time']);
        $end      = Carbon::parse($date . ' ' . $data['end_time']);

        // Tidak boleh booking di waktu yang sudah lewat
        if ($start->lt(now())) {
            throw ValidationException::withMessages([
                'date' => 'Tidak bisa membuat reservasi di waktu yang sudah lewat.'
            ]);
        }

        // Maksimal H-30
        if ($start->gt(now()->addDays(30))) {
            throw ValidationException::withMessages([
                'date' => 'Reservasi hanya bisa dilakukan maksimal H-30 sebelum tanggal meeting.'
            ]);
        }

        // Validasi start < end
        if ($start->greaterThanOrEqualTo($end)) {
            throw ValidationException::withMessages([
                'time' => 'Waktu mulai harus lebih awal dari waktu selesai.'
            ]);
        }

        // Validasi durasi maksimal 3 jam (180 menit)
        $duration = $start->diffInMinutes($end, false);
        if ($duration > 180) {
            throw ValidationException::withMessages([
                'duration' => "Durasi meeting maksimal 3 jam. Anda input: {$duration} menit."
            ]);
        }

        // Normalisasi data setelah validasi
        $data['date']        = $date;
        $data['start_time']  = $start->format('H:i');
        $data['end_time']    = $end->format('H:i');
        $data['day_of_week'] = Carbon::parse($date)->locale('id')->dayName;

        /**
         * 1️⃣ Cek bentrok dengan Fixed Schedule
         */
        $conflictFixed = FixedSchedule::where('room_id', $data['room_id'])
            ->where('day_of_week', $data['day_of_week'])
            ->where(function ($q) use ($start, $end) {
                $q->whereBetween('start_time', [$start->format('H:i'), $end->format('H:i')])
                  ->orWhereBetween('end_time', [$start->format('H:i'), $end->format('H:i')])
                  ->orWhere(function ($q2) use ($start, $end) {
                      $q2->where('start_time', '<=', $start->format('H:i'))
                         ->where('end_time', '>=', $end->format('H:i'));
                  });
            })
            ->exists();

        if ($conflictFixed) {
            $data['status'] = 'rejected';
            $data['reason'] = 'Ditolak otomatis karena bentrok dengan Fixed Schedule.';
            $reservation = Reservation::create($data);

            // Kirim email ke user
            if ($reservation->user && $reservation->user->email) {
                Mail::to($reservation->user->email)
                    ->send(new ReservationRejectedByFixedScheduleMail($reservation));
            }

            // Tambahkan log auto reject karena fixed schedule
            ReservationLog::create([
                'reservation_id' => $reservation->id,
                'action' => 'AUTO_REJECT_FIXED',
                'description' => 'Reservasi otomatis ditolak karena bentrok dengan Fixed Schedule.',
                'user_id' => Auth::id() ?? $data['user_id'],
            ]);

            return $reservation;
        }

        /**
         * 2️⃣ Cek bentrok dengan reservasi user sendiri
         */
        $conflictReservations = Reservation::overlapping(
            $data['room_id'], $start, $end
        )
            ->whereDate('date', $date)
            ->whereIn('status', ['pending', 'approved'])
            ->where('user_id', $data['user_id'])
            ->get();

        if ($conflictReservations->count() > 0) {
            $data['status'] = 'rejected';
            $data['reason'] = 'Ditolak otomatis karena bentrok dengan reservasi lain.';
            $reservation = Reservation::create($data);

            // Kirim email ke user
            if ($reservation->user && $reservation->user->email) {
                Mail::to($reservation->user->email)
                    ->send(new ReservationRejectedMail($reservation));
            }

            // Tambahkan log auto reject karena reservasi bentrok
            ReservationLog::create([
                'reservation_id' => $reservation->id,
                'action' => 'AUTO_REJECT_CONFLICT',
                'description' => 'Reservasi otomatis ditolak karena bentrok dengan reservasi lain.',
                'user_id' => Auth::id() ?? $data['user_id'],
            ]);

            return $reservation;
        }

        /**
         * 3️⃣ Tidak ada bentrok → buat reservasi baru
         */
        $reservation = Reservation::create($data);

        // Tambahkan log CREATE
        ReservationLog::create([
            'reservation_id' => $reservation->id,
            'action' => 'CREATE',
            'description' => 'Reservasi baru dibuat oleh karyawan dengan ID: ' . ($data['user_id'] ?? 'tidak diketahui'),
            'user_id' => Auth::id() ?? $data['user_id'],
        ]);

        return $reservation;
    }

    /**
     * Ambil semua reservasi milik user tertentu.
     */
    public function getUserReservations(int $userId)
    {
        return Reservation::with('room')
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Ambil detail satu reservasi milik user tertentu.
     */
    public function getUserReservationById(int $userId, int $id)
    {
        $reservation = Reservation::with(['user', 'room'])
            ->where('user_id', $userId)
            ->find($id);

        if (! $reservation) {
            abort(403, 'Anda tidak punya akses untuk melihat reservasi ini.');
        }

        return $reservation;
    }

    /**
     * Batalkan reservasi oleh karyawan.
     */
    public function cancel(int $reservationId, int $userId, string $reason)
    {
        $reservation = Reservation::where('id', $reservationId)
            ->where('user_id', $userId)
            ->whereIn('status', ['pending', 'approved'])
            ->firstOrFail();

        $oldStatus = strtoupper($reservation->status);

        $reservation->update([
            'status' => 'rejected', 
            'reason' => $reason,
        ]);

        // Tambahkan log pembatalan
        ReservationLog::create([
            'reservation_id' => $reservation->id,
            'action' => 'CANCEL',
            'description' => 'Reservasi dibatalkan oleh karyawan. Alasan: ' . $reason . 
                             '. Status sebelumnya: ' . $oldStatus . '.',
            'user_id' => $userId,
        ]);

        return $reservation;
    }

    /**
     * Ambil semua reservasi user dengan filter.
     */
    public function getUserReservationsWithFilters(int $userId, array $filters = [], int $perPage = 10)
    {
        $query = Reservation::with('room')
            ->where('user_id', $userId)
            ->orderBy('date', 'desc')
            ->orderBy('start_time', 'asc');

        if (!empty($filters['date'])) {
            $query->whereDate('date', $filters['date']);
        }

        if (!empty($filters['day_of_week'])) {
            $query->where('day_of_week', $filters['day_of_week']);
        }

        if (!empty($filters['start_time'])) {
            $query->whereTime('start_time', '>=', $filters['start_time']);
        }

        if (!empty($filters['end_time'])) {
            $query->whereTime('end_time', '<=', $filters['end_time']);
        }

        return $query->paginate($perPage);
    }
}
