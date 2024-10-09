<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DriverRequest extends FormRequest
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
            'id_number' => 'required|unique:drivers,id_number|digits_between:6,12',
            'phone_number' => 'required|unique:drivers,phone_number|regex:/^(\+\d{1,3})?\d{7,14}$/',
            'email' => 'required|email|unique:drivers,email|max:255',
            'date_of_birth' => 'required|date',
            'gender' => 'required|in:male,female,other', // Assuming gender field accepts these values
        ];
    }

    /**
     * Custom messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'driver_name.required' => 'Driver\'s name is required',
            'id_number.required' => 'Driver\'s ID number is required',
            'id_number.unique' => 'This ID number is already registered',
            'id_number.digits_between' => 'ID number must be between 6 and 12 digits',
            'phone_number.required' => 'Driver\'s phone number is required',
            'phone_number.unique' => 'This phone number is already registered',
            'phone_number.regex' => 'Please provide a valid phone number',
            'email.required' => 'Driver\'s email is required',
            'email.email' => 'Please provide a valid email address',
            'email.unique' => 'This email is already registered',
            'date_of_birth.required' => 'Driver\'s date of birth is required',
            'date_of_birth.date' => 'Please provide a valid date of birth',
            'gender.required' => 'Driver\'s gender is required',
            'gender.in' => 'Please select a valid gender (male, female, or other)',
        ];
    }
}
