<?php

namespace App\Http\Requests;

use App\Models\Zona;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateZonaRequest extends FormRequest
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
        $zonaParam = $this->route('zona');
        $zonaId = $zonaParam instanceof Zona ? $zonaParam->id : $zonaParam;

        return [
            'nombre' => ['sometimes', 'required', 'string', 'max:100'],
            'codigo' => ['sometimes', 'required', 'string', 'max:20', Rule::unique('zonas', 'codigo')->ignore($zonaId)],
            'descripcion' => ['nullable', 'string'],
            'nivel_seguridad' => ['nullable', 'integer', 'min:1', 'max:5'],
            'activa' => ['nullable', 'boolean'],
            'ubicacion_gps' => ['nullable', 'string', 'max:255'],
        ];
    }
}
