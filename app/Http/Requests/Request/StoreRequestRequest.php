<?php

namespace App\Http\Requests\Request;

use Illuminate\Foundation\Http\FormRequest;

class StoreRequestRequest extends FormRequest
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
            'origin_place_id' => 'required|string',
            'destination_place_id' => 'required|string',
            'machine_uuid' => 'required|uuid|exists:pgsql.transport.machinery,uuid',
            'desired_date' => 'nullable|date_format:Y-m-d',
        ];
    }
}
