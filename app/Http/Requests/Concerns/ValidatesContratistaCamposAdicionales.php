<?php

namespace App\Http\Requests\Concerns;

use App\Support\LicenciaConduccionCategorias;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

trait ValidatesContratistaCamposAdicionales
{
    /**
     * @return array<string, mixed>
     */
    protected function camposAdicionalesRules(string $prefix = ''): array
    {
        $p = $prefix === '' ? '' : $prefix.'.';

        return [
            "{$p}fecha_nacimiento" => ['nullable', 'date', 'before:today'],
            "{$p}cargo" => ['nullable', 'string', 'max:255'],
            "{$p}manipulador_alimentos" => ['required', 'boolean'],
            "{$p}manipulador_vigencia" => ['nullable', 'date'],
            "{$p}manipulador_archivo" => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            "{$p}licencia_conduccion" => ['required', 'boolean'],
            "{$p}licencia_archivo" => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            "{$p}licencia_categoria" => ['nullable', 'array'],
            "{$p}licencia_categoria.*" => ['string', Rule::in(array_keys(LicenciaConduccionCategorias::OPCIONES))],
            "{$p}licencia_vencimiento" => ['nullable', 'date'],
            "{$p}cedula_archivo" => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function camposAdicionalesAttributes(string $prefix = '', string $etiqueta = ''): array
    {
        $p = $prefix === '' ? '' : $prefix.'.';
        $pref = $etiqueta !== '' ? "{$etiqueta} — " : '';

        return [
            "{$p}fecha_nacimiento" => $pref.'fecha de nacimiento',
            "{$p}cargo" => $pref.'cargo',
            "{$p}manipulador_alimentos" => $pref.'manipulador de alimentos',
            "{$p}manipulador_vigencia" => $pref.'vigencia del manipulador de alimentos',
            "{$p}manipulador_archivo" => $pref.'documento del manipulador de alimentos',
            "{$p}licencia_conduccion" => $pref.'licencia de conducción',
            "{$p}licencia_archivo" => $pref.'archivo de licencia de conducción',
            "{$p}licencia_categoria" => $pref.'categoría de licencia',
            "{$p}licencia_categoria.*" => $pref.'categoría de licencia',
            "{$p}licencia_vencimiento" => $pref.'fecha de vencimiento de licencia',
            "{$p}cedula_archivo" => $pref.'cédula de la persona',
        ];
    }

    /**
     * @param  array<string, mixed>  $datos
     */
    protected function prepararCamposAdicionales(array &$datos): void
    {
        foreach (['manipulador_alimentos', 'licencia_conduccion'] as $booleano) {
            if (array_key_exists($booleano, $datos)) {
                $datos[$booleano] = filter_var($datos[$booleano], FILTER_VALIDATE_BOOLEAN);
            }
        }

        if (array_key_exists('cargo', $datos) && is_string($datos['cargo'])) {
            $datos['cargo'] = trim($datos['cargo']) === '' ? null : trim($datos['cargo']);
        }

        if (array_key_exists('licencia_categoria', $datos)) {
            $categorias = $datos['licencia_categoria'];
            if (! is_array($categorias)) {
                $categorias = ($categorias === '' || $categorias === null) ? [] : [$categorias];
            }
            $categorias = array_values(array_unique(array_filter($categorias, fn ($v) => is_string($v) && $v !== '')));
            $datos['licencia_categoria'] = $categorias === [] ? null : $categorias;
        }

        if (($datos['manipulador_alimentos'] ?? false) === false) {
            $datos['manipulador_vigencia'] = null;
        }

        if (($datos['licencia_conduccion'] ?? false) === false) {
            $datos['licencia_categoria'] = null;
            $datos['licencia_vencimiento'] = null;
        }
    }

    protected function validarCamposAdicionalesEnValidator(Validator $validator, string $prefix = '', ?object $contratistaExistente = null): void
    {
        $validator->after(function (Validator $validator) use ($prefix, $contratistaExistente): void {
            $manipulador = (bool) $this->input($prefix === '' ? 'manipulador_alimentos' : "{$prefix}.manipulador_alimentos");
            $licencia = (bool) $this->input($prefix === '' ? 'licencia_conduccion' : "{$prefix}.licencia_conduccion");

            $campoManipulador = fn (string $name) => $prefix === '' ? $name : "{$prefix}.{$name}";

            if ($manipulador && ! $this->filled($campoManipulador('manipulador_vigencia'))) {
                $validator->errors()->add(
                    $campoManipulador('manipulador_vigencia'),
                    'La vigencia del manipulador de alimentos es obligatoria cuando responde Sí.'
                );
            }

            if ($manipulador
                && ! $this->hasFile($campoManipulador('manipulador_archivo'))
                && empty($contratistaExistente?->manipulador_archivo)) {
                $validator->errors()->add(
                    $campoManipulador('manipulador_archivo'),
                    'Debe adjuntar el documento del manipulador de alimentos cuando responde Sí.'
                );
            }

            if (! $licencia) {
                return;
            }

            $campo = fn (string $name) => $prefix === '' ? $name : "{$prefix}.{$name}";

            if (! $this->filled($campo('licencia_categoria'))) {
                $validator->errors()->add($campo('licencia_categoria'), 'La categoría de licencia es obligatoria cuando tiene licencia de conducción.');
            }

            if (! $this->filled($campo('licencia_vencimiento'))) {
                $validator->errors()->add($campo('licencia_vencimiento'), 'La fecha de vencimiento de la licencia es obligatoria.');
            }

            if (! $this->hasFile($campo('licencia_archivo')) && empty($contratistaExistente?->licencia_archivo)) {
                $validator->errors()->add($campo('licencia_archivo'), 'Debe adjuntar el documento de la licencia de conducción.');
            }

            if (! $this->hasFile($campo('cedula_archivo')) && empty($contratistaExistente?->cedula_archivo)) {
                $validator->errors()->add($campo('cedula_archivo'), 'Debe adjuntar la cédula de la persona.');
            }
        });
    }

    /**
     * @return list<string>
     */
    protected function camposAdicionalesPersistibles(): array
    {
        return [
            'fecha_nacimiento',
            'cargo',
            'manipulador_alimentos',
            'manipulador_vigencia',
            'licencia_conduccion',
            'licencia_categoria',
            'licencia_vencimiento',
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function archivosAdicionalesMap(): array
    {
        return [
            'manipulador_archivo' => 'manipulador_archivo',
            'licencia_archivo' => 'licencia_archivo',
            'cedula_archivo' => 'cedula_archivo',
        ];
    }
}
