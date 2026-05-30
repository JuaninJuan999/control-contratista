<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserUsabilidadSesion;
use App\Support\DuracionFormateada;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class UsabilidadController extends Controller
{
    public function index(Request $request): View
    {
        $desde = $this->parseFecha($request->input('desde'), now()->startOfMonth(), false);
        $hasta = $this->parseFecha($request->input('hasta'), now(), true);
        $usuarioId = $request->filled('usuario') ? (int) $request->input('usuario') : null;

        if ($desde->greaterThan($hasta)) {
            [$desde, $hasta] = [$hasta->copy()->startOfDay(), $desde->copy()->endOfDay()];
        }

        $sesiones = UserUsabilidadSesion::query()
            ->with('user:id,nombre,apellido,username,rol')
            ->when($usuarioId, fn ($query) => $query->where('user_id', $usuarioId))
            ->where('iniciada_at', '<=', $hasta)
            ->where(function ($query) use ($desde): void {
                $query->whereNull('finalizada_at')
                    ->orWhere('finalizada_at', '>=', $desde);
            })
            ->orderByDesc('iniciada_at')
            ->get();

        $resumenUsuarios = $sesiones
            ->groupBy('user_id')
            ->map(function ($grupo) {
                $usuario = $grupo->first()->user;

                return [
                    'usuario' => $usuario,
                    'sesiones' => $grupo->count(),
                    'segundos' => (int) $grupo->sum('segundos_activos'),
                    'ultima_actividad' => $grupo->max('ultima_actividad_at'),
                ];
            })
            ->sortByDesc('segundos')
            ->values();

        $totalSegundos = (int) $sesiones->sum('segundos_activos');
        $sesionesActivas = UserUsabilidadSesion::query()
            ->with('user:id,username,nombre,apellido')
            ->whereNull('finalizada_at')
            ->orderByDesc('ultima_actividad_at')
            ->get();

        $usuarios = User::query()
            ->orderBy('username')
            ->get(['id', 'username', 'nombre', 'apellido']);

        $graficas = $this->prepararDatosGraficas($sesiones, $resumenUsuarios, $desde, $hasta);

        return view('usabilidad.index', compact(
            'desde',
            'hasta',
            'usuarioId',
            'sesiones',
            'resumenUsuarios',
            'totalSegundos',
            'sesionesActivas',
            'usuarios',
            'graficas',
        ));
    }

    /**
     * @param  \Illuminate\Support\Collection<int, UserUsabilidadSesion>  $sesiones
     * @param  \Illuminate\Support\Collection<int, array{usuario: ?User, sesiones: int, segundos: int, ultima_actividad: mixed}>  $resumenUsuarios
     * @return array<string, mixed>
     */
    private function prepararDatosGraficas($sesiones, $resumenUsuarios, Carbon $desde, Carbon $hasta): array
    {
        $porDia = [];
        $cursor = $desde->copy()->startOfDay();

        while ($cursor->lessThanOrEqualTo($hasta)) {
            $clave = $cursor->format('Y-m-d');
            $porDia[$clave] = [
                'label' => $cursor->format('d/m'),
                'segundos' => 0,
                'sesiones' => 0,
            ];
            $cursor->addDay();
        }

        foreach ($sesiones as $sesion) {
            $clave = $sesion->iniciada_at->format('Y-m-d');

            if (! array_key_exists($clave, $porDia)) {
                continue;
            }

            $porDia[$clave]['segundos'] += (int) $sesion->segundos_activos;
            $porDia[$clave]['sesiones']++;
        }

        $coloresUsuarios = ['#10b981', '#0ea5e9', '#8b5cf6', '#f59e0b', '#ef4444', '#14b8a6', '#6366f1', '#ec4899'];

        return [
            'por_usuario' => [
                'labels' => $resumenUsuarios->map(fn (array $fila) => $fila['usuario']?->username ?? '—')->values()->all(),
                'minutos' => $resumenUsuarios->map(fn (array $fila) => round($fila['segundos'] / 60, 1))->values()->all(),
                'colores' => $resumenUsuarios->values()->map(
                    fn ($fila, int $i) => $coloresUsuarios[$i % count($coloresUsuarios)]
                )->all(),
            ],
            'por_dia' => [
                'labels' => array_column(array_values($porDia), 'label'),
                'minutos' => array_map(
                    fn (array $dia) => round($dia['segundos'] / 60, 1),
                    array_values($porDia)
                ),
                'sesiones' => array_column(array_values($porDia), 'sesiones'),
            ],
            'distribucion' => [
                'labels' => $resumenUsuarios->map(fn (array $fila) => $fila['usuario']?->username ?? '—')->values()->all(),
                'segundos' => $resumenUsuarios->map(fn (array $fila) => (int) $fila['segundos'])->values()->all(),
                'colores' => $resumenUsuarios->values()->map(
                    fn ($fila, int $i) => $coloresUsuarios[$i % count($coloresUsuarios)]
                )->all(),
            ],
        ];
    }

    private function parseFecha(?string $valor, Carbon $default, bool $finDeDia = false): Carbon
    {
        if ($valor === null || trim($valor) === '') {
            $fecha = $default->copy();
        } else {
            try {
                $fecha = Carbon::parse($valor);
            } catch (\Throwable) {
                $fecha = $default->copy();
            }
        }

        return $finDeDia ? $fecha->copy()->endOfDay() : $fecha->copy()->startOfDay();
    }
}
