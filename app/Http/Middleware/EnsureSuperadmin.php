<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSuperadmin
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()?->esSuperadmin()) {
            return $next($request);
        }

        abort(403, 'Solo el superadministrador puede acceder a este módulo.');
    }
}
