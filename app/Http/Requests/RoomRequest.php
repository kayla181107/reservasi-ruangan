<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RoomRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // sebaiknya diatur dengan policy/role check (misalnya admin-only)
    }

    public function rules(): array
    {
        return [
            'name'        => 'required|string|max:255',
            'capacity'    => 'nullable|integer|min:1',
            'description' => 'nullable|string',
            'status'      => 'required|in:active,inactive',
        ];
    }
}
