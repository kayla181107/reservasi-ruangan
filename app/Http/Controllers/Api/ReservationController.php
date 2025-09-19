<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Reservation;
use Illuminate\Support\Facades\Validator;

class ReservationController extends Controller
{
    public function index()
    {
        $reservations = Reservation::all();
        return response()->json($reservations, 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id'    => 'required|exists:users,id',
            'room_id'    => 'required|exists:rooms,id',
            'date'       => 'required|date',
            'start_time' => 'required',
            'end_time'   => 'required|after:start_time',
            'status'     => 'required|in:pending,approved,rejected',
            'reason'     => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $reservation = Reservation::create($request->all());
        return response()->json($reservation, 201);
    }

    public function show($id)
    {
        $reservation = Reservation::find($id);
        if (!$reservation) {
            return response()->json(['message' => 'Reservation not found'], 404);
        }
        return response()->json($reservation, 200);
    }

    public function update(Request $request, $id)
    {
        $reservation = Reservation::find($id);
        if (!$reservation) {
            return response()->json(['message' => 'Reservation not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'user_id'    => 'sometimes|exists:users,id',
            'room_id'    => 'sometimes|exists:rooms,id',
            'date'       => 'sometimes|date',
            'start_time' => 'sometimes',
            'end_time'   => 'sometimes|after:start_time',
            'status'     => 'sometimes|in:pending,approved,rejected',
            'reason'     => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $reservation->update($request->all());
        return response()->json($reservation, 200);
    }

    public function destroy($id)
    {
        $reservation = Reservation::find($id);
        if (!$reservation) {
            return response()->json(['message' => 'Reservation not found'], 404);
        }

        $reservation->delete();
        return response()->json(['message' => 'Reservation deleted'], 200);
    }
}
