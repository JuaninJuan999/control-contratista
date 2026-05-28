<?php

namespace App\Http\Requests;

use App\Models\Vehiculo;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateVehiculoRequest extends FormRequest
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
        /** @var Vehiculo $vehiculo */
        $vehiculo = $this->route('vehiculo');

        return [
            'placa' => [
                'required',
                'string',
                'max:16',
                Rule::unique('vehiculos', 'placa')->ignore($vehiculo->id),
            ],
            'soat_fin' => ['required', 'date'],
            'tecnomecanica_fin' => ['required', 'date'],
            'empresa_id' => ['required', 'integer', 'exists:empresas,id'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'placa' => 'placa del vehículo',
            'soat_fin' => 'fecha fin del SOAT',
            'tecnomecanica_fin' => 'fecha fin de la tecnomecánica',
            'empresa_id' => 'empresa',
        ];
    }

    protected function prepareForValidation(): void
    {
        $placa = $this->input('placa');

        $this->merge([
            'placa' => is_string($placa) ? strtoupper(preg_replace('/\s+/', '', trim($placa))) : $placa,
            'empresa_id' => $this->filled('empresa_id') ? (int) $this->input('empresa_id') : null,
        ]);
    }
}
