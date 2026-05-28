<?php

namespace App\Http\Requests;

use App\Models\Empresa;
use Illuminate\Validation\Rule;

class UpdateEmpresaRequest extends EmpresaRequest
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
        $empresa = $this->route('empresa');

        return array_merge([
            'nombre' => ['required', 'string', 'max:255'],
            'nit' => [
                'nullable',
                'string',
                'max:32',
                Rule::unique('empresas', 'nit')->ignore($empresa instanceof Empresa ? $empresa->id : $empresa),
            ],
        ], $this->empresaFieldRules());
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return array_merge([
            'nombre' => 'nombre o razón social',
            'nit' => 'NIT',
        ], $this->empresaFieldAttributes());
    }

    protected function prepareForValidation(): void
    {
        $this->prepareEmpresaFieldsForValidation();
    }
}
