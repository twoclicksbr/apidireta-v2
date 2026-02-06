<?php

namespace App\Http\Requests\Person;

use Illuminate\Foundation\Http\FormRequest;

class PersonRequest extends FormRequest
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
        $isUpdate = $this->route('id') !== null;

        return [
            'tenant_id' => [
                $isUpdate ? 'sometimes' : 'required',
                'integer',
                'exists:tenants,id',
            ],
            'name' => [
                $isUpdate ? 'sometimes' : 'required',
                'string',
                'max:255',
            ],
            'birth_date' => [
                $isUpdate ? 'sometimes' : 'required',
                'date',
                'before:today',
            ],
            'whatsapp' => [
                $isUpdate ? 'sometimes' : 'required',
                'string',
                'max:255',
            ],
            'status' => [
                $isUpdate ? 'sometimes' : 'required',
                'string',
                'max:255',
            ],
        ];
    }
}
