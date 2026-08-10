<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePlanillaArchivoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->puedeEditar() ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'archivo' => ['required', 'file', 'mimes:pdf,xlsx,xls', 'max:10240'],
            'periodo_anio' => ['required', 'integer', 'min:2000', 'max:2100'],
            'periodo_mes' => ['required', 'integer', 'min:1', 'max:12'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'archivo' => 'archivo de seguridad social',
            'periodo_anio' => 'año de vigencia',
            'periodo_mes' => 'mes de vigencia',
        ];
    }

    public function messages(): array
    {
        return [
            'archivo.required' => 'Debe seleccionar un archivo de seguridad social.',
            'archivo.mimes' => 'El archivo debe ser PDF o Excel (.xlsx, .xls).',
            'periodo_anio.required' => 'Indique el año de vigencia de la planilla.',
            'periodo_mes.required' => 'Indique el mes de vigencia de la planilla.',
        ];
    }
}
