<?php

use Illuminate\Support\Facades\Artisan;
use App\Models\Reservation;
use App\Models\FixedSchedule;
use Carbon\Carbon;

Artisan::command('rooms:update-status', function () {
    $now = Carbon::now();

    // 1️ Ambil semua fixed schedule untuk hari ini (prioritas utama)
    $fixedSchedules = FixedSchedule::with('room')
        ->whereDate('date', $now->toDateString())
        ->get();

    foreach ($fixedSchedules as $fs) {
        $date   = Carbon::parse($fs->date)->toDateString();
        $start  = Carbon::parse($date . ' ' . $fs->start_time);
        $end    = Carbon::parse($date . ' ' . $fs->end_time);

        if ($now->between($start, $end)) {
            $fs->room->update(['status' => 'active']);
            $this->info("✅ [FixedSchedule] Ruangan {$fs->room->name} AKTIF");
            continue; // langsung skip reservasi, fixed schedule menang
        } elseif ($now->greaterThan($end)) {
            $fs->room->update(['status' => 'inactive']);
            $this->info("⏰ [FixedSchedule] Ruangan {$fs->room->name} NON-AKTIF");
        } else {
            $this->info("⌛ [FixedSchedule] Ruangan {$fs->room->name} menunggu jam mulai");
        }
    }

    // 2️⃣ Ambil semua reservasi yang approved untuk hari ini (hanya yang tidak kena fixed schedule)
    $reservations = Reservation::with('room')
        ->where('status', 'approved')
        ->whereDate('date', $now->toDateString())
        ->get();

    foreach ($reservations as $res) {
        $date   = Carbon::parse($res->date)->toDateString();
        $start  = Carbon::parse($date . ' ' . $res->start_time);
        $end    = Carbon::parse($date . ' ' . $res->end_time);

        // 🔍 Cek apakah ada fixed schedule yang bentrok dengan reservasi ini
        $hasFixed = FixedSchedule::where('room_id', $res->room_id)
            ->where('date', $res->date)
            ->where(function ($q) use ($start, $end) {
                $q->whereBetween('start_time', [$start->format('H:i:s'), $end->format('H:i:s')])
                  ->orWhereBetween('end_time', [$start->format('H:i:s'), $end->format('H:i:s')])
                  ->orWhere(function ($q2) use ($start, $end) {
                      $q2->where('start_time', '<=', $start->format('H:i:s'))
                         ->where('end_time', '>=', $end->format('H:i:s'));
                  });
            })
            ->exists();

        if ($hasFixed) {
            $this->warn("⚡ [Reservasi] Ruangan {$res->room->name} diabaikan (ada FixedSchedule)");
            continue; 
        }

        if ($now->between($start, $end)) {
            $res->room->update(['status' => 'active']);
            $this->info("✅ [Reservasi] Ruangan {$res->room->name} AKTIF");
        } elseif ($now->greaterThan($end)) {
            $res->room->update(['status' => 'inactive']);
            $this->info("⏰ [Reservasi] Ruangan {$res->room->name} NON-AKTIF");
        } else {
            $this->info("⌛ [Reservasi] Ruangan {$res->room->name} menunggu jam mulai");
        }
    }
})->purpose('Update status ruangan dengan prioritas FixedSchedule > Reservation');
