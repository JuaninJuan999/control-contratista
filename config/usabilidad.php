<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Inactividad máxima (segundos)
    |--------------------------------------------------------------------------
    |
    | Si un usuario no realiza peticiones durante este tiempo, la sesión de
    | usabilidad se cierra y, si cerrar_sesion_por_inactividad es true, también
    | se cierra la sesión de autenticación del sistema.
    |
    */

    'inactividad_segundos' => (int) env('USABILIDAD_INACTIVIDAD_SEGUNDOS', 900),

    'cerrar_sesion_por_inactividad' => filter_var(
        env('USABILIDAD_CERRAR_SESION', true),
        FILTER_VALIDATE_BOOL
    ),

];
