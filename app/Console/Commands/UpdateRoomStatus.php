<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Reservation;
use App\Models\ReservationLog;
use App\Models\FixedSchedule;
use App\Models\Room;
use Carbon\Carbon;

class UpdateRoomStatus extends Command
{
    protected $signature = 'rooms:update-status';
    protected $description = 'Update status ruangan berdasarkan reservasi & jadwal tetap, serta mencatat log setiap perubahan';

    public function handle()
    {
        $now = Carbon::now('Asia/Jakarta');
        $today = $now->format('Y-m-d');

        // Konversi hari Inggris ke day_of_week
        $todayDay = match(strtolower($now->format('l'))) {
            'monday' => 'Senin',
            'tuesday' => 'Selasa',
            'wednesday' => 'Rabu',
            'thursday' => 'Kamis',
            'friday' => 'Jumat',
            'saturday' => 'Sabtu',
            'sunday' => 'Minggu',
        };

        // Ambil semua reservasi approved hari ini
        $reservations = Reservation::with('room')
            ->whereDate('date', $today)
            ->where('status', 'approved')
            ->get();

        // Ambil semua fixed schedule hari ini
        $fixedSchedules = FixedSchedule::with('room')
            ->where('day_of_week', $todayDay)
            ->get();

        // Gabungkan semua room_id dari reservasi & fixed schedule
        $allRoomIds = $reservations->pluck('room_id')
            ->merge($fixedSchedules->pluck('room_id'))
            ->unique();

        foreach ($allRoomIds as $roomId) {

            $roomReservations = $reservations->where('room_id', $roomId);
            $roomFixed = $fixedSchedules->where('room_id', $roomId);
            $room = $roomReservations->first()?->room ?? $roomFixed->first()?->room;

            if (!$room) continue;

            // Cek reservasi aktif sekarang
            $reservationActive = $roomReservations->contains(function ($r) use ($now) {
                $start = Carbon::parse($r->date . ' ' . $r->start_time);
                $end = Carbon::parse($r->date . ' ' . $r->end_time);
                return $now->between($start, $end);
            });

            // Cek fixed schedule aktif sekarang
            $fixedActive = $roomFixed->contains(function ($fs) use ($now) {
                $start = Carbon::parse($now->format('Y-m-d') . ' ' . $fs->start_time);
                $end = Carbon::parse($now->format('Y-m-d') . ' ' . $fs->end_time);
                return $now->between($start, $end);
            });

            // Update status ruangan
            if ($reservationActive || $fixedActive) {
                if ($room->status !== 'active') {
                    $room->update(['status' => 'active']);

                    $logReservation = $roomReservations->first();
                    $description = $logReservation
                        ? "Ruangan {$room->name} diaktifkan karena sedang digunakan (reservasi)."
                        : "Ruangan {$room->name} diaktifkan karena jadwal tetap hari ini.";

                    ReservationLog::create([
                        'reservation_id' => $logReservation?->id,
                        'action' => 'Aktif',
                        'description' => $description,
                    ]);

                    $this->info("Room {$room->name} diaktifkan.");
                }
            } else {
                if ($room->status !== 'inactive') {
                    $room->update(['status' => 'inactive']);

                    $lastReservation = $roomReservations->sortByDesc('end_time')->first();
                    $description = $lastReservation
                        ? "Ruangan {$room->name} dinonaktifkan karena reservasi selesai."
                        : "Ruangan {$room->name} dinonaktifkan karena tidak ada jadwal hari ini.";

                    ReservationLog::create([
                        'reservation_id' => $lastReservation?->id,
                        'action' => 'Nonaktif',
                        'description' => $description,
                    ]);

                    $this->info("Room {$room->name} dinonaktifkan.");
                }
            }
        }

        $this->info('Semua status ruangan & log telah diperbarui.');
        return self::SUCCESS;
    }
}
