<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ShiftRequest extends FormRequest
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
            'estimated_revenue' => 'required',
            'start_time' => 'required',
            'end_time' => 'required',
            'shift_date' => 'required',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'cook_id.required' => 'A cook ID is required.',
            'estimated_revenue.required' => 'Estimated revenue is required.',
            'start_time.required' => 'A start time is required.',
            'end_time.required' => 'An end time is required.',
            'shift_date.required' => 'A date is required.',
        ];
    }
}
