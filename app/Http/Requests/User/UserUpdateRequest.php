<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class UserUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|min:1|max:255',
            'surname' => 'required|string|min:1|max:255',
            'language' => 'nuable|string|max:10',
            'pix_key' => 'nullable|string|max:255',
        ];
    }
}
