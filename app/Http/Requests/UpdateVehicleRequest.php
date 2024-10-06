<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateVehicleRequest extends FormRequest
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
            'license_plate' => 'required',
            'make' => 'required',
            'model' => 'required',
            
        ];
    }

    /**
     * 
     * Rules messages
     */
    public function messages():array
    {
        return  [
            'license_plate.required' => 'Vehicle\'s License plate is required',
            'make.required' => 'Vehicle\'s Make is required',
            'model.required' => 'Vehicle\'s Model is required',
        ];
    }
}
