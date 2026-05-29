<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vehiculos', function (Blueprint $table): void {
            $table->string('soat_archivo')->nullable()->after('soat_fin');
            $table->string('tecnomecanica_archivo')->nullable()->after('tecnomecanica_fin');
            $table->string('inspeccion_sanitaria_archivo')->nullable()->after('tarjeta_propiedad_archivo');
        });
    }

    public function down(): void
    {
        Schema::table('vehiculos', function (Blueprint $table): void {
            $table->dropColumn(['soat_archivo', 'tecnomecanica_archivo', 'inspeccion_sanitaria_archivo']);
        });
    }
};
