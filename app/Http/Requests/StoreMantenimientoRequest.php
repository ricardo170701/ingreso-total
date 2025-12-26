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
            'tipo' => ['required', 'in:programado,realizado'],
            'fecha_fin_programada' => ['nullable', 'date', 'required_if:tipo,programado', 'after_or_equal:fecha_mantenimiento'],
            'defectos' => ['required', 'array'],
            'defectos.*.id' => ['required', 'integer', 'exists:defectos,id'],
            'defectos.*.nivel_gravedad' => ['required', 'integer', 'in:0,1,2,3'], // 0=sin defecto, 1=ligero, 2=grave, 3=muy grave
            'otros_defectos' => ['nullable', 'string', 'max:1000'],
            'observaciones' => ['nullable', 'string', 'max:2000'],
            'imagenes' => ['nullable', 'array', 'max:10'],
            'imagenes.*' => ['image', 'mimes:jpeg,jpg,png,gif', 'max:2048'], // Max 2MB por imagen
        ];
    }
}
