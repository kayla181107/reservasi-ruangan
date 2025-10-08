<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Http\Resources\User\UserResource;
use App\Services\User\UserService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    // List semua user dengan filter role, name, dan pagination
    public function index(Request $request)
    {
        $filters = [
            'role' => $request->input('role'),
            'name' => $request->input('name'),
        ];

        $perPage = $request->input('per_page', 10);

        $users = $this->userService->getAllFiltered($filters, $perPage);

        return UserResource::collection($users);
    }

    // Admin membuat user baru (admin/karyawan)
    public function store(StoreUserRequest $request)
    {
        $user = $this->userService->create($request->validated());
        return (new UserResource($user))
            ->additional(['message' => 'User created successfully']);
    }

    // Detail user
    public function show($id)
    {
        return new UserResource($this->userService->find($id));
    }

    // Admin update data user atau ubah role (misalnya karyawan → admin)
    public function update(UpdateUserRequest $request, $id)
    {
        $user = $this->userService->update($id, $request->validated());
        return (new UserResource($user))
            ->additional(['message' => 'User updated successfully']);
    }

    // Hapus user
    public function destroy($id): JsonResponse
    {
        $this->userService->delete($id);
        return response()->json(['message' => 'User deleted successfully']);
    }
}
