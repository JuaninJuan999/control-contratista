<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('contratistas_externos', 'fecha_ultima_ir')) {
            return;
        }

        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE contratistas_externos ALTER COLUMN fecha_ultima_ir DROP NOT NULL');
        }
    }

    public function down(): void
    {
        if (! Schema::hasColumn('contratistas_externos', 'fecha_ultima_ir')) {
            return;
        }

        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE contratistas_externos ALTER COLUMN fecha_ultima_ir SET NOT NULL');
        }
    }
};
