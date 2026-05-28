<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\ContratistaExternoController;
use App\Http\Controllers\ContratistaInternoController;
use App\Http\Controllers\EmpresaController;
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

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
    Route::resource('empresas', EmpresaController::class)->except(['show']);
    Route::resource('contratistas-externos', ContratistaExternoController::class)->except(['show', 'destroy']);
    Route::patch('contratistas-externos/{contratistas_externo}/activo', [ContratistaExternoController::class, 'toggleActivo'])
        ->name('contratistas-externos.toggle-activo');
    Route::resource('contratistas-internos', ContratistaInternoController::class)->except(['show', 'destroy']);
    Route::patch('contratistas-internos/{contratistas_interno}/activo', [ContratistaInternoController::class, 'toggleActivo'])
        ->name('contratistas-internos.toggle-activo');
    Route::patch('contratistas-internos/{contratistaInterno}/mes', [ContratistaInternoController::class, 'toggleMes'])
        ->name('contratistas-internos.toggle-mes');
    Route::resource('vehiculos', VehiculoController::class)->except(['show', 'destroy']);
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');
});
