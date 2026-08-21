<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\BusquedaGlobalController;
use App\Http\Controllers\ContratistaExternoController;
use App\Http\Controllers\ContratistaInternoController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmpresaController;
use App\Http\Controllers\PlanillaController;
use App\Http\Controllers\PlanillaContratistaController;
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

    Route::middleware('access.usuarios')->group(function () {
        Route::get('empresas/planilla/plantilla', [PlanillaContratistaController::class, 'plantilla'])
            ->name('empresas.planilla.plantilla');
    });

    Route::resource('empresas', EmpresaController::class)->except(['show']);

    Route::get('planillas', [PlanillaController::class, 'index'])->name('planillas.index');
    Route::get('planillas/archivo/{archivo}/descargar', [PlanillaController::class, 'descargar'])
        ->name('planillas.archivo.descargar');

    Route::middleware('access.usuarios')->group(function () {
        Route::get('empresas/{empresa}/planilla/importar', [PlanillaContratistaController::class, 'create'])
            ->name('empresas.planilla.create');
        Route::post('empresas/{empresa}/planilla/vista-previa', [PlanillaContratistaController::class, 'preview'])
            ->name('empresas.planilla.preview');
        Route::post('empresas/{empresa}/planilla/importar', [PlanillaContratistaController::class, 'importar'])
            ->name('empresas.planilla.importar');
    });

    Route::post('planillas/{empresa}/archivo', [PlanillaController::class, 'storeArchivo'])
        ->name('planillas.archivo.store');
    Route::patch('planillas/{empresa}/tipo', [PlanillaController::class, 'updateTipo'])
        ->name('planillas.tipo.update');
    Route::delete('planillas/archivo/{archivo}', [PlanillaController::class, 'destroyArchivo'])
        ->name('planillas.archivo.destroy');

    Route::resource('contratistas-externos', ContratistaExternoController::class)->except(['show']);
    Route::patch('contratistas-externos/{contratistas_externo}/activo', [ContratistaExternoController::class, 'toggleActivo'])
        ->name('contratistas-externos.toggle-activo');
    Route::resource('contratistas-internos', ContratistaInternoController::class)->except(['show']);
    Route::patch('contratistas-internos/{contratistas_interno}/activo', [ContratistaInternoController::class, 'toggleActivo'])
        ->name('contratistas-internos.toggle-activo');
    Route::patch('contratistas-internos/{contratistaInterno}/mes', [ContratistaInternoController::class, 'toggleMes'])
        ->name('contratistas-internos.toggle-mes');
    Route::get('contratistas-internos/planilla/{archivo}/descargar', [ContratistaInternoController::class, 'descargarPlanilla'])
        ->name('contratistas-internos.planilla.descargar');
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
