<?php

use App\Http\Middleware\EnsureCanAccessUsuariosModule;
use App\Http\Middleware\EnsureSuperadmin;
use App\Http\Middleware\RestrictConsultaAccess;
use App\Http\Middleware\TrackUserUsabilidad;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'restrict.consulta' => RestrictConsultaAccess::class,
            'access.usuarios' => EnsureCanAccessUsuariosModule::class,
            'access.superadmin' => EnsureSuperadmin::class,
        ]);

        $middleware->appendToGroup('web', TrackUserUsabilidad::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
