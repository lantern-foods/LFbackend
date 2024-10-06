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
            'client_id' => 'required',
            'kitchen_name' => 'required|unique:cooks',
            'id_number' => 'required|unique:cooks',
            'mpesa_number' => 'required',
            'alt_phone_number' => 'required',
            'health_number' => 'required|unique:cooks',
            'health_expiry_date' => 'required',
            'physical_address' => 'required',
            'google_map_pin' => 'required',
            'shrt_desc' => 'required',
        ];
    }

    /**
     * Rules messages
     */
    public function messages(): array
    {
        return [
            'client_id.required' => 'Kindly NOTE you must be first a client for you to be eligible to be a cook',
            'kitchen_name.required' => 'Cook\'s kitchen name is required',
            'id_number.required' => 'Cook\'s id number is required',
            'mpesa_number.required' => 'Cook\'s mpesa number is required',
            'alt_phone_number.required' => 'Cook\'s alternate phone number is required',
            'health_number.required' => 'Cooks\'s Health certificate number is required',
            'health_expiry_date.required' => 'Cook\'s health cert expiry date is required',
            'physical_address.required' => 'Cooks\'s physical address is required',
            'shrt_desc.required' => 'Cooks\'s short description is required',
            'google_map_pin.required' => 'Cooks\'s Google map location is required',
        ];
    }
}
