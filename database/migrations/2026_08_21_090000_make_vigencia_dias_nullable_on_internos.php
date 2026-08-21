<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('contratistas_internos', 'vigencia_dias')) {
            return;
        }

        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE contratistas_internos ALTER COLUMN vigencia_dias DROP NOT NULL');
        }
    }

    public function down(): void
    {
        if (! Schema::hasColumn('contratistas_internos', 'vigencia_dias')) {
            return;
        }

        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'pgsql') {
            DB::statement('UPDATE contratistas_internos SET vigencia_dias = 365 WHERE vigencia_dias IS NULL');
            DB::statement('ALTER TABLE contratistas_internos ALTER COLUMN vigencia_dias SET NOT NULL');
        }
    }
};
