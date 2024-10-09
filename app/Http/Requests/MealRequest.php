<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MealRequest extends FormRequest
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
            'cook_id' => 'required',
            'meal_name' => 'required|string|max:255',
            'meal_price' => 'required|numeric|min:0',
            'min_qty' => 'required|integer|min:1',
            'max_qty' => 'required|integer|gte:min_qty',
            'meal_type' => 'required|string|max:100',
            'prep_time' => 'required|integer|min:0', // Assuming prep time is in minutes
            'meal_desc' => 'required|string',
            'ingredients' => 'required|string',
            'serving_advice' => 'required|string',
        ];
    }

    /**
     * Custom messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'cook_id.required' => 'Cook\'s ID is required!',
            'meal_name.required' => 'Meal\'s name is required!',
            'meal_name.max' => 'Meal\'s name should not exceed 255 characters!',
            'meal_price.required' => 'Meal\'s price is required!',
            'meal_price.numeric' => 'Meal\'s price must be a number!',
            'meal_price.min' => 'Meal\'s price must be at least 0!',
            'min_qty.required' => 'Minimum quantity for the meal is required!',
            'min_qty.integer' => 'Minimum quantity must be an integer!',
            'min_qty.min' => 'Minimum quantity must be at least 1!',
            'max_qty.required' => 'Maximum quantity for the meal is required!',
            'max_qty.integer' => 'Maximum quantity must be an integer!',
            'max_qty.gte' => 'Maximum quantity must be greater than or equal to the minimum quantity!',
            'meal_type.required' => 'Meal type is required!',
            'meal_type.max' => 'Meal type should not exceed 100 characters!',
            'prep_time.required' => 'Preparation time is required!',
            'prep_time.integer' => 'Preparation time must be an integer!',
            'prep_time.min' => 'Preparation time must be at least 0 minutes!',
            'meal_desc.required' => 'Meal description is required!',
            'ingredients.required' => 'Ingredients are required!',
            'serving_advice.required' => 'Serving advice is required!',
        ];
    }
}
