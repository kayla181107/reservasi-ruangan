<?php

namespace App\Http\Requests\Karyawan;

use Illuminate\Foundation\Http\FormRequest;

class ReservationStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'room_id'     => 'required|exists:rooms,id',
            'date'        => 'required|date|after_or_equal:today', 
            'day_of_week' => 'required|string|in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday',
            'start_time'  => 'required|date_format:H:i', 
            'end_time'    => 'required|date_format:H:i|after:start_time', 
            'reason'      => 'nullable|string|max:255',
        ];
    }
}
