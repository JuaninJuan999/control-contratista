<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('contratistas_externos', 'fecha_vencimiento')) {
            return;
        }

        if (Schema::getConnection()->getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE contratistas_externos ALTER COLUMN fecha_vencimiento DROP NOT NULL');
        }
    }

    public function down(): void
    {
        if (! Schema::hasColumn('contratistas_externos', 'fecha_vencimiento')) {
            return;
        }

        if (Schema::getConnection()->getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE contratistas_externos ALTER COLUMN fecha_vencimiento SET NOT NULL');
        }
    }
};
