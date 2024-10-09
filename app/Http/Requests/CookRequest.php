<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CookRequest extends FormRequest
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
            'client_id' => 'required|integer|exists:clients,id',
            'kitchen_name' => 'required|string|max:255|unique:cooks',
            'id_number' => 'required|string|max:20|unique:cooks',
            'mpesa_number' => 'required|string|max:20',
            'alt_phone_number' => 'required|string|max:20',
            'health_number' => 'required|string|max:50|unique:cooks',
            'health_expiry_date' => 'required|date|after:today',
            'physical_address' => 'required|string|max:255',
            'google_map_pin' => 'required|string|max:255',
            'shrt_desc' => 'required|string|max:500',
        ];
    }

    /**
     * Custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'client_id.required' => 'You must be a registered client to become a cook.',
            'client_id.exists' => 'Invalid client ID provided.',
            'kitchen_name.required' => 'The kitchen name is required.',
            'kitchen_name.unique' => 'This kitchen name is already in use.',
            'id_number.required' => 'The ID number is required.',
            'id_number.unique' => 'This ID number is already in use.',
            'mpesa_number.required' => 'The Mpesa number is required.',
            'alt_phone_number.required' => 'The alternate phone number is required.',
            'health_number.required' => 'The health certificate number is required.',
            'health_number.unique' => 'This health certificate number is already in use.',
            'health_expiry_date.required' => 'The health certificate expiry date is required.',
            'health_expiry_date.after' => 'The health certificate expiry date must be a future date.',
            'physical_address.required' => 'The physical address is required.',
            'google_map_pin.required' => 'The Google map pin is required.',
            'shrt_desc.required' => 'The short description is required.',
        ];
    }
}
