<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Unifica los campos entre contratistas externos e internos:
     * - Externos reciben control mensual y ARL.
     * - Internos reciben inducción/reinducción (I/R) y su vencimiento.
     */
    public function up(): void
    {
        Schema::table('contratistas_externos', function (Blueprint $table): void {
            if (! Schema::hasColumn('contratistas_externos', 'arl')) {
                $table->string('arl', 120)->nullable()->after('empresa_id');
            }
            if (! Schema::hasColumn('contratistas_externos', 'meses_por_anio')) {
                $table->json('meses_por_anio')->nullable()->after('arl');
            }
        });

        Schema::table('contratistas_internos', function (Blueprint $table): void {
            if (! Schema::hasColumn('contratistas_internos', 'fecha_ultima_ir')) {
                $table->date('fecha_ultima_ir')->nullable()->after('empresa_id');
            }
            if (! Schema::hasColumn('contratistas_internos', 'vigencia_dias')) {
                $table->unsignedSmallInteger('vigencia_dias')->default(365)->after('fecha_ultima_ir');
            }
            if (! Schema::hasColumn('contratistas_internos', 'fecha_vencimiento')) {
                $table->date('fecha_vencimiento')->nullable()->after('vigencia_dias');
            }
        });
    }

    public function down(): void
    {
        Schema::table('contratistas_externos', function (Blueprint $table): void {
            $table->dropColumn(['arl', 'meses_por_anio']);
        });

        Schema::table('contratistas_internos', function (Blueprint $table): void {
            $table->dropColumn(['fecha_ultima_ir', 'vigencia_dias', 'fecha_vencimiento']);
        });
    }
};
