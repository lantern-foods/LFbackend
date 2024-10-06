<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VehicleRequest extends FormRequest
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
            'license_plate' => 'required|unique:vehicles',
            'make' => 'required',
            'model' => 'required',
            'deliverycmpy_id' => 'required'
        ];
    }

    /**
     * 
     * Rules messages
     */
    public function messages():array
    {
        return  [
            'deliverycmpy_id.required' => 'Kindly NOTE you must a company account for delivery to be able to create a vehicle!',
            'license_plate.required' => 'Vehicle\'s License plate is required',
            'make.required' => 'Vehicle\'s Make is required',
            'model.required' => 'Vehicle\'s Model is required',
        ];
    }
}
