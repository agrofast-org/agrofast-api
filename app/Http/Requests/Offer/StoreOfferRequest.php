<?php

namespace App\Http\Requests\Offer;

use Illuminate\Foundation\Http\FormRequest;

class StoreOfferRequest extends FormRequest
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
            'request_uuid' => 'required|exists:pgsql.transport.request,uuid',
            'carrier_uuid' => 'required|exists:pgsql.transport.carrier,uuid',
            'price' => 'required|numeric|min:0',
            'message' => 'nullable|string|max:1000',
        ];
    }
}
