<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GenerateCodigoQrRequest extends FormRequest
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
            'user_id' => ['required', 'integer', 'exists:users,id'],
            // Puertas específicas para este QR (opcional). Si viene vacío, se usa el cargo del usuario.
            'puertas' => ['nullable', 'array'],
            'puertas.*' => ['integer', 'exists:puertas,id'],

            // Reglas horarias opcionales (se aplican a todas las puertas enviadas)
            'hora_inicio' => ['nullable', 'date_format:H:i'],
            'hora_fin' => ['nullable', 'date_format:H:i'],
            'dias_semana' => ['nullable', 'string', 'max:20'], // "1,2,3,4,5"
            'fecha_inicio' => ['nullable', 'date'],
            'fecha_fin' => ['nullable', 'date'],
        ];
    }
}
