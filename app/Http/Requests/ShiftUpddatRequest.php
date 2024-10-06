<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ShiftUpddatRequest extends FormRequest
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


            'end_time' => 'required',
            'shift_date' => 'required',
        ];
    }
    public function messages(): array
    {
        return [

            'end_time.required' => 'An end time is required.',
            'shift_date.required' => 'A date is required.',
        ];
    }
}
