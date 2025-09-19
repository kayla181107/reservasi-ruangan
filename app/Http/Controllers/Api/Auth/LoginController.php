<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\Auth\LoginResource;
use App\Services\Auth\LoginService;
use Illuminate\support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class LoginController extends Controller
{


    protected LoginService $loginService;

    public function __construct(LoginService $loginService)
    {
        $this->loginService = $loginService;
    }

    public function login(LoginRequest $request)
{
    $user = $this->loginService->attemptLogin($request->validated());

    if (!$user) {
        return response()->json(['message' => 'Email atau password salah.'], 401);
    }

    return new LoginResource($user); // langsung lempar User
}

}