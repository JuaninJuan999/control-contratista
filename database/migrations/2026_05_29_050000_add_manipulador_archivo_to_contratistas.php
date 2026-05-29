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
                $table->string('manipulador_archivo')->nullable()->after('manipulador_vigencia');
            });
        }
    }

    public function down(): void
    {
        foreach (['contratistas_externos', 'contratistas_internos'] as $tabla) {
            Schema::table($tabla, function (Blueprint $table): void {
                $table->dropColumn('manipulador_archivo');
            });
        }
    }
};
