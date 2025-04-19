<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class DocumentStoreRequest extends FormRequest
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

    public function messages(): array
    {
        return [
            'emission_date.required' => 'document_emission_date_required',
            'emission_date.date_format' => 'document_emission_date_format',
            'emission_date.before_or_equal' => 'document_emission_date_before_or_equal',
            'type.required' => 'document_type_required',
            'type.string' => 'document_type_string',
            'type.exists' => 'document_type_exists',
            'number.required' => 'document_number_required',
            'number.string' => 'document_number_string',
            'number.min' => 'document_number_min',
            'number.max' => 'document_number_max',
        ];
    }
}
