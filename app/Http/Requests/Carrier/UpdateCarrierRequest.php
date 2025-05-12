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
        $ufs = [
            'AC', 'AL', 'AP', 'AM', 'BA', 'CE', 'DF', 'ES', 'GO', 'MA',
            'MT', 'MS', 'MG', 'PA', 'PB', 'PR', 'PE', 'PI', 'RJ', 'RN',
            'RS', 'RO', 'RR', 'SC', 'SP', 'SE', 'TO',
        ];
        $tractions = ['4x2', '6x2', '6x4', '8x2'];

        return [
            'name' => ['required', 'string', 'max:255'],
            'plate' => [
                'required',
                'string',
                'size:7',
                Rule::unique('pgsql.transport.carrier', 'plate'),
            ],
            'renavam' => ['required', 'string', 'max:255', 'regex:/^[0-9]+$/'],
            'chassi' => ['required', 'string', 'max:255'],
            'model' => ['required', 'string', 'max:255'],
            'manufacturer' => ['required', 'string', 'max:255'],
            'manufacturer_date' => ['required', 'date', 'before_or_equal:today'],

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
            'documents.*' => ['uuid', 'exists:pgsql.file.file,uuid'],
            'pictures' => ['nullable', 'array'],
            'pictures.*' => ['uuid', 'exists:pgsql.file.file,uuid'],
            'obs' => ['nullable', 'string'],
        ];
    }
}
