<?php

namespace App\Console\Commands;

use App\Services\AlertasPlanillaEmpresaService;
use Illuminate\Console\Command;

class EnviarAlertasPlanillaProximaVencer extends Command
{
    protected $signature = 'alertas:planilla-proxima-vencer
                            {--dias= : Días de anticipación (por defecto, config alertas_planilla.dias_anticipacion)}
                            {--dry-run : Listar empresas sin enviar correos}';

    protected $description = 'Envía alertas por correo cuando la fecha límite de empresas está próxima a vencer';

    public function handle(AlertasPlanillaEmpresaService $servicio): int
    {
        if (! config('alertas_planilla.habilitado')) {
            $this->warn('Las alertas de planilla están deshabilitadas (ALERTAS_PLANILLA_HABILITADAS=false).');

            return self::SUCCESS;
        }

        $dias = $this->option('dias') !== null
            ? (int) $this->option('dias')
            : null;

        if ($this->option('dry-run')) {
            $empresas = $servicio->empresasProximasAVencer($dias);

            if ($empresas->isEmpty()) {
                $this->info('No hay empresas próximas a vencer.');

                return self::SUCCESS;
            }

            $this->table(
                ['Empresa', 'NIT', 'Fecha límite', 'Días restantes', 'Correos empresa'],
                $empresas->map(fn ($e) => [
                    $e->nombre,
                    $e->nit ?? '—',
                    $e->limite?->format('d/m/Y'),
                    $e->dias_para_limite,
                    is_array($e->correos) ? implode(', ', $e->correos) : '—',
                ])->all()
            );

            return self::SUCCESS;
        }

        $resumen = $servicio->enviarProximasAVencer($dias);

        $this->info(sprintf(
            'Alertas procesadas: %d empresa(s), %d correo(s) a empresas, %d correo(s) interno(s), %d omitida(s).',
            $resumen['empresas'],
            $resumen['correos_empresa'],
            $resumen['correos_internos'],
            $resumen['omitidas'],
        ));

        return self::SUCCESS;
    }
}
