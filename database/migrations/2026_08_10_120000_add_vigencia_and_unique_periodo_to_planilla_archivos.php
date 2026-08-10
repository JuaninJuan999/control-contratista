<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('empresa_planilla_archivos', function (Blueprint $table): void {
            $table->date('vigencia_hasta')->nullable()->after('periodo_mes');
            $table->unique(['empresa_id', 'periodo_anio', 'periodo_mes'], 'empresa_planilla_periodo_unique');
        });
    }

    public function down(): void
    {
        Schema::table('empresa_planilla_archivos', function (Blueprint $table): void {
            $table->dropUnique('empresa_planilla_periodo_unique');
            $table->dropColumn('vigencia_hasta');
        });
    }
};
