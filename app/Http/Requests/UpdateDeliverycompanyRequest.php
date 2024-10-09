<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDeliverycompanyRequest extends FormRequest
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
            'phone_number' => 'required|string|unique:delivery_companies,phone_number,' . $this->deliverycompany, // Ensure unique except for current record
            'email' => 'required|email|unique:delivery_companies,email,' . $this->deliverycompany, // Ensure unique except for current record
            'company' => 'required|string|max:255|unique:delivery_companies,company,' . $this->deliverycompany, // Ensure unique except for current record
        ];
    }

    /**
     * Custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'full_name.required' => 'Delivery company\'s full name is required!',
            'phone_number.required' => 'Delivery company\'s phone number is required!',
            'phone_number.unique' => 'This phone number is already in use by another company!',
            'email.required' => 'Delivery company\'s email address is required!',
            'email.email' => 'Please provide a valid email address!',
            'email.unique' => 'This email address is already in use by another company!',
            'company.required' => 'Delivery company\'s name is required!',
            'company.unique' => 'This company name is already in use by another company!',
        ];
    }
}
