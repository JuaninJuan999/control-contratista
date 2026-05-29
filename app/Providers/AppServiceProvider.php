<?php

namespace App\Providers;

use App\Services\BusquedaGlobalIndice;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('layouts.app', function ($view): void {
            if (! auth()->check()) {
                return;
            }

            $view->with('busquedaGlobalIndice', app(BusquedaGlobalIndice::class)->items());
        });
    }
}
