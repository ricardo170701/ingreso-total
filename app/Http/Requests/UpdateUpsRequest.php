<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUpsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user();
    }

    public function rules(): array
    {
        $upsId = $this->route('up')?->id ?? $this->route('ups')?->id;

        return [
            'codigo' => ['required', 'string', 'max:100', 'unique:ups,codigo,' . $upsId],
            'comp' => ['nullable', 'string', 'max:100'],
            'fecha_adquisicion' => ['nullable', 'date'],
            'elemt' => ['nullable', 'string', 'max:100'],
            'ri' => ['nullable', 'string', 'max:100'],
            'nombre' => ['required', 'string', 'max:255'],
            'piso_id' => ['nullable', 'integer', 'exists:pisos,id'],
            'estado' => ['nullable', 'string', 'max:50'],
            'ubicacion' => ['nullable', 'string', 'max:255'],
            'marca' => ['nullable', 'string', 'max:255'],
            'modelo' => ['nullable', 'string', 'max:255'],
            'serial' => ['nullable', 'string', 'max:255'],
            'foto' => ['nullable', 'image', 'max:4096'], // 4MB
            'ficha_tecnica' => ['nullable', 'file', 'mimes:pdf', 'max:10240'], // 10MB
            'potencia_va' => ['nullable', 'integer', 'min:0', 'max:2147483647'],
            'potencia_kva' => ['nullable', 'numeric', 'min:0'],
            'potencia_w' => ['nullable', 'integer', 'min:0', 'max:2147483647'],
            'potencia_kw' => ['nullable', 'numeric', 'min:0'],
            'cantidad_baterias' => ['nullable', 'integer', 'min:0'],
            'voltaje_baterias' => ['nullable', 'numeric', 'min:0'],
            'activo' => ['nullable', 'boolean'],
            'observaciones' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
