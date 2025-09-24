<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FixedScheduleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'room_id'     => 'required|exists:rooms,id',
            'date'        => 'nullable|date|after_or_equal:today',
            'day_of_week' => 'required|in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday',
            'start_time'  => 'required|date_format:H:i',
            'end_time'    => 'required|date_format:H:i|after:start_time',
            'description' => 'nullable|string',
        ];
    }
}
