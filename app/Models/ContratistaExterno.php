<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'nombres_apellidos',
    'tipo_documento',
    'numero_documento',
    'fecha_nacimiento',
    'cargo',
    'manipulador_alimentos',
    'manipulador_vigencia',
    'licencia_conduccion',
    'licencia_archivo',
    'licencia_categoria',
    'licencia_vencimiento',
    'licencia_vencimiento_archivo',
    'tarjeta_propiedad_archivo',
    'empresa_id',
    'fecha_ultima_ir',
    'vigencia_dias',
    'activo',
])]
class ContratistaExterno extends Model
{
    protected $table = 'contratistas_externos';

    protected static function booted(): void
    {
        static::saving(function (ContratistaExterno $contratista): void {
            $inicio = CarbonImmutable::parse($contratista->fecha_ultima_ir)->startOfDay();
            $contratista->fecha_vencimiento = $inicio->addDays((int) $contratista->vigencia_dias);
        });
    }

    protected function casts(): array
    {
        return [
            'fecha_nacimiento' => 'date',
            'manipulador_alimentos' => 'boolean',
            'manipulador_vigencia' => 'date',
            'licencia_conduccion' => 'boolean',
            'licencia_vencimiento' => 'date',
            'fecha_ultima_ir' => 'date',
            'fecha_vencimiento' => 'date',
            'vigencia_dias' => 'integer',
            'activo' => 'boolean',
        ];
    }

    /**
     * Días hasta el vencimiento (negativos si ya venció).
     */
    public function getDiasFaltantesAttribute(): int
    {
        $fin = CarbonImmutable::parse($this->fecha_vencimiento)->startOfDay();
        $hoy = now()->toImmutable()->startOfDay();

        return $hoy->diffInDays($fin, false);
    }

    public function getEstadoAttribute(): string
    {
        return $this->dias_faltantes >= 0 ? 'VIGENTE' : 'VENCIDA';
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }
}
