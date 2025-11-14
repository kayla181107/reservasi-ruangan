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
    // ====================== 🧭 DASHBOARD ADMIN ======================
    public function stats()
    {
        $roomsCount = Room::count();
        $reservationsCount = Reservation::count();
        $fixedSchedulesCount = FixedSchedule::count();
        $usersCount = User::count();

        $monthNow = now()->month;
        $monthPrev = now()->subMonth()->month;
        $yearNow = now()->year;

        $getCount = function ($model, $month, $column = 'created_at') use ($yearNow) {
            return $model::whereYear($column, $yearNow)
                ->whereMonth($column, $month)
                ->count() ?? 0;
        };

        $reservationNow = $getCount(Reservation::class, $monthNow, 'date');
        $reservationPrev = $getCount(Reservation::class, $monthPrev, 'date');
        $roomNow = $getCount(Room::class, $monthNow);
        $roomPrev = $getCount(Room::class, $monthPrev);
        $scheduleNow = $getCount(FixedSchedule::class, $monthNow);
        $schedulePrev = $getCount(FixedSchedule::class, $monthPrev);
        $userNow = $getCount(User::class, $monthNow);
        $userPrev = $getCount(User::class, $monthPrev);

        $percentChange = function ($now, $prev) {
            if ($prev > 0) {
                return round((($now - $prev) / $prev) * 100, 1);
            } elseif ($now > 0 && $prev == 0) {
                return 100;
            }
            return 0;
        };

        $reservationChange = $percentChange($reservationNow, $reservationPrev);
        $roomChange = $percentChange($roomNow, $roomPrev);
        $scheduleChange = $percentChange($scheduleNow, $schedulePrev);
        $userChange = $percentChange($userNow, $userPrev);

        // === Data chart reservasi per bulan ===
        $monthlyReservations = Reservation::select(
            DB::raw('MONTH(date) as month'),
            DB::raw('COUNT(*) as total')
        )
            ->whereYear('date', $yearNow)
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $labels = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
        $data = array_fill(0, 12, 0);

        foreach ($monthlyReservations as $row) {
            $index = $row->month - 1;
            $data[$index] = $row->total;
        }

        return response()->json([
            'success' => true,
            'type' => 'admin',
            'data' => [
                'reservations' => $reservationsCount,
                'rooms' => $roomsCount,
                'fixedSchedules' => $fixedSchedulesCount,
                'users' => $usersCount,
                'reservationChange' => $reservationChange,
                'roomChange' => $roomChange,
                'scheduleChange' => $scheduleChange,
                'userChange' => $userChange,
                'currentMonth' => $labels[$monthNow - 1],
                'chart' => [
                    'labels' => $labels,
                    'data' => $data,
                    'year' => $yearNow,
                ],
            ],
        ], 200);
    }

    // ====================== 👨‍💼 DASHBOARD KARYAWAN ======================
    public function karyawanStats()
    {
        $userId = auth()->id();

        if (!$userId) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized: user not logged in',
            ], 401);
        }

        // Hitung total reservasi milik user (karyawan)
        $totalReservations = Reservation::where('user_id', $userId)->count();
        $approved = Reservation::where('user_id', $userId)->where('status', 'approved')->count();
        $pending = Reservation::where('user_id', $userId)->where('status', 'pending')->count();
        $rejected = Reservation::where('user_id', $userId)->where('status', 'rejected')->count();

        // Data per bulan untuk chart
        $yearNow = now()->year;
        $monthlyReservations = Reservation::select(
            DB::raw('MONTH(date) as month'),
            DB::raw('COUNT(*) as total')
        )
            ->where('user_id', $userId)
            ->whereYear('date', $yearNow)
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $labels = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
        $data = array_fill(0, 12, 0);

        foreach ($monthlyReservations as $row) {
            $index = $row->month - 1;
            $data[$index] = $row->total;
        }

        return response()->json([
            'success' => true,
            'type' => 'karyawan',
            'data' => [
                'total_reservations' => $totalReservations,
                'approved' => $approved,
                'pending' => $pending,
                'rejected' => $rejected,
                'chart' => [
                    'labels' => $labels,
                    'data' => $data,
                    'year' => $yearNow,
                ],
            ],
        ], 200);
    }
}
