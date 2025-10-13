<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function profile(Request $request)
    {
        return response()->json($request->user());
    }

    // PUT Profile
    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'name'                  => ['required', 'string', 'max:255'],
            'email'                 => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'current_password'      => ['nullable', 'current_password'], // password lama
            'password'              => ['nullable', 'min:6', 'confirmed'], // password baru
            'password_confirmation' => ['nullable', 'min:6'],
        ]);

        if (!empty($validated['password'])) {
            if (empty($validated['current_password'])) {
                return response()->json([
                    'message' => 'Password lama wajib diisi untuk mengubah password.'
                ], 422);
            }

            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        unset($validated['current_password']); 
        unset($validated['password_confirmation']); 

        $user->update($validated);

        return response()->json([
            'message' => 'Profile berhasil diperbarui',
            'user'    => $user
        ]);
    }
}
