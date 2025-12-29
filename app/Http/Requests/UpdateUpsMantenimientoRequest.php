<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUpsMantenimientoRequest extends FormRequest
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
            'fotos.*' => ['image', 'max:4096'],
            'documentos' => ['nullable', 'array', 'max:5'],
            'documentos.*' => ['file', 'mimes:pdf', 'max:10240'],
            'imagenes_eliminar' => ['nullable', 'array'],
            'imagenes_eliminar.*' => ['integer', 'exists:ups_mantenimiento_imagenes,id'],
            'documentos_eliminar' => ['nullable', 'array'],
            'documentos_eliminar.*' => ['integer', 'exists:ups_mantenimiento_documentos,id'],
        ];
    }
}
