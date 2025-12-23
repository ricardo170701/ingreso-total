<?php

namespace App\Http\Requests;

use App\Models\Puerta;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePuertaRequest extends FormRequest
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
        $puertaParam = $this->route('puerta');
        $puertaId = $puertaParam instanceof Puerta ? $puertaParam->id : $puertaParam;

        return [
            'zona_id' => ['nullable', 'integer', 'exists:zonas,id'],
            'nombre' => ['sometimes', 'required', 'string', 'max:255'],
            'ubicacion' => ['nullable', 'string', 'max:255'],
            'descripcion' => ['nullable', 'string'],
            'codigo_fisico' => ['nullable', 'string', 'max:50', Rule::unique('puertas', 'codigo_fisico')->ignore($puertaId)],
            'requiere_discapacidad' => ['nullable', 'boolean'],
            'activo' => ['nullable', 'boolean'],
        ];
    }
}
