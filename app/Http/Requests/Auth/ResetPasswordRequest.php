<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class ResetPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'token' => [
                'required',
                'string',
                'min:10',
                'max:255'
            ],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                'regex:/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/'
            ],
            'password' => [
                'required',
                'string',
                'min:8',
                'max:255',
                'confirmed',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/'
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'token.required' => 'Reset token is required',
            'token.min' => 'Invalid reset token',
            'email.required' => __('validation.email_required'),
            'email.email' => __('validation.email_email'),
            'email.max' => __('validation.email_max'),
            'email.regex' => __('validation.email_regex'),
            'password.required' => __('validation.password_required'),
            'password.min' => 'Password must be at least 8 characters',
            'password.max' => __('validation.password_max'),
            'password.confirmed' => __('validation.password_confirmed'),
            'password.regex' => 'Password must contain at least one uppercase, one lowercase, and one number',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422)
        );
    }

    protected function prepareForValidation(): void
    {
        // Ensure email is a string before processing
        $email = is_string($this->email) ? $this->email : '';

        $this->merge([
            'email' => strtolower(trim($email)),
        ]);
    }
}

