<?php

namespace App\Http\Requests\Support;

use Illuminate\Foundation\Http\FormRequest;

class SupportStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $message = 'string|max:1000';
        $answer_to = 'nullable|exists:pgsql.chat.message,uuid';

        return [
            'message' => "required_without:messages|{$message}",
            'messages' => 'nullable|array',
            'messages.*.message' => "required|{$message}",
            'messages.*.answer_to' => $answer_to,
        ];
    }
}
