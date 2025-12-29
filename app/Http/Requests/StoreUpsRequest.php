<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUpsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user();
    }

    public function rules(): array
    {
        return [
            'codigo' => ['required', 'string', 'max:100', 'unique:ups,codigo'],
            'nombre' => ['required', 'string', 'max:255'],
            'piso_id' => ['nullable', 'integer', 'exists:pisos,id'],
            'ubicacion' => ['nullable', 'string', 'max:255'],
            'marca' => ['nullable', 'string', 'max:255'],
            'modelo' => ['nullable', 'string', 'max:255'],
            'serial' => ['nullable', 'string', 'max:255'],
            'foto' => ['nullable', 'image', 'max:4096'], // 4MB
            'potencia_va' => ['nullable', 'integer', 'min:0', 'max:2147483647'],
            'potencia_w' => ['nullable', 'integer', 'min:0', 'max:2147483647'],
            'activo' => ['nullable', 'boolean'],
            'observaciones' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
