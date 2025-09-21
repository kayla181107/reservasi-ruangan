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
            'name'        => [
                'sometimes',
                'string',
                'max:100',
                Rule::unique('rooms', 'name')->ignore($this->route('id')), 
                // pastikan sesuai parameter route api.php {id}
            ],
            'capacity'    => 'sometimes|integer|min:1',
            'location'    => 'nullable|string|max:255',
            'description' => 'nullable|string|max:500',
            'status'      => 'sometimes|in:active,inactive',
        ];
    }

    public function messages(): array
    {
        return [
            'name.unique'     => 'Nama ruangan sudah digunakan.',
            'capacity.integer'=> 'Kapasitas harus angka.',
            'capacity.min'    => 'Kapasitas minimal 1.',
            'status.in'       => 'Status hanya boleh active/inactive.',
        ];
    }
}
