<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDriverRequest extends FormRequest
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
            'driver_name' => 'required|string|max:255',
            'id_number' => 'required|string|max:50|unique:drivers,id_number,' . $this->driver, // Unique except for current driver
            'phone_number' => 'required|string|max:20|unique:drivers,phone_number,' . $this->driver, // Unique except for current driver
            'email' => 'required|email|unique:drivers,email,' . $this->driver, // Unique except for current driver
            'date_of_birth' => 'required|date|before:today',
            'gender' => 'required|string|in:male,female,other',
        ];
    }

    /**
     * Custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'driver_name.required' => 'Driver\'s name is required!',
            'id_number.required' => 'Driver\'s ID number is required!',
            'id_number.unique' => 'This ID number is already in use by another driver!',
            'phone_number.required' => 'Driver\'s phone number is required!',
            'phone_number.unique' => 'This phone number is already in use by another driver!',
            'email.required' => 'Driver\'s email address is required!',
            'email.email' => 'Please provide a valid email address!',
            'email.unique' => 'This email is already in use by another driver!',
            'date_of_birth.required' => 'Driver\'s date of birth is required!',
            'date_of_birth.before' => 'Date of birth must be a valid date before today!',
            'gender.required' => 'Driver\'s gender is required!',
            'gender.in' => 'Gender must be male, female, or other!',
        ];
    }
}
