<?php

namespace App\Http\Requests\User;

use App\Rules\DocumentBelongsTo;
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
            'documents' => ['nullable', 'array', 'max:2'],
            'documents.*.id' => ['nullable', 'integer', 'exists:pgsql.hr.document,id'],
            'documents.*.emission_date' => [
                'required',
                'date_format:Y-m-d',
                'before_or_equal:today',
            ],
            'documents.*.document_type' => [
                'required',
                'string',
                'exists:pgsql.hr.document_type,key',
            ],
            'documents.*.number' => [
                'required',
                'string',
                'min:1',
                'max:255',
                new DocumentBelongsTo(),
            ],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'documents' => array_map(function ($document) {
                return [
                    ...$document,
                    'number' => preg_replace('/[^a-zA-Z0-9]/', '', $document['number'] ?? ''),
                ];
            }, $this->input('documents', [])),
        ]);
    }

    protected function passedValidation(): void
    {
        $this->replace([
            'name' => ucwords(strtolower($this->input('name'))),
            'surname' => ucwords(strtolower($this->input('surname'))),
            'documents' => array_map(function ($document) {
                return [
                    ...$document,
                    'number' => preg_replace('/[^a-zA-Z0-9]/', '', $document['number'] ?? ''),
                ];
            }, $this->input('documents', [])), ]);
    }
}
