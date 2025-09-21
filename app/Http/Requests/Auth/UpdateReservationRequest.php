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
            'date'       => 'sometimes|date|after_or_equal:today',
            'start_time' => 'sometimes|date_format:H:i',
            'end_time'   => 'sometimes|date_format:H:i|after:start_time',
            'status'     => 'sometimes|in:pending,approved,rejected,canceled',
            'reason'     => 'nullable|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'room_id.exists'          => 'Room tidak valid.',
            'user_id.exists'          => 'User tidak valid.',
            'date.date'               => 'Tanggal harus valid.',
            'date.after_or_equal'     => 'Tanggal minimal hari ini.',
            'start_time.date_format'  => 'Format waktu mulai harus HH:MM.',
            'end_time.date_format'    => 'Format waktu selesai harus HH:MM.',
            'end_time.after'          => 'Waktu selesai harus setelah waktu mulai.',
            'status.in'               => 'Status tidak valid.',
        ];
    }
}
