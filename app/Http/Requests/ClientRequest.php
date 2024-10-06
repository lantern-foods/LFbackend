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
            'full_name' => 'required',
            'phone_number' => 'required|unique:clients',
            'email_address' => 'required|email|unique:clients',
            // 'whatsapp_number' => 'required|unique:clients',
        ];
    }

    /*
     * Rules messages
     */
    public function messages(): array
    {
        return [
            'full_name.required' => "Client's full name is required!",
            'email_address.required' => "Client's email address is required!",
            'phone_number.required' => "Client's phone number is required!",
            'email_address.email' => "Client's email address is invalid!",
            'phone_number.unique' => "Client's phone number already exists!",
            'email_address.unique' => "Client's email address already exists!",
            // 'whatsapp_number' => 'Client\'s whatsapp phone number is required!',
        ];
    }
}
