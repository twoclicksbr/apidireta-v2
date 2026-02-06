<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserRequest extends FormRequest
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
            'person_id' => [
                $isUpdate ? 'sometimes' : 'required',
                'integer',
                'exists:persons,id',
            ],
            'email' => [
                $isUpdate ? 'sometimes' : 'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($this->route('id')),
            ],
            'password' => [
                $isUpdate ? 'sometimes' : 'required',
                'string',
                'min:8',
            ],
            'status' => [
                $isUpdate ? 'sometimes' : 'required',
                'string',
                'max:255',
            ],
        ];
    }
}
