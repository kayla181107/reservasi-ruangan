<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\FixedScheduleRequest;
use App\Http\Resources\Admin\FixedScheduleResource as AdminResource;
use App\Http\Resources\Karyawan\FixedScheduleResource as KaryawanResource;
use App\Services\FixedScheduleService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FixedScheduleController extends Controller
{
    protected $fixedScheduleService;

    public function __construct(FixedScheduleService $fixedScheduleService)
    {
        $this->fixedScheduleService = $fixedScheduleService;
    }

    
    public function index(Request $request)
    {
        $user = Auth::user();

        $filters = [
            'day_of_week' => $request->query('day_of_week'),
            'start_time'  => $request->query('start_time'),
            'end_time'    => $request->query('end_time'),
            'room_id'     => $request->query('room_id'),
            'search'      => $request->query('search'),
            'per_page'      => $request->query('per_page',10),

        ];

        $page = (int) max(1, $request->query('page', 1));
        $perPage = (int) $request->query('per_page', 10);
        $perPage = min(max($perPage, 1), 100); // batas aman: 1–100 per halaman

        $validDays = ['senin','selasa','rabu','kamis','jumat','sabtu','minggu'];
        if (!empty($filters['day_of_week']) && !in_array(strtolower(trim($filters['day_of_week'])), $validDays)) {
            return response()->json([
                'status'  => 'failed',
                'message' => 'Hari tidak valid. Gunakan Senin hingga Minggu.',
                'data'    => null
            ], 400);
        }

        $timeRegex = '/^([01]\d|2[0-3]):[0-5]\d$/';
        if (!empty($filters['start_time']) && !preg_match($timeRegex, $filters['start_time'])) {
            return response()->json([
                'status'  => 'failed',
                'message' => 'Format jam mulai tidak valid. Gunakan HH:MM.',
                'data'    => null
            ], 400);
        }

        if (!empty($filters['end_time']) && !preg_match($timeRegex, $filters['end_time'])) {
            return response()->json([
                'status'  => 'failed',
                'message' => 'Format jam selesai tidak valid. Gunakan HH:MM.',
                'data'    => null
            ], 400);
        }

        if (!empty($filters['start_time']) && !empty($filters['end_time']) && $filters['end_time'] <= $filters['start_time']) {
            return response()->json([
                'status'  => 'failed',
                'message' => 'Waktu selesai harus lebih besar dari waktu mulai.',
                'data'    => null
            ], 400);
        }

        if (!empty($filters['room_id']) && !is_numeric($filters['room_id'])) {
            return response()->json([
                'status'  => 'failed',
                'message' => 'ID room harus berupa angka.',
                'data'    => null
            ], 400);
        }

        try {
            $query = $this->fixedScheduleService->query()
                ->with(['user', 'room']);

            $query->when($filters['day_of_week'], fn($q) =>
                $q->where('day_of_week', strtolower($filters['day_of_week']))
            )
            ->when($filters['start_time'], fn($q) =>
                $q->whereTime('start_time', '>=', $filters['start_time'])
            )
            ->when($filters['end_time'], fn($q) =>
                $q->whereTime('end_time', '<=', $filters['end_time'])
            )
            ->when($filters['room_id'], fn($q) =>
                $q->where('room_id', $filters['room_id'])
            )
            ->when($filters['search'], function ($q) use ($filters) {
                $search = $filters['search'];
                $q->where(function ($sub) use ($search) {
                    $sub->whereHas('user', fn($u) => $u->where('name', 'like', "%{$search}%"))
                        ->orWhereHas('room', fn($r) => $r->where('name', 'like', "%{$search}%"))
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhere('day_of_week', 'like', "%{$search}%");
                });
            });

            $paginator = $query
                ->orderBy('id', 'asc')
                ->paginate($perPage, ['*'], 'page', $page)
                ->appends($request->query());

            $start = ($paginator->currentPage() - 1) * $paginator->perPage();

            $collectionWithNo = $paginator->getCollection()->values()->map(function ($item, $index) use ($start) {
                $item->setAttribute('no_urut', $start + $index + 1);
                return $item;
            });

            $paginator->setCollection($collectionWithNo);

            if ($paginator->isEmpty()) {
                return response()->json([
                    'status'  => 'success',
                    'message' => 'Tidak ada data jadwal tetap ditemukan.',
                    'data'    => [],
                    'meta'    => [
                        'current_page' => $paginator->currentPage(),
                        'per_page'     => $paginator->perPage(),
                        'total'        => $paginator->total(),
                        'last_page'    => $paginator->lastPage(),
                    ]
                ], 200);
            }

            $data = $collectionWithNo->map(function ($item) use ($user, $request) {
                if ($user->hasRole('admin')) {
                    $arr = (new AdminResource($item))->toArray($request);
                } else {
                    $arr = (new KaryawanResource($item))->toArray($request);
                }
                $arr['no_urut'] = $item->no_urut;
                return $arr;
            })->values();

             return response()->json([
                'status'  => 'success',
                'message' => 'Data jadwal tetap berhasil ditampilkan.',
                'data'    => $data,
                'meta'    => [
                    'current_page' => $paginator->currentPage(),
                    'per_page'     => $paginator->perPage(),
                    'total'        => $paginator->total(),
                    'last_page'    => $paginator->lastPage(),
                ]
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'status'  => 'failed',
                'message' => 'Terjadi kesalahan server: ' . $th->getMessage(),
                'data'    => null,
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $fixedSchedule = $this->fixedScheduleService->find($id);

            if (!$fixedSchedule) {
                return response()->json([
                    'status'  => 'failed',
                    'message' => 'Data jadwal tetap tidak ditemukan.',
                    'data'    => null,
                ], 404);
            }

            $user = Auth::user();
            $data = $user->hasRole('admin')
                ? new AdminResource($fixedSchedule)
                : new KaryawanResource($fixedSchedule);

            return response()->json([
                'status'  => 'success',
                'message' => 'Detail jadwal tetap berhasil ditampilkan.',
                'data'    => $data,
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status'  => 'failed',
                'message' => 'Gagal menampilkan data: ' . $th->getMessage(),
                'data'    => null,
            ], 500);
        }
    }

    
    public function store(FixedScheduleRequest $request)
    {
        $user = Auth::user();

        if (!$user->hasRole('admin')) {
            return response()->json([
                'status'  => 'failed',
                'message' => 'Hanya admin yang dapat menambahkan jadwal tetap.',
                'data'    => null,
            ], 403);
        }

        try {
            $data = $request->validated();
            $data['user_id'] = $user->id;

            $fixedSchedule = $this->fixedScheduleService->create($data);

            return response()->json([
                'status'  => 'success',
                'message' => 'Jadwal tetap berhasil ditambahkan.',
                'data'    => new AdminResource($fixedSchedule),
            ], 201);
        } catch (\Throwable $th) {
            return response()->json([
                'status'  => 'failed',
                'message' => 'Gagal menambahkan jadwal tetap: ' . $th->getMessage(),
                'data'    => null,
            ], 500);
        }
    }

    public function update(FixedScheduleRequest $request, $id)
    {
        $user = Auth::user();

        if (!$user->hasRole('admin')) {
            return response()->json([
                'status'  => 'failed',
                'message' => 'Hanya admin yang dapat memperbarui jadwal tetap.',
                'data'    => null,
            ], 403);
        }

        try {
            $data = $request->validated();
            $data['user_id'] = $user->id;

            $fixedSchedule = $this->fixedScheduleService->update($id, $data);

            if (!$fixedSchedule) {
                return response()->json([
                    'status'  => 'failed',
                    'message' => 'Data jadwal tetap tidak ditemukan.',
                    'data'    => null,
                ], 404);
            }

            return response()->json([
                'status'  => 'success',
                'message' => 'Jadwal tetap berhasil diperbarui.',
                'data'    => new AdminResource($fixedSchedule),
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status'  => 'failed',
                'message' => 'Gagal memperbarui jadwal tetap: ' . $th->getMessage(),
                'data'    => null,
            ], 500);
        }
    }

    public function destroy($id)
    {
        $user = Auth::user();

        if (!$user->hasRole('admin')) {
            return response()->json([
                'status'  => 'failed',
                'message' => 'Hanya admin yang dapat menghapus jadwal tetap.',
                'data'    => null,
            ], 403);
        }

        try {
            $deleted = $this->fixedScheduleService->delete($id);

            if (!$deleted) {
                return response()->json([
                    'status'  => 'failed',
                    'message' => 'Data jadwal tetap tidak ditemukan.',
                    'data'    => null,
                ], 404);
            }

            return response()->json([
                'status'  => 'success',
                'message' => 'Jadwal tetap berhasil dihapus.',
                'data'    => null,
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status'  => 'failed',
                'message' => 'Gagal menghapus jadwal tetap: ' . $th->getMessage(),
                'data'    => null,
            ], 500);
        }
    }
}
