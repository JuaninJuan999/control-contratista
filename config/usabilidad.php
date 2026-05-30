<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Inactividad máxima (segundos)
    |--------------------------------------------------------------------------
    |
    | Si un usuario no realiza peticiones durante este tiempo, la sesión de
    | usabilidad se cierra y no se contabiliza el tiempo de inactividad.
    |
    */

    'inactividad_segundos' => (int) env('USABILIDAD_INACTIVIDAD_SEGUNDOS', 900),

];
