<?php

use App\Support\PlanillaTipo;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contratistas_internos', function (Blueprint $table): void {
            $table->string('tipo_planilla', 20)->nullable()->after('arl');
            $table->date('limite')->nullable()->after('tipo_planilla');
        });

        DB::table('contratistas_internos')
            ->orderBy('id')
            ->chunkById(200, function ($filas): void {
                foreach ($filas as $fila) {
                    $planillaEmpresa = DB::table('empresas')
                        ->where('id', $fila->empresa_id)
                        ->value('planilla');

                    $tipo = in_array($planillaEmpresa, PlanillaTipo::valores(), true)
                        ? $planillaEmpresa
                        : PlanillaTipo::DEPENDIENTE;

                    DB::table('contratistas_internos')
                        ->where('id', $fila->id)
                        ->update(['tipo_planilla' => $tipo]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('contratistas_internos', function (Blueprint $table): void {
            $table->dropColumn(['tipo_planilla', 'limite']);
        });
    }
};
