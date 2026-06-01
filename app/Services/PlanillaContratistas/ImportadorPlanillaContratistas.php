<?php

namespace App\Services\PlanillaContratistas;

use App\Models\ContratistaExterno;
use App\Models\ContratistaInterno;
use App\Models\Empresa;
use Illuminate\Support\Facades\DB;

class ImportadorPlanillaContratistas
{
    /**
     * @param  list<PlanillaFilaContratista>  $filas
     */
    public function analizar(Empresa $empresa, array $filas): ResultadoAnalisisPlanilla
    {
        $empresa->load(['contratistasExternos', 'contratistasInternos']);

        $resultado = new ResultadoAnalisisPlanilla;
        $clavesExcel = [];

        foreach ($filas as $fila) {
            $clave = $fila->claveDocumento();
            $clavesExcel[$clave] = true;

            $existente = $this->buscarEnEmpresa($empresa, $fila->tipoDocumento, $fila->numeroDocumento);

            if ($existente !== null) {
                $resultado->actualizados[] = [
                    'fila' => $fila->numeroFila,
                    'clave' => $existente['clave'],
                    'documento' => $fila->tipoDocumento.' '.$fila->numeroDocumento,
                    'nombre' => $fila->nombresApellidos ?? $existente['nombre'],
                    'tipo' => $existente['tipo'],
                ];

                continue;
            }

            if ($this->perteneceOtraEmpresa($fila->tipoDocumento, $fila->numeroDocumento, $empresa->id)) {
                $resultado->errores[] = [
                    'fila' => $fila->numeroFila,
                    'documento' => $fila->tipoDocumento.' '.$fila->numeroDocumento,
                    'mensaje' => 'Este documento ya está registrado en otra empresa.',
                ];

                continue;
            }

            $faltantes = $this->faltantesParaCrear($fila);

            if ($faltantes !== []) {
                continue;
            }

            $resultado->nuevos[] = [
                'fila' => $fila->numeroFila,
                'clave' => $fila->tipoContratista.'-nuevo-'.$clave,
                'documento' => $fila->tipoDocumento.' '.$fila->numeroDocumento,
                'nombre' => $fila->nombresApellidos,
                'tipo' => $fila->tipoContratista,
                'sin_fecha_induccion' => true,
            ];
        }

        foreach ($this->contratistasEmpresa($empresa) as $contratista) {
            if (isset($clavesExcel[$contratista['doc_clave']])) {
                continue;
            }

            $resultado->inactivados[] = [
                'clave' => $contratista['clave'],
                'documento' => $contratista['documento'],
                'nombre' => $contratista['nombre'],
                'tipo' => $contratista['tipo'],
            ];
        }

        return $resultado;
    }

    /**
     * @param  list<PlanillaFilaContratista>  $filas
     */
    public function aplicar(Empresa $empresa, array $filas): ResultadoAnalisisPlanilla
    {
        $analisis = $this->analizar($empresa, $filas);

        if ($analisis->tieneErroresBloqueantes()) {
            return $analisis;
        }

        if ($empresa->limite === null) {
            $analisis->errores[] = [
                'fila' => 0,
                'documento' => '—',
                'mensaje' => 'La empresa debe tener una fecha límite definida antes de importar la planilla.',
            ];

            return $analisis;
        }

        $anio = (int) $empresa->limite->year;
        $mes = (int) $empresa->limite->month;
        $clavesExcel = [];
        $clavesVigentes = [];

        DB::transaction(function () use ($empresa, $filas, $anio, $mes, &$clavesExcel, &$clavesVigentes): void {
            $empresa->load(['contratistasExternos', 'contratistasInternos']);

            foreach ($filas as $fila) {
                $claveDoc = $fila->claveDocumento();
                $clavesExcel[$claveDoc] = true;

                $existente = $this->buscarEnEmpresa($empresa, $fila->tipoDocumento, $fila->numeroDocumento);

                if ($existente !== null) {
                    /** @var ContratistaExterno|ContratistaInterno $modelo */
                    $modelo = $existente['modelo'];
                    $datos = ['activo' => true];

                    if ($fila->nombresApellidos !== null && $fila->nombresApellidos !== '') {
                        $datos['nombres_apellidos'] = $fila->nombresApellidos;
                    }
                    if ($fila->arl !== null && $fila->arl !== '') {
                        $datos['arl'] = $fila->arl;
                    }

                    $modelo->update($datos);
                    $modelo->marcarMes($anio, $mes, 'ok');
                    $modelo->save();
                    $clavesVigentes[] = $existente['clave'];

                    continue;
                }

                if ($this->faltantesParaCrear($fila) !== []) {
                    continue;
                }

                if ($this->perteneceOtraEmpresa($fila->tipoDocumento, $fila->numeroDocumento, $empresa->id)) {
                    continue;
                }

                $datos = [
                    'nombres_apellidos' => $fila->nombresApellidos,
                    'tipo_documento' => $fila->tipoDocumento,
                    'numero_documento' => $fila->numeroDocumento,
                    'empresa_id' => $empresa->id,
                    'arl' => $fila->arl,
                    'fecha_ultima_ir' => null,
                    'vigencia_dias' => 365,
                    'activo' => true,
                ];

                if ($fila->tipoContratista === 'interno') {
                    $modelo = ContratistaInterno::query()->create($datos);
                    $clave = 'interno-'.$modelo->id;
                } else {
                    $modelo = ContratistaExterno::query()->create($datos);
                    $clave = 'externo-'.$modelo->id;
                }

                $modelo->marcarMes($anio, $mes, 'ok');
                $modelo->save();
                $clavesVigentes[] = $clave;
            }

            foreach ($this->contratistasEmpresa($empresa) as $contratista) {
                if (isset($clavesExcel[$contratista['doc_clave']])) {
                    continue;
                }

                /** @var ContratistaExterno|ContratistaInterno $modelo */
                $modelo = $contratista['modelo'];
                $modelo->update(['activo' => false]);
                $modelo->marcarMes($anio, $mes, 'rechazado');
                $modelo->save();
            }
        });

        return $analisis;
    }

