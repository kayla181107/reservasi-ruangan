<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class StoreRoomRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'        => 'required|string|max:100|unique:rooms,name',
            'capacity'    => 'nullable|integer|min:1',
            'description' => 'nullable|string|max:500',
            'status'      => 'in:active,inactive',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'  => 'Nama ruangan wajib diisi.',
            'name.unique'    => 'Nama ruangan sudah digunakan.',
            'capacity.integer' => 'Kapasitas harus berupa angka.',
            'capacity.min'   => 'Kapasitas minimal 1.',
            'status.in'      => 'Status hanya boleh active atau inactive.',
        ];
    }
}
