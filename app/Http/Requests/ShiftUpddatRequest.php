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
            'end_time' => 'required|date_format:H:i|after:start_time',
            'shift_date' => 'required|date',
        ];
    }

    /**
     * Custom error messages for validator rules.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'end_time.required' => 'An end time is required.',
            'end_time.date_format' => 'End time must be in the format HH:mm.',
            'end_time.after' => 'End time must be after the start time.',
            'shift_date.required' => 'A date is required.',
            'shift_date.date' => 'The shift date must be a valid date.',
        ];
    }
}
