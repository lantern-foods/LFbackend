<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PackageEditRequest extends FormRequest
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
        return [
            'package_name' => 'required|string|max:255',
            'package_description' => 'required|string|max:1000',
            'discount' => 'required|numeric|min:0|max:100',
        ];
    }

    /**
     * Custom messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'package_name.required' => 'Package name is required.',
            'package_description.required' => 'Package description is required.',
            'discount.required' => 'Package discount is required.',
            'discount.numeric' => 'Package discount must be a numeric value.',
            'discount.min' => 'Discount cannot be negative.',
            'discount.max' => 'Discount cannot exceed 100%.',
        ];
    }
}
