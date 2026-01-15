<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
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
        $userParam = $this->route('user');
        $userId = $userParam instanceof User ? $userParam->id : $userParam;

        return [
            'email' => ['sometimes', 'required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($userId)],
            'password' => ['nullable', 'string', 'min:8', 'max:255'],

            'role_id' => [
                'nullable',
                'integer',
                Rule::exists('roles', 'id')->whereIn('name', ['visitante', 'servidor_publico', 'contratista', 'funcionario']),
            ],
            'role_name' => ['nullable', 'string', 'max:50', Rule::in(['visitante', 'servidor_publico', 'contratista', 'funcionario'])],

            'cargo_id' => ['nullable', 'integer', 'exists:cargos,id'],
            'cargo_texto' => ['nullable', 'string', 'max:150'],

            'name' => ['nullable', 'string', 'max:255'],
            'nombre' => ['nullable', 'string', 'max:100'],
            'apellido' => ['nullable', 'string', 'max:100'],
            'n_identidad' => ['required', 'string', 'max:50', Rule::unique('users', 'n_identidad')->ignore($userId)],
            'observaciones' => ['nullable', 'string', 'max:500'],
            'secretaria_id' => ['nullable', 'integer', 'exists:secretarias,id'],
            'gerencia_id' => [
                'nullable',
                'integer',
                'exists:gerencias,id',
                // Validar que la gerencia pertenezca a la secretaría seleccionada (si se envía secretaria_id)
                function ($attribute, $value, $fail) {
                    if ($value && $this->input('secretaria_id')) {
                        $gerencia = \App\Models\Gerencia::find($value);
                        if ($gerencia && $gerencia->secretaria_id != $this->input('secretaria_id')) {
                            $fail('La gerencia seleccionada no pertenece a la secretaría seleccionada.');
                        }
                    }
                },
            ],
            'foto_perfil' => ['nullable', 'string'],
            'foto' => ['nullable', 'file', 'image', 'max:4096'],

            // Tipo de contrato (sin documentos PDF)
            'tipo_contrato' => ['nullable', 'string', Rule::in(['prestacion_servicios', 'contratista_externo', 'contrato_indefinido'])],

            'activo' => ['nullable', 'boolean'],
            'fecha_expiracion' => ['nullable', 'date'],
            'es_discapacitado' => ['nullable', 'boolean'],
        ];
    }
}
