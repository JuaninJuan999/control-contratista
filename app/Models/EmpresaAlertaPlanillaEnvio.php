<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmpresaAlertaPlanillaEnvio extends Model
{
    public const UPDATED_AT = null;

    public const CANAL_EMPRESA = 'empresa';

    public const CANAL_INTERNO = 'interno';

    protected $fillable = [
        'empresa_id',
        'canal',
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

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }
}
