<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class StoreFixedScheduleRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Bisa tambahin policy/role check kalau pakai Passport
        return true;
    }

    public function rules(): array
    {
        return [
            'room_id' => 'required|exists:rooms,id',
            'day_of_week' => 'required|in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'description' => 'nullable|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'room_id.required' => 'Room wajib dipilih.',
            'room_id.exists' => 'Room tidak valid.',
            'day_of_week.in' => 'Hari harus Senin sampai Minggu.',
            'start_time.date_format' => 'Format jam mulai harus HH:mm.',
            'end_time.after' => 'Jam selesai harus setelah jam mulai.',
        ];
    }
}
