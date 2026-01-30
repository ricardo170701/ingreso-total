<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpsertCargoPuertaAccesoRequest extends FormRequest
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
            'puerta_id' => ['required_without:puertas', 'integer', 'exists:puertas,id'],
            'puertas' => ['required_without:puerta_id', 'array', 'min:1'],
            'puertas.*' => ['integer', 'exists:puertas,id'],
            'hora_inicio' => ['nullable', 'date_format:H:i'],
            'hora_fin' => ['nullable', 'date_format:H:i'],
            'dias_semana' => ['nullable', 'string', 'max:20'],
            'fecha_inicio' => ['nullable', 'date'],
            'fecha_fin' => ['nullable', 'date'],
            'activo' => ['nullable', 'boolean'],
        ];
    }
}
