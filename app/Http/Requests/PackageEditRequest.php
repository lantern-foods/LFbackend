<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PackageEditRequest extends FormRequest
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
            'package_name' => 'required',
            'package_description' => 'required',
            'discount' => 'required',
        ];
    }

     /**
     * Rules messages
     */
    public function messages(): array
    {
        return [
            'package_name.required' => 'meal\'s Package name is required',
            'package_description.required' => 'meal\'s Package description is required',
            'discount.required' => 'meal\'s Package discount is required',
        ];
        
    }
}
