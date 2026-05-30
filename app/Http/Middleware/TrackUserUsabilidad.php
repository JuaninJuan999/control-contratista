<?php

namespace App\Http\Middleware;

use App\Services\UserUsabilidadTracker;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrackUserUsabilidad
{
    public function __construct(
        private readonly UserUsabilidadTracker $tracker
    ) {}

    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $usuario = $request->user();

        if ($usuario !== null && ! $request->routeIs('login', 'login.store')) {
            $this->tracker->registrarActividad($usuario);
        }

        return $next($request);
    }
}
