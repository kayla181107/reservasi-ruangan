<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class StoreReservationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Bisa pakai Gate/Policy kalau perlu cek role user
    }

    public function rules(): array
    {
        return [
            'user_id'    => 'required|exists:users,id',
            'room_id'    => 'required|exists:rooms,id',
            'date'       => 'required|date|after_or_equal:today',
            'start_time' => 'required|date_format:H:i',
            'end_time'   => 'required|date_format:H:i|after:start_time',
            'status'     => 'in:pending,approved,rejected,canceled',
            'reason'     => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'user_id.required'    => 'User wajib dipilih.',
            'user_id.exists'      => 'User tidak valid.',
            'room_id.required'    => 'Room wajib dipilih.',
            'room_id.exists'      => 'Room tidak valid.',
            'date.required'       => 'Tanggal wajib diisi.',
            'date.date'           => 'Tanggal tidak valid.',
            'date.after_or_equal' => 'Tanggal tidak boleh sebelum hari ini.',
            'start_time.required' => 'Waktu mulai wajib diisi.',
            'start_time.date_format' => 'Format waktu mulai harus HH:MM.',
            'end_time.required'   => 'Waktu selesai wajib diisi.',
            'end_time.date_format'=> 'Format waktu selesai harus HH:MM.',
            'end_time.after'      => 'Waktu selesai harus setelah waktu mulai.',
            'status.in'           => 'Status hanya boleh pending, approved, rejected, atau canceled.',
            'reason.string'       => 'Alasan harus berupa teks.',
        ];
    }
}
