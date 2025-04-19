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
            'language' => 'required|string|max:10',
            'documents' => ['nullable', 'array', 'max:2'],
            'documents.*.id' => ['nullable', 'integer', 'exists:pgsql.hr.document,id'],
            'documents.*.emission_date' => [
                'required',
                'date_format:Y-m-d',
                'before_or_equal:today',
            ],
            'documents.*.type' => [
                'required',
                'string',
                'exists:pgsql.hr.document_type,key',
            ],
            'documents.*.number' => [
                'required',
                'string',
                'min:1',
                'max:255',
            ],
        ];
    }
}
