<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpsertCargoPisoAccesoRequest extends FormRequest
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
            // Permitir uno o varios pisos
            'piso_id' => ['required_without:pisos', 'integer', 'exists:pisos,id'],
            'pisos' => ['required_without:piso_id', 'array', 'min:1'],
            'pisos.*' => ['integer', 'exists:pisos,id'],
            'hora_inicio' => ['nullable', 'date_format:H:i'],
            'hora_fin' => ['nullable', 'date_format:H:i'],
            'dias_semana' => ['nullable', 'string', 'max:20'], // "1,2,3,4,5"
            'fecha_inicio' => ['nullable', 'date'],
            'fecha_fin' => ['nullable', 'date'],
            'activo' => ['nullable', 'boolean'],
        ];
    }
}
