<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CookProfileEditRequest extends FormRequest
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
            'kitchen_name' => 'required|string|max:255',
            'mpesa_number' => 'required|string|max:20',
            'physical_address' => 'required|string|max:255',
            'google_map_pin' => 'required|string|max:255',
        ];
    }

    /**
     * Custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'kitchen_name.required' => 'The kitchen name is required.',
            'mpesa_number.required' => 'The Mpesa number is required.',
            'physical_address.required' => 'The physical address is required.',
            'google_map_pin.required' => 'The Google Map pin is required.',
        ];
    }
}
