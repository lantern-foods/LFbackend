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
            'meal_price' => 'required',
            'min_qty' => 'required',
            'max_qty' => 'required',
        ];
    }

    /*
     *
     * Rules messages 
     */
    public function messages():array
    {
        return [
            'meal_price.required' => 'Meal\'s price is required!',
            'min_qty.required' => 'Meal\'s minimum quantity for order is required!',
            'max_qty.required' => 'Meal\'s maximum quantity for order is required!',
            
        ];
    }
}
