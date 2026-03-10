<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class StudentRegistrationRequest extends FormRequest
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
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', 'regex:/^[a-zA-Z\s\.\-]+$/'],
            'student_id' => ['required', 'string', 'max:50', 'unique:users,student_id', 'regex:/^[a-zA-Z0-9\-]+$/'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
            'course' => ['required', 'string', 'max:100'],
            'assigned_location_id' => ['required'],
            'new_location_name' => ['required_if:assigned_location_id,other', 'nullable', 'string', 'max:255'],
            'new_location_address' => ['required_if:assigned_location_id,other', 'nullable', 'string', 'max:500'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Please enter your full name.',
            'name.regex' => 'Name can only contain letters, spaces, dots, and hyphens.',
            'student_id.required' => 'Please enter your student ID.',
            'student_id.unique' => 'This student ID is already registered.',
            'student_id.regex' => 'Student ID can only contain letters, numbers, and hyphens.',
            'email.required' => 'Please enter your email address.',
            'email.email' => 'Please enter a valid email address.',
            'email.unique' => 'This email is already registered.',
            'password.required' => 'Please enter a password.',
            'password.confirmed' => 'Password confirmation does not match.',
            'password.min' => 'Password must be at least 8 characters.',
            'course.required' => 'Please enter your course.',
            'assigned_location_id.required' => 'Please select your OJT training site.',
            'new_location_name.required_if' => 'Please provide the OJT site name.',
            'new_location_address.required_if' => 'Please provide the OJT site address.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'name' => 'full name',
            'student_id' => 'student ID',
            'email' => 'email address',
            'password' => 'password',
            'course' => 'course',
            'assigned_location_id' => 'OJT training site',
            'new_location_name' => 'OJT site name',
            'new_location_address' => 'OJT site address',
        ];
    }
}
