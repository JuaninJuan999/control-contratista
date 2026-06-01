<?php

namespace App\Support;

final class PlanillaContratistasColumnas
{
    /** @var list<string> */
    public const ENCABEZADOS = [
        'Documento',
        'Tipo de Documento',
        'Nombre y Apellido',
        'Interno / Externo',
        'ARL',
    ];

    public const ARCHIVO_PLANTILLA = 'plantilla-importacion-contratistas.xlsx';

    /**
     * @return array<string, list<string>>
     */
    public static function aliasEncabezados(): array
    {
        return [
            'documento' => ['documento', 'cedula', 'cédula', 'numero documento', 'número documento', 'doc', 'identificacion', 'identificación'],
            'tipo_documento' => ['tipo de documento', 'tipo documento', 'tipo doc'],
            'nombres_apellidos' => ['nombre y apellido', 'nombres y apellidos', 'nombre', 'nombres', 'contratista'],
            'tipo_contratista' => ['interno / externo', 'interno/externo', 'tipo contratista', 'tipo', 'interno externo'],
            'arl' => ['arl'],
        ];
    }
}
