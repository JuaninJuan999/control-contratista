<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $campos = function (Blueprint $table): void {
            $table->date('fecha_nacimiento')->nullable()->after('numero_documento');
            $table->string('cargo', 255)->nullable()->after('fecha_nacimiento');
            $table->boolean('manipulador_alimentos')->default(false)->after('cargo');
            $table->date('manipulador_vigencia')->nullable()->after('manipulador_alimentos');
            $table->boolean('licencia_conduccion')->default(false)->after('manipulador_vigencia');
            $table->string('licencia_archivo')->nullable()->after('licencia_conduccion');
            $table->string('licencia_categoria', 10)->nullable()->after('licencia_archivo');
            $table->date('licencia_vencimiento')->nullable()->after('licencia_categoria');
            $table->string('licencia_vencimiento_archivo')->nullable()->after('licencia_vencimiento');
            $table->string('tarjeta_propiedad_archivo')->nullable()->after('licencia_vencimiento_archivo');
        };

        Schema::table('contratistas_externos', $campos);
        Schema::table('contratistas_internos', $campos);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $columnas = [
            'fecha_nacimiento',
            'cargo',
            'manipulador_alimentos',
            'manipulador_vigencia',
            'licencia_conduccion',
            'licencia_archivo',
            'licencia_categoria',
            'licencia_vencimiento',
            'licencia_vencimiento_archivo',
            'tarjeta_propiedad_archivo',
        ];

        Schema::table('contratistas_externos', function (Blueprint $table) use ($columnas) {
            $table->dropColumn($columnas);
        });

        Schema::table('contratistas_internos', function (Blueprint $table) use ($columnas) {
            $table->dropColumn($columnas);
        });
    }
};
