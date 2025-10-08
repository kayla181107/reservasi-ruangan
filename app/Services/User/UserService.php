<?php

namespace App\Services\User;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserService
{
    public function getAll()
    {
        // Ambil semua user dengan role-nya
        return User::with('roles')->get();
    }

    // Method baru untuk filter + pagination
    public function getAllFiltered(array $filters = [], $perPage = 10)
    {
        $query = User::with('roles');

        if (!empty($filters['role'])) {
            $query->whereHas('roles', function ($q) use ($filters) {
                $q->where('name', $filters['role']);
            });
        }

        if (!empty($filters['name'])) {
            $query->where('name', 'like', "%{$filters['name']}%");
        }

        return $query->orderBy('name', 'asc')->paginate($perPage);
    }

    public function find($id)
    {
        return User::with('roles')->findOrFail($id);
    }

    public function create(array $data)
    {
        $data['password'] = Hash::make($data['password']);

        $user = User::create($data);

        if (!empty($data['role'])) {
            $user->assignRole($data['role']);
        }

        return $user->load('roles');
    }

    public function update($id, array $data)
    {
        $user = User::findOrFail($id);

        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $user->update($data);

        if (!empty($data['role'])) {
            $user->syncRoles([$data['role']]);
        }

        return $user->load('roles');
    }

    public function delete($id)
    {
        $user = User::findOrFail($id);
        $user->delete();
    }
}
