<?php

namespace App\Services;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SesionUsuarioService
{
    public function __construct(
        private readonly UserUsabilidadTracker $usabilidadTracker
    ) {}

    public function cerrar(Request $request, bool $porInactividad = false): RedirectResponse
    {
        $usuario = $request->user();

        if ($usuario !== null) {
            if ($porInactividad) {
                $this->usabilidadTracker->cerrarSesionPorInactividad($usuario);
            } else {
                $this->usabilidadTracker->cerrarSesionActual($usuario);
            }
        }

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        $redirect = redirect()->route('login');

        if ($porInactividad) {
            $minutos = (int) ceil($this->usabilidadTracker->inactividadSegundos() / 60);

            return $redirect->with(
                'status',
                "Su sesión se cerró por inactividad de {$minutos} minutos sin uso."
            );
        }

        return $redirect;
    }
}
