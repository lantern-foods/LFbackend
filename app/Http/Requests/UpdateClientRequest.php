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
            'full_name' => 'required',
            'phone_number' => 'required',
            'email_address' => 'required',
            // 'whatsapp_number' => 'required',
        ];
    }

    public function messages(): array
    {
        return [
            'full_name.required' => 'Client\'s full name is required!',
            'email_address.required' => 'Client\'s email address is required!',
            'phone_number.required' => 'Client\'s phone number is required!',
            // 'whatsapp_number' => 'Client\'s whatsapp phone number is required!',
        ];
    }
}
