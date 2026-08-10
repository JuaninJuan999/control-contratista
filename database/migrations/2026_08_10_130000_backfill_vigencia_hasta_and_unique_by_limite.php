<?php

use App\Models\EmpresaPlanillaArchivo;
use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        EmpresaPlanillaArchivo::query()
            ->whereNull('vigencia_hasta')
            ->with('empresa')
            ->each(function (EmpresaPlanillaArchivo $archivo): void {
                $empresa = $archivo->empresa;

                if ($empresa === null) {
                    return;
                }

                $periodoEmpresa = $empresa->periodoVigenciaActual();

                if (
                    $periodoEmpresa !== null
                    && $archivo->periodo_anio === $periodoEmpresa['anio']
                    && $archivo->periodo_mes === $periodoEmpresa['mes']
                    && $empresa->limite !== null
                ) {
                    $archivo->update(['vigencia_hasta' => $empresa->limite]);

                    return;
                }

                $diaReferencia = $empresa->limite?->day ?? 28;
                $base = Carbon::create($archivo->periodo_anio, $archivo->periodo_mes, 1);
                $dia = min($diaReferencia, $base->daysInMonth);

                $archivo->update([
                    'vigencia_hasta' => Carbon::create($archivo->periodo_anio, $archivo->periodo_mes, $dia),
                ]);
            });

        Schema::table('empresa_planilla_archivos', function (Blueprint $table): void {
            $table->dropUnique('empresa_planilla_periodo_unique');
            $table->unique(['empresa_id', 'vigencia_hasta'], 'empresa_planilla_vigencia_unique');
        });
    }

    public function down(): void
    {
        Schema::table('empresa_planilla_archivos', function (Blueprint $table): void {
            $table->dropUnique('empresa_planilla_vigencia_unique');
            $table->unique(['empresa_id', 'periodo_anio', 'periodo_mes'], 'empresa_planilla_periodo_unique');
        });
    }
};
