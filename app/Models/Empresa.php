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
