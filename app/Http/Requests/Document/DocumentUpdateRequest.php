<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class DocumentUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'emission_date' => [
                'required',
                'date_format:Y-m-d',
                'before_or_equal:today',
            ],
            'type' => [
                'required',
                'string',
                'exists:pgsql.hr.document_type,key',
            ],
            'number' => [
                'required',
                'string',
                'min:1',
                'max:255',
            ],
        ];
    }
}
