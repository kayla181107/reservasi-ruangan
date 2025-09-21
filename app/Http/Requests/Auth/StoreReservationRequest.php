<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class StoreReservationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Bisa pakai Gate/Policy kalau mau cek role user
    }

    public function rules(): array
    {
        return [
            'room_id' => 'required|exists:rooms,id',
            'user_id' => 'required|exists:users,id',
            'start_time' => 'required|date|after_or_equal:now',
            'end_time' => 'required|date|after:start_time',
            'purpose' => 'required|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'room_id.required' => 'Room wajib dipilih.',
            'room_id.exists' => 'Room tidak valid.',
            'user_id.required' => 'User wajib dipilih.',
            'user_id.exists' => 'User tidak valid.',
            'start_time.after_or_equal' => 'Waktu mulai minimal sekarang.',
            'end_time.after' => 'Waktu selesai harus setelah mulai.',
            'purpose.required' => 'Tujuan reservasi wajib diisi.',
        ];
    }
}
