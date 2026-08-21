<?php

namespace App\Console\Commands;

use App\Services\AlertasPlanillaEmpresaService;
use App\Support\AlertaPlanillaHito;
use Illuminate\Console\Command;

class EnviarAlertasPlanillaProximaVencer extends Command
{
    protected $signature = 'alertas:planilla-proxima-vencer
                            {--dry-run : Listar registros con hito de hoy sin enviar correos}';

    protected $description = 'Envía alertas SS en hitos fijos: 10 días antes, 5 días antes y 10 días después del vencimiento';

    public function handle(AlertasPlanillaEmpresaService $servicio): int
    {
        if (! config('alertas_planilla.habilitado')) {
            $this->warn('Las alertas de planilla están deshabilitadas (ALERTAS_PLANILLA_HABILITADAS=false).');

            return self::SUCCESS;
        }

        $this->info('Hitos configurados: proximidad '.implode(', ', config('alertas_planilla.dias_alertas_proxima', [10, 5]))
            .' días · vencida '.config('alertas_planilla.dias_alerta_vencida', 10).' días después.');

        if ($this->option('dry-run')) {
            $empresas = $servicio->empresasConHitoHoy();
            $contratistas = $servicio->contratistasConHitoHoy();

            if ($empresas->isEmpty() && $contratistas->isEmpty()) {
                $this->info('Hoy no corresponde enviar alertas (ningún hito coincide).');

                return self::SUCCESS;
            }

            if ($empresas->isNotEmpty()) {
                $this->info('Empresas (planilla dependiente) — alertas de hoy:');
                $this->table(
                    ['Empresa', 'Hito', 'Días al límite', 'Fecha límite', 'Correos empresa'],
                    $empresas->map(fn ($fila) => [
                        $fila['empresa']->nombre,
                        AlertaPlanillaHito::etiqueta($fila['hito']),
                        $fila['dias'],
                        $fila['empresa']->limite?->format('d/m/Y'),
                        is_array($fila['empresa']->correos) ? implode(', ', $fila['empresa']->correos) : '—',
                    ])->all()
                );
            }

            if ($contratistas->isNotEmpty()) {
                $this->newLine();
                $this->info('Contratistas internos (planilla independiente) — alertas de hoy:');
                $this->table(
                    ['Contratista', 'Empresa', 'Hito', 'Días al límite', 'Fecha límite SS'],
                    $contratistas->map(fn ($fila) => [
                        $fila['contratista']->nombres_apellidos,
                        $fila['contratista']->empresa?->nombre ?? '—',
                        AlertaPlanillaHito::etiqueta($fila['hito']),
                        $fila['dias'],
                        $fila['contratista']->limiteEfectivo()?->format('d/m/Y'),
                    ])->all()
                );
            }

            return self::SUCCESS;
        }

        $resumenEmpresas = $servicio->enviarProximasAVencer();
        $resumenContratistas = $servicio->enviarContratistasIndependientesProximosAVencer();

        $this->info(sprintf(
            'Empresas dependientes: %d alerta(s) enviada(s), %d correo(s) a empresas, %d correo(s) interno(s), %d omitida(s).',
            $resumenEmpresas['empresas'],
            $resumenEmpresas['correos_empresa'],
            $resumenEmpresas['correos_internos'],
            $resumenEmpresas['omitidas'],
        ));

        $this->info(sprintf(
            'Contratistas independientes: %d alerta(s) enviada(s), %d correo(s) a empresas, %d correo(s) interno(s), %d omitido(s).',
            $resumenContratistas['contratistas'],
            $resumenContratistas['correos_empresa'],
            $resumenContratistas['correos_internos'],
            $resumenContratistas['omitidas'],
        ));

        return self::SUCCESS;
    }
}
