<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DriverImageRequest extends FormRequest
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
            'driverId' => 'required',
            'id_front' => 'required|mimes:jpeg,png',
            'id_back' => 'required|mimes:jpeg,png',
            'profile_pic' => 'required|mimes:jpeg,png'
        ];
    }

    /**
     * Rules messages
     */
    public function messages(): array
    {
        return [
            'driverId.required' => 'Sorry, you must be a registered delivery driver inorder to upload documents',
            'id_front.required' => 'Cook\'s ID front face image is required',
            'id_back.required' => 'Cook\'s ID back face image is requried',
            'profile pic.required' => 'Cooks\'s Passport photo / profile picture is required',
        ];
    }
}
