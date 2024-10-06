<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PackageRequest extends FormRequest
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
            'cook_id' => 'required ',
            'package_name' => 'required',
            'package_description' => 'required',
            'discount' => 'required',
            'meals' => 'required|array',
            'meals.*.meal_id' => 'required',
            'meals.*.quantity' => 'required|integer|min:1',
        ];
    }

    /**
     * Rules messages
     */
    public function messages(): array
    {
        return [
            'cook_id.required' => 'Cook\'s id is required!',
            'package_name.required' => 'meal\'s Package name is required',
            'package_description.required' => 'meal\'s Package description is required',
            'discount.required' => 'meal\'s Package discount is required',
            'meals.required' => 'meal\'s Package meals is required',
        ];
        
    }
}