    /**
     * @return list<string>
     */
    private function faltantesParaCrear(PlanillaFilaContratista $fila): array
    {
        $faltantes = [];

        if ($fila->nombresApellidos === null || trim($fila->nombresApellidos) === '') {
            $faltantes[] = 'Nombre y Apellido';
        }
        if ($fila->arl === null || trim($fila->arl) === '') {
            $faltantes[] = 'ARL';
        }

        return $faltantes;
    }

    /**
     * @return array{modelo: ContratistaExterno|ContratistaInterno, clave: string, doc_clave: string, nombre: string, tipo: string}|null
     */
    private function buscarEnEmpresa(Empresa $empresa, string $tipoDocumento, string $numeroDocumento): ?array
    {
        $externo = $empresa->contratistasExternos
            ->first(fn (ContratistaExterno $c) => $c->tipo_documento === $tipoDocumento && $c->numero_documento === $numeroDocumento);

        if ($externo !== null) {
            return $this->filaContratista($externo, 'externo');
        }

        $interno = $empresa->contratistasInternos
            ->first(fn (ContratistaInterno $c) => $c->tipo_documento === $tipoDocumento && $c->numero_documento === $numeroDocumento);

        if ($interno !== null) {
            return $this->filaContratista($interno, 'interno');
        }

        return null;
    }

    /**
     * @return array{modelo: ContratistaExterno|ContratistaInterno, clave: string, doc_clave: string, documento: string, nombre: string, tipo: string}
     */
    private function filaContratista(ContratistaExterno|ContratistaInterno $modelo, string $tipo): array
    {
        return [
            'modelo' => $modelo,
            'clave' => $tipo.'-'.$modelo->id,
            'doc_clave' => mb_strtoupper($modelo->tipo_documento, 'UTF-8').'|'.$modelo->numero_documento,
            'documento' => $modelo->tipo_documento.' '.$modelo->numero_documento,
            'nombre' => $modelo->nombres_apellidos,
            'tipo' => $tipo,
        ];
    }

    private function perteneceOtraEmpresa(string $tipoDocumento, string $numeroDocumento, int $empresaId): bool
    {
        $filtro = fn ($query) => $query
            ->where('tipo_documento', $tipoDocumento)
            ->where('numero_documento', $numeroDocumento)
            ->where('empresa_id', '!=', $empresaId);

        return ContratistaExterno::query()->where($filtro)->exists()
            || ContratistaInterno::query()->where($filtro)->exists();
    }

    /**
     * @return list<array{modelo: ContratistaExterno|ContratistaInterno, clave: string, doc_clave: string, documento: string, nombre: string, tipo: string}>
     */
    private function contratistasEmpresa(Empresa $empresa): array
    {
        $lista = [];

        foreach ($empresa->contratistasExternos as $contratista) {
            $lista[] = $this->filaContratista($contratista, 'externo');
        }

        foreach ($empresa->contratistasInternos as $contratista) {
            $lista[] = $this->filaContratista($contratista, 'interno');
        }

        return $lista;
    }
}
