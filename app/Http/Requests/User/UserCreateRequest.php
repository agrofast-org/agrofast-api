<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserCreateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'             => 'required|string|max:255',
            'surname'          => 'required|string|max:255',
            'number'           => ['required', 'regex:/^\d{13}$/'],
            'email'            => 'required|email|max:255',
            'password'         => [
                'required',
                'string',
                'min:8',
                'regex:/^(?=.*[A-Za-z])(?=.*\d).+$/',
            ],
            'password_confirm' => 'required|same:password',
            'remember'         => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'             => 'name_required',
            'surname.required'          => 'surname_required',
            'number.required'           => 'number_required',
            'number.regex'              => 'invalid_number',
            'email.required'            => 'email_required',
            'email.email'               => 'invalid_email',
            'password.required'         => 'password_required',
            'password.min'              => 'password_length',
            'password.regex'            => 'password_character',
            'password_confirm.required' => 'password_confirm_required',
            'password_confirm.same'     => 'password_not_coincide',
        ];
    }
}
