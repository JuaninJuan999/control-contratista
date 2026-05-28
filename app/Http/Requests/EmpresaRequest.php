<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

abstract class EmpresaRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    protected function empresaFieldRules(): array
    {
        return [
            'telefono' => ['nullable', 'string', 'max:50'],
            'correos' => ['nullable', 'array'],
            'correos.*' => ['required', 'email', 'max:255'],
            'limite' => ['nullable', 'date'],
            'planilla' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function empresaFieldAttributes(): array
    {
        return [
            'telefono' => 'teléfono',
            'correos' => 'correos',
            'correos.*' => 'correo',
            'limite' => 'límite',
            'planilla' => 'planilla',
        ];
    }

    protected function prepareEmpresaFieldsForValidation(): void
    {
        $nit = $this->input('nit');
        $correos = $this->input('correos', []);

        if (is_array($correos)) {
            $correos = array_values(array_filter(array_map(
                fn ($correo) => is_string($correo) ? trim($correo) : '',
                $correos
            ), fn (string $correo) => $correo !== ''));
        } else {
            $correos = [];
        }

        $telefono = $this->input('telefono');
        $planilla = $this->input('planilla');

        $this->merge([
            'nombre' => is_string($this->input('nombre')) ? trim($this->input('nombre')) : $this->input('nombre'),
            'nit' => is_string($nit) ? (trim($nit) === '' ? null : preg_replace('/\s+/', '', trim($nit))) : $nit,
            'telefono' => is_string($telefono) ? (trim($telefono) === '' ? null : trim($telefono)) : $telefono,
            'correos' => $correos === [] ? null : $correos,
            'limite' => $this->filled('limite') ? $this->input('limite') : null,
            'planilla' => is_string($planilla) ? (trim($planilla) === '' ? null : trim($planilla)) : $planilla,
        ]);
    }
}
