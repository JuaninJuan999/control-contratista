<?php

namespace App\Models\Concerns;

use App\Models\ContratistaInternoPlanillaArchivo;
use App\Models\EmpresaPlanillaArchivo;
use App\Support\PlanillaTipo;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

trait ContratistaInternoSeguridadSocial
{
    public function planillaArchivos(): HasMany
    {
        return $this->hasMany(ContratistaInternoPlanillaArchivo::class)
            ->orderByDesc('vigencia_hasta')
            ->orderByDesc('periodo_anio')
            ->orderByDesc('periodo_mes');
    }

    public function esPlanillaDependiente(): bool
    {
        return ($this->tipo_planilla ?? PlanillaTipo::DEPENDIENTE) === PlanillaTipo::DEPENDIENTE;
    }

    public function esPlanillaIndependiente(): bool
    {
        return $this->tipo_planilla === PlanillaTipo::INDEPENDIENTE;
    }

    public function limiteEfectivo(): ?Carbon
    {
        if ($this->esPlanillaIndependiente()) {
            return $this->limite?->copy()->startOfDay();
        }

        return $this->relationLoaded('empresa')
            ? $this->empresa?->limite?->copy()->startOfDay()
            : $this->empresa()->value('limite')?->copy()->startOfDay();
    }

    public function diasParaLimiteSs(): ?int
    {
        $limite = $this->limiteEfectivo();

        if ($limite === null) {
            return null;
        }

        $hoy = now()->startOfDay();

        return (int) $hoy->diffInDays(CarbonImmutable::parse($limite)->startOfDay(), false);
    }

    public function estadoLimiteSs(): ?string
    {
        $dias = $this->diasParaLimiteSs();

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

    public function planillaSsAlDia(): bool
    {
        $limite = $this->limiteEfectivo();

        if ($limite === null || $this->estadoLimiteSs() === 'VENCIDA') {
            return false;
        }

        return $this->archivoPlanillaVigenteActual() !== null;
    }

    public function archivoPlanillaVigenteActual(): EmpresaPlanillaArchivo|ContratistaInternoPlanillaArchivo|null
    {
        $limite = $this->limiteEfectivo();

        if ($limite === null) {
            return null;
        }

        if ($this->esPlanillaDependiente()) {
            $empresa = $this->empresa;

            if ($empresa === null) {
                return null;
            }

            if ($this->relationLoaded('empresa') && $empresa->relationLoaded('planillaArchivos')) {
                return $empresa->planillaArchivos->first(
                    fn (EmpresaPlanillaArchivo $archivo) => $archivo->vigencia_hasta?->copy()->startOfDay()->equalTo($limite)
                );
            }

            return $empresa->archivoPlanillaVigenteActual();
        }

        if ($this->relationLoaded('planillaArchivos')) {
            return $this->planillaArchivos->first(
                fn (ContratistaInternoPlanillaArchivo $archivo) => $archivo->vigencia_hasta?->copy()->startOfDay()->equalTo($limite)
            );
        }

        return $this->planillaArchivos()
            ->whereDate('vigencia_hasta', $limite)
            ->first();
    }

    /**
     * @return array{anio: int, mes: int}|null
     */
    public function periodoVigenciaSsActual(): ?array
    {
        $limite = $this->limiteEfectivo();

        if ($limite === null) {
            return null;
        }

        return [
            'anio' => (int) $limite->year,
            'mes' => (int) $limite->month,
        ];
    }

    public function esMesVigenciaSsActiva(int $anio, int $mes): bool
    {
        $periodo = $this->periodoVigenciaSsActual();

        if ($periodo === null) {
            return false;
        }

        return $periodo['anio'] === $anio && $periodo['mes'] === $mes;
    }

    /**
     * Datos para renderizar una celda del control mensual SS (internos).
     *
     * @return array{
     *     abrev: string,
     *     estado: string,
     *     es_vigencia_activa: bool,
     *     mostrar_badge: bool,
     *     dias: int|null,
     *     urgencia: string|null,
     *     titulo: string,
     *     editable: bool
     * }
     */
    public function controlMesSsUi(int $anio, int $mes, bool $puedeEditar = false): array
    {
        $abrev = self::MESES[$mes] ?? (string) $mes;
        $estadoHistorico = $this->estadoMes($anio, $mes);
        $esVigenciaActiva = $this->esMesVigenciaSsActiva($anio, $mes);
        $dias = $this->diasParaLimiteSs();
        $planillaAlDia = $this->planillaSsAlDia();
        $limite = $this->limiteEfectivo();

        $mostrarBadge = $esVigenciaActiva
            && $limite !== null
            && $planillaAlDia
            && $dias !== null
            && $dias >= 0;

        $urgencia = null;
        if ($mostrarBadge && $dias !== null) {
            if ($dias <= 10) {
                $urgencia = 'proxima';
            } else {
                $urgencia = 'vigente';
            }
        }

        if ($esVigenciaActiva && $limite !== null && ! $planillaAlDia) {
            $estado = $estadoHistorico === 'ok' ? 'ok' : 'vacio';
            $titulo = $abrev.' — vigencia hasta '.$limite->format('d/m/Y').'. Falta planilla SS o está vencida.';
        } elseif ($mostrarBadge) {
            $estado = 'ok';
            $titulo = $abrev.' — '.$dias.' día'.($dias === 1 ? '' : 's').' restantes (vence '.$limite->format('d/m/Y').')';
        } elseif ($estadoHistorico === 'ok') {
            $estado = 'ok';
            $titulo = $abrev.' — registrado como vigente. Clic para cambiar estado.';
        } elseif ($estadoHistorico === 'rechazado') {
            $estado = 'rechazado';
            $titulo = $abrev.' — no vigente. Clic para cambiar estado.';
        } else {
            $estado = 'vacio';
            $titulo = $abrev.' — sin registro. Clic para marcar vigente.';
        }

        if ($mostrarBadge) {
            $titulo = $abrev.' — '.$dias.' día'.($dias === 1 ? '' : 's').' restantes (vence '.$limite->format('d/m/Y').'). Clic para ajustar manualmente si hubo error.';
        }

        return [
            'abrev' => $abrev,
            'estado' => $estado,
            'es_vigencia_activa' => $esVigenciaActiva,
            'mostrar_badge' => $mostrarBadge,
            'dias' => $mostrarBadge ? $dias : null,
            'urgencia' => $urgencia,
            'titulo' => $titulo,
            'editable' => $puedeEditar,
        ];
    }

    public function marcarMesVigenciaSsActual(): void
    {
        $periodo = $this->periodoVigenciaSsActual();

        if ($periodo === null) {
            return;
        }

        $this->marcarMes($periodo['anio'], $periodo['mes'], 'ok');
        $this->save();
    }
}
