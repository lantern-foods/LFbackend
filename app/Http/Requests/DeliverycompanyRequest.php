<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DeliverycompanyRequest extends FormRequest
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
            'phone_number' => 'required|unique:delivery_companies',
            'email' => 'required|email|unique:delivery_companies',
            'company' => 'required|unique:delivery_companies',
           
        ];
    }

    /**
     * Rules messages
     */
    public function messages(): array
    {

        return [
            'full_name.required' => 'Delivery companie\'s full name is required!',
            'email.required' => 'Delivery companie\'s email address is required!',
            'phone_number.required' => 'Delivery companie\'s phone number is required!',
            'company.required' => 'Delivery companie\'s company name is required!',
            
        ];
    }
}
