<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tercer estado del control mensual: meses marcados como NO vigentes (rojo).
     */
    public function up(): void
    {
        foreach (['contratistas_externos', 'contratistas_internos'] as $tabla) {
            Schema::table($tabla, function (Blueprint $table) use ($tabla): void {
                if (! Schema::hasColumn($tabla, 'meses_rechazados')) {
                    $table->json('meses_rechazados')->nullable()->after('meses_por_anio');
                }
            });
        }
    }

    public function down(): void
    {
        foreach (['contratistas_externos', 'contratistas_internos'] as $tabla) {
            Schema::table($tabla, function (Blueprint $table): void {
                $table->dropColumn('meses_rechazados');
            });
        }
    }
};
