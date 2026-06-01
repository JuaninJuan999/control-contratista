<?php

namespace App\Http\Middleware;

use App\Services\SesionUsuarioService;
use App\Services\UserUsabilidadTracker;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrackUserUsabilidad
{
    public function __construct(
        private readonly UserUsabilidadTracker $tracker,
        private readonly SesionUsuarioService $sesionUsuario
    ) {}

    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $usuario = $request->user();

        if ($usuario !== null && ! $request->routeIs('login', 'login.store', 'logout')) {
            if (
                config('usabilidad.cerrar_sesion_por_inactividad')
                && $this->tracker->expiradaPorInactividad($usuario)
            ) {
                return $this->sesionUsuario->cerrar($request, porInactividad: true);
            }

            $this->tracker->registrarActividad($usuario);
        }

        return $next($request);
    }
}
