<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['contratistas_externos', 'contratistas_internos'] as $tabla) {
            Schema::table($tabla, function (Blueprint $table): void {
                $table->string('cedula_archivo')->nullable()->after('licencia_vencimiento');
            });

            Schema::table($tabla, function (Blueprint $table): void {
                $table->dropColumn(['licencia_vencimiento_archivo', 'tarjeta_propiedad_archivo']);
            });
        }

        Schema::table('vehiculos', function (Blueprint $table): void {
            $table->string('tarjeta_propiedad_archivo')->nullable()->after('tecnomecanica_fin');
        });
    }

    public function down(): void
    {
        foreach (['contratistas_externos', 'contratistas_internos'] as $tabla) {
            Schema::table($tabla, function (Blueprint $table): void {
                $table->string('licencia_vencimiento_archivo')->nullable()->after('licencia_vencimiento');
                $table->string('tarjeta_propiedad_archivo')->nullable()->after('licencia_vencimiento_archivo');
            });

            Schema::table($tabla, function (Blueprint $table): void {
                $table->dropColumn('cedula_archivo');
            });
        }

        Schema::table('vehiculos', function (Blueprint $table): void {
            $table->dropColumn('tarjeta_propiedad_archivo');
        });
    }
};
