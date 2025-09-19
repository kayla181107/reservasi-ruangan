<?php

namespace App\Services\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class LoginService
{
    public function attemptLogin(array $credentials): ?User
    {
        $user = User::where('email', $credentials['email'])->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            return null;
        }

        // Tambahkan token sebagai atribut sementara
        $user->token = $user->createToken('API Token')->accessToken;

        return $user; 
    }
}
