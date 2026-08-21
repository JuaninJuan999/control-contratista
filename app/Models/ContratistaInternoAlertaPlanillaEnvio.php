<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContratistaInternoAlertaPlanillaEnvio extends Model
{
    public const UPDATED_AT = null;

    public const CANAL_EMPRESA = 'empresa';

    public const CANAL_INTERNO = 'interno';

    protected $fillable = [
        'contratista_interno_id',
        'canal',
        'hito',
        'vigencia_hasta',
        'fecha_envio',
    ];

    protected function casts(): array
    {
        return [
            'vigencia_hasta' => 'date',
            'fecha_envio' => 'date',
        ];
    }

    public function contratistaInterno(): BelongsTo
    {
        return $this->belongsTo(ContratistaInterno::class);
    }
}
