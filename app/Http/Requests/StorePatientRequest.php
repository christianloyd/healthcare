<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Utils\ValidationHelper;

class StorePatientRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return in_array(auth()->user()->role, ['bhw', 'midwife']);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'first_name' => ValidationHelper::nameRules(2, 50),
            'last_name' => ValidationHelper::nameRules(2, 50),
            'age' => ValidationHelper::maternalAgeRules(),
            'occupation' => [
                'required',
                'string',
                'max:50',
                'regex:/^[a-zA-Z\s\.\-\/]+$/'
            ],
            'contact' => ValidationHelper::phoneNumberRules(),
            'emergency_contact' => ValidationHelper::phoneNumberRules(),
            'address' => [
                'required',
                'string',
                'max:255'
            ],
            'registration_date' => [
                'nullable',
                'date',
                'before_or_equal:today'
            ]
        ];
    }

    /**
     * Get custom error messages for validation rules.
     */
    public function messages(): array
    {
        return array_merge(
            ValidationHelper::nameMessages('first_name'),
            ValidationHelper::nameMessages('last_name'),
            ValidationHelper::phoneNumberMessages('contact'),
            ValidationHelper::phoneNumberMessages('emergency_contact'),
            [
                'age.required' => 'Age is required.',
                'age.integer' => 'Age must be a valid number.',
                'age.min' => 'Age must be at least 15 years.',
                'age.max' => 'Age cannot exceed 50 years.',

                'occupation.required' => 'Occupation is required.',
                'occupation.max' => 'Occupation cannot exceed 50 characters.',
                'occupation.regex' => 'Occupation should only contain letters, spaces, dots, hyphens, and forward slashes.',

                'address.required' => 'Address is required.',
                'address.max' => 'Address cannot exceed 255 characters.'
            ]
        );
    }
}
