<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'placa',
    'soat_fin',
    'tecnomecanica_fin',
    'empresa_id',
])]
class Vehiculo extends Model
{
    protected function casts(): array
    {
        return [
            'soat_fin' => 'date',
            'tecnomecanica_fin' => 'date',
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
