<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RestrictConsultaAccess
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $usuario = $request->user();

        if ($usuario === null || $usuario->puedeEditar()) {
            return $next($request);
        }

        if ($request->routeIs('logout')) {
            return $next($request);
        }

        if (in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
            abort(403, 'Su rol de consulta no permite realizar cambios.');
        }

        if ($request->routeIs('*.create', '*.edit')) {
            abort(403, 'Su rol de consulta solo permite visualizar información.');
        }

        return $next($request);
    }
}
