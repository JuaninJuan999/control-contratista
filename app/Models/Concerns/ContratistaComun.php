<?php

namespace App\Models\Concerns;

use App\Models\Empresa;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Lógica compartida por contratistas externos e internos.
 *
 * Ambos módulos manejan el mismo conjunto de datos: inducción/reinducción (I/R),
 * ARL, control mensual y los campos adicionales (manipulador, licencia, etc.).
 */
trait ContratistaComun
{
    public const MESES = [
        1 => 'EN',
        2 => 'FE',
        3 => 'MA',
        4 => 'AB',
        5 => 'MY',
        6 => 'JN',
        7 => 'JL',
        8 => 'AG',
        9 => 'SE',
        10 => 'OC',
        11 => 'NO',
        12 => 'DI',
    ];

    public static function bootContratistaComun(): void
    {
        static::saving(function ($contratista): void {
            if ($contratista->fecha_ultima_ir) {
                $inicio = CarbonImmutable::parse($contratista->fecha_ultima_ir)->startOfDay();
                $contratista->fecha_vencimiento = $inicio->addDays((int) $contratista->vigencia_dias);
            } else {
                $contratista->fecha_vencimiento = null;
            }
        });
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'fecha_nacimiento' => 'date',
            'manipulador_alimentos' => 'boolean',
            'manipulador_vigencia' => 'date',
            'licencia_conduccion' => 'boolean',
            'licencia_categoria' => 'array',
            'licencia_vencimientos' => 'array',
            'fecha_ultima_ir' => 'date',
            'fecha_vencimiento' => 'date',
            'vigencia_dias' => 'integer',
            'meses_por_anio' => 'array',
            'meses_rechazados' => 'array',
            'activo' => 'boolean',
        ];
    }

    /**
     * Días hasta el vencimiento de la I/R (negativos si ya venció).
     * Null cuando aún no se ha registrado la I/R.
     */
    public function getDiasFaltantesAttribute(): ?int
    {
        if (! $this->fecha_vencimiento) {
            return null;
        }

        $fin = CarbonImmutable::parse($this->fecha_vencimiento)->startOfDay();
        $hoy = now()->toImmutable()->startOfDay();

        return (int) $hoy->diffInDays($fin, false);
    }

    public function getEstadoAttribute(): string
    {
        if ($this->dias_faltantes === null) {
            return 'SIN REGISTRO';
        }

        return $this->dias_faltantes >= 0 ? 'VIGENTE' : 'VENCIDA';
    }

    /**
     * @return array<string, string>
     */
    public function licenciaVencimientosFormateados(): array
    {
        $vencimientos = $this->licencia_vencimientos ?? [];

        if (! is_array($vencimientos)) {
            return [];
        }

        $formateados = [];

        foreach ($vencimientos as $categoria => $fecha) {
            if (! is_string($categoria) || ! is_string($fecha) || trim($fecha) === '') {
                continue;
            }

            try {
                $formateados[$categoria] = Carbon::parse($fecha)->format('Y-m-d');
            } catch (\Throwable) {
                continue;
            }
        }

        ksort($formateados);

        return $formateados;
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    /**
     * @return list<int>
     */
    public function mesesRegistradosEn(int $anio): array
    {
        return $this->mesesDeColumna('meses_por_anio', $anio);
    }

    /**
     * @return list<int>
     */
    public function mesesRechazadosEn(int $anio): array
    {
        return $this->mesesDeColumna('meses_rechazados', $anio);
    }

    public function mesRegistrado(int $anio, int $mes): bool
    {
        return in_array($mes, $this->mesesRegistradosEn($anio), true);
    }

    public function mesRechazado(int $anio, int $mes): bool
    {
        return in_array($mes, $this->mesesRechazadosEn($anio), true);
    }

    /**
     * Estado del mes: 'ok' (verde), 'rechazado' (rojo) o 'vacio'.
     */
    public function estadoMes(int $anio, int $mes): string
    {
        if ($this->mesRegistrado($anio, $mes)) {
            return 'ok';
        }

        if ($this->mesRechazado($anio, $mes)) {
            return 'rechazado';
        }

        return 'vacio';
    }

    /**
     * Cicla el estado del mes: vacío → ok → rechazado → vacío.
     */
    public function toggleMes(int $anio, int $mes): void
    {
        $siguiente = match ($this->estadoMes($anio, $mes)) {
            'vacio' => 'ok',
            'ok' => 'rechazado',
            default => 'vacio',
        };

        $this->marcarMes($anio, $mes, $siguiente);
        $this->save();
    }

    /**
     * Fija explícitamente el estado de un mes (no guarda; el llamador debe hacer save()).
     */
    public function marcarMes(int $anio, int $mes, string $estado): void
    {
        $this->quitarMesDe('meses_por_anio', $anio, $mes);
        $this->quitarMesDe('meses_rechazados', $anio, $mes);

        if ($estado === 'ok') {
            $this->agregarMesA('meses_por_anio', $anio, $mes);
        } elseif ($estado === 'rechazado') {
            $this->agregarMesA('meses_rechazados', $anio, $mes);
        }
    }

    /**
     * @return list<int>
     */
    private function mesesDeColumna(string $columna, int $anio): array
    {
        $registros = $this->{$columna} ?? [];

        if (! is_array($registros)) {
            return [];
        }

        $meses = $registros[(string) $anio] ?? $registros[$anio] ?? [];

        if (! is_array($meses)) {
            return [];
        }

        return array_values(array_unique(array_map('intval', $meses)));
    }

    private function agregarMesA(string $columna, int $anio, int $mes): void
    {
        $registros = is_array($this->{$columna} ?? null) ? $this->{$columna} : [];
        $meses = $this->mesesDeColumna($columna, $anio);

        if (! in_array($mes, $meses, true)) {
            $meses[] = $mes;
            sort($meses);
        }

        $registros[(string) $anio] = $meses;
        $this->{$columna} = $registros;
    }

    private function quitarMesDe(string $columna, int $anio, int $mes): void
    {
        $registros = is_array($this->{$columna} ?? null) ? $this->{$columna} : [];
        $clave = (string) $anio;
        $meses = array_values(array_filter($this->mesesDeColumna($columna, $anio), fn (int $m) => $m !== $mes));

        if ($meses === []) {
            unset($registros[$clave]);
        } else {
            $registros[$clave] = $meses;
        }

        $this->{$columna} = $registros === [] ? null : $registros;
    }
}
