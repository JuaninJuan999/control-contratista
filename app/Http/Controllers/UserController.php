<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(): View
    {
        $usuarios = User::query()
            ->orderByDesc('activo')
            ->orderBy('nombre')
            ->orderBy('apellido')
            ->get();

        return view('usuarios.index', compact('usuarios'));
    }

    public function create(): View
    {
        return view('usuarios.create');
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        User::query()->create([
            'nombre' => $validated['nombre'],
            'apellido' => $validated['apellido'],
            'name' => trim($validated['nombre'].' '.$validated['apellido']),
            'username' => $validated['username'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'activo' => true,
            'rol' => $validated['rol'],
        ]);

        return redirect()
            ->route('usuarios.index')
            ->with('success', 'Usuario creado correctamente.');
    }

    public function edit(User $usuario): View|RedirectResponse
    {
        if ($redirect = $this->rechazarSiNoPuedeGestionar($usuario)) {
            return $redirect;
        }

        return view('usuarios.edit', compact('usuario'));
    }

    public function update(UpdateUserRequest $request, User $usuario): RedirectResponse
    {
        if ($redirect = $this->rechazarSiNoPuedeGestionar($usuario)) {
            return $redirect;
        }

        $validated = $request->validated();
        $datos = [
            'nombre' => $validated['nombre'],
            'apellido' => $validated['apellido'],
            'name' => trim($validated['nombre'].' '.$validated['apellido']),
            'username' => $validated['username'],
            'email' => $validated['email'],
            'rol' => $validated['rol'],
        ];

        if (! empty($validated['password'])) {
            $datos['password'] = Hash::make($validated['password']);
        }

        $usuario->update($datos);

        return redirect()
            ->route('usuarios.index')
            ->with('success', 'Usuario actualizado correctamente.');
    }

    public function toggleActivo(User $usuario): RedirectResponse
    {
        if ($redirect = $this->rechazarSiNoPuedeGestionar($usuario)) {
            return $redirect;
        }

        if ($usuario->is(Auth::user())) {
            return redirect()
                ->route('usuarios.index')
                ->with('error', 'No puedes inactivar tu propio usuario mientras tienes la sesión abierta.');
        }

        $usuario->update(['activo' => ! $usuario->activo]);

        $mensaje = $usuario->activo
            ? 'Usuario reactivado correctamente.'
            : 'Usuario inactivado correctamente.';

        return redirect()
            ->route('usuarios.index')
            ->with('success', $mensaje);
    }

    public function destroy(User $usuario): RedirectResponse
    {
        if ($redirect = $this->rechazarSiNoPuedeGestionar($usuario)) {
            return $redirect;
        }

        if (! Auth::user()?->puedeEliminarUsuarios()) {
            return redirect()
                ->route('usuarios.index')
                ->with('error', 'No tiene permiso para eliminar usuarios.');
        }

        if ($usuario->is(Auth::user())) {
            return redirect()
                ->route('usuarios.index')
                ->with('error', 'No puedes eliminar tu propio usuario mientras tienes la sesión abierta.');
        }

        $usuario->delete();

        return redirect()
            ->route('usuarios.index')
            ->with('success', 'Usuario eliminado correctamente.');
    }

    private function rechazarSiNoPuedeGestionar(User $usuario): ?RedirectResponse
    {
        if ($usuario->puedeSerGestionadoPor(Auth::user())) {
            return null;
        }

        return redirect()
            ->route('usuarios.index')
            ->with('error', 'No tiene permiso para modificar un usuario Superadministrador.');
    }
}
