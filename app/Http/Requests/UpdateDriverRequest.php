<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDriverRequest extends FormRequest
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
            'driver_name' => 'required',
            'id_number' => 'required',
            'phone_number' => 'required',
            'email' => 'required',
            'date_of_birth' => 'required',
            'gender' =>'required'
        ];
    }

    /**
     * Rules messages
     */
    public function messages(): array
    {

        return  [
            'driver_name.required' => 'Drivers\'s name is required',
            'id_number.required' => 'Drivers\'s id number is required',
            'phone_number.reequired' => 'Driver\'s phone number is required',
            'email.required' => 'Driver\'s email is required',
            'date_of_birth.required' => 'Driver\'s date of birth is required',
            'gender.required' => 'Drivers\'s gender is required',
        ];
    }
}
