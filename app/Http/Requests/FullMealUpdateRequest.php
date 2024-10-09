<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FullMealUpdateRequest extends FormRequest
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
            'meal_name' => 'required',
            'meal_price' => 'required',
            'min_qty' => 'required',
            'max_qty' => 'required',
            'meal_type' => 'required',
            'prep_time' => 'required',
            'meal_desc' => 'required',
            'ingredients' => 'required',
            'serving_advice' => 'required',
        ];
    }

    /**
     * Custom messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'meal_name.required' => 'Meal\'s name is required!',
            'meal_price.required' => 'Meal\'s price is required!',
            'min_qty.required' => 'Meal\'s minimum quantity for order is required!',
            'max_qty.required' => 'Meal\'s maximum quantity for order is required!',
            'meal_type.required' => 'Meal\'s type is required!',
            'prep_time.required' => 'Meal\'s preparation time is required!',
            'meal_desc.required' => 'Meal\'s description is required!',
            'ingredients.required' => 'Meal\'s ingredients are required!',
            'serving_advice.required' => 'Meal\'s serving advice is required!',
        ];
    }
}
