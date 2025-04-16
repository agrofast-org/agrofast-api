<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
            'documents' => ['required', 'array', 'max:2'],
            'documents.*.id' => ['nullable', 'integer', 'exists:pgsql.hr.document,id'],
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

    public function messages(): array
    {
        return [
            'name.required' => 'name_required',
            'name.string' => 'name_string',
            'name.min' => 'name_min',
            'name.max' => 'name_max',

            'surname.required' => 'surname_required',
            'surname.string' => 'surname_string',
            'surname.min' => 'surname_min',
            'surname.max' => 'surname_max',

            'documents.required' => 'documents_required',
            'documents.array' => 'documents_array',
            'documents.min' => 'documents_min',

            'documents.*.number.required' => 'document_number_required_when_creating',
            'documents.*.type.required' => 'document_type_required_when_creating',
        ];
    }
}
