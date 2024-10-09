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
            'driverId' => 'required|exists:drivers,id',
            'id_front' => 'required|mimes:jpeg,png,jpg|max:2048',  // Max 2MB file size
            'id_back' => 'required|mimes:jpeg,png,jpg|max:2048',   // Max 2MB file size
            'profile_pic' => 'required|mimes:jpeg,png,jpg|max:2048', // Max 2MB file size
        ];
    }

    /**
     * Custom messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'driverId.required' => 'You must be a registered delivery driver to upload documents.',
            'driverId.exists' => 'The provided driver ID does not exist.',
            'id_front.required' => 'The driver\'s ID front image is required.',
            'id_front.mimes' => 'The ID front image must be a file of type: jpeg, png, jpg.',
            'id_front.max' => 'The ID front image must not exceed 2MB in size.',
            'id_back.required' => 'The driver\'s ID back image is required.',
            'id_back.mimes' => 'The ID back image must be a file of type: jpeg, png, jpg.',
            'id_back.max' => 'The ID back image must not exceed 2MB in size.',
            'profile_pic.required' => 'The driver\'s profile picture is required.',
            'profile_pic.mimes' => 'The profile picture must be a file of type: jpeg, png, jpg.',
            'profile_pic.max' => 'The profile picture must not exceed 2MB in size.',
        ];
    }
}
