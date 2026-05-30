<?php

namespace App\Models;

use App\Models\Concerns\ContratistaComun;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'nombres_apellidos',
    'tipo_documento',
    'numero_documento',
    'fecha_nacimiento',
    'cargo',
    'manipulador_alimentos',
    'manipulador_vigencia',
    'manipulador_archivo',
    'licencia_conduccion',
    'licencia_archivo',
    'licencia_categoria',
    'licencia_vencimientos',
    'cedula_archivo',
    'empresa_id',
    'arl',
    'fecha_ultima_ir',
    'vigencia_dias',
    'meses_por_anio',
    'meses_rechazados',
    'activo',
])]
class ContratistaExterno extends Model
{
    use ContratistaComun;

    protected $table = 'contratistas_externos';
}
