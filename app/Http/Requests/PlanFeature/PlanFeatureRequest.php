<?php

namespace App\Http\Requests\PlanFeature;

use Illuminate\Foundation\Http\FormRequest;

class PlanFeatureRequest extends FormRequest
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
            'id_plan' => [
                $isUpdate ? 'sometimes' : 'required',
                'integer',
                'exists:plans,id',
            ],
            'name' => [
                $isUpdate ? 'sometimes' : 'required',
                'string',
                'max:255',
            ],
            'description' => [
                'nullable',
                'string',
            ],
            'value' => [
                $isUpdate ? 'sometimes' : 'required',
                'string',
                'max:255',
            ],
            'order' => [
                'sometimes',
                'integer',
                'min:0',
            ],
            'active' => [
                'sometimes',
                'boolean',
            ],
        ];
    }
}
