<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
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
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $this->user, // Unique except for the current user
            'username' => 'required|string|unique:users,username,' . $this->user, // Unique except for the current user
        ];
    }

    /**
     * Custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'User\'s full name is required!',
            'name.string' => 'User\'s name must be a valid string!',
            'name.max' => 'User\'s name must not exceed 255 characters!',

            'email.required' => 'User\'s email is required!',
            'email.email' => 'Please provide a valid email address!',
            'email.unique' => 'This email is already in use by another user!',

            'username.required' => 'Username is required!',
            'username.string' => 'Username must be a valid string!',
            'username.unique' => 'This username is already in use by another user!',
        ];
    }
}
