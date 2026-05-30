<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\BusquedaGlobalController;
use App\Http\Controllers\ContratistaExternoController;
use App\Http\Controllers\ContratistaInternoController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmpresaController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UsabilidadController;
use App\Http\Controllers\VehiculoController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route(
        auth()->check() ? 'dashboard' : 'login'
    );
});

Route::middleware('guest')->group(function () {
    Route::get('login', [LoginController::class, 'create'])->name('login');
    Route::post('login', [LoginController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('login.store');
});

Route::middleware(['auth', 'restrict.consulta'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/buscar', [BusquedaGlobalController::class, 'index'])->name('busqueda.global');
    Route::get('/buscar/sugerencias', [BusquedaGlobalController::class, 'sugerencias'])->name('busqueda.sugerencias');
    Route::resource('empresas', EmpresaController::class)->except(['show']);
    Route::resource('contratistas-externos', ContratistaExternoController::class)->except(['show', 'destroy']);
    Route::patch('contratistas-externos/{contratistas_externo}/activo', [ContratistaExternoController::class, 'toggleActivo'])
        ->name('contratistas-externos.toggle-activo');
    Route::patch('contratistas-externos/{contratistaExterno}/mes', [ContratistaExternoController::class, 'toggleMes'])
        ->name('contratistas-externos.toggle-mes');
    Route::resource('contratistas-internos', ContratistaInternoController::class)->except(['show', 'destroy']);
    Route::patch('contratistas-internos/{contratistas_interno}/activo', [ContratistaInternoController::class, 'toggleActivo'])
        ->name('contratistas-internos.toggle-activo');
    Route::patch('contratistas-internos/{contratistaInterno}/mes', [ContratistaInternoController::class, 'toggleMes'])
        ->name('contratistas-internos.toggle-mes');
    Route::resource('vehiculos', VehiculoController::class)->except(['show', 'destroy']);

    Route::middleware('access.usuarios')->group(function () {
        Route::resource('usuarios', UserController::class)->except(['show']);
        Route::patch('usuarios/{usuario}/activo', [UserController::class, 'toggleActivo'])
            ->name('usuarios.toggle-activo');
    });

    Route::middleware('access.superadmin')->group(function () {
        Route::get('usabilidad', [UsabilidadController::class, 'index'])->name('usabilidad.index');
    });

    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');
});
