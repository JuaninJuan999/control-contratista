<?php

namespace App\Services;

use App\Mail\PlanillaProximaVencerContratistaEmpresaMail;
use App\Mail\PlanillaProximaVencerContratistaInternoMail;
use App\Mail\PlanillaProximaVencerEmpresaMail;
use App\Mail\PlanillaProximaVencerInternoMail;
use App\Models\ContratistaInterno;
use App\Models\ContratistaInternoAlertaPlanillaEnvio;
use App\Models\Empresa;
use App\Models\EmpresaAlertaPlanillaEnvio;
use App\Support\AlertaPlanillaHito;
use App\Support\PlanillaTipo;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class AlertasPlanillaEmpresaService
{
    /** @return array{empresas: int, correos_empresa: int, correos_internos: int, omitidas: int} */
    public function enviarProximasAVencer(?int $diasAnticipacion = null): array
    {
        unset($diasAnticipacion);

        return $this->enviarAlertasEmpresasDependientes();
    }

    /** @return array{contratistas: int, correos_empresa: int, correos_internos: int, omitidas: int} */
    public function enviarContratistasIndependientesProximosAVencer(?int $diasAnticipacion = null): array
    {
        unset($diasAnticipacion);

        return $this->enviarAlertasContratistasIndependientes();
    }

    /** @return array{empresas: int, correos_empresa: int, correos_internos: int, omitidas: int} */
    private function enviarAlertasEmpresasDependientes(): array
    {
        if (! config('alertas_planilla.habilitado')) {
            return ['empresas' => 0, 'correos_empresa' => 0, 'correos_internos' => 0, 'omitidas' => 0];
        }

        $fechaEnvio = now()->startOfDay();
        $correosInternos = config('alertas_planilla.correos_internos', []);

        $resumen = [
            'empresas' => 0,
            'correos_empresa' => 0,
            'correos_internos' => 0,
            'omitidas' => 0,
        ];

        foreach ($this->empresasParaAlertar() as $empresa) {
            $hito = AlertaPlanillaHito::paraDias($empresa->dias_para_limite);

            if ($hito === null) {
                continue;
            }

            $diasRestantes = (int) $empresa->dias_para_limite;
            $vigenciaHasta = $empresa->limite->copy()->startOfDay();

            $envioEmpresa = $this->enviarCorreoEmpresa($empresa, $diasRestantes, $vigenciaHasta, $fechaEnvio, $hito);
            $envioInterno = $this->enviarCorreoInterno($empresa, $diasRestantes, $vigenciaHasta, $fechaEnvio, $correosInternos, $hito);

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

    /** @return array{contratistas: int, correos_empresa: int, correos_internos: int, omitidas: int} */
    private function enviarAlertasContratistasIndependientes(): array
    {
        if (! config('alertas_planilla.habilitado')) {
            return ['contratistas' => 0, 'correos_empresa' => 0, 'correos_internos' => 0, 'omitidas' => 0];
        }

        $fechaEnvio = now()->startOfDay();
        $correosInternos = config('alertas_planilla.correos_internos', []);

        $resumen = [
            'contratistas' => 0,
            'correos_empresa' => 0,
            'correos_internos' => 0,
            'omitidas' => 0,
        ];

        foreach ($this->contratistasIndependientesParaAlertar() as $contratista) {
            $hito = AlertaPlanillaHito::paraDias($contratista->diasParaLimiteSs());

            if ($hito === null) {
                continue;
            }

            $diasRestantes = (int) $contratista->diasParaLimiteSs();
            $vigenciaHasta = $contratista->limiteEfectivo()->copy()->startOfDay();

            $envioEmpresa = $this->enviarCorreoContratistaEmpresa($contratista, $diasRestantes, $vigenciaHasta, $fechaEnvio, $hito);
            $envioInterno = $this->enviarCorreoContratistaInterno($contratista, $diasRestantes, $vigenciaHasta, $fechaEnvio, $correosInternos, $hito);

            if (! $envioEmpresa && ! $envioInterno) {
                $resumen['omitidas']++;

                continue;
            }

            $resumen['contratistas']++;
            $resumen['correos_empresa'] += $envioEmpresa ? 1 : 0;
            $resumen['correos_internos'] += $envioInterno ? 1 : 0;
        }

        return $resumen;
    }

    /**
     * Empresas con hito de alerta hoy (solo planilla dependiente).
     *
     * @return Collection<int, array{empresa: Empresa, hito: string, dias: int}>
     */
    public function empresasConHitoHoy(): Collection
    {
        return $this->empresasParaAlertar()
            ->map(function (Empresa $empresa): ?array {
                $hito = AlertaPlanillaHito::paraDias($empresa->dias_para_limite);

                if ($hito === null) {
                    return null;
                }

                return [
                    'empresa' => $empresa,
                    'hito' => $hito,
                    'dias' => (int) $empresa->dias_para_limite,
                ];
            })
            ->filter()
            ->values();
    }

    /**
     * Contratistas independientes con hito de alerta hoy.
     *
     * @return Collection<int, array{contratista: ContratistaInterno, hito: string, dias: int}>
     */
    public function contratistasConHitoHoy(): Collection
    {
        return $this->contratistasIndependientesParaAlertar()
            ->map(function (ContratistaInterno $contratista): ?array {
                $dias = $contratista->diasParaLimiteSs();
                $hito = AlertaPlanillaHito::paraDias($dias);

                if ($hito === null) {
                    return null;
                }

                return [
                    'contratista' => $contratista,
                    'hito' => $hito,
                    'dias' => (int) $dias,
                ];
            })
            ->filter()
            ->values();
    }

    /** @return Collection<int, Empresa> */
    public function empresasProximasAVencer(?int $diasAnticipacion = null): Collection
    {
        unset($diasAnticipacion);

        return $this->empresasConHitoHoy()->pluck('empresa');
    }

    /** @return Collection<int, ContratistaInterno> */
    public function contratistasIndependientesProximosAVencer(?int $diasAnticipacion = null): Collection
    {
        unset($diasAnticipacion);

        return $this->contratistasConHitoHoy()->pluck('contratista');
    }

    /** @return Collection<int, Empresa> */
    private function empresasParaAlertar(): Collection
    {
        return Empresa::query()
            ->whereNotNull('limite')
            ->where('planilla', PlanillaTipo::DEPENDIENTE)
            ->orderBy('limite')
            ->orderBy('nombre')
            ->get();
    }

    /** @return Collection<int, ContratistaInterno> */
    private function contratistasIndependientesParaAlertar(): Collection
    {
        return ContratistaInterno::query()
            ->where('activo', true)
            ->where('tipo_planilla', PlanillaTipo::INDEPENDIENTE)
            ->whereNotNull('limite')
            ->with(['empresa', 'planillaArchivos'])
            ->orderBy('limite')
            ->orderBy('nombres_apellidos')
            ->get();
    }

    private function enviarCorreoEmpresa(Empresa $empresa, int $diasRestantes, $vigenciaHasta, $fechaEnvio, string $hito): bool
    {
        $correos = collect(is_array($empresa->correos) ? $empresa->correos : [])
            ->map(fn ($correo) => trim((string) $correo))
            ->filter(fn (string $correo) => filter_var($correo, FILTER_VALIDATE_EMAIL))
            ->unique()
            ->values();

        if ($correos->isEmpty()) {
            return false;
        }

        if ($this->yaEnviado($empresa, EmpresaAlertaPlanillaEnvio::CANAL_EMPRESA, $vigenciaHasta, $hito)) {
            return false;
        }

        try {
            Mail::to($correos->all())->send(new PlanillaProximaVencerEmpresaMail($empresa, $diasRestantes, $hito));
            $this->registrarEnvio($empresa, EmpresaAlertaPlanillaEnvio::CANAL_EMPRESA, $vigenciaHasta, $fechaEnvio, $hito);

            return true;
        } catch (Throwable $exception) {
            Log::error('Error al enviar alerta de planilla a empresa', [
                'empresa_id' => $empresa->id,
                'hito' => $hito,
                'correos' => $correos->all(),
                'error' => $exception->getMessage(),
            ]);

            return false;
        }
    }

    /** @param  list<string>  $correosInternos */
    private function enviarCorreoInterno(Empresa $empresa, int $diasRestantes, $vigenciaHasta, $fechaEnvio, array $correosInternos, string $hito): bool
    {
        $correos = collect($correosInternos)
            ->map(fn ($correo) => trim((string) $correo))
            ->filter(fn (string $correo) => filter_var($correo, FILTER_VALIDATE_EMAIL))
            ->unique()
            ->values();

        if ($correos->isEmpty()) {
            return false;
        }

        if ($this->yaEnviado($empresa, EmpresaAlertaPlanillaEnvio::CANAL_INTERNO, $vigenciaHasta, $hito)) {
            return false;
        }

        try {
            Mail::to($correos->all())->send(new PlanillaProximaVencerInternoMail($empresa, $diasRestantes, $hito));
            $this->registrarEnvio($empresa, EmpresaAlertaPlanillaEnvio::CANAL_INTERNO, $vigenciaHasta, $fechaEnvio, $hito);

            return true;
        } catch (Throwable $exception) {
            Log::error('Error al enviar alerta interna de planilla', [
                'empresa_id' => $empresa->id,
                'hito' => $hito,
                'correos' => $correos->all(),
                'error' => $exception->getMessage(),
            ]);

            return false;
        }
    }

    private function enviarCorreoContratistaEmpresa(
        ContratistaInterno $contratista,
        int $diasRestantes,
        $vigenciaHasta,
        $fechaEnvio,
        string $hito,
    ): bool {
        $empresa = $contratista->empresa;

        if ($empresa === null) {
            return false;
        }

        $correos = collect(is_array($empresa->correos) ? $empresa->correos : [])
            ->map(fn ($correo) => trim((string) $correo))
            ->filter(fn (string $correo) => filter_var($correo, FILTER_VALIDATE_EMAIL))
            ->unique()
            ->values();

        if ($correos->isEmpty()) {
            return false;
        }

        if ($this->yaEnviadoContratista($contratista, ContratistaInternoAlertaPlanillaEnvio::CANAL_EMPRESA, $vigenciaHasta, $hito)) {
            return false;
        }

        try {
            Mail::to($correos->all())->send(new PlanillaProximaVencerContratistaEmpresaMail($contratista, $diasRestantes, $hito));
            $this->registrarEnvioContratista($contratista, ContratistaInternoAlertaPlanillaEnvio::CANAL_EMPRESA, $vigenciaHasta, $fechaEnvio, $hito);

            return true;
        } catch (Throwable $exception) {
            Log::error('Error al enviar alerta de planilla SS independiente a empresa', [
                'contratista_interno_id' => $contratista->id,
                'hito' => $hito,
                'correos' => $correos->all(),
                'error' => $exception->getMessage(),
            ]);

            return false;
        }
    }

    /** @param  list<string>  $correosInternos */
    private function enviarCorreoContratistaInterno(
        ContratistaInterno $contratista,
        int $diasRestantes,
        $vigenciaHasta,
        $fechaEnvio,
        array $correosInternos,
        string $hito,
    ): bool {
        $correos = collect($correosInternos)
            ->map(fn ($correo) => trim((string) $correo))
            ->filter(fn (string $correo) => filter_var($correo, FILTER_VALIDATE_EMAIL))
            ->unique()
            ->values();

        if ($correos->isEmpty()) {
            return false;
        }

        if ($this->yaEnviadoContratista($contratista, ContratistaInternoAlertaPlanillaEnvio::CANAL_INTERNO, $vigenciaHasta, $hito)) {
            return false;
        }

        try {
            Mail::to($correos->all())->send(new PlanillaProximaVencerContratistaInternoMail($contratista, $diasRestantes, $hito));
            $this->registrarEnvioContratista($contratista, ContratistaInternoAlertaPlanillaEnvio::CANAL_INTERNO, $vigenciaHasta, $fechaEnvio, $hito);

            return true;
        } catch (Throwable $exception) {
            Log::error('Error al enviar alerta interna de planilla SS independiente', [
                'contratista_interno_id' => $contratista->id,
                'hito' => $hito,
                'correos' => $correos->all(),
                'error' => $exception->getMessage(),
            ]);

            return false;
        }
    }

    private function yaEnviadoContratista(ContratistaInterno $contratista, string $canal, $vigenciaHasta, string $hito): bool
    {
        return ContratistaInternoAlertaPlanillaEnvio::query()
            ->where('contratista_interno_id', $contratista->id)
            ->where('canal', $canal)
            ->where('hito', $hito)
            ->whereDate('vigencia_hasta', $vigenciaHasta)
            ->exists();
    }

    private function registrarEnvioContratista(ContratistaInterno $contratista, string $canal, $vigenciaHasta, $fechaEnvio, string $hito): void
    {
        ContratistaInternoAlertaPlanillaEnvio::query()->create([
            'contratista_interno_id' => $contratista->id,
            'canal' => $canal,
            'hito' => $hito,
            'vigencia_hasta' => $vigenciaHasta,
            'fecha_envio' => $fechaEnvio,
        ]);
    }

    private function yaEnviado(Empresa $empresa, string $canal, $vigenciaHasta, string $hito): bool
    {
        return EmpresaAlertaPlanillaEnvio::query()
            ->where('empresa_id', $empresa->id)
            ->where('canal', $canal)
            ->where('hito', $hito)
            ->whereDate('vigencia_hasta', $vigenciaHasta)
            ->exists();
    }

    private function registrarEnvio(Empresa $empresa, string $canal, $vigenciaHasta, $fechaEnvio, string $hito): void
    {
        EmpresaAlertaPlanillaEnvio::query()->create([
            'empresa_id' => $empresa->id,
            'canal' => $canal,
            'hito' => $hito,
            'vigencia_hasta' => $vigenciaHasta,
            'fecha_envio' => $fechaEnvio,
        ]);
    }
}
