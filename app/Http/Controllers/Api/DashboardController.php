<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Room;
use App\Models\Reservation;
use App\Models\FixedSchedule;

class DashboardController extends Controller
{
    public function stats()
    {
        return response()->json([
            'rooms' => Room::count(),
            'reservations' => Reservation::count(),
            'approved' => Reservation::where('status', 'approved')->count(),
            'rejected' => Reservation::where('status', 'rejected')->count(),
            'fixedSchedules' => FixedSchedule::count(),
            'chart' => [
                'labels' => ['Jan','Feb','Mar','Apr','May','Jun'],
                'data'   => [5, 10, 8, 15, 12, 20], 
            ]
        ]);
    }
}
