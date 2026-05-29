<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['nombre', 'nit', 'telefono', 'correos', 'limite', 'planilla'])]
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
}
