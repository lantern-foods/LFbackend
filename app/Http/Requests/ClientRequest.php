<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ClientRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Allow the request to proceed by default
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
            'phone_number' => 'required|string|unique:clients,phone_number|max:15',
            'email_address' => 'required|email|unique:clients,email_address|max:255',
            // 'whatsapp_number' => 'required|unique:clients,whatsapp_number|max:15',
        ];
    }

    /**
     * Custom error messages for validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'full_name.required' => "Client's full name is required!",
            'email_address.required' => "Client's email address is required!",
            'email_address.email' => "Client's email address is invalid!",
            'email_address.unique' => "Client's email address already exists!",
            'phone_number.required' => "Client's phone number is required!",
            'phone_number.unique' => "Client's phone number already exists!",
            // 'whatsapp_number.required' => "Client's WhatsApp phone number is required!",
        ];
    }
}
