<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Daftar command yang akan dijalankan aplikasi.
     *
     * @var array
     */
    protected $commands = [
        \App\Console\Commands\UpdateRoomStatus::class, // Command update status ruangan
    ];

    /**
     * Jadwal perintah yang harus dijalankan oleh scheduler.
     */
    protected function schedule(Schedule $schedule)
    {
        // Jalankan update status ruangan setiap 1 menit
        $schedule->command('rooms:update-status')
                 ->everyMinute()
                 ->appendOutputTo(storage_path('logs/room_status.log')); // simpan output ke log
    }

    /**
     * Daftarkan command untuk aplikasi.
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
