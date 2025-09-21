<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFixedScheduleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Bisa tambahkan Gate/Policy kalau mau
    }

    public function rules(): array
    {
        return [
            'room_id'     => 'sometimes|exists:rooms,id',
            'day_of_week' => 'sometimes|in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday',
            'start_time'  => 'sometimes|date_format:H:i',
            'end_time'    => 'sometimes|date_format:H:i|after:start_time',
            'description' => 'nullable|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'room_id.exists'     => 'Room tidak valid.',
            'day_of_week.in'     => 'Hari harus Senin sampai Minggu.',
            'start_time.date_format' => 'Format jam mulai harus HH:mm.',
            'end_time.after'     => 'Jam selesai harus setelah jam mulai.',
        ];
    }
}
