<?php

use App\Console\Commands\EnviarAlertasPlanillaProximaVencer;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command(EnviarAlertasPlanillaProximaVencer::class)
    ->dailyAt(config('alertas_planilla.hora_envio', '07:00'))
    ->timezone(config('alertas_planilla.zona_horaria', 'America/Bogota'))
    ->when(fn () => config('alertas_planilla.habilitado'));
