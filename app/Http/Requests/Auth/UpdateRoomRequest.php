<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRoomRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Bisa validasi role admin di sini
    }

    public function rules(): array
    {
        return [
            'name' => [
                'sometimes',
                'string',
                'max:100',
                Rule::unique('rooms', 'name')->ignore($this->route('id')), 
                // pastikan parameter route pakai {id} di api.php
            ],
            'capacity'    => 'sometimes|nullable|integer|min:1',
            'description' => 'sometimes|nullable|string|max:500',
            'status'      => 'sometimes|in:active,inactive',
        ];
    }

    public function messages(): array
    {
        return [
            'name.unique'       => 'Nama ruangan sudah digunakan.',
            'capacity.integer'  => 'Kapasitas harus berupa angka.',
            'capacity.min'      => 'Kapasitas minimal 1.',
            'status.in'         => 'Status hanya boleh active atau inactive.',
        ];
    }
}
