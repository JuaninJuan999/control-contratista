<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserUsabilidadSesion extends Model
{
    protected $table = 'user_usabilidad_sesiones';

    protected $fillable = [
        'user_id',
        'iniciada_at',
        'ultima_actividad_at',
        'finalizada_at',
        'segundos_activos',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'iniciada_at' => 'datetime',
            'ultima_actividad_at' => 'datetime',
            'finalizada_at' => 'datetime',
            'segundos_activos' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function estaAbierta(): bool
    {
        return $this->finalizada_at === null;
    }
}
