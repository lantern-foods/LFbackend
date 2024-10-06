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
           'kitchen_name' => 'required',
           'mpesa_number' =>'required',
           'physical_address' => 'required',
           'google_map_pin' =>'required',
        ];
    }
    /**
     * Rules messages
     */

    public function messages():array
    {
        return [
            'kitchen_name.required' => 'Cook\'s Kitchen name is required',
            'mpesa_number.required' => 'Cook\'s Mpesa number is required',
            'physical_address.required' => 'Cook\'s physical address is required',
            'google_map_pin.required' => 'Cook\'s google map location is required',
        ];
    }
}
