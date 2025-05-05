<?php

namespace App\Http\Requests\Carrier;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCarrierRequest extends FormRequest
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
        $ufs = [
            'AC', 'AL', 'AP', 'AM', 'BA', 'CE', 'DF', 'ES', 'GO', 'MA',
            'MT', 'MS', 'MG', 'PA', 'PB', 'PR', 'PE', 'PI', 'RJ', 'RN',
            'RS', 'RO', 'RR', 'SC', 'SP', 'SE', 'TO',
        ];
        $tractions = ['4x2', '6x2', '6x4', '8x2'];

        return [
            'plate' => [
                'required',
                'string',
                'size:7',
                'regex:/^[A-Z]{3}[0-9][A-Z][0-9]{2}$/i',
                Rule::unique('transport.carriers', 'plate'),
            ],
            'renavam' => ['required', 'string', 'max:255', 'regex:/^[0-9]+$/'],
            'chassi' => ['required', 'string', 'max:255'],
            'model' => ['required', 'string', 'max:255'],
            'manufacturer' => ['required', 'string', 'max:255'],
            'manufacture_year' => ['required', 'integer', 'min:1900', "max:{$currentYear}"],

            'licensing_uf' => ['required', 'string', 'size:2', Rule::in($ufs)],
            'vehicle_type' => ['required', 'string', 'max:255'],
            'body_type' => ['required', 'string', 'max:255'],
            'plank_length' => ['required', 'numeric', 'min:0'],
            'tare' => ['required', 'numeric', 'min:0'],
            'pbtc' => ['required', 'numeric', 'min:0'],
            'axles' => ['required', 'integer', 'min:1'],
            'tires_per_axle' => ['required', 'integer', 'min:1'],
            'traction' => ['required', 'string', Rule::in($tractions)],
            'rntrc' => ['required', 'string', 'max:255'],

            'documents' => ['nullable', 'array'],
            'documents.*' => ['file', 'mimes:pdf,jpeg,png', 'max:5120'],
            'vehicle_photos' => ['nullable', 'array'],
            'vehicle_photos.*' => ['image', 'mimes:jpeg,png,webp', 'max:5120'],
            'obs' => ['nullable', 'string'],
        ];
    }
}
