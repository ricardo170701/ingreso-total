<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GenerateCodigoQrRequest extends FormRequest
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
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'secretaria_id' => ['nullable', 'integer', Rule::exists('secretarias', 'id')],
            'gerencia_id' => [
                'nullable',
                'integer',
                Rule::exists('gerencias', 'id'),
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
            'responsable_id' => [
                'nullable',
                'integer',
                Rule::exists('users', 'id'),
                // Validar que el responsable sea un usuario servidor público o proveedor
                function ($attribute, $value, $fail) {
                    if ($value) {
                        $responsable = \App\Models\User::query()
                            ->with('role')
                            ->find($value);
                        if (!$responsable) {
                            $fail('El responsable seleccionado no existe.');
                            return;
                        }
                        $roleName = $responsable->role?->name ?? '';
                        $staffRoles = ['servidor_publico', 'proveedor', 'funcionario']; // 'funcionario' legado
                        if (!in_array($roleName, $staffRoles, true)) {
                            $fail('El responsable debe ser un servidor público o proveedor.');
                        }
                    }
                },
            ],
            // Pisos (recomendado para visitantes). Se expanden a puertas al generar el QR.
            'pisos' => ['nullable', 'array'],
            'pisos.*' => ['integer', Rule::exists('pisos', 'id')],

            // Puertas específicas para este QR (legacy / opcional)
            'puertas' => ['nullable', 'array'],
            'puertas.*' => ['integer', 'exists:puertas,id'],

            // Reglas horarias opcionales (se aplican a todas las puertas enviadas)
            'hora_inicio' => ['nullable', 'date_format:H:i'],
            'hora_fin' => ['nullable', 'date_format:H:i'],
            'dias_semana' => ['nullable', 'string', 'max:20'], // "1,2,3,4,5"
            'fecha_inicio' => ['nullable', 'date'],
            'fecha_fin' => ['nullable', 'date'],
        ];
    }
}
