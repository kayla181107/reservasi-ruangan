<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FixedSchedule;
use Illuminate\Http\Request;

class FixedScheduleController extends Controller
{
    public function index()
    {
        $schedules = FixedSchedule::with('room')->get();
        return response()->json($schedules, 200);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'room_id'     => 'required|exists:rooms,id',
            'day_of_week' => 'required|in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
            'start_time'  => 'required|date_format:H:i',
            'end_time'    => 'required|date_format:H:i|after:start_time',
            'description' => 'nullable|string|max:255',
        ]);

        $schedule = FixedSchedule::create($validated);

        return response()->json([
            'message' => 'Fixed schedule created successfully',
            'data'    => $schedule,
        ], 201);
    }

    public function show(FixedSchedule $fixedSchedule)
    {
        return response()->json($fixedSchedule->load('room'), 200);
    }

    public function update(Request $request, FixedSchedule $fixedSchedule)
    {
        $validated = $request->validate([
            'room_id'     => 'sometimes|exists:rooms,id',
            'day_of_week' => 'sometimes|in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
            'start_time'  => 'sometimes|date_format:H:i',
            'end_time'    => 'sometimes|date_format:H:i|after:start_time',
            'description' => 'nullable|string|max:255',
        ]);

        $fixedSchedule->update($validated);

        return response()->json([
            'message' => 'Fixed schedule updated successfully',
            'data'    => $fixedSchedule,
        ], 200);
    }

    public function destroy(FixedSchedule $fixedSchedule)
    {
        $fixedSchedule->delete();

        return response()->json([
            'message' => 'Fixed schedule deleted successfully',
        ], 204);
    }
}
