<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('empresa_alerta_planilla_envios', function (Blueprint $table): void {
            $table->dropUnique('empresa_alerta_planilla_unica');
            $table->string('hito', 32)->default('legacy')->after('canal');
            $table->unique(['empresa_id', 'canal', 'vigencia_hasta', 'hito'], 'empresa_alerta_planilla_hito_unica');
        });

        Schema::table('contratista_interno_alerta_planilla_envios', function (Blueprint $table): void {
            $table->dropUnique('contratista_interno_alerta_planilla_unica');
            $table->string('hito', 32)->default('legacy')->after('canal');
            $table->unique(
                ['contratista_interno_id', 'canal', 'vigencia_hasta', 'hito'],
                'contratista_interno_alerta_planilla_hito_unica'
            );
        });
    }

    public function down(): void
    {
        Schema::table('contratista_interno_alerta_planilla_envios', function (Blueprint $table): void {
            $table->dropUnique('contratista_interno_alerta_planilla_hito_unica');
            $table->dropColumn('hito');
            $table->unique(
                ['contratista_interno_id', 'canal', 'vigencia_hasta', 'fecha_envio'],
                'contratista_interno_alerta_planilla_unica'
            );
        });

        Schema::table('empresa_alerta_planilla_envios', function (Blueprint $table): void {
            $table->dropUnique('empresa_alerta_planilla_hito_unica');
            $table->dropColumn('hito');
            $table->unique(['empresa_id', 'canal', 'vigencia_hasta', 'fecha_envio'], 'empresa_alerta_planilla_unica');
        });
    }
};
