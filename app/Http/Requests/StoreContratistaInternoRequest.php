<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\ValidatesContratistaCamposAdicionales;
use App\Http\Requests\Concerns\ValidatesContratistaPlanillaSs;
use App\Models\Empresa;
use App\Support\NumeroDocumento;
use App\Support\PlanillaTipo;
use App\Support\TiposDocumento;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreContratistaInternoRequest extends FormRequest
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
        return array_merge([
            'nombres_apellidos' => ['required', 'string', 'max:255'],
            'tipo_documento' => ['required', 'string', Rule::in(TiposDocumento::valores())],
            'numero_documento' => [
                'required',
                'string',
                'max:32',
                Rule::unique('contratistas_internos', 'numero_documento')->where(
                    fn ($query) => $query->where('tipo_documento', $this->input('tipo_documento'))
                ),
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
        $this->validarCamposAdicionalesEnValidator($validator);
        $validator->after(function (Validator $validator): void {
            $this->validarPlanillaSsEnValidator($validator);

            if ($this->input('tipo_planilla') === PlanillaTipo::INDEPENDIENTE && ! $this->hasFile('planilla_ss_archivo')) {
                $validator->errors()->add(
                    'planilla_ss_archivo',
                    'Debe adjuntar la planilla de seguridad social para planilla independiente.'
                );
            }
        });
    }

    protected function prepareForValidation(): void
    {
        $numero = $this->input('numero_documento');
        $tipoDocumento = $this->input('tipo_documento');
        $nombres = $this->input('nombres_apellidos');
        $arl = $this->input('arl');

        $empresaId = $this->filled('empresa_id') ? (int) $this->input('empresa_id') : null;
        $tipoPlanilla = $this->input('tipo_planilla');
        if (! is_string($tipoPlanilla) || ! in_array(strtoupper(trim($tipoPlanilla)), PlanillaTipo::valores(), true)) {
            $empresa = $empresaId ? Empresa::query()->find($empresaId) : null;
            $tipoPlanilla = $empresa?->planilla ?? PlanillaTipo::DEPENDIENTE;
        }

        $datos = [
            'numero_documento' => NumeroDocumento::normalizar(
                is_string($numero) ? $numero : null,
                is_string($tipoDocumento) ? $tipoDocumento : null
            ),
            'nombres_apellidos' => is_string($nombres) ? trim($nombres) : $nombres,
            'arl' => is_string($arl) ? trim($arl) : $arl,
            'empresa_id' => $empresaId,
            'tipo_planilla' => strtoupper((string) $tipoPlanilla),
            'limite' => strtoupper((string) $tipoPlanilla) === PlanillaTipo::INDEPENDIENTE && $this->filled('limite')
                ? $this->input('limite')
                : null,
            'manipulador_alimentos' => $this->boolean('manipulador_alimentos'),
            'licencia_conduccion' => $this->boolean('licencia_conduccion'),
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
