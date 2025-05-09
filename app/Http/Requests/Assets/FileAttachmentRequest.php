<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class FileAttachmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'file' => 'nullable|file|mimes:jpg,jpeg,png,gif,webp,svg|max:2048|required_without:files',
            'files' => 'nullable|array|required_without:file',
            'files.*' => 'file|mimes:jpg,jpeg,png,gif,webp,svg|max:2048',
            'description' => 'nullable|string|max:255',
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:50',
        ];
    }
}
