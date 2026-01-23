<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePuertaRequest extends FormRequest
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
            'zona_id' => ['nullable', 'integer', 'exists:zonas,id'],
            'piso_id' => ['nullable', 'integer', 'exists:pisos,id'],
            'tipo_puerta_id' => ['nullable', 'integer', 'exists:tipo_puertas,id'],
            'material_id' => ['nullable', 'integer', 'exists:materials,id'],
            'ip_entrada' => ['nullable', 'ip'],
            'ip_salida' => ['nullable', 'ip'],
            'imagen' => ['nullable', 'image', 'mimes:jpeg,jpg,png,gif,webp', 'max:2048'], // Max 2MB
            'tiempo_apertura' => ['nullable', 'integer', 'min:1', 'max:300'], // Entre 1 y 300 segundos
            'tiempo_discapacitados' => ['nullable', 'integer', 'min:1', 'max:600'], // Entre 1 y 600 segundos (10 minutos)
            'alto' => ['nullable', 'numeric', 'min:0', 'max:1000'], // En centímetros
            'largo' => ['nullable', 'numeric', 'min:0', 'max:1000'], // En centímetros
            'ancho' => ['nullable', 'numeric', 'min:0', 'max:1000'], // En centímetros
            'peso' => ['nullable', 'numeric', 'min:0', 'max:10000'], // En kilogramos
            'nombre' => ['required', 'string', 'max:255'],
            'ubicacion' => ['nullable', 'string', 'max:255'],
            'descripcion' => ['nullable', 'string'],
            'codigo_fisico' => ['nullable', 'string', 'max:50', 'unique:puertas,codigo_fisico'],
            'codigo_fisico_salida' => ['nullable', 'string', 'max:50', 'unique:puertas,codigo_fisico_salida'],
            'requiere_discapacidad' => ['nullable', 'boolean'],
            'es_oculta' => ['nullable', 'boolean'],
            'requiere_permiso_datacenter' => ['nullable', 'boolean'],
            'solo_servidores_publicos' => ['nullable', 'boolean'],
            'activo' => ['nullable', 'boolean'],
        ];
    }
}
