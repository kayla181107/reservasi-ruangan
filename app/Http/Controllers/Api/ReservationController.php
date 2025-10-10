<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

// Services
use App\Services\Admin\ReservationService as AdminReservationService;
use App\Services\Karyawan\ReservationService as KaryawanReservationService;

// Requests
use App\Http\Requests\Karyawan\ReservationStoreRequest;
use App\Http\Requests\Karyawan\ReservationCancelRequest;

// Resources
use App\Http\Resources\Admin\ReservationResource as AdminReservationResource;
use App\Http\Resources\Karyawan\ReservationResource as KaryawanReservationResource;

// Mail
use App\Mail\ReservationCanceledByUserMail;

class ReservationController extends Controller
{
    protected $adminService;
    protected $karyawanService;

    public function __construct(
        AdminReservationService $adminService,
        KaryawanReservationService $karyawanService
    ) {
        $this->adminService    = $adminService;
        $this->karyawanService = $karyawanService;
    }

    // INDEX (GET /reservations)
    public function index(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Token tidak valid atau sudah kedaluwarsa.'
            ], 401);
        }

        // Filter
        $filters = [
            'date'        => $request->query('date'),
            'day_of_week' => $request->query('day_of_week'),
            'start_time'  => $request->query('start_time'),
            'end_time'    => $request->query('end_time'),
            'status'      => $request->query('status'),
            'per_page'      => $request->query('per_page', 10),

        ];

        // Pagination 
        $page = (int) max(1, $request->query('page', 1));
        $perPage = (int) $request->query('per_page', 10); // ← bisa diatur dari frontend bebas
        $perPage = min(max(1, $perPage), 100); // minimal 1, maksimal 100 per halaman

        // VALIDASI INPUT
        if (!empty($filters['day_of_week'])) {
            $validDays = ['senin','selasa','rabu','kamis','jumat','sabtu','minggu'];
            if (!in_array(strtolower(trim($filters['day_of_week'])), $validDays)) {
                return response()->json([
                    'status'  => 'failed',
                    'message' => 'Hari tidak valid. Gunakan Senin hingga Minggu.',
                    'data'    => null
                ], 400);
            }
        }

        if (!empty($filters['status'])) {
            $validStatus = ['approved', 'rejected', 'pending'];
            if (!in_array(strtolower(trim($filters['status'])), $validStatus)) {
                return response()->json([
                    'status'  => 'failed',
                    'message' => 'Status tidak valid. Gunakan approved, rejected, atau pending.',
                    'data'    => null
                ], 400);
            }
        }

        if (!empty($filters['date'])) {
            try {
                $dt = Carbon::createFromFormat('Y-m-d', $filters['date']);
                if ($dt->format('Y-m-d') !== $filters['date']) {
                    throw new \Exception('wrong format');
                }
            } catch (\Throwable $e) {
                return response()->json([
                    'status'  => 'failed',
                    'message' => 'Format tanggal tidak valid. Gunakan format YYYY-MM-DD.',
                    'data'    => null
                ], 400);
            }
        }

        $timeRegex = '/^([01]\d|2[0-3]):[0-5]\d$/';
        if (!empty($filters['start_time'])) {
            if (!preg_match($timeRegex, $filters['start_time'])) {
                return response()->json([
                    'status'  => 'failed',
                    'message' => 'Format jam mulai tidak valid. Gunakan format HH:MM.',
                    'data'    => null
                ], 400);
            }
            if (empty($filters['end_time'])) {
                return response()->json([
                    'status'  => 'failed',
                    'message' => 'Waktu selesai wajib diisi jika waktu mulai diisi.',
                    'data'    => null
                ], 400);
            }
        }

        if (!empty($filters['end_time']) && !preg_match($timeRegex, $filters['end_time'])) {
            return response()->json([
                'status'  => 'failed',
                'message' => 'Format jam selesai tidak valid. Gunakan format HH:MM.',
                'data'    => null
            ], 400);
        }

        // QUERY + PAGINATION
        try {
            $query = \App\Models\Reservation::query()
                ->when($user->hasRole('karyawan'), fn($q) => $q->where('user_id', $user->id))
                ->orderBy('id', 'asc'); // ← data urut dari ID terkecil ke terbesar

            $query
                ->when($filters['date'], fn($q) => $q->whereDate('date', $filters['date']))
                ->when($filters['day_of_week'], fn($q) => $q->where('day_of_week', $filters['day_of_week']))
                ->when($filters['start_time'], fn($q) => $q->whereTime('start_time', '>=', $filters['start_time']))
                ->when($filters['end_time'], fn($q) => $q->whereTime('end_time', '<=', $filters['end_time']))
                ->when($filters['status'], fn($q) => $q->where('status', $filters['status']));

            $reservations = $query->paginate($perPage, ['*'], 'page', $page)->appends($request->query());

            if ($reservations->isEmpty()) {
                return response()->json([
                    'status'  => 'success',
                    'message' => 'Tidak ada data reservasi ditemukan.',
                    'data'    => [],
                    'meta'    => [
                        'current_page' => $page,
                        'per_page' => $perPage,
                        'total' => 0,
                        'last_page' => 0
                    ]
                ], 200);
            }

            $resource = $user->hasRole('admin')
                ? AdminReservationResource::collection($reservations)
                : KaryawanReservationResource::collection($reservations);

            return response()->json([
                'status'  => 'success',
                'message' => 'Data reservasi berhasil ditampilkan.',
                'data'    => $resource,
                'meta'    => [
                    'current_page' => $reservations->currentPage(),
                    'per_page'     => $reservations->perPage(),
                    'total'        => $reservations->total(),
                    'last_page'    => $reservations->lastPage(),
                ],
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'status'  => 'failed',
                'message' => 'Terjadi kesalahan server: ' . $th->getMessage(),
                'data'    => null
            ], 500);
        }
    }

    // SHOW
    public function show($id)
    {
        $user = Auth::user();

        try {
            if ($user->hasRole('admin')) {
                $reservation = $this->adminService->getById($id);
                return response()->json([
                    'status'  => 'success',
                    'message' => 'Detail reservasi berhasil ditampilkan.',
                    'data'    => new AdminReservationResource($reservation),
                ], 200);
            }

            if ($user->hasRole('karyawan')) {
                $reservation = $this->karyawanService->getUserReservationById($user->id, $id);
                return response()->json([
                    'status'  => 'success',
                    'message' => 'Detail reservasi berhasil ditampilkan.',
                    'data'    => new KaryawanReservationResource($reservation),
                ], 200);
            }

            return response()->json([
                'status'  => 'failed',
                'message' => 'Akses tidak diizinkan.',
                'data'    => null,
            ], 403);

        } catch (\Throwable $th) {
            return response()->json([
                'status'  => 'failed',
                'message' => 'Reservasi tidak ditemukan atau terjadi kesalahan.',
                'data'    => null,
            ], 404);
        }
    }

    // STORE
    public function store(ReservationStoreRequest $request)
    {
        $user = Auth::user();

        if (!$user->hasRole('karyawan')) {
            return response()->json([
                'status'  => 'failed',
                'message' => 'Hanya karyawan yang dapat membuat reservasi.',
                'data'    => null,
            ], 403);
        }

        try {
            $reservation = $this->karyawanService->create([
                'user_id'     => $user->id,
                'room_id'     => $request->room_id,
                'date'        => $request->date,
                'day_of_week' => Carbon::parse($request->date)->dayName,
                'start_time'  => $request->start_time,
                'end_time'    => $request->end_time,
                'reason'      => $request->reason ?? '-',
            ]);

            return response()->json([
                'status'  => 'success',
                'message' => 'Reservasi berhasil dibuat.',
                'data'    => new KaryawanReservationResource($reservation),
            ], 201);

        } catch (\Throwable $th) {
            return response()->json([
                'status'  => 'failed',
                'message' => 'Gagal membuat reservasi: ' . $th->getMessage(),
                'data'    => null,
            ], 500);
        }
    }

    // APPROVE
    public function approve(Request $request, $id)
    {
        $user = Auth::user();
        if (!$user->hasRole('admin')) {
            return response()->json([
                'status'  => 'failed',
                'message' => 'Hanya admin yang dapat menyetujui reservasi.',
            ], 403);
        }

        try {
            $reservation = $this->adminService->updateStatus($id, [
                'status' => 'approved',
                'reason' => $request->reason ?? 'Disetujui oleh admin'
            ]);

            return response()->json([
                'status'  => 'success',
                'message' => 'Reservasi berhasil disetujui.',
                'data'    => new AdminReservationResource($reservation),
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'status'  => 'failed',
                'message' => 'Gagal menyetujui reservasi: ' . $th->getMessage(),
            ], 500);
        }
    }

    // REJECTED
    public function rejected(Request $request, $id)
    {
        $user = Auth::user();
        if (!$user->hasRole('admin')) {
            return response()->json([
                'status'  => 'failed',
                'message' => 'Hanya admin yang dapat menolak reservasi.',
            ], 403);
        }

        $request->validate([
            'reason' => 'required|string|min:3|max:255',
        ], [
            'reason.required' => 'Alasan penolakan wajib diisi.',
            'reason.min' => 'Alasan penolakan minimal 3 karakter.',
            'reason.max' => 'Alasan penolakan maksimal 255 karakter.',
        ]);

        try {
            $reservation = $this->adminService->updateStatus($id, [
                'status' => 'rejected',
                'reason' => $request->reason,
            ]);

            return response()->json([
                'status'  => 'success',
                'message' => 'Reservasi berhasil ditolak dengan alasan.',
                'data'    => [
                    'reservation' => new AdminReservationResource($reservation),
                    'reason' => $request->reason,
                ],
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'status'  => 'failed',
                'message' => 'Gagal menolak reservasi: ' . $th->getMessage(),
            ], 500);
        }
    }

    // DESTROY
    public function destroy($id)
    {
        $user = Auth::user();
        if (!$user->hasRole('admin')) {
            return response()->json([
                'status'  => 'failed',
                'message' => 'Hanya admin yang dapat menghapus reservasi.',
            ], 403);
        }

        try {
            $this->adminService->delete($id);
            return response()->json([
                'status'  => 'success',
                'message' => 'Reservasi berhasil dihapus.',
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'status'  => 'failed',
                'message' => 'Gagal menghapus reservasi: ' . $th->getMessage(),
            ], 500);
        }
    }

    // CANCEL
    public function cancel(ReservationCancelRequest $request, $id)
    {
        $user = Auth::user();
        if (!$user->hasRole('karyawan')) {
            return response()->json([
                'status'  => 'failed',
                'message' => 'Hanya karyawan yang dapat membatalkan reservasi.',
            ], 403);
        }

        try {
            $reservation = $this->karyawanService->cancel(
                $id,
                $user->id,
                $request->validated()['reason'] ?? 'Dibatalkan oleh pengguna'
            );

            Mail::to('admin@reservasi.com')->send(new ReservationCanceledByUserMail($reservation));

            return response()->json([
                'status'  => 'success',
                'message' => 'Reservasi berhasil dibatalkan.',
                'data'    => new KaryawanReservationResource($reservation),
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'status'  => 'failed',
                'message' => 'Gagal membatalkan reservasi: ' . $th->getMessage(),
            ], 500);
        }
    }
}
