<?php

namespace App\Services\User;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserService
{
    public function getAll()
    {
        return User::with('roles')->get();
    }

    public function getAllFiltered(array $filters = [], ?int $perPage = null, int $page = 1)
    {
        $query = User::with('roles');

        if (!empty($filters['role'])) {
            $query->whereHas('roles', function ($q) use ($filters) {
                $q->where('name', $filters['role']);
            });
        }

        if (!empty($filters['name'])) {
            $query->where('name', 'like', '%' . $filters['name'] . '%');
        }

        $perPage = $perPage ?: 10;

        return $query->orderBy('id', 'asc')
                     ->paginate($perPage, ['*'], 'page', $page);
    }

    public function find($id)
    {
        return User::with('roles')->find($id);
    }

    public function create(array $data)
    {
        $data['password'] = Hash::make($data['password']);

        $user = User::create($data);

        if (!empty($data['role']) || !empty($data['role_id'])) {
            $role = $data['role'] ?? Role::find($data['role_id'])->name ?? null;
            if ($role) {
                $user->assignRole($role);
            }
        }

        return $user->load('roles');
    }

    public function update($id, array $data)
    {
        $user = User::find($id);
        if (!$user) {
            return null;
        }

        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $user->update($data);

        if (!empty($data['role']) || !empty($data['role_id'])) {
            $role = $data['role'] ?? Role::find($data['role_id'])->name ?? null;
            if ($role) {
                $user->syncRoles([$role]);
            }
        }

        return $user->load('roles');
    }

    public function delete($id)
    {
        $user = User::find($id);
        if (!$user) {
            return false;
        }

        $user->delete();
        return true;
    }
}
