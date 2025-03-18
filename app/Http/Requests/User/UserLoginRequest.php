<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class UserLoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => 'required|email|exists:pgsql.hr.user|max:255',
            'password' => [
                'required',
                'string',
                'min:8',
                'regex:/^(?=.*[A-Za-z])(?=.*\d).+$/',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'email.email' => 'invalid_email',
            'email.exists' => 'user_not_found',
            'password.required' => 'password_required',
            'password.min' => 'password_length',
            'password.regex' => 'password_character',
        ];
    }
}
