<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUpsMantenimientoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user();
    }

    public function rules(): array
    {
        return [
            'fecha_mantenimiento' => ['required', 'date'],
            'tipo' => ['required', 'in:programado,realizado'],
            'fecha_fin_programada' => ['nullable', 'date', 'required_if:tipo,programado', 'after_or_equal:fecha_mantenimiento'],
            'descripcion' => ['nullable', 'string', 'max:5000'],
            'fotos' => ['nullable', 'array', 'max:6'],
            'fotos.*' => ['image', 'max:4096'], // 4MB c/u
            'documentos' => ['nullable', 'array', 'max:5'],
            'documentos.*' => ['file', 'mimes:pdf', 'max:10240'], // 10MB c/u
        ];
    }
}
