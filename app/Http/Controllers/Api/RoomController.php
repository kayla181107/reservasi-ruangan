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
        try {
            $filters = [
                'name' => $request->query('name'),
                'capacity' => $request->query('capacity'),
                'status' => $request->query('status'),
            ];

            $pagination = [
                'page' => $request->query('page', 1),
                'per_page' => $request->query('per_page', 1000), 
            ];

            if (!is_numeric($pagination['page'])) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Parameter page harus berupa angka.',
                    'data' => null,
                ], 422);
            }

            if (!is_numeric($pagination['per_page'])) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Parameter per_page harus berupa angka.',
                    'data' => null,
                ], 422);
            }

            if (!empty($filters['capacity']) && !is_numeric($filters['capacity'])) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Capacity harus berupa angka.',
                    'data' => null,
                ], 422);
            }

            $rooms = $this->roomService->getAll($filters, $pagination);

            if (!empty($filters['name'])) {
                $rooms->setCollection(
                    $rooms->getCollection()->filter(function ($room) use ($filters) {
                        $keyword = strtolower(trim($filters['name']));
                        return str_contains(strtolower($room->name), $keyword)
                            || str_contains(strtolower($room->description ?? ''), $keyword);
                    })
                );
            }

            if (!empty($filters['capacity'])) {
                $rooms->setCollection(
                    $rooms->getCollection()->filter(function ($room) use ($filters) {
                        return $room->capacity == $filters['capacity'];
                    })
                );
            }

            if (!empty($filters['capacity']) && $rooms->count() === 0) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Tidak ditemukan ruangan dengan kapasitas ' . $filters['capacity'],
                    'data' => [],
                ], 200);
            }

            $resource = Auth::user()->hasRole('admin')
                ? AdminRoomResource::collection($rooms)
                : KaryawanRoomResource::collection($rooms);

            return response()->json([
                'status' => 'success',
                'message' => 'Data ruangan berhasil diambil',
                'data' => $resource,
                'meta' => [
                    'current_page' => $rooms->currentPage(),
                    'per_page' => $rooms->perPage(),
                    'total' => $rooms->total(),
                    'last_page' => $rooms->lastPage(),
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Gagal mengambil data ruangan: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    public function show($id)
    {
        if (!is_numeric($id)) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Parameter id harus berupa angka.',
                'data' => null
            ], 422);
        }

        try {
            $room = $this->roomService->find($id);

            if (!$room) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Data tidak ditemukan',
                    'data' => null
                ], 200);
            }

            $resource = Auth::user()->hasRole('admin')
                ? new AdminRoomResource($room)
                : new KaryawanRoomResource($room);

            return response()->json([
                'status' => 'success',
                'message' => 'Detail ruangan berhasil diambil',
                'data' => $resource
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Gagal menampilkan detail ruangan: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    public function store(RoomRequest $request)
    {
        if (!Auth::user()->hasRole('admin')) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Unauthorized'
            ], 403);
        }

        if (!is_numeric($request->capacity)) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Capacity harus berupa angka.',
                'data' => null
            ], 422);
        }

        try {
            $room = $this->roomService->create($request->validated());

            return response()->json([
                'status' => 'success',
                'message' => 'Ruangan berhasil ditambahkan',
                'data' => new AdminRoomResource($room)
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Gagal menambahkan ruangan: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    public function update(RoomRequest $request, $id)
    {
        if (!Auth::user()->hasRole('admin')) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Unauthorized'
            ], 403);
        }

        if (!is_numeric($id)) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Parameter id harus berupa angka.',
                'data' => null
            ], 422);
        }

        if (!is_numeric($request->capacity)) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Capacity harus berupa angka.',
                'data' => null
            ], 422);
        }

        try {
            $room = $this->roomService->update($id, $request->validated());

            if (!$room) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Data tidak ditemukan untuk diperbarui',
                    'data' => null
                ], 200);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Ruangan berhasil diperbarui',
                'data' => new AdminRoomResource($room)
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Gagal memperbarui ruangan: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    public function destroy($id)
    {
        if (!Auth::user()->hasRole('admin')) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Unauthorized'
            ], 403);
        }

        if (!is_numeric($id)) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Parameter id harus berupa angka.',
                'data' => null
            ], 422);
        }

        try {
            $deleted = $this->roomService->delete($id);

            if (!$deleted) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Data tidak ditemukan untuk dihapus',
                    'data' => null
                ], 200);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Ruangan berhasil dihapus',
                'data' => null
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Validasi gagal',
                'errors' => $e->errors(),
                'data' => null
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Gagal menghapus ruangan: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }
}
