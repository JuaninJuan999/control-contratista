<?php

namespace App\Models;

use App\Support\PeriodoPlanilla;
use App\Support\PlanillaTipo;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['nombre', 'nit', 'telefono', 'correos', 'limite', 'planilla', 'tipo_empresa'])]
class Empresa extends Model
{
    protected function casts(): array
    {
        return [
            'correos' => 'array',
            'limite' => 'date',
        ];
    }

    /**
     * Días hasta la fecha límite (negativos si ya venció).
     */
    public function getDiasParaLimiteAttribute(): ?int
    {
        if ($this->limite === null) {
            return null;
        }

        $hoy = now()->startOfDay();

        return (int) $hoy->diffInDays($this->limite->copy()->startOfDay(), false);
    }

    public function getEstadoLimiteAttribute(): ?string
    {
        $dias = $this->dias_para_limite;

        if ($dias === null) {
            return null;
        }

        if ($dias < 0) {
            return 'VENCIDA';
        }

        if ($dias <= 10) {
            return 'PRÓXIMA A VENCER';
        }

        return 'VIGENTE';
    }

    public function contratistasExternos(): HasMany
    {
        return $this->hasMany(ContratistaExterno::class);
    }

    public function contratistasInternos(): HasMany
    {
        return $this->hasMany(ContratistaInterno::class);
    }

    public function vehiculos(): HasMany
    {
        return $this->hasMany(Vehiculo::class);
    }

    public function planillaArchivos(): HasMany
    {
        return $this->hasMany(EmpresaPlanillaArchivo::class)
            ->orderByDesc('vigencia_hasta')
            ->orderByDesc('periodo_anio')
            ->orderByDesc('periodo_mes');
    }

    /** @return array{anio: int, mes: int}|null */
    public function periodoVigenciaActual(): ?array
    {
        if ($this->limite === null) {
            return null;
        }

        return [
            'anio' => (int) $this->limite->year,
            'mes' => (int) $this->limite->month,
        ];
    }

    /** Planilla SS adjunta para la fecha límite vigente actual. */
    public function archivoPlanillaVigenteActual(): ?EmpresaPlanillaArchivo
    {
        if ($this->limite === null) {
            return null;
        }

        $limite = $this->limite->copy()->startOfDay();

        return $this->planillaArchivos->first(
            fn (EmpresaPlanillaArchivo $archivo) => $archivo->vigencia_hasta?->copy()->startOfDay()->equalTo($limite)
        );
    }

    public function archivoPlanillaPeriodo(int $anio, int $mes): ?EmpresaPlanillaArchivo
    {
        return $this->planillaArchivos
            ->first(fn (EmpresaPlanillaArchivo $archivo) => $archivo->periodo_anio === $anio && $archivo->periodo_mes === $mes);
    }

    /** Hay planilla SS válida para el ciclo de vigencia actual (fecha límite no vencida y archivo coincidente). */
    public function planillaVigenteAdjunta(): bool
    {
        if ($this->limite === null || $this->estado_limite === 'VENCIDA') {
            return false;
        }

        return $this->archivoPlanillaVigenteActual() !== null;
    }

    public function requierePlanillaAdjunta(): bool
    {
        if ($this->limite === null) {
            return false;
        }

        if ($this->estado_limite === 'VENCIDA') {
            return true;
        }

        return $this->archivoPlanillaVigenteActual() === null;
    }

    public function periodoVigenciaEtiqueta(): string
    {
        $periodo = $this->periodoVigenciaActual();

        if ($periodo === null) {
            return 'Sin fecha límite';
        }

        return PeriodoPlanilla::etiqueta($periodo['anio'], $periodo['mes']);
    }

    public function esPlanillaIndependiente(): bool
    {
        return $this->planilla === PlanillaTipo::INDEPENDIENTE;
    }

    public function esPlanillaDependiente(): bool
    {
        return $this->planilla === PlanillaTipo::DEPENDIENTE;
    }

    /**
     * Resumen de vigencia SS de contratistas internos (planilla independiente por persona).
     *
     * @return array{
     *     vigentes: int,
     *     proximas: int,
     *     vencidas: int,
     *     sin_fecha: int,
     *     total: int,
     *     items: list<array{nombre: string, tipo: string, limite: ?\Illuminate\Support\Carbon, estado: ?string, dias: ?int, tipo_planilla: ?string}>
     * }
     */
    public function resumenVigenciaSsContratistas(): array
    {
        $this->loadMissing([
            'contratistasInternos.planillaArchivos',
            'contratistasExternos',
            'planillaArchivos',
        ]);

        $resumen = [
            'vigentes' => 0,
            'proximas' => 0,
            'vencidas' => 0,
            'sin_fecha' => 0,
            'total' => 0,
            'items' => [],
        ];

        foreach ($this->contratistasInternos as $contratista) {
            $contratista->setRelation('empresa', $this);
            $estado = $contratista->estadoLimiteSs();
            $resumen['total']++;

            match ($estado) {
                'VIGENTE' => $resumen['vigentes']++,
                'PRÓXIMA A VENCER' => $resumen['proximas']++,
                'VENCIDA' => $resumen['vencidas']++,
                default => $resumen['sin_fecha']++,
            };

            $resumen['items'][] = [
                'nombre' => $contratista->nombres_apellidos,
                'tipo' => 'interno',
                'limite' => $contratista->limiteEfectivo(),
                'estado' => $estado,
                'dias' => $contratista->diasParaLimiteSs(),
                'tipo_planilla' => $contratista->tipo_planilla,
            ];
        }

        foreach ($this->contratistasExternos as $contratista) {
            $resumen['total']++;
            $resumen['items'][] = [
                'nombre' => $contratista->nombres_apellidos,
                'tipo' => 'externo',
                'limite' => null,
                'estado' => null,
                'dias' => null,
                'tipo_planilla' => null,
            ];
        }

        usort($resumen['items'], function (array $a, array $b): int {
            $orden = ['VENCIDA' => 0, 'PRÓXIMA A VENCER' => 1, 'VIGENTE' => 2, null => 3];
            $oa = $orden[$a['estado']] ?? 4;
            $ob = $orden[$b['estado']] ?? 4;
            if ($oa !== $ob) {
                return $oa <=> $ob;
            }

            return strcasecmp($a['nombre'], $b['nombre']);
        });

        return $resumen;
    }

    /**
     * Estado límite para la fila principal: empresa dependiente usa su fecha;
     * independiente usa el peor estado entre sus internos.
     */
    public function estadoLimiteParaListado(): ?string
    {
        if (! $this->esPlanillaIndependiente()) {
            return $this->estado_limite;
        }

        $resumen = $this->resumenVigenciaSsContratistas();

        if ($resumen['vencidas'] > 0) {
            return 'VENCIDA';
        }

        if ($resumen['proximas'] > 0) {
            return 'PRÓXIMA A VENCER';
        }

        if ($resumen['vigentes'] > 0) {
            return 'VIGENTE';
        }

        return $resumen['total'] > 0 ? null : $this->estado_limite;
    }
}
