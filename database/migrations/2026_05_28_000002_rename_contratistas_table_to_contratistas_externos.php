<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Proyectos que ya ejecutaron la migración original sobre la tabla `contratistas`.
     */
    public function up(): void
    {
        if (Schema::hasTable('contratistas') && ! Schema::hasTable('contratistas_externos')) {
            Schema::rename('contratistas', 'contratistas_externos');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('contratistas_externos') && ! Schema::hasTable('contratistas')) {
            Schema::rename('contratistas_externos', 'contratistas');
        }
    }
};
