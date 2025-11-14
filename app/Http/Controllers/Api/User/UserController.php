<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Http\Resources\User\UserResource;
use App\Services\User\UserService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Spatie\Permission\Models\Role; 

class UserController extends Controller
{
    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function index(Request $request): JsonResponse
    {
        try {
            $filters = [
                'role' => $request->input('role'),
                'name' => $request->input('name'),
            ];

            $page = $request->input('page', 1);
            $perPage = $request->input('per_page', 10);

            $users = $this->userService->getAllFiltered($filters, $perPage, $page);

            return response()->json([
                'status' => 'success',
                'message' => 'Data user berhasil diambil',
                'data' => UserResource::collection($users),
                'meta' => [
                    'current_page' => $users->currentPage(),
                    'per_page' => $users->perPage(),
                    'total' => $users->total(),
                    'last_page' => $users->lastPage(),
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Gagal mengambil data user: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    public function store(StoreUserRequest $request): JsonResponse
    {
        try {
            $user = $this->userService->create($request->validated());

            return response()->json([
                'status' => 'success',
                'message' => 'User berhasil dibuat',
                'data' => new UserResource($user)
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Gagal membuat user: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    public function show($id): JsonResponse
    {
        try {
            $user = $this->userService->find($id);

            if (!$user) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'User tidak ditemukan',
                    'data' => null
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Detail user berhasil diambil',
                'data' => new UserResource($user)
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Gagal menampilkan detail user: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    public function update(UpdateUserRequest $request, $id): JsonResponse
    {
        try {
            $user = $this->userService->update($id, $request->validated());

            if (!$user) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'User tidak ditemukan untuk diperbarui',
                    'data' => null
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'User berhasil diperbarui',
                'data' => new UserResource($user)
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Gagal memperbarui user: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        try {
            $deleted = $this->userService->delete($id);

            if (!$deleted) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'User tidak ditemukan untuk dihapus',
                    'data' => null
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'User berhasil dihapus',
                'data' => null
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Gagal menghapus user: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    public function getRoles(): JsonResponse
    {
        try {
            $roles = Role::select('id', 'name')->get();

            return response()->json([
                'status' => 'success',
                'message' => 'Daftar role berhasil diambil',
                'data' => $roles
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Gagal mengambil role: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }
}
