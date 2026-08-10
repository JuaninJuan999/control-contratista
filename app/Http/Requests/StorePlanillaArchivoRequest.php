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
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'archivo' => 'archivo de seguridad social',
        ];
    }

    public function messages(): array
    {
        return [
            'archivo.required' => 'Debe seleccionar un archivo de seguridad social.',
            'archivo.mimes' => 'El archivo debe ser PDF o Excel (.xlsx, .xls).',
        ];
    }
}
