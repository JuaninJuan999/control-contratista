<?php

namespace App\Models;

use App\Services\PlanillaContratistaInternoStorage;
use App\Support\PeriodoPlanilla;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContratistaInternoPlanillaArchivo extends Model
{
    protected $table = 'contratista_interno_planilla_archivos';

    protected $fillable = [
        'contratista_interno_id',
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

    public function contratistaInterno(): BelongsTo
    {
        return $this->belongsTo(ContratistaInterno::class);
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function periodoEtiqueta(): string
    {
        return PeriodoPlanilla::etiqueta($this->periodo_anio, $this->periodo_mes, true);
    }

    public function esVigenciaActual(): bool
    {
        $contratista = $this->contratistaInterno;

        if ($contratista === null || $this->vigencia_hasta === null) {
            return false;
        }

        $limite = $contratista->limiteEfectivo();

        if ($limite === null || $contratista->estadoLimiteSs() === 'VENCIDA') {
            return false;
        }

        return $this->vigencia_hasta->isSameDay($limite);
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
        return PlanillaContratistaInternoStorage::urlPublica($this->archivo);
    }
}
