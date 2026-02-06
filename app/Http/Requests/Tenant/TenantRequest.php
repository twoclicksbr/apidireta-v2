<?php

namespace App\Http\Requests\Tenant;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TenantRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $tenantId = $this->route('id');
        $isUpdate = $tenantId !== null;

        return [
            'name' => [
                $isUpdate ? 'sometimes' : 'required',
                'string',
                'max:255',
                Rule::unique('tenants', 'name')->ignore($tenantId),
            ],
            'slug' => [
                $isUpdate ? 'sometimes' : 'required',
                'string',
                'max:255',
                Rule::unique('tenants', 'slug')->ignore($tenantId),
            ],
            'expires_at' => [
                $isUpdate ? 'sometimes' : 'required',
                'date',
                'after:today',
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'O nome do tenant é obrigatório.',
            'name.unique' => 'Já existe um tenant com este nome.',
            'slug.required' => 'O slug é obrigatório.',
            'slug.unique' => 'Já existe um tenant com este slug.',
            'expires_at.required' => 'A data de expiração é obrigatória.',
            'expires_at.date' => 'A data de expiração deve ser uma data válida.',
            'expires_at.after' => 'A data de expiração deve ser posterior a hoje.',
        ];
    }
}
