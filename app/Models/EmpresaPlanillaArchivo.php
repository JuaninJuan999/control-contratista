<?php

namespace App\Models;

use App\Support\PeriodoPlanilla;
use App\Services\PlanillaEmpresaStorage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmpresaPlanillaArchivo extends Model
{
    protected $table = 'empresa_planilla_archivos';

    protected $fillable = [
        'empresa_id',
        'periodo_anio',
        'periodo_mes',
        'vigencia_hasta',
        'archivo',
        'nombre_original',
        'mime',
        'tamano_bytes',
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'vigencia_hasta' => 'date',
        ];
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function periodoEtiqueta(): string
    {
        return PeriodoPlanilla::etiqueta($this->periodo_anio, $this->periodo_mes, true);
    }

    public function esPeriodoVigenteActual(): bool
    {
        $empresa = $this->empresa;

        if ($empresa === null || $empresa->limite === null || $this->vigencia_hasta === null) {
            return false;
        }

        if ($empresa->estado_limite === 'VENCIDA') {
            return false;
        }

        return $this->vigencia_hasta->isSameDay($empresa->limite);
    }

    public function tamanoLegible(): string
    {
        if ($this->tamano_bytes === null) {
            return '—';
        }

        if ($this->tamano_bytes < 1024) {
            return $this->tamano_bytes.' B';
        }

        if ($this->tamano_bytes < 1048576) {
            return round($this->tamano_bytes / 1024, 1).' KB';
        }

        return round($this->tamano_bytes / 1048576, 1).' MB';
    }

    public function urlPublica(): ?string
    {
        return PlanillaEmpresaStorage::urlPublica($this->archivo);
    }
}
