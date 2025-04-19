<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class UserStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'surname' => 'required|string|max:255',
            'language' => 'required|string|max:10',
            'number' => 'regex:/^\d{13}$/',
            'email' => 'required|email|unique:pgsql.hr.user,email|max:255',
            'password' => [
                'bail',
                'required',
                'string',
                'min:8',
                'regex:/^(?=.*[A-Za-z])(?=.*\d).+$/',
            ],
            'password_confirm' => 'required|same:password',
            'terms_and_privacy_agreement' => 'required|accepted',
            'remember' => 'nullable|string',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => ucwords($this->input('name')),
            'surname' => ucwords($this->input('surname')),
        ]);
    }
}
