<?php

namespace App\Http\Requests;

use App\Models\Cargo;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;
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
        $user = $userParam instanceof User ? $userParam : null;
        $userId = $user?->id ?? $userParam;

        $roleName = $this->inputRoleName($user);

        return [
            // Visitantes pueden no tener correo (no podrán iniciar sesión ni generar QR)
            'email' => array_values(array_filter([
                'sometimes',
                $roleName === 'visitante' ? 'nullable' : 'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($userId),
            ])),
            'password' => ['nullable', 'string', 'min:8', 'max:255'],

            'role_id' => [
                'nullable',
                'integer',
                Rule::exists('roles', 'id')->whereIn('name', ['visitante', 'servidor_publico', 'proveedor', 'funcionario']),
            ],
            'role_name' => ['nullable', 'string', 'max:50', Rule::in(['visitante', 'servidor_publico', 'proveedor', 'funcionario'])],

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
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $actor = $this->user();
            $cargoId = $this->input('cargo_id');
            if (!$actor || !$cargoId) {
                return;
            }

            $userParam = $this->route('user');
            $userEdit = $userParam instanceof User ? $userParam : null;

            // Si no tiene permiso superior, solo se permite mantener el cargo superior actual (no reasignarlo)
            $cargo = Cargo::query()->whereKey($cargoId)->first();
            if ($cargo && $cargo->requiere_permiso_superior && !$actor->hasPermission('view_cargos_permiso_superior')) {
                $esMismoCargo = $userEdit && (int) $userEdit->cargo_id === (int) $cargoId;
                if (!$esMismoCargo) {
                    $validator->errors()->add('cargo_id', 'No tienes permiso para asignar cargos con permiso superior.');
                }
            }
        });
    }

    protected function prepareForValidation(): void
    {
        // Convertir "" -> null para que "nullable" funcione como se espera
        if ($this->has('email')) {
            $email = $this->input('email');
            if (is_string($email) && trim($email) === '') {
                $this->merge(['email' => null]);
            }
        }

        // Sin permiso "editar rol de usuario", no se permite cambiar rol ni cargo
        $userParam = $this->route('user');
        $userEdit = $userParam instanceof User ? $userParam : null;
        if ($userEdit && $this->user() && !$this->user()->hasPermission('edit_user_role')) {
            $this->merge([
                'role_id' => $userEdit->role_id,
                'cargo_id' => $userEdit->cargo_id,
                'cargo_texto' => $userEdit->cargo_texto ?? '',
            ]);
        }
    }

    private function inputRoleName(?User $user): ?string
    {
        $roleName = $this->input('role_name');
        if (is_string($roleName) && $roleName !== '') {
            return $roleName;
        }

        $roleId = $this->input('role_id');
        if ($roleId) {
            return Role::query()->whereKey($roleId)->value('name');
        }

        $roleIdFromUser = $user?->role_id;
        if ($roleIdFromUser) {
            return Role::query()->whereKey($roleIdFromUser)->value('name');
        }

        return null;
    }
}
