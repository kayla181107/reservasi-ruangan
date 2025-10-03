<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FixedScheduleRequest extends FormRequest
{
    public function authorize(): bool
    {
        // hanya admin yang bisa create/update
        return auth()->user()?->hasRole('admin') ?? false;
    }

    public function rules(): array
    {
        return [
            'room_id'     => 'required|exists:rooms,id',
            'day_of_week' => 'required|in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu,Minggu',
            'start_time'  => 'required|time_format:H:i',
            'end_time'    => 'required|time_format:H:i|after:start_time',
            'description' => 'nullable|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'day_of_week.in' => 'Hari harus salah satu dari: Senin, Selasa, Rabu, Kamis, Jumat, Sabtu, Minggu',
            'end_time.after' => 'Waktu selesai harus lebih besar dari waktu mulai',
        ];
    }
}
