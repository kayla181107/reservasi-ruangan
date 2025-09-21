<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class UpdateReservationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'room_id'    => 'sometimes|exists:rooms,id',
            'user_id'    => 'sometimes|exists:users,id',
            'start_time' => 'sometimes|date|after_or_equal:now',
            'end_time'   => 'sometimes|date|after:start_time',
            'purpose'    => 'sometimes|string|max:255',
            'status'     => 'sometimes|in:pending,approved,rejected,canceled',
            'reason'     => 'nullable|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'room_id.exists'    => 'Room tidak valid.',
            'user_id.exists'    => 'User tidak valid.',
            'start_time.after_or_equal' => 'Waktu mulai minimal sekarang.',
            'end_time.after'    => 'Waktu selesai harus setelah mulai.',
            'status.in'         => 'Status tidak valid.',
        ];
    }
}
