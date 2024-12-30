<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name'     => 'required|string|max:255',
            'surname'  => 'required|string|max:255',
            'number'   => 'required|string|max:20|regex:/^[\+0-9\s\-]+$/',
            'email'    => 'nullable|email|unique:user,email,'.$this->id,
            'password' => 'required|string|min:8|confirmed',
        ];
    }
}
