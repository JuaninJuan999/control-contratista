<?php

namespace App\Models;

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
    'arl',
    'meses_por_anio',
    'activo',
])]
class ContratistaInterno extends Model
{
    public const MESES = [
        1 => 'EN',
        2 => 'FE',
        3 => 'MA',
        4 => 'AB',
        5 => 'MY',
        6 => 'JN',
        7 => 'JL',
        8 => 'AG',
        9 => 'SE',
        10 => 'OC',
        11 => 'NO',
        12 => 'DI',
    ];

    protected $table = 'contratistas_internos';

    protected function casts(): array
    {
        return [
            'fecha_nacimiento' => 'date',
            'manipulador_alimentos' => 'boolean',
            'manipulador_vigencia' => 'date',
            'licencia_conduccion' => 'boolean',
            'licencia_vencimiento' => 'date',
            'meses_por_anio' => 'array',
            'activo' => 'boolean',
        ];
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    /**
     * @return list<int>
     */
    public function mesesRegistradosEn(int $anio): array
    {
        $registros = $this->meses_por_anio ?? [];
        $meses = $registros[(string) $anio] ?? $registros[$anio] ?? [];

        if (! is_array($meses)) {
            return [];
        }

        return array_values(array_unique(array_map('intval', $meses)));
    }

    public function mesRegistrado(int $anio, int $mes): bool
    {
        return in_array($mes, $this->mesesRegistradosEn($anio), true);
    }

    public function toggleMes(int $anio, int $mes): void
    {
        $registros = $this->meses_por_anio ?? [];
        $clave = (string) $anio;
        $meses = $this->mesesRegistradosEn($anio);

        if (in_array($mes, $meses, true)) {
            $meses = array_values(array_filter($meses, fn (int $m) => $m !== $mes));
        } else {
            $meses[] = $mes;
            sort($meses);
        }

        if ($meses === []) {
            unset($registros[$clave]);
        } else {
            $registros[$clave] = $meses;
        }

        $this->meses_por_anio = $registros === [] ? null : $registros;
        $this->save();
    }
}
