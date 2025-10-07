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
        $filters = [
            'day_of_week' => $request->input('day_of_week'),
            'start_time' => $request->input('start_time'),
            'end_time' => $request->input('end_time'),
            'room_id' => $request->input('room_id'),
            'search' => $request->input('search'),
        ];

        $perPage = $request->input('per_page', 10);

        $fixedSchedules = $this->fixedScheduleService->getAllFiltered($filters, $perPage);

        $user = Auth::user();
        if ($user->hasRole('admin')) {
            return AdminResource::collection($fixedSchedules);
        } else {
            return KaryawanResource::collection($fixedSchedules);
        }
    }

    public function show($id)
    {
        $fixedSchedule = $this->fixedScheduleService->find($id);

        $user = Auth::user();
        if ($user->hasRole('admin')) {
            return new AdminResource($fixedSchedule);
        } else {
            return new KaryawanResource($fixedSchedule);
        }
    }

    public function store(FixedScheduleRequest $request)
    {
        if (!Auth::user()->hasRole('admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $fixedSchedule = $this->fixedScheduleService->create($request->validated());
        return new AdminResource($fixedSchedule);
    }

    public function update(FixedScheduleRequest $request, $id)
    {
        if (!Auth::user()->hasRole('admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $fixedSchedule = $this->fixedScheduleService->update($id, $request->validated());
        return new AdminResource($fixedSchedule);
    }

    public function destroy($id)
    {
        if (!Auth::user()->hasRole('admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $this->fixedScheduleService->delete($id);
        return response()->json(['message' => 'Fixed schedule deleted successfully']);
    }
}
