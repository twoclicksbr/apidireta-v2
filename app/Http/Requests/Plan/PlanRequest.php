<?php

namespace App\Http\Requests\Plan;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PlanRequest extends FormRequest
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
            'name' => [
                $isUpdate ? 'sometimes' : 'required',
                'string',
                'max:255',
                Rule::unique('plans', 'name')->ignore($this->route('id')),
            ],
            'slug' => [
                $isUpdate ? 'sometimes' : 'required',
                'string',
                'max:255',
                Rule::unique('plans', 'slug')->ignore($this->route('id')),
            ],
            'monthly_price' => [
                $isUpdate ? 'sometimes' : 'required',
                'numeric',
                'min:0',
            ],
            'annual_price' => [
                $isUpdate ? 'sometimes' : 'required',
                'numeric',
                'min:0',
            ],
        ];
    }
}
