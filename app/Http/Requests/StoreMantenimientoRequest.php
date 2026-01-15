<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMantenimientoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return (bool) $this->user();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'puerta_id' => ['required', 'integer', 'exists:puertas,id'],
            'fecha_mantenimiento' => ['required', 'date'],
            'fecha_fin_programada' => ['nullable', 'date', 'required_if:tipo,programado', 'after_or_equal:fecha_mantenimiento'],
            'tipo' => ['required', 'in:programado,realizado'],
            'descripcion_mantenimiento' => ['nullable', 'string', 'max:5000'],
            'documentos' => ['nullable', 'array', 'max:5'],
            'documentos.*' => ['file', 'mimes:pdf', 'max:10240'], // Max 10MB por PDF
        ];
    }
}
