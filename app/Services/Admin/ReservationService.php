<?php

namespace App\Services\Admin;

use App\Models\Reservation;
use App\Models\ReservationLog;
use Illuminate\Support\Facades\Mail;
use App\Mail\ReservationApprovedMail;
use App\Mail\ReservationRejectedMail;
use App\Mail\ReservationCanceledByOverlapMail;
use App\Services\Traits\ReservationCommonTrait;
use Illuminate\Validation\ValidationException;

class ReservationService
{
    use ReservationCommonTrait;

    public function updateStatus($id, array $data)
    {
        $reservation = Reservation::with(['user', 'room'])->findOrFail($id);

        // 🚫 Cegah ubah status jika tanggal reservasi sudah lewat
        if (now()->toDateString() > $reservation->date) {
            throw ValidationException::withMessages([
                'date' => 'Tidak dapat mengubah status. Reservasi sudah melewati tanggal yang ditentukan.'
            ]);
        }

        // 🚫 Jika tanggal sama dengan hari ini, tapi waktu reservasi sudah berakhir
        if (now()->toDateString() === $reservation->date && now()->format('H:i:s') > $reservation->end_time) {
            throw ValidationException::withMessages([
                'time' => 'Tidak dapat mengubah status. Waktu reservasi sudah berakhir hari ini.'
            ]);
        }

        if (!in_array($data['status'], ['approved', 'rejected', 'pending'])) {
            throw ValidationException::withMessages([
                'status' => 'Status reservasi tidak valid.'
            ]);
        }

        $oldStatus = strtoupper($reservation->status);
        $newStatus = strtoupper($data['status']);

        $reservation->update([
            'status' => $data['status'],
            'reason' => $data['reason'] ?? null,
        ]);

        // Log perubahan status oleh admin
        ReservationLog::create([
            'reservation_id' => $reservation->id,
            'user_id' => auth()->id(),
            'action' => 'UPDATE_STATUS',
            'description' => "Status reservasi diubah dari {$oldStatus} menjadi {$newStatus} oleh admin " . (auth()->user()->name ?? 'System'),
        ]);

        // ====== Jika disetujui (APPROVED) ======
        if ($data['status'] === 'approved') {
            // 🕒 Jangan aktifkan ruangan langsung
            // Ruangan akan otomatis aktif saat waktu reservasi tiba lewat command UpdateRoomStatus

            // Kirim email ke user
            if ($reservation->user && $reservation->user->email) {
                Mail::to($reservation->user->email)
                    ->send(new ReservationApprovedMail($reservation));
            }

            // Auto reject reservasi lain yang bentrok
            $overlaps = Reservation::where('room_id', $reservation->room_id)
                ->where('day_of_week', $reservation->day_of_week)
                ->where('id', '!=', $reservation->id)
                ->where('status', 'pending')
                ->where(function ($q) use ($reservation) {
                    $q->whereBetween('start_time', [$reservation->start_time, $reservation->end_time])
                      ->orWhereBetween('end_time', [$reservation->start_time, $reservation->end_time])
                      ->orWhere(function ($q2) use ($reservation) {
                          $q2->where('start_time', '<=', $reservation->start_time)
                             ->where('end_time', '>=', $reservation->end_time);
                      });
                })
                ->get();

            foreach ($overlaps as $overlap) {
                $overlap->update([
                    'status' => 'rejected',
                    'reason' => 'Ditolak otomatis karena bentrok dengan reservasi lain yang sudah disetujui.',
                ]);

                // Log auto reject overlap
                ReservationLog::create([
                    'reservation_id' => $overlap->id,
                    'action' => 'AUTO_REJECT_OVERLAP',
                    'description' => "Reservasi #{$overlap->id} ditolak otomatis karena bentrok dengan reservasi yang disetujui (#{$reservation->id}).",
                    'user_id' => auth()->id(),
                ]);

                if ($overlap->user && $overlap->user->email) {
                    Mail::to($overlap->user->email)
                        ->send(new ReservationCanceledByOverlapMail($overlap, $reservation));
                }
            }
        }

        // ====== Jika ditolak (REJECTED) ======
        if ($data['status'] === 'rejected' && $reservation->user && $reservation->user->email) {
            Mail::to($reservation->user->email)
                ->send(new ReservationRejectedMail($reservation, $data['reason'] ?? null));
        }

        return $reservation;
    }

    public function delete($id)
    {
        $reservation = Reservation::findOrFail($id);
        $reservation->delete();

        // Log penghapusan reservasi
        ReservationLog::create([
            'reservation_id' => $id,
            'action' => 'DELETE',
            'description' => 'Reservasi dihapus oleh admin ' . (auth()->user()->name ?? 'System'),
            'user_id' => auth()->id(),
        ]);

        return true;
    }

    public function getAllWithFilters(array $filters = [], int $perPage = 10)
    {
        $query = Reservation::with(['user', 'room'])
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
