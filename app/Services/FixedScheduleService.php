<?php

namespace App\Services;

use App\Models\FixedSchedule;
use Illuminate\Support\Facades\DB;

class FixedScheduleService
{
    
    public function query()
    {
        return FixedSchedule::query();
    }

    
    public function getAll($filters = [], $page = 1, $perPage = 10)
    {
        if (!empty($filters['per_page'])) {
            $perPage = (int) $filters['per_page'];
        }

        $query = $this->query()
            ->with(['user', 'room'])
            ->orderBy('id', 'asc'); 

        if (!empty($filters['day_of_week'])) {
            $query->where('day_of_week', strtolower($filters['day_of_week']));
        }

        if (!empty($filters['start_time'])) {
            $query->whereTime('start_time', '>=', $filters['start_time']);
        }

        if (!empty($filters['end_time'])) {
            $query->whereTime('end_time', '<=', $filters['end_time']);
        }

        if (!empty($filters['room_id'])) {
            $query->where('room_id', $filters['room_id']);
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->whereHas('user', fn($u) => $u->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('room', fn($r) => $r->where('name', 'like', "%{$search}%"))
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('day_of_week', 'like', "%{$search}%");
            });
        }

        $page = max((int)$page, 1);
        $perPage = min(max((int)$perPage, 1), 100); 

        return $query->paginate($perPage, ['*'], 'page', $page);
    }

   
    public function find($id)
    {
        return FixedSchedule::with(['user', 'room'])->find($id);
    }

    
    public function create(array $data)
    {
        return DB::transaction(function () use ($data) {
            return FixedSchedule::create($data);
        });
    }

    
    public function update($id, array $data)
    {
        return DB::transaction(function () use ($id, $data) {
            $fixedSchedule = FixedSchedule::find($id);
            if (!$fixedSchedule) {
                return null;
            }

            $fixedSchedule->update($data);
            return $fixedSchedule;
        });
    }

    
    public function delete($id)
    {
        return DB::transaction(function () use ($id) {
            $fixedSchedule = FixedSchedule::find($id);
            if (!$fixedSchedule) {
                return false;
            }

            return $fixedSchedule->delete();
        });
    }
}
