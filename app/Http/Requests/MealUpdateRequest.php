<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MealUpdateRequest extends FormRequest
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
            'meal_price' => 'required|numeric|min:0',
            'min_qty' => 'required|integer|min:1',
            'max_qty' => 'required|integer|gte:min_qty',
        ];
    }

    /**
     * Custom messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'meal_price.required' => 'Meal\'s price is required!',
            'meal_price.numeric' => 'Meal\'s price must be a numeric value!',
            'meal_price.min' => 'Meal\'s price must be at least 0!',
            'min_qty.required' => 'Meal\'s minimum quantity for order is required!',
            'min_qty.integer' => 'Minimum quantity must be an integer!',
            'min_qty.min' => 'Minimum quantity must be at least 1!',
            'max_qty.required' => 'Meal\'s maximum quantity for order is required!',
            'max_qty.integer' => 'Maximum quantity must be an integer!',
            'max_qty.gte' => 'Maximum quantity must be greater than or equal to the minimum quantity!',
        ];
    }
}
