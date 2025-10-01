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

    /**
     * GET /reservations
     */
    public function index()
    {
        $user = Auth::user();

        if ($user->hasRole('admin')) {
            $reservations = $this->adminService->getAll();
            return AdminReservationResource::collection($reservations);
        }

        if ($user->hasRole('karyawan')) {
            $reservations = $this->karyawanService->getUserReservations($user->id);
            return KaryawanReservationResource::collection($reservations);
        }

        abort(403, 'Anda tidak memiliki akses.');
    }

    /**
     * GET /reservations/{id}
     */
    public function show($id)
    {
        $user = Auth::user();

        if ($user->hasRole('admin')) {
            $reservation = $this->adminService->getById($id);
            return new AdminReservationResource($reservation);
        }

        if ($user->hasRole('karyawan')) {
            $reservation = $this->karyawanService->getUserReservationById($user->id, $id);
            return new KaryawanReservationResource($reservation);
        }

        abort(403, 'Anda tidak memiliki akses.');
    }

    /**
     * POST /karyawan/reservations
     */
    public function store(ReservationStoreRequest $request)
    {
        $user = Auth::user();

        if (! $user->hasRole('karyawan')) {
            abort(403, 'Hanya karyawan yang bisa membuat reservasi.');
        }

        $reservation = $this->karyawanService->create([
            'user_id'     => $user->id,
            'room_id'     => $request->room_id,
            'date'        => $request->date,
            'day_of_week' => Carbon::parse($request->date)->locale('id')->dayName,
            'start_time'  => $request->start_time,
            'end_time'    => $request->end_time,
            'reason'      => $request->reason ?? '-',
        ]);

        return new KaryawanReservationResource($reservation);
    }

    /**
     * PUT /admin/reservations/{id}/approve
     */
    public function approve(Request $request, $id)
    {
        $user = Auth::user();
        if (! $user->hasRole('admin')) {
            abort(403, 'Hanya admin yang bisa menyetujui reservasi.');
        }

        $reservation = $this->adminService->updateStatus($id, [
            'status' => 'approved',
            'reason' => $request->reason ?? 'Disetujui oleh admin'
        ]);

        return new AdminReservationResource($reservation);
    }

    /**
     * PUT /admin/reservations/{id}/reject
     */
    public function rejected(Request $request, $id)
    {
        $user = Auth::user();
        if (! $user->hasRole('admin')) {
            abort(403, 'Hanya admin yang bisa menolak reservasi.');
        }

        $reservation = $this->adminService->updateStatus($id, [
            'status' => 'rejected',
            'reason' => $request->reason ?? 'Tidak ada alasan diberikan'
        ]);

        return new AdminReservationResource($reservation);
    }

    /**
     * DELETE /admin/reservations/{id}
     */
    public function destroy($id)
    {
        $user = Auth::user();
        if (! $user->hasRole('admin')) {
            abort(403, 'Hanya admin yang bisa menghapus reservasi.');
        }

        $this->adminService->delete($id);

        return response()->json([
            'message' => 'Reservasi berhasil dihapus.',
        ]);
    }

    /**
     * PUT /karyawan/reservations/{id}/cancel
     */
    public function cancel(ReservationCancelRequest $request, $id)
    {
        $user = Auth::user();
        if (! $user->hasRole('karyawan')) {
            abort(403, 'Hanya karyawan yang bisa membatalkan reservasi.');
        }

        $reservation = $this->karyawanService->cancel(
            $id,
            $user->id,
            $request->validated()['reason'] ?? 'Dibatalkan oleh pengguna'
        );

        $adminEmail = "admin@reservasi.com";
        Mail::to($adminEmail)->send(new ReservationCanceledByUserMail($reservation));

        return new KaryawanReservationResource($reservation);
    }
}
