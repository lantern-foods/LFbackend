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
            'name'=>'required',
            'email'=>'required|email',
            'username'=>'required',
            'password'=>'required'
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'=>'User\'s full name is required!',
            'email.required'=>'User\'s email is required!',
            'username.required'=>'Username is required!',
            'password.required'=>'Password is required!'
        ];
    }
}
