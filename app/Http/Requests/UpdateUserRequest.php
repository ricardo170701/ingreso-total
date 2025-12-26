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

            'role_id' => ['nullable', 'integer', 'exists:roles,id'],
            'role_name' => ['nullable', 'string', 'max:50', 'exists:roles,name'],

            'cargo_id' => ['nullable', 'integer', 'exists:cargos,id'],

            'name' => ['nullable', 'string', 'max:255'],
            'username' => ['nullable', 'string', 'max:50', Rule::unique('users', 'username')->ignore($userId)],
            'nombre' => ['nullable', 'string', 'max:100'],
            'apellido' => ['nullable', 'string', 'max:100'],
            'departamento_id' => ['nullable', 'integer', 'exists:departamentos,id'],
            'foto_perfil' => ['nullable', 'string'],

            'activo' => ['nullable', 'boolean'],
            'fecha_expiracion' => ['nullable', 'date'],
            'es_discapacitado' => ['nullable', 'boolean'],
        ];
    }
}
