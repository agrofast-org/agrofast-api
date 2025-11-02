<?php

namespace App\Http\Requests\CashOut;

use Illuminate\Foundation\Http\FormRequest;

class StoreCashOutRequest extends FormRequest
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
        return [
            'amount' => 'required|numeric|min:1',
            'obs' => ['nullable', 'string'],
        ];
    }
}
