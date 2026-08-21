<?php

namespace App\Http\Controllers\Concerns;

use App\Models\ContratistaInterno;
use App\Models\ContratistaInternoPlanillaArchivo;
use App\Services\PlanillaContratistaInternoStorage;
use App\Support\PlanillaTipo;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;

trait GuardaPlanillaSsInterno
{
    protected function guardarPlanillaSsIndependiente(ContratistaInterno $contratista, Request $request): void
    {
        if ($contratista->tipo_planilla !== PlanillaTipo::INDEPENDIENTE) {
            return;
        }

        if (! $request->hasFile('planilla_ss_archivo')) {
            return;
        }

        $archivo = $request->file('planilla_ss_archivo');
        if (! $archivo instanceof UploadedFile) {
            return;
        }

        $limite = $contratista->limite?->copy()->startOfDay();
        if ($limite === null) {
            return;
        }

        $this->persistirPlanillaSsIndependiente($contratista, $archivo, $limite);
    }

    protected function persistirPlanillaSsIndependiente(
        ContratistaInterno $contratista,
        UploadedFile $archivo,
        $limite
    ): void {
        $anio = (int) $limite->year;
        $mes = (int) $limite->month;

        $datosArchivo = [
            'periodo_anio' => $anio,
            'periodo_mes' => $mes,
            'archivo' => PlanillaContratistaInternoStorage::guardar((int) $contratista->id, $archivo),
            'nombre_original' => $archivo->getClientOriginalName(),
            'mime' => $archivo->getClientMimeType(),
            'tamano_bytes' => $archivo->getSize(),
            'user_id' => auth()->id(),
            'vigencia_hasta' => $limite,
        ];

        $registro = ContratistaInternoPlanillaArchivo::query()
            ->where('contratista_interno_id', $contratista->id)
            ->whereDate('vigencia_hasta', $limite)
            ->first();

        if ($registro !== null) {
            PlanillaContratistaInternoStorage::eliminar($registro->archivo);
            $registro->update($datosArchivo);
        } else {
            ContratistaInternoPlanillaArchivo::query()->create([
                'contratista_interno_id' => $contratista->id,
                ...$datosArchivo,
            ]);
        }

        $contratista->marcarMesVigenciaSsActual();
    }
}
