<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\FixedScheduleRequest;
use App\Http\Resources\Admin\FixedScheduleResource as AdminResource;
use App\Http\Resources\Karyawan\FixedScheduleResource as KaryawanResource;
use App\Services\FixedScheduleService;
use Illuminate\Support\Facades\Auth;
use App\Models\FixedSchedule;

class FixedScheduleController extends Controller
{
    protected $service;

    public function __construct(FixedScheduleService $service)
    {
        $this->service = $service;
    }

    // List semua fixed schedule
    public function index()
    {
        $schedules = $this->service->getAll();

        return Auth::user()->hasRole('admin')
            ? AdminResource::collection($schedules)
            : KaryawanResource::collection($schedules);
    }

    // Buat fixed schedule baru
    public function store(FixedScheduleRequest $request)
    {
        $data = $request->validated(); // sudah termasuk day_of_week, start_time, end_time
        $schedule = $this->service->create($data);

        return new AdminResource($schedule);
    }

    // Detail fixed schedule
    public function show(FixedSchedule $schedule)
    {
        return Auth::user()->hasRole('admin')
            ? new AdminResource($schedule->load(['room', 'user']))
            : new KaryawanResource($schedule->load(['room', 'user']));
    }

    // Update fixed schedule
    public function update(FixedScheduleRequest $request, FixedSchedule $schedule)
    {
        $data = $request->validated();
        $schedule = $this->service->update($schedule, $data);

        return new AdminResource($schedule);
    }

    // Hapus fixed schedule
    public function destroy(FixedSchedule $schedule)
    {
        $this->service->delete($schedule);
        return response()->json(['message' => 'FixedSchedule deleted successfully']);
    }
}
