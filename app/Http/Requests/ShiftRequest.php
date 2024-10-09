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
            'cook_id' => 'required|integer|exists:cooks,id',
            'estimated_revenue' => 'required|numeric|min:0',
            'start_time' => 'required|date_format:H:i',
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
            'cook_id.required' => 'A cook ID is required.',
            'cook_id.integer' => 'Cook ID must be an integer.',
            'cook_id.exists' => 'The selected cook does not exist.',
            'estimated_revenue.required' => 'Estimated revenue is required.',
            'estimated_revenue.numeric' => 'Estimated revenue must be a numeric value.',
            'estimated_revenue.min' => 'Estimated revenue cannot be less than 0.',
            'start_time.required' => 'A start time is required.',
            'start_time.date_format' => 'Start time must be in the format HH:mm.',
            'end_time.required' => 'An end time is required.',
            'end_time.date_format' => 'End time must be in the format HH:mm.',
            'end_time.after' => 'End time must be after the start time.',
            'shift_date.required' => 'A shift date is required.',
            'shift_date.date' => 'Shift date must be a valid date.',
        ];
    }
}
