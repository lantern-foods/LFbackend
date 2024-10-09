<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateVehicleRequest extends FormRequest
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
            'license_plate' => 'required|string|max:10|unique:vehicles,license_plate,' . $this->vehicle, // Ensure license plate is unique and limited to 10 characters
            'make' => 'required|string|max:50',
            'model' => 'required|string|max:50',
        ];
    }

    /**
     * Custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'license_plate.required' => 'The vehicle\'s license plate is required.',
            'license_plate.string' => 'The license plate must be a valid string.',
            'license_plate.max' => 'The license plate cannot exceed 10 characters.',
            'license_plate.unique' => 'This license plate is already in use by another vehicle.',

            'make.required' => 'The vehicle\'s make is required.',
            'make.string' => 'The make must be a valid string.',
            'make.max' => 'The make cannot exceed 50 characters.',

            'model.required' => 'The vehicle\'s model is required.',
            'model.string' => 'The model must be a valid string.',
            'model.max' => 'The model cannot exceed 50 characters.',
        ];
    }
}
