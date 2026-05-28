<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\ValidatesContratistaCamposAdicionales;
use App\Models\ContratistaExterno;
use Illuminate\Foundation\Http\FormRequest;
use App\Support\TiposDocumento;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateContratistaExternoRequest extends FormRequest
{
    use ValidatesContratistaCamposAdicionales;

    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var ContratistaExterno $contratista */
        $contratista = $this->route('contratistas_externo');

        return array_merge([
            'nombres_apellidos' => ['required', 'string', 'max:255'],
            'tipo_documento' => ['required', 'string', Rule::in(TiposDocumento::valores())],
            'numero_documento' => [
                'required',
                'string',
                'max:32',
                Rule::unique('contratistas_externos', 'numero_documento')
                    ->where(fn ($query) => $query->where('tipo_documento', $this->input('tipo_documento')))
                    ->ignore($contratista->id),
            ],
            'empresa_id' => ['required', 'integer', 'exists:empresas,id'],
            'fecha_ultima_ir' => ['required', 'date'],
            'vigencia_dias' => ['required', 'integer', 'min:1', 'max:3650'],
        ], $this->camposAdicionalesRules());
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return array_merge([
            'nombres_apellidos' => 'nombres y apellidos',
            'tipo_documento' => 'tipo de documento',
            'numero_documento' => 'documento',
            'empresa_id' => 'empresa',
            'fecha_ultima_ir' => 'fecha de última inducción/reinducción',
            'vigencia_dias' => 'vigencia',
        ], $this->camposAdicionalesAttributes());
    }

    public function withValidator(Validator $validator): void
    {
        /** @var ContratistaExterno $contratista */
        $contratista = $this->route('contratistas_externo');
        $this->validarCamposAdicionalesEnValidator($validator, '', $contratista);
    }

    protected function prepareForValidation(): void
    {
        $numero = $this->input('numero_documento');
        $nombres = $this->input('nombres_apellidos');

        $datos = [
            'numero_documento' => is_string($numero) ? preg_replace('/\s+/', '', trim($numero)) : $numero,
            'nombres_apellidos' => is_string($nombres) ? trim($nombres) : $nombres,
            'empresa_id' => $this->filled('empresa_id') ? (int) $this->input('empresa_id') : null,
            'manipulador_alimentos' => $this->boolean('manipulador_alimentos'),
            'licencia_conduccion' => $this->boolean('licencia_conduccion'),
        ];

        $this->prepararCamposAdicionales($datos);
        $this->merge($datos);
    }
}
