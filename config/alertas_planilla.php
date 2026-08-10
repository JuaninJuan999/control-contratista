<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Alertas de planilla de seguridad social
    |--------------------------------------------------------------------------
    |
    | Recordatorios cuando la fecha límite de una empresa está próxima a vencer.
    |
    */

    'habilitado' => env('ALERTAS_PLANILLA_HABILITADAS', true),

    'dias_anticipacion' => (int) env('ALERTAS_PLANILLA_DIAS', 10),

    /** @var list<string> */
    'correos_internos' => array_values(array_filter(array_map(
        trim(...),
        explode('|', (string) env(
            'ALERTAS_PLANILLA_CORREOS_INTERNOS',
            'siso@colbeef.com|aux.siso@colbeef.com|practicante.siso@colbeef.com'
        ))
    ))),

    'hora_envio' => env('ALERTAS_PLANILLA_HORA', '07:00'),

    'zona_horaria' => env('ALERTAS_PLANILLA_ZONA', 'America/Bogota'),

];
