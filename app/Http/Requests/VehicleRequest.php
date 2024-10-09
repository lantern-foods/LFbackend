<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VehicleRequest extends FormRequest
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
            'license_plate' => 'required|string|unique:vehicles,license_plate|max:10', // License plate must be unique, string, and max length of 10
            'make' => 'required|string|max:50', // Make should be a string and max 50 characters
            'model' => 'required|string|max:50', // Model should be a string and max 50 characters
            'deliverycmpy_id' => 'required|integer|exists:delivery_companies,id', // Delivery company ID must exist in the delivery_companies table
        ];
    }

    /**
     * Custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'license_plate.required' => 'The vehicle\'s license plate is required.',
            'license_plate.string' => 'The vehicle\'s license plate must be a valid string.',
            'license_plate.unique' => 'This license plate is already in use.',
            'license_plate.max' => 'The license plate cannot exceed 10 characters.',

            'make.required' => 'The vehicle\'s make is required.',
            'make.string' => 'The make must be a valid string.',
            'make.max' => 'The vehicle make cannot exceed 50 characters.',

            'model.required' => 'The vehicle\'s model is required.',
            'model.string' => 'The model must be a valid string.',
            'model.max' => 'The vehicle model cannot exceed 50 characters.',

            'deliverycmpy_id.required' => 'A delivery company ID is required to create a vehicle.',
            'deliverycmpy_id.integer' => 'The delivery company ID must be a valid number.',
            'deliverycmpy_id.exists' => 'The delivery company must exist in the system.',
        ];
    }
}
