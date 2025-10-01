<?php

use Illuminate\Support\Facades\Artisan;
use App\Models\Reservation;
use App\Models\FixedSchedule;
use App\Models\Room;
use Carbon\Carbon;

Artisan::command('rooms:update-status', function () {
    $now = Carbon::now();

    //  Reset semua ruangan menjadi inactive dulu
    Room::query()->update(['status' => 'inactive']);
    $this->info("🔄 semua ruanagan di reset ke INACTIVE");

    //  FixedSchedule 
    $fixedSchedules = FixedSchedule::with('room')
        ->whereDate('date', $now->toDateString()) 
        ->get();

    foreach ($fixedSchedules as $fs) {
        $date   = Carbon::parse($fs->date)->toDateString(); 
        $start  = Carbon::parse($date . ' ' . $fs->start_time); 
        $end    = Carbon::parse($date . ' ' . $fs->end_time);   

        if ($now->between($start, $end)) {
            $fs->room->update(['status' => 'active']); 
            $this->info("✅ [FixedSchedule] ruangan {$fs->room->name} AKTIF");
        }
    }

    //  Reservation 
    $reservations = Reservation::with('room')
        ->where('status', 'approved')
        ->whereDate('date', $now->toDateString()) 
        ->get();

    foreach ($reservations as $res) {
        $date   = Carbon::parse($res->date)->toDateString(); 
        $start  = Carbon::parse($date . ' ' . $res->start_time); 
        $end    = Carbon::parse($date . ' ' . $res->end_time);   

        $hasFixed = FixedSchedule::where('room_id', $res->room_id)
            ->where('date', $res->date)
            ->whereTime('start_time', '<=', $now->format('H:i:s')) 
            ->whereTime('end_time', '>=', $now->format('H:i:s'))   
            ->exists();

        if ($hasFixed) {
            $this->warn("⚡ [Reservation] ruangan {$res->room->name} di abaikan (ada FixedSchedule)");
            continue;
        }

        if ($now->between($start, $end)) {
            $res->room->update(['status' => 'active']);
            $this->info("✅ [Reservation] ruanagan {$res->room->name} AKTIF");
        }
    }
})->purpose('Update status ruangan dengan prioritas FixedSchedule > Reservation');
