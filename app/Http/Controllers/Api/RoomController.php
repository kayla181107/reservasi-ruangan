<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\RoomRequest;
use App\Http\Resources\Admin\RoomResource as AdminRoomResource;
use App\Http\Resources\Karyawan\RoomResource as KaryawanRoomResource;
use App\Services\RoomService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class RoomController extends Controller
{
    protected $roomService;

    public function __construct(RoomService $roomService)
    {
        $this->roomService = $roomService;
    }

    public function index(Request $request)
    {
        // ambil parameter filter dan pagination dari request
        $filters = [
            'name' => $request->query('name'),
            'capacity' => $request->query('capacity'),
            'status' => $request->query('status'),
        ];

        $pagination = [
            'page' => $request->query('page', 1),
            'per_page' => $request->query('per_page', 10),
        ];

        $rooms = $this->roomService->getAll($filters, $pagination);

        // sesuaikan resource berdasarkan role
        $resource = Auth::user()->hasRole('admin')
            ? AdminRoomResource::collection($rooms)
            : KaryawanRoomResource::collection($rooms);

        // tambahkan meta data pagination di response
        return $resource->additional([
            'meta' => [
                'current_page' => $rooms->currentPage(),
                'per_page' => $rooms->perPage(),
                'total' => $rooms->total(),
                'last_page' => $rooms->lastPage(),
            ]
        ]);
    }

    public function show($id)
    {
        $room = $this->roomService->find($id);

        return Auth::user()->hasRole('admin')
            ? new AdminRoomResource($room)
            : new KaryawanRoomResource($room);
    }

    public function store(RoomRequest $request)
    {
        if (!Auth::user()->hasRole('admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $room = $this->roomService->create($request->validated());
        return new AdminRoomResource($room);
    }

    public function update(RoomRequest $request, $id)
    {
        if (!Auth::user()->hasRole('admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $room = $this->roomService->update($id, $request->validated());
        return new AdminRoomResource($room);
    }

    public function destroy($id)
    {
        if (!Auth::user()->hasRole('admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        try {
            $this->roomService->delete($id);
            return response()->json(['message' => 'Room deleted successfully']);
        } catch (ValidationException $e) {
            return response()->json([
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 400);
        }
    }
}
