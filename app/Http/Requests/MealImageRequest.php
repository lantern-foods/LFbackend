<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MealImageRequest extends FormRequest
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
            'meal_id' => 'required',
            'image_url.*' => 'required|mimes:jpeg,png,jpg', // Ensure the uploaded files are images
        ];
    }

    /**
     * Custom messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'meal_id.required' => 'Meal\'s id is required',
            'image_url.required' => 'At least one meal image is required',
            'image_url.mimes' => 'Each meal image must be in jpeg, png, or jpg format',
        ];
    }
}
