<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserUsabilidadSesion;
use Illuminate\Support\Carbon;

class UserUsabilidadTracker
{
    public function inactividadSegundos(): int
    {
        return max(60, (int) config('usabilidad.inactividad_segundos', 900));
    }

    public function iniciarSesion(User $user): UserUsabilidadSesion
    {
        $this->cerrarSesionesAbiertas($user);

        return $this->crearSesion($user, now());
    }

    public function registrarActividad(User $user): void
    {
        $sesion = $this->sesionAbierta($user);
        $ahora = now();

        if ($sesion === null) {
            $this->crearSesion($user, $ahora);

            return;
        }

        $gap = (int) $sesion->ultima_actividad_at->diffInSeconds($ahora);

        if ($gap > $this->inactividadSegundos()) {
            $this->finalizarSesion($sesion, $sesion->ultima_actividad_at);
            $this->crearSesion($user, $ahora);

            return;
        }

        if ($gap > 0) {
            $sesion->segundos_activos += $gap;
            $sesion->ultima_actividad_at = $ahora;
            $sesion->save();
        }
    }

    public function cerrarSesionActual(User $user): void
    {
        $sesion = $this->sesionAbierta($user);

        if ($sesion === null) {
            return;
        }

        $this->finalizarSesion($sesion, now());
    }

    public function sesionAbierta(User $user): ?UserUsabilidadSesion
    {
        return UserUsabilidadSesion::query()
            ->where('user_id', $user->id)
            ->whereNull('finalizada_at')
            ->latest('id')
            ->first();
    }

    private function cerrarSesionesAbiertas(User $user): void
    {
        UserUsabilidadSesion::query()
            ->where('user_id', $user->id)
            ->whereNull('finalizada_at')
            ->orderBy('id')
            ->each(fn (UserUsabilidadSesion $sesion) => $this->finalizarSesion(
                $sesion,
                $sesion->ultima_actividad_at
            ));
    }

    private function crearSesion(User $user, Carbon $momento): UserUsabilidadSesion
    {
        return UserUsabilidadSesion::query()->create([
            'user_id' => $user->id,
            'iniciada_at' => $momento,
            'ultima_actividad_at' => $momento,
            'segundos_activos' => 0,
        ]);
    }

    private function finalizarSesion(UserUsabilidadSesion $sesion, Carbon $momento): void
    {
        if ($sesion->finalizada_at !== null) {
            return;
        }

        $gap = (int) $sesion->ultima_actividad_at->diffInSeconds($momento);

        if ($gap > 0) {
            $sesion->segundos_activos += min($gap, $this->inactividadSegundos());
        }

        $sesion->finalizada_at = $momento;
        $sesion->save();
    }
}
