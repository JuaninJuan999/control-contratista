<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'placa',
    'soat_fin',
    'soat_archivo',
    'tecnomecanica_fin',
    'tecnomecanica_archivo',
    'tarjeta_propiedad_archivo',
    'inspeccion_sanitaria',
    'inspeccion_sanitaria_fin',
    'inspeccion_sanitaria_archivo',
    'empresa_id',
])]
class Vehiculo extends Model
{
    /** @var array<string, string> */
    public const DOCUMENTOS = [
        'soat_archivo' => 'SOAT',
        'tecnomecanica_archivo' => 'Tecnomecánica',
        'tarjeta_propiedad_archivo' => 'Tarjeta de propiedad',
        'inspeccion_sanitaria_archivo' => 'Inspección sanitaria',
    ];

    protected function casts(): array
    {
        return [
            'soat_fin' => 'date',
            'tecnomecanica_fin' => 'date',
            'inspeccion_sanitaria' => 'boolean',
            'inspeccion_sanitaria_fin' => 'date',
        ];
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    public function getSoatEstadoAttribute(): string
    {
        return $this->fechaEstado($this->soat_fin);
    }

    public function getTecnomecanicaEstadoAttribute(): string
    {
        return $this->fechaEstado($this->tecnomecanica_fin);
    }

    public function getInspeccionSanitariaEstadoAttribute(): string
    {
        if (! $this->inspeccion_sanitaria) {
            return '—';
        }

        return $this->fechaEstado($this->inspeccion_sanitaria_fin);
    }

    private function fechaEstado(mixed $fecha): string
    {
        if ($fecha === null) {
            return '—';
        }

        $hoy = now()->toImmutable()->startOfDay();
        $fin = CarbonImmutable::parse($fecha)->startOfDay();

        return $fin->gte($hoy) ? 'VIGENTE' : 'VENCIDA';
    }
}
