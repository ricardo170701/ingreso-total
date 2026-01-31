<?php

namespace App\Http\Requests;

use App\Models\Cargo;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCargoRequest extends FormRequest
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
        $cargoParam = $this->route('cargo');
        $cargoId = $cargoParam instanceof Cargo ? $cargoParam->id : $cargoParam;

        return [
            'name' => ['sometimes', 'required', 'string', 'max:100', Rule::unique('cargos', 'name')->ignore($cargoId)],
            'description' => ['nullable', 'string'],
            'activo' => ['nullable', 'boolean'],
            'requiere_permiso_superior' => ['nullable', 'boolean'],
        ];
    }
}
