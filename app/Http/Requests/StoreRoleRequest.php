<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRoleRequest extends FormRequest
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
            // Roles fijos: funcionario | visitante
            'name' => ['required', 'string', 'max:50', Rule::in(['funcionario', 'visitante']), 'unique:roles,name'],
            'description' => ['nullable', 'string', 'max:255'],
        ];
    }
}
