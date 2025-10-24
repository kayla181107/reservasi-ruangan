<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Room;
use App\Models\Reservation;
use App\Models\FixedSchedule;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function stats()
    {
        // Hitung jumlah data utama
        $roomsCount = Room::count();
        $reservationsCount = Reservation::count();
        $fixedSchedulesCount = FixedSchedule::count();
        $usersCount = User::count();

        // === 📊 Data chart: Jumlah reservasi tiap bulan ===
        $monthlyReservations = Reservation::select(
            DB::raw('MONTH(created_at) as month'),
            DB::raw('COUNT(*) as total')
        )
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // Label bulan (Jan - Des)
        $labels = [
            'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun',
            'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'
        ];

        // Buat array data berdasarkan bulan
        $data = array_fill(0, 12, 0); // isi awal 0
        foreach ($monthlyReservations as $row) {
            $index = $row->month - 1; // karena bulan mulai dari 1
            $data[$index] = $row->total;
        }

        // Return JSON ke frontend
        return response()->json([
            'reservations' => $reservationsCount,
            'rooms' => $roomsCount,
            'fixedSchedules' => $fixedSchedulesCount,
            'users' => $usersCount,
            'chart' => [
                'labels' => $labels, 
                'data' => $data,
            ],
        ]);
    }
}
