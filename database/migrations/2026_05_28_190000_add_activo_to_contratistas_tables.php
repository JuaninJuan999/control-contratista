<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contratistas_externos', function (Blueprint $table): void {
            $table->boolean('activo')->default(true)->after('fecha_vencimiento');
        });

        Schema::table('contratistas_internos', function (Blueprint $table): void {
            $table->boolean('activo')->default(true)->after('meses_por_anio');
        });
    }

    public function down(): void
    {
        Schema::table('contratistas_externos', function (Blueprint $table): void {
            $table->dropColumn('activo');
        });

        Schema::table('contratistas_internos', function (Blueprint $table): void {
            $table->dropColumn('activo');
        });
    }
};
