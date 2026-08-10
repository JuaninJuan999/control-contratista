<?php

namespace App\Services;

use App\Mail\PlanillaProximaVencerEmpresaMail;
use App\Mail\PlanillaProximaVencerInternoMail;
use App\Models\Empresa;
use App\Models\EmpresaAlertaPlanillaEnvio;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class AlertasPlanillaEmpresaService
{
    /** @return array{empresas: int, correos_empresa: int, correos_internos: int, omitidas: int} */
    public function enviarProximasAVencer(?int $diasAnticipacion = null): array
    {
        if (! config('alertas_planilla.habilitado')) {
            return ['empresas' => 0, 'correos_empresa' => 0, 'correos_internos' => 0, 'omitidas' => 0];
        }

        $dias = $diasAnticipacion ?? (int) config('alertas_planilla.dias_anticipacion', 10);
        $fechaEnvio = now()->startOfDay();
        $correosInternos = config('alertas_planilla.correos_internos', []);

        $resumen = [
            'empresas' => 0,
            'correos_empresa' => 0,
            'correos_internos' => 0,
            'omitidas' => 0,
        ];

        foreach ($this->empresasProximasAVencer($dias) as $empresa) {
            $diasRestantes = (int) $empresa->dias_para_limite;
            $vigenciaHasta = $empresa->limite->copy()->startOfDay();

            $envioEmpresa = $this->enviarCorreoEmpresa($empresa, $diasRestantes, $vigenciaHasta, $fechaEnvio);
            $envioInterno = $this->enviarCorreoInterno($empresa, $diasRestantes, $vigenciaHasta, $fechaEnvio, $correosInternos);

            if (! $envioEmpresa && ! $envioInterno) {
                $resumen['omitidas']++;

                continue;
            }

            $resumen['empresas']++;
            $resumen['correos_empresa'] += $envioEmpresa ? 1 : 0;
            $resumen['correos_internos'] += $envioInterno ? 1 : 0;
        }

        return $resumen;
    }

    /** @return Collection<int, Empresa> */
    public function empresasProximasAVencer(?int $diasAnticipacion = null): Collection
    {
        $dias = $diasAnticipacion ?? (int) config('alertas_planilla.dias_anticipacion', 10);
        $hoy = now()->startOfDay();
        $tope = $hoy->copy()->addDays($dias)->endOfDay();

        return Empresa::query()
            ->whereNotNull('limite')
            ->whereBetween('limite', [$hoy, $tope])
            ->orderBy('limite')
            ->orderBy('nombre')
            ->get()
            ->filter(fn (Empresa $empresa) => $empresa->estado_limite === 'PRÓXIMA A VENCER');
    }

    private function enviarCorreoEmpresa(Empresa $empresa, int $diasRestantes, $vigenciaHasta, $fechaEnvio): bool
    {
        $correos = collect(is_array($empresa->correos) ? $empresa->correos : [])
            ->map(fn ($correo) => trim((string) $correo))
            ->filter(fn (string $correo) => filter_var($correo, FILTER_VALIDATE_EMAIL))
            ->unique()
            ->values();

        if ($correos->isEmpty()) {
            return false;
        }

        if ($this->yaEnviado($empresa, EmpresaAlertaPlanillaEnvio::CANAL_EMPRESA, $vigenciaHasta, $fechaEnvio)) {
            return false;
        }

        try {
            Mail::to($correos->all())->send(new PlanillaProximaVencerEmpresaMail($empresa, $diasRestantes));
            $this->registrarEnvio($empresa, EmpresaAlertaPlanillaEnvio::CANAL_EMPRESA, $vigenciaHasta, $fechaEnvio);

            return true;
        } catch (Throwable $exception) {
            Log::error('Error al enviar alerta de planilla a empresa', [
                'empresa_id' => $empresa->id,
                'correos' => $correos->all(),
                'error' => $exception->getMessage(),
            ]);

            return false;
        }
    }

    /** @param  list<string>  $correosInternos */
    private function enviarCorreoInterno(Empresa $empresa, int $diasRestantes, $vigenciaHasta, $fechaEnvio, array $correosInternos): bool
    {
        $correos = collect($correosInternos)
            ->map(fn ($correo) => trim((string) $correo))
            ->filter(fn (string $correo) => filter_var($correo, FILTER_VALIDATE_EMAIL))
            ->unique()
            ->values();

        if ($correos->isEmpty()) {
            return false;
        }

        if ($this->yaEnviado($empresa, EmpresaAlertaPlanillaEnvio::CANAL_INTERNO, $vigenciaHasta, $fechaEnvio)) {
            return false;
        }

        try {
            Mail::to($correos->all())->send(new PlanillaProximaVencerInternoMail($empresa, $diasRestantes));
            $this->registrarEnvio($empresa, EmpresaAlertaPlanillaEnvio::CANAL_INTERNO, $vigenciaHasta, $fechaEnvio);

            return true;
        } catch (Throwable $exception) {
            Log::error('Error al enviar alerta interna de planilla', [
                'empresa_id' => $empresa->id,
                'correos' => $correos->all(),
                'error' => $exception->getMessage(),
            ]);

            return false;
        }
    }

    private function yaEnviado(Empresa $empresa, string $canal, $vigenciaHasta, $fechaEnvio): bool
    {
        return EmpresaAlertaPlanillaEnvio::query()
            ->where('empresa_id', $empresa->id)
            ->where('canal', $canal)
            ->whereDate('vigencia_hasta', $vigenciaHasta)
            ->whereDate('fecha_envio', $fechaEnvio)
            ->exists();
    }

    private function registrarEnvio(Empresa $empresa, string $canal, $vigenciaHasta, $fechaEnvio): void
    {
        EmpresaAlertaPlanillaEnvio::query()->create([
            'empresa_id' => $empresa->id,
            'canal' => $canal,
            'vigencia_hasta' => $vigenciaHasta,
            'fecha_envio' => $fechaEnvio,
        ]);
    }
}
