<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\ValidatesContratistaCamposAdicionales;
use App\Models\ContratistaExterno;
use App\Models\ContratistaInterno;
use App\Models\Vehiculo;
use App\Support\TiposDocumento;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreEmpresaRequest extends EmpresaRequest
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
        return array_merge([
            'nombre' => ['required', 'string', 'max:255'],
            'nit' => ['nullable', 'string', 'max:32', 'unique:empresas,nit'],
            'personas' => ['nullable', 'array'],
            'personas.*.tipo_contratista' => ['required', 'string', Rule::in(['interno', 'externo'])],
            'personas.*.nombres_apellidos' => ['required', 'string', 'max:255'],
            'personas.*.tipo_documento' => ['required', 'string', Rule::in(TiposDocumento::valores())],
            'personas.*.numero_documento' => ['required', 'string', 'max:32'],
            'personas.*.fecha_ultima_ir' => ['nullable', 'date'],
            'personas.*.vigencia_dias' => ['nullable', 'integer', 'min:1', 'max:3650'],
            'personas.*.arl' => ['nullable', 'string', 'max:120'],
            'vehiculos' => ['nullable', 'array'],
            'vehiculos.*.placa' => ['required', 'string', 'max:16'],
            'vehiculos.*.soat_fin' => ['required', 'date'],
            'vehiculos.*.tecnomecanica_fin' => ['required', 'date'],
            'vehiculos.*.soat_archivo' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'vehiculos.*.tecnomecanica_archivo' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'vehiculos.*.tarjeta_propiedad_archivo' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'vehiculos.*.inspeccion_sanitaria' => ['nullable', 'boolean'],
            'vehiculos.*.inspeccion_sanitaria_fin' => ['nullable', 'date'],
            'vehiculos.*.inspeccion_sanitaria_archivo' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ], $this->camposAdicionalesRules('personas.*'), $this->empresaFieldRules());
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        $attrs = array_merge([
            'nombre' => 'nombre o razón social',
            'nit' => 'NIT',
            'personas' => 'personas',
        ], $this->empresaFieldAttributes());

        $personas = $this->input('personas', []);
        if (is_array($personas)) {
            foreach (array_keys($personas) as $index) {
                $num = (int) $index + 1;
                $attrs["personas.{$index}.tipo_contratista"] = "persona {$num} — tipo (interno/externo)";
                $attrs["personas.{$index}.nombres_apellidos"] = "persona {$num} — nombres y apellidos";
                $attrs["personas.{$index}.tipo_documento"] = "persona {$num} — tipo de documento";
                $attrs["personas.{$index}.numero_documento"] = "persona {$num} — documento";
                $attrs["personas.{$index}.fecha_ultima_ir"] = "persona {$num} — fecha de última I/R";
                $attrs["personas.{$index}.vigencia_dias"] = "persona {$num} — vigencia";
                $attrs["personas.{$index}.arl"] = "persona {$num} — ARL";
                $attrs = array_merge(
                    $attrs,
                    $this->camposAdicionalesAttributes("personas.{$index}", "persona {$num}")
                );
            }
        }

        $vehiculos = $this->input('vehiculos', []);
        if (is_array($vehiculos)) {
            foreach (array_keys($vehiculos) as $index) {
                $num = (int) $index + 1;
                $attrs["vehiculos.{$index}.placa"] = "vehículo {$num} — placa";
                $attrs["vehiculos.{$index}.soat_fin"] = "vehículo {$num} — fecha fin del SOAT";
                $attrs["vehiculos.{$index}.tecnomecanica_fin"] = "vehículo {$num} — fecha fin de la tecnomecánica";
                $attrs["vehiculos.{$index}.soat_archivo"] = "vehículo {$num} — documento del SOAT";
                $attrs["vehiculos.{$index}.tecnomecanica_archivo"] = "vehículo {$num} — documento de la tecnomecánica";
                $attrs["vehiculos.{$index}.tarjeta_propiedad_archivo"] = "vehículo {$num} — tarjeta de propiedad";
                $attrs["vehiculos.{$index}.inspeccion_sanitaria"] = "vehículo {$num} — inspección sanitaria";
                $attrs["vehiculos.{$index}.inspeccion_sanitaria_fin"] = "vehículo {$num} — fecha de vencimiento de la inspección sanitaria";
                $attrs["vehiculos.{$index}.inspeccion_sanitaria_archivo"] = "vehículo {$num} — documento de la inspección sanitaria";
            }
        }

        return $attrs;
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $personas = $this->input('personas', []);
            if (is_array($personas) && $personas !== []) {
                $vistosExternos = [];
                $vistosInternos = [];

                foreach ($personas as $index => $persona) {
                    if (! is_array($persona)) {
                        continue;
                    }

                    $tipoContratista = ($persona['tipo_contratista'] ?? 'externo') === 'interno' ? 'interno' : 'externo';
                    $tipo = $persona['tipo_documento'] ?? '';
                    $numero = $persona['numero_documento'] ?? '';

                    if (empty($persona['fecha_ultima_ir'])) {
                        $validator->errors()->add(
                            "personas.{$index}.fecha_ultima_ir",
                            'La fecha de última I/R es obligatoria.'
                        );
                    }

                    $arlPersona = is_string($persona['arl'] ?? null) ? trim($persona['arl']) : '';
                    if ($arlPersona === '') {
                        $validator->errors()->add(
                            "personas.{$index}.arl",
                            'La ARL es obligatoria.'
                        );
                    }

                    if ($tipoContratista === 'externo') {
                        $this->validarCamposAdicionalesEnValidator($validator, "personas.{$index}");

                        if ($tipo === '' || $numero === '') {
                            continue;
                        }

                        $clave = 'externo|'.$tipo.'|'.$numero;
                        if (isset($vistosExternos[$clave])) {
                            $validator->errors()->add(
                                "personas.{$index}.numero_documento",
                                'Este documento está repetido en la lista de personas.'
                            );
                        } else {
                            $vistosExternos[$clave] = true;
                        }

                        if (ContratistaExterno::query()->where('tipo_documento', $tipo)->where('numero_documento', $numero)->exists()) {
                            $validator->errors()->add(
                                "personas.{$index}.numero_documento",
                                'Ya existe un contratista externo con este tipo y número de documento.'
                            );
                        }

                        continue;
                    }

                    $this->validarCamposAdicionalesEnValidator($validator, "personas.{$index}");

                    if ($tipo === '' || $numero === '') {
                        continue;
                    }

                    $clave = 'interno|'.$tipo.'|'.$numero;
                    if (isset($vistosInternos[$clave])) {
                        $validator->errors()->add(
                            "personas.{$index}.numero_documento",
                            'Este documento está repetido en la lista de personas.'
                        );
                    } else {
                        $vistosInternos[$clave] = true;
                    }

                    if (ContratistaInterno::query()->where('tipo_documento', $tipo)->where('numero_documento', $numero)->exists()) {
                        $validator->errors()->add(
                            "personas.{$index}.numero_documento",
                            'Ya existe un contratista interno con este tipo y número de documento.'
                        );
                    }
                }
            }

            $vehiculos = $this->input('vehiculos', []);
            if (is_array($vehiculos) && $vehiculos !== []) {
                $placasVistas = [];

                foreach ($vehiculos as $index => $vehiculo) {
                    if (! is_array($vehiculo)) {
                        continue;
                    }

                    $placa = is_string($vehiculo['placa'] ?? null)
                        ? strtoupper(preg_replace('/\s+/', '', trim($vehiculo['placa'])))
                        : '';

                    if ($placa === '') {
                        continue;
                    }

                    if (isset($placasVistas[$placa])) {
                        $validator->errors()->add(
                            "vehiculos.{$index}.placa",
                            'Esta placa está repetida en la lista de vehículos.'
                        );
                    } else {
                        $placasVistas[$placa] = true;
                    }

                    if (Vehiculo::query()->where('placa', $placa)->exists()) {
                        $validator->errors()->add(
                            "vehiculos.{$index}.placa",
                            'Ya existe un vehículo registrado con esta placa.'
                        );
                    }
                }
            }
        });
    }

    protected function prepareForValidation(): void
    {
        $this->prepareEmpresaFieldsForValidation();
        $this->preparePersonasForValidation();
        $this->prepareVehiculosForValidation();
    }

    protected function preparePersonasForValidation(): void
    {
        $personas = $this->input('personas', []);

        if (! is_array($personas)) {
            $this->merge(['personas' => null]);

            return;
        }

        $personas = array_values(array_filter(array_map(function ($persona) {
            if (! is_array($persona)) {
                return null;
            }

            $nombres = is_string($persona['nombres_apellidos'] ?? null) ? trim($persona['nombres_apellidos']) : '';
            $numero = is_string($persona['numero_documento'] ?? null)
                ? preg_replace('/\s+/', '', trim($persona['numero_documento']))
                : '';
            $fecha = $persona['fecha_ultima_ir'] ?? '';
            $tipo = is_string($persona['tipo_documento'] ?? null) ? trim($persona['tipo_documento']) : '';
            $tipoContratista = is_string($persona['tipo_contratista'] ?? null) ? trim($persona['tipo_contratista']) : 'externo';
            if ($tipoContratista !== 'interno') {
                $tipoContratista = 'externo';
            }
            $vigencia = $persona['vigencia_dias'] ?? null;
            $arl = is_string($persona['arl'] ?? null) ? trim($persona['arl']) : '';

            $tieneDatos = $nombres !== '' || $numero !== '' || $fecha !== '' || $arl !== '';

            if (! $tieneDatos) {
                return null;
            }

            $datos = [
                'tipo_contratista' => $tipoContratista,
                'nombres_apellidos' => $nombres,
                'tipo_documento' => $tipo !== '' ? $tipo : 'CC',
                'numero_documento' => $numero,
                'arl' => $arl,
                'fecha_ultima_ir' => $fecha !== '' ? $fecha : null,
                'vigencia_dias' => is_numeric($vigencia) ? (int) $vigencia : 365,
            ];

            if (array_key_exists('licencia_vencimientos', $persona) && is_array($persona['licencia_vencimientos'])) {
                $datos['licencia_vencimientos'] = $persona['licencia_vencimientos'];
            }

            foreach (['fecha_nacimiento', 'cargo', 'manipulador_vigencia', 'licencia_categoria'] as $campo) {
                if (array_key_exists($campo, $persona)) {
                    $valor = $persona[$campo];
                    $datos[$campo] = is_string($valor) && trim($valor) === '' ? null : $valor;
                }
            }

            $datos['manipulador_alimentos'] = filter_var($persona['manipulador_alimentos'] ?? false, FILTER_VALIDATE_BOOLEAN);
            $datos['licencia_conduccion'] = filter_var($persona['licencia_conduccion'] ?? false, FILTER_VALIDATE_BOOLEAN);
            $this->prepararCamposAdicionales($datos);

            return $datos;
        }, $personas)));

        $this->merge([
            'personas' => $personas === [] ? null : $personas,
        ]);
    }

    protected function prepareVehiculosForValidation(): void
    {
        $vehiculos = $this->input('vehiculos', []);

        if (! is_array($vehiculos)) {
            $this->merge(['vehiculos' => null]);

            return;
        }

        $vehiculos = array_values(array_filter(array_map(function ($vehiculo) {
            if (! is_array($vehiculo)) {
                return null;
            }

            $placa = is_string($vehiculo['placa'] ?? null)
                ? strtoupper(preg_replace('/\s+/', '', trim($vehiculo['placa'])))
                : '';
            $soat = $vehiculo['soat_fin'] ?? '';
            $tecno = $vehiculo['tecnomecanica_fin'] ?? '';

            if ($placa === '' && $soat === '' && $tecno === '') {
                return null;
            }

            $inspeccion = filter_var($vehiculo['inspeccion_sanitaria'] ?? false, FILTER_VALIDATE_BOOLEAN);
            $inspeccionFin = $vehiculo['inspeccion_sanitaria_fin'] ?? '';

            return [
                'placa' => $placa,
                'soat_fin' => $soat !== '' ? $soat : null,
                'tecnomecanica_fin' => $tecno !== '' ? $tecno : null,
                'inspeccion_sanitaria' => $inspeccion,
                'inspeccion_sanitaria_fin' => ($inspeccion && $inspeccionFin !== '') ? $inspeccionFin : null,
            ];
        }, $vehiculos)));

        $this->merge([
            'vehiculos' => $vehiculos === [] ? null : $vehiculos,
        ]);
    }
}
