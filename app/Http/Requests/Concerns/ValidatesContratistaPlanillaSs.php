<?php

namespace App\Http\Requests\Concerns;

use App\Support\PlanillaTipo;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

trait ValidatesContratistaPlanillaSs
{
    /**
     * @return array<string, mixed>
     */
    protected function planillaSsRules(): array
    {
        return [
            'tipo_planilla' => ['required', 'string', Rule::in(PlanillaTipo::valores())],
            'limite' => ['nullable', 'date'],
            'planilla_ss_archivo' => ['nullable', 'file', 'mimes:pdf,xlsx,xls', 'max:10240'],
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function planillaSsAttributes(): array
    {
        return [
            'tipo_planilla' => 'tipo de planilla',
            'limite' => 'fecha límite de seguridad social',
            'planilla_ss_archivo' => 'planilla de seguridad social',
        ];
    }

    protected function preparePlanillaSsForValidation(): void
    {
        $tipo = $this->input('tipo_planilla');
        $limite = $this->input('limite');

        $this->merge([
            'tipo_planilla' => is_string($tipo) ? strtoupper(trim($tipo)) : $tipo,
            'limite' => $this->filled('limite') ? $limite : null,
        ]);
    }

    protected function validarPlanillaSsEnValidator(Validator $validator): void
    {
        if ($this->input('tipo_planilla') !== PlanillaTipo::INDEPENDIENTE) {
            return;
        }

        if (! $this->filled('limite')) {
            $validator->errors()->add('limite', 'La fecha límite es obligatoria para planilla independiente.');
        }
    }
}
