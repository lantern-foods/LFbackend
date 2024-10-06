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
            'cook_id' => 'required',
            'id_front' => 'required|mimes:jpeg,png,jpg',
            'id_back' => 'required|mimes:jpeg,png,jpg',
            'health_cert' => 'required|mimes:jpeg,png,pdf',
            'profile_pic' => 'required|mimes:jpeg,png,jpg'
        ];
    }

    /**
     * Rules messages
     */
    public function messages(): array
    {
        return [
            'cook_id.required' => 'Sorry, you must be a registered cook inorder to upload documents',
            'id_front.required' => 'Cook\'s ID front face image is required',
            'id_back.required' => 'Cook\'s ID back face image is requried',
            'health_cert.required' => 'Cook\'s health certificate document is reqiured',
            'profile pic.required' => 'Cooks\'s Passport photo / profile picture is required',
        ];
    }
}
