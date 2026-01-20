<?php

namespace App\Http\Requests;

use App\Models\Role;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
        $roleName = $this->inputRoleName();

        return [
            // Visitantes pueden no tener correo (no podrán iniciar sesión ni generar QR)
            'email' => array_values(array_filter([
                $roleName === 'visitante' ? 'nullable' : 'required',
                'email',
                'max:255',
                Rule::unique('users', 'email'),
            ])),
            'password' => ['required', 'string', 'min:8', 'max:255'],

            // Aceptamos role por id o por name para facilidad
            'role_id' => [
                'nullable',
                'integer',
                Rule::exists('roles', 'id')->whereIn('name', ['visitante', 'servidor_publico', 'proveedor', 'funcionario']),
            ],
            'role_name' => ['nullable', 'string', 'max:50', Rule::in(['visitante', 'servidor_publico', 'proveedor', 'funcionario'])],

            'cargo_id' => ['nullable', 'integer', 'exists:cargos,id'],
            'cargo_texto' => ['nullable', 'string', 'max:150'],

            // Perfil
            'name' => ['nullable', 'string', 'max:255'],
            'nombre' => ['nullable', 'string', 'max:100'],
            'apellido' => ['nullable', 'string', 'max:100'],
            'n_identidad' => ['required', 'string', 'max:50', 'unique:users,n_identidad'],
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

            // Campos para proveedor
            'nombre_empresa' => [
                'nullable',
                'string',
                'max:255',
                function ($attribute, $value, $fail) use ($roleName) {
                    if ($roleName === 'proveedor' && empty($value)) {
                        $fail('El nombre de la empresa es requerido para proveedores.');
                    }
                },
            ],
            'cargo_empresa' => [
                'nullable',
                'string',
                'max:255',
                function ($attribute, $value, $fail) use ($roleName) {
                    if ($roleName === 'proveedor' && empty($value)) {
                        $fail('El cargo en la empresa es requerido para proveedores.');
                    }
                },
            ],
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

    protected function prepareForValidation(): void
    {
        // Convertir "" -> null para que "nullable" funcione como se espera
        $email = $this->input('email');
        if (is_string($email) && trim($email) === '') {
            $this->merge(['email' => null]);
        }
    }

    private function inputRoleName(): ?string
    {
        $roleName = $this->input('role_name');
        if (is_string($roleName) && $roleName !== '') {
            return $roleName;
        }

        $roleId = $this->input('role_id');
        if ($roleId) {
            return Role::query()->whereKey($roleId)->value('name');
        }

        return null;
    }
}
