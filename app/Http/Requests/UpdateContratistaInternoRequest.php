<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\ValidatesContratistaCamposAdicionales;
use App\Http\Requests\Concerns\ValidatesContratistaPlanillaSs;
use App\Models\ContratistaInterno;
use App\Support\PlanillaTipo;
use App\Support\TiposDocumento;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateContratistaInternoRequest extends FormRequest
{
    use ValidatesContratistaCamposAdicionales;
    use ValidatesContratistaPlanillaSs;

    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var ContratistaInterno $contratista */
        $contratista = $this->route('contratistas_interno');

        return array_merge([
            'nombres_apellidos' => ['required', 'string', 'max:255'],
            'tipo_documento' => ['required', 'string', Rule::in(TiposDocumento::valores())],
            'numero_documento' => [
                'required',
                'string',
                'max:32',
                Rule::unique('contratistas_internos', 'numero_documento')
                    ->where(fn ($query) => $query->where('tipo_documento', $this->input('tipo_documento')))
                    ->ignore($contratista->id),
            ],
            'empresa_id' => ['required', 'integer', 'exists:empresas,id'],
            'arl' => ['required', 'string', 'max:120'],
        ], $this->camposAdicionalesRules(), $this->planillaSsRules());
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
            'arl' => 'ARL',
        ], $this->camposAdicionalesAttributes(), $this->planillaSsAttributes());
    }

    public function withValidator(Validator $validator): void
    {
        /** @var ContratistaInterno $contratista */
        $contratista = $this->route('contratistas_interno');
        $this->validarCamposAdicionalesEnValidator($validator, '', $contratista);
        $validator->after(function (Validator $validator): void {
            $this->validarPlanillaSsEnValidator($validator);
        });
    }

    protected function prepareForValidation(): void
    {
        /** @var ContratistaInterno $contratista */
        $contratista = $this->route('contratistas_interno');

        $numero = $this->input('numero_documento');
        $nombres = $this->input('nombres_apellidos');
        $arl = $this->input('arl');

        $tipoPlanilla = $this->input('tipo_planilla', $contratista->tipo_planilla ?? PlanillaTipo::DEPENDIENTE);

        $datos = [
            'numero_documento' => is_string($numero) ? preg_replace('/\s+/', '', trim($numero)) : $numero,
            'nombres_apellidos' => is_string($nombres) ? trim($nombres) : $nombres,
            'arl' => is_string($arl) ? trim($arl) : $arl,
            'empresa_id' => $this->filled('empresa_id') ? (int) $this->input('empresa_id') : null,
            'tipo_planilla' => is_string($tipoPlanilla) ? strtoupper(trim($tipoPlanilla)) : PlanillaTipo::DEPENDIENTE,
            'limite' => strtoupper((string) $tipoPlanilla) === PlanillaTipo::INDEPENDIENTE && $this->filled('limite')
                ? $this->input('limite')
                : null,
            'manipulador_alimentos' => $this->has('manipulador_alimentos')
                ? $this->boolean('manipulador_alimentos')
                : (bool) $contratista->manipulador_alimentos,
            'licencia_conduccion' => $this->has('licencia_conduccion')
                ? $this->boolean('licencia_conduccion')
                : (bool) $contratista->licencia_conduccion,
            'licencia_categoria' => $this->input('licencia_categoria'),
            'licencia_vencimientos' => $this->input('licencia_vencimientos'),
            'fecha_ultima_ir' => null,
            'vigencia_dias' => null,
        ];

        $this->preparePlanillaSsForValidation();
        $this->prepararCamposAdicionales($datos);
        $this->merge($datos);
    }
}
