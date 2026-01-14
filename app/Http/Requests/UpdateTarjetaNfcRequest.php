<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTarjetaNfcRequest extends FormRequest
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
        $tarjetaId = $this->route('tarjeta_nfc')?->id ?? $this->route('tarjetaNfc')?->id;

        return [
            'codigo' => ['required', 'string', 'max:100', Rule::unique('tarjetas_nfc', 'codigo')->ignore($tarjetaId)],
            'nombre' => ['nullable', 'string', 'max:255'],
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            'gerencia_id' => ['nullable', 'integer', 'exists:gerencias,id'],
            'fecha_asignacion' => ['nullable', 'date'],
            'fecha_expiracion' => ['nullable', 'date', 'after_or_equal:fecha_asignacion'],
            'activo' => ['nullable', 'boolean'],
            'observaciones' => ['nullable', 'string', 'max:1000'],
            // Para actualización de permisos
            'pisos' => ['nullable', 'array'],
            'pisos.*' => ['integer', 'exists:pisos,id'],
            'puertas' => ['nullable', 'array'],
            'puertas.*' => ['integer', 'exists:puertas,id'],
            'hora_inicio' => ['nullable', 'date_format:H:i'],
            'hora_fin' => ['nullable', 'date_format:H:i', 'after:hora_inicio'],
            'dias_semana' => ['nullable', 'string', 'max:20'],
            'fecha_inicio' => ['nullable', 'date'],
            'fecha_fin' => ['nullable', 'date', 'after_or_equal:fecha_inicio'],
        ];
    }
}
