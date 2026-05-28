<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCanAccessUsuariosModule
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()?->puedeAccederModuloUsuarios()) {
            return $next($request);
        }

        abort(403, 'No tiene acceso al módulo de gestión de usuarios.');
    }
}
