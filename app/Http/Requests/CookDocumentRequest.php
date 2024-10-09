<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CookDocumentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Always allow authorization for this request
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'cook_id' => 'required|exists:cooks,id', // Ensure the cook exists
            'id_front' => 'required|mimes:jpeg,png,jpg|max:2048', // Max size: 2MB
            'id_back' => 'required|mimes:jpeg,png,jpg|max:2048', // Max size: 2MB
            'health_cert' => 'required|mimes:jpeg,png,pdf|max:2048', // Allow PDF, Max size: 2MB
            'profile_pic' => 'required|mimes:jpeg,png,jpg|max:2048', // Max size: 2MB
        ];
    }

    /**
     * Custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'cook_id.required' => 'You must be a registered cook to upload documents.',
            'cook_id.exists' => 'The provided cook ID does not exist.',
            'id_front.required' => 'The front image of your ID is required.',
            'id_back.required' => 'The back image of your ID is required.',
            'health_cert.required' => 'Your health certificate is required.',
            'profile_pic.required' => 'A profile picture or passport photo is required.',
            'id_front.mimes' => 'The front ID must be a file of type: jpeg, png, jpg.',
            'id_back.mimes' => 'The back ID must be a file of type: jpeg, png, jpg.',
            'health_cert.mimes' => 'The health certificate must be a file of type: jpeg, png, or pdf.',
            'profile_pic.mimes' => 'The profile picture must be a file of type: jpeg, png, jpg.',
            'id_front.max' => 'The front ID image may not be larger than 2MB.',
            'id_back.max' => 'The back ID image may not be larger than 2MB.',
            'health_cert.max' => 'The health certificate may not be larger than 2MB.',
            'profile_pic.max' => 'The profile picture may not be larger than 2MB.',
        ];
    }
}
