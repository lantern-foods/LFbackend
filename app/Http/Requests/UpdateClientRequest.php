<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateClientRequest extends FormRequest
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
            'phone_number' => 'required|string|unique:clients,phone_number,' . $this->client, // Assuming $this->client refers to the current client's ID
            'email_address' => 'required|email|unique:clients,email_address,' . $this->client,
            // 'whatsapp_number' => 'nullable|string|unique:clients,whatsapp_number,' . $this->client,
        ];
    }

    /**
     * Custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'full_name.required' => 'Client\'s full name is required!',
            'phone_number.required' => 'Client\'s phone number is required!',
            'phone_number.unique' => 'This phone number is already associated with another client.',
            'email_address.required' => 'Client\'s email address is required!',
            'email_address.email' => 'Please provide a valid email address!',
            'email_address.unique' => 'This email address is already associated with another client.',
            // 'whatsapp_number.unique' => 'This WhatsApp number is already in use!',
        ];
    }
}
