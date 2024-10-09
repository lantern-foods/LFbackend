<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserRequest extends FormRequest
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
            'name' => 'required|string|max:255', // Name must be a valid string with a max length of 255
            'email' => 'required|email|unique:users,email,' . $this->user, // Email must be valid and unique in the users table
            'username' => 'required|string|max:100|unique:users,username,' . $this->user, // Username must be unique and limited to 100 characters
            'password' => 'required|string|min:8|confirmed', // Password must be at least 8 characters long and confirmed
        ];
    }

    /**
     * Custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'The user\'s full name is required.',
            'name.string' => 'The user\'s name must be a valid string.',
            'name.max' => 'The user\'s name cannot exceed 255 characters.',

            'email.required' => 'The user\'s email address is required.',
            'email.email' => 'The user\'s email address must be a valid email.',
            'email.unique' => 'This email address is already in use.',

            'username.required' => 'A username is required.',
            'username.string' => 'The username must be a valid string.',
            'username.max' => 'The username cannot exceed 100 characters.',
            'username.unique' => 'This username is already in use.',

            'password.required' => 'A password is required.',
            'password.string' => 'The password must be a valid string.',
            'password.min' => 'The password must be at least 8 characters long.',
            'password.confirmed' => 'The password confirmation does not match.',
        ];
    }
}
