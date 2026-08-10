<?php

namespace App\Http\Requests;

use App\Support\EmpresaTipo;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEmpresaTipoRequest extends FormRequest
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
            'tipo_empresa' => ['nullable', 'string', Rule::in(EmpresaTipo::valores())],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'tipo_empresa' => 'tipo de empresa',
        ];
    }

    protected function prepareForValidation(): void
    {
        $tipo = $this->input('tipo_empresa');

        $this->merge([
            'tipo_empresa' => is_string($tipo) && trim($tipo) !== ''
                ? strtoupper(trim($tipo))
                : null,
        ]);
    }
}
