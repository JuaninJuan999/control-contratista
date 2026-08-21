<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Alertas de planilla de seguridad social
    |--------------------------------------------------------------------------
    |
    | Recordatorios cuando la fecha límite SS de empresas (dependiente) o contratistas
    | internos (independiente) está próxima a vencer.
    |
    */

    'habilitado' => env('ALERTAS_PLANILLA_HABILITADAS', true),

    /** Días restantes exactos en los que se envía alerta de proximidad (máx. 2 envíos). */
    'dias_alertas_proxima' => array_map('intval', array_filter(array_map(
        trim(...),
        explode(',', (string) env('ALERTAS_PLANILLA_DIAS_PROXIMA', '10,5'))
    ))),

    /** Días transcurridos tras el vencimiento para el único aviso de vigencia vencida. */
    'dias_alerta_vencida' => (int) env('ALERTAS_PLANILLA_DIAS_VENCIDA', 10),

    /** @deprecated Use dias_alertas_proxima. Mantenido por compatibilidad. */
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
