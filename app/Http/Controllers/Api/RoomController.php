<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Room;
use Illuminate\Support\Facades\Validator;

class RoomController extends Controller
{
    // Tampilkan semua ruangan
    public function index()
    {
        $rooms = Room::all();
        return response()->json($rooms, 200);
    }

    // Simpan ruangan baru
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'        => 'required|string|max:255',
            'capacity'    => 'required|integer|min:1',
            'description' => 'nullable|string',
            'status'      => 'required|in:available,unavailable',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $room = Room::create($request->all());
        return response()->json($room, 201);
    }

    // Tampilkan ruangan berdasarkan ID
    public function show($id)
    {
        $room = Room::find($id);
        if (!$room) {
            return response()->json(['message' => 'Room not found'], 404);
        }
        return response()->json($room, 200);
    }

    // Update ruangan
    public function update(Request $request, $id)
    {
        $room = Room::find($id);
        if (!$room) {
            return response()->json(['message' => 'Room not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'name'        => 'sometimes|string|max:255',
            'capacity'    => 'sometimes|integer|min:1',
            'description' => 'nullable|string',
            'status'      => 'sometimes|in:available,unavailable',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $room->update($request->all());
        return response()->json($room, 200);
    }

    // Hapus ruangan
    public function destroy($id)
    {
        $room = Room::find($id);
        if (!$room) {
            return response()->json(['message' => 'Room not found'], 404);
        }

        $room->delete();
        return response()->json(['message' => 'Room deleted'], 200);
    }
}
