<?php

namespace App\Services;

use App\Models\FixedSchedule;
use App\Models\Reservation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;
use App\Mail\ReservationRejectedByFixedScheduleMail;

class FixedScheduleService
{
    public function getAll()
    {
        return FixedSchedule::with(['room', 'user'])->latest()->get();
    }

    public function create(array $data)
    {
        return DB::transaction(function () use ($data) {

            $data['user_id'] = Auth::id();

            $fixedSchedule = FixedSchedule::create($data);

            // Cari reservation bentrok
            $conflictReservations = Reservation::where('room_id', $fixedSchedule->room_id)
                ->whereIn('status', ['pending', 'approved'])
                ->whereRaw("DAYNAME(date) = ?", [self::mapDayToEnglish($fixedSchedule->day_of_week)])
                ->where(function ($q) use ($fixedSchedule) {
                    $q->whereBetween('start_time', [$fixedSchedule->start_time, $fixedSchedule->end_time])
                      ->orWhereBetween('end_time', [$fixedSchedule->start_time, $fixedSchedule->end_time])
                      ->orWhere(function ($q2) use ($fixedSchedule) {
                          $q2->where('start_time', '<=', $fixedSchedule->start_time)
                             ->where('end_time', '>=', $fixedSchedule->end_time);
                      });
                })
                ->get();

            foreach ($conflictReservations as $reservation) {
                $reservation->update([
                    'status' => 'rejected',
                    'reason' => 'Ditolak otomatis karena bentrok dengan Fixed Schedule.'
                ]);

                if ($reservation->user?->email) {
                    Mail::to($reservation->user->email)
                        ->send(new ReservationRejectedByFixedScheduleMail($reservation));
                }
            }

            return $fixedSchedule;
        });
    }

    public function update(FixedSchedule $fixedSchedule, array $data)
    {
        return DB::transaction(function () use ($fixedSchedule, $data) {

            $data['user_id'] = Auth::id();

            $fixedSchedule->update($data);

            // Cek konflik
            $conflictReservations = Reservation::where('room_id', $fixedSchedule->room_id)
                ->whereIn('status', ['pending', 'approved'])
                ->whereRaw("DAYNAME(date) = ?", [self::mapDayToEnglish($fixedSchedule->day_of_week)])
                ->where(function ($q) use ($fixedSchedule) {
                    $q->whereBetween('start_time', [$fixedSchedule->start_time, $fixedSchedule->end_time])
                      ->orWhereBetween('end_time', [$fixedSchedule->start_time, $fixedSchedule->end_time])
                      ->orWhere(function ($q2) use ($fixedSchedule) {
                          $q2->where('start_time', '<=', $fixedSchedule->start_time)
                             ->where('end_time', '>=', $fixedSchedule->end_time);
                      });
                })
                ->get();

            foreach ($conflictReservations as $reservation) {
                $reservation->update([
                    'status' => 'rejected',
                    'reason' => 'Ditolak otomatis karena bentrok dengan Fixed Schedule.'
                ]);

                if ($reservation->user?->email) {
                    Mail::to($reservation->user->email)
                        ->send(new ReservationRejectedByFixedScheduleMail($reservation));
                }
            }

            return $fixedSchedule;
        });
    }

    public function delete(FixedSchedule $fixedSchedule)
    {
        return $fixedSchedule->delete();
    }

    private static function mapDayToEnglish(string $dayIndo): string
    {
        $map = [
            'Senin' => 'Monday',
            'Selasa' => 'Tuesday',
            'Rabu' => 'Wednesday',
            'Kamis' => 'Thursday',
            'Jumat' => 'Friday',
            'Sabtu' => 'Saturday',
            'Minggu' => 'Sunday',
        ];
        return $map[$dayIndo] ?? $dayIndo;
    }
}
