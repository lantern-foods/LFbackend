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
            'cook_id' => 'required|integer|exists:cooks,id',
            'package_name' => 'required|string|max:255',
            'package_description' => 'required|string|max:1000',
            'discount' => 'required|numeric|min:0|max:100',
            'meals' => 'required|array',
            'meals.*.meal_id' => 'required|integer|exists:meals,id',
            'meals.*.quantity' => 'required|integer|min:1',
        ];
    }

    /**
     * Custom messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'cook_id.required' => 'Cook ID is required.',
            'cook_id.integer' => 'Cook ID must be an integer.',
            'cook_id.exists' => 'The selected cook does not exist.',
            'package_name.required' => 'Package name is required.',
            'package_name.string' => 'Package name must be a string.',
            'package_name.max' => 'Package name cannot exceed 255 characters.',
            'package_description.required' => 'Package description is required.',
            'package_description.string' => 'Package description must be a string.',
            'package_description.max' => 'Package description cannot exceed 1000 characters.',
            'discount.required' => 'Package discount is required.',
            'discount.numeric' => 'Package discount must be a numeric value.',
            'discount.min' => 'Discount cannot be less than 0.',
            'discount.max' => 'Discount cannot exceed 100%.',
            'meals.required' => 'Meals are required for the package.',
            'meals.array' => 'Meals must be an array.',
            'meals.*.meal_id.required' => 'Meal ID is required for each meal.',
            'meals.*.meal_id.integer' => 'Meal ID must be an integer.',
            'meals.*.meal_id.exists' => 'The selected meal does not exist.',
            'meals.*.quantity.required' => 'Quantity is required for each meal.',
            'meals.*.quantity.integer' => 'Quantity must be an integer.',
            'meals.*.quantity.min' => 'Quantity must be at least 1.',
        ];
    }
}
