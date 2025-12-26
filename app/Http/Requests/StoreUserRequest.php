<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // La autorización fina (por rol) se valida en el controller por ahora.
        // Aquí solo exigimos que el usuario esté autenticado (web o sanctum).
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
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'max:255'],

            // Aceptamos role por id o por name para facilidad
            'role_id' => ['nullable', 'integer', 'exists:roles,id'],
            'role_name' => ['nullable', 'string', 'max:50', 'exists:roles,name'],

            'cargo_id' => ['nullable', 'integer', 'exists:cargos,id'],

            // Perfil
            'name' => ['nullable', 'string', 'max:255'],
            'username' => ['nullable', 'string', 'max:50', 'unique:users,username'],
            'nombre' => ['nullable', 'string', 'max:100'],
            'apellido' => ['nullable', 'string', 'max:100'],
            'departamento_id' => ['nullable', 'integer', 'exists:departamentos,id'],
            'foto_perfil' => ['nullable', 'string'],

            'activo' => ['nullable', 'boolean'],
            'fecha_expiracion' => ['nullable', 'date'],
            'es_discapacitado' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'role_id.exists' => 'El role_id no existe.',
            'role_name.exists' => 'El role_name no existe.',
            'cargo_id.exists' => 'El cargo_id no existe.',
        ];
    }
}
