<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Corrige query strings mal formadas cuando nginx usa
 * try_files ... /index.php?$is_args$args (doble "?").
 *
 * Ejemplo: ?buscar=TRANS llega como clave "?buscar" en lugar de "buscar".
 */
class FixNginxQueryString
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $corregido = false;

        foreach ($request->query->all() as $clave => $valor) {
            if (! is_string($clave) || ! str_starts_with($clave, '?')) {
                continue;
            }

            $claveCorregida = substr($clave, 1);

            if ($claveCorregida === '' || $request->query->has($claveCorregida)) {
                continue;
            }

            $request->query->set($claveCorregida, $valor);
            $request->query->remove($clave);
            $corregido = true;
        }

        if ($corregido) {
            $request->server->set('QUERY_STRING', http_build_query($request->query->all()));
        }

        return $next($request);
    }
}
