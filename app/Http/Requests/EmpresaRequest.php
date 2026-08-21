<?php

namespace App\Http\Requests;

use App\Models\Empresa;
use App\Support\EmpresaTipo;
use App\Support\PlanillaTipo;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

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
            'planilla' => [
                Rule::requiredIf(fn () => $this->input('tipo_empresa') === EmpresaTipo::INTERNA),
                'nullable',
                'string',
                Rule::in($this->planillasPermitidas()),
            ],
            'tipo_empresa' => ['required', 'string', Rule::in(EmpresaTipo::valores())],
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
            'tipo_empresa' => 'tipo de empresa',
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
        $tipoEmpresa = $this->input('tipo_empresa');

        $tipoEmpresa = is_string($tipoEmpresa)
            ? (trim($tipoEmpresa) === '' ? null : strtoupper(trim($tipoEmpresa)))
            : $tipoEmpresa;

        // Una empresa externa no maneja NIT, planilla ni contacto: se descartan por
        // si llegan valores residuales al reclasificar una empresa que era interna.
        $esExterna = $tipoEmpresa === EmpresaTipo::EXTERNA;

        $this->merge([
            'nombre' => is_string($this->input('nombre')) ? trim($this->input('nombre')) : $this->input('nombre'),
            'nit' => $esExterna ? null : (is_string($nit) ? (trim($nit) === '' ? null : preg_replace('/\s+/', '', trim($nit))) : $nit),
            'telefono' => $esExterna ? null : (is_string($telefono) ? (trim($telefono) === '' ? null : trim($telefono)) : $telefono),
            'correos' => $esExterna || $correos === [] ? null : $correos,
            'limite' => ! $esExterna && $this->filled('limite') ? $this->input('limite') : null,
            'planilla' => $esExterna ? null : (is_string($planilla) ? (trim($planilla) === '' ? null : strtoupper(trim($planilla))) : $planilla),
            'tipo_empresa' => $tipoEmpresa,
        ]);
    }

    /** @return list<string> */
    protected function planillasPermitidas(): array
    {
        $valores = PlanillaTipo::valores();

        $empresa = $this->route('empresa');
        if ($empresa instanceof Empresa && is_string($empresa->planilla) && $empresa->planilla !== '') {
            $valores[] = strtoupper($empresa->planilla);
        }

        return array_values(array_unique($valores));
    }

    protected function validarPlanillaEmpresaEnValidator(Validator $validator): void
    {
        if ($this->input('tipo_empresa') === EmpresaTipo::INTERNA
            && $this->input('planilla') === PlanillaTipo::DEPENDIENTE
            && ! $this->filled('limite')) {
            $validator->errors()->add(
                'limite',
                'La fecha límite es obligatoria cuando la planilla es dependiente.'
            );
        }
    }
}
