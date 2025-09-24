<?php

namespace App\Http\Controllers\Api\Karyawan;

use App\Http\Controllers\Controller;
use App\Http\Requests\Karyawan\ReservationStoreRequest;
use App\Http\Resources\Karyawan\ReservationResource;
use App\Services\Karyawan\ReservationService;
use Illuminate\Support\Facades\Auth;

class ReservationController extends Controller
{
    private $service;

    public function __construct(ReservationService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        $reservations = $this->service->getUserReservations(Auth::id());
        return ReservationResource::collection($reservations);
    }

    public function store(ReservationStoreRequest $request)
    {
        $reservation = $this->service->create([
            'user_id'     => Auth::id(),
            'room_id'     => $request->room_id,
            'date'        => $request->date,
            'day_of_week' => $request->day_of_week, 
            'start_time'  => $request->start_time,
            'end_time'    => $request->end_time,
            'reason'      => $request->reason ?? null,
            'status'      => 'pending', // default saat create
        ]);

        return new ReservationResource($reservation);
    }
}
