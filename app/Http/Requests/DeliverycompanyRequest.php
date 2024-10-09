<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DeliverycompanyRequest extends FormRequest
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
            'full_name' => 'required|string|max:255',
            'phone_number' => 'required|string|max:20|unique:delivery_companies',
            'email' => 'required|email|unique:delivery_companies',
            'company' => 'required|string|max:255|unique:delivery_companies',
        ];
    }

    /**
     * Custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'full_name.required' => 'The delivery company\'s full name is required.',
            'full_name.string' => 'The full name must be a valid string.',
            'phone_number.required' => 'The delivery company\'s phone number is required.',
            'phone_number.unique' => 'This phone number is already in use by another delivery company.',
            'phone_number.string' => 'The phone number must be a valid string.',
            'email.required' => 'The delivery company\'s email address is required.',
            'email.email' => 'Please provide a valid email address.',
            'email.unique' => 'This email is already registered by another delivery company.',
            'company.required' => 'The delivery company\'s name is required.',
            'company.unique' => 'This company name is already in use by another delivery company.',
            'company.string' => 'The company name must be a valid string.',
        ];
    }
}
