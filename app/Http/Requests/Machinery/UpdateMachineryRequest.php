<?php

namespace App\Http\Requests\Machinery;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMachineryRequest extends FormRequest
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
        $currentYear = date('Y');

        return [
            'name' => ['required', 'string', 'max:255'],
            'model' => ['required', 'string', 'max:255'],
            'plate' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', 'max:255'],
            'manufacturer' => ['required', 'string', 'max:255'],
            'manufacturer_date' => ['required', 'date', "before_or_equal:{$currentYear}-12-31"],

            'weight' => ['nullable', 'numeric', 'min:0'],
            'length' => ['nullable', 'numeric', 'min:0'],
            'width' => ['nullable', 'numeric', 'min:0'],
            'height' => ['nullable', 'numeric', 'min:0'],

            'axles' => ['nullable', 'integer', 'min:1'],
            'tire_config' => ['nullable', 'string', 'max:255'],

            'pictures' => ['nullable', 'array'],
            'pictures.*' => ['uuid', 'exists:pgsql.file.file,uuid'],

            'obs' => ['nullable', 'string'],
        ];
    }
}
