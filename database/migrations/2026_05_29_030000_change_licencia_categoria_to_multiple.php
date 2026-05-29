<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['contratistas_externos', 'contratistas_internos'] as $tabla) {
            Schema::table($tabla, function (Blueprint $table): void {
                $table->text('licencia_categoria')->nullable()->change();
            });

            DB::table($tabla)->where('licencia_categoria', '')->update(['licencia_categoria' => null]);

            DB::statement(
                "UPDATE {$tabla} SET licencia_categoria = to_jsonb(ARRAY[licencia_categoria])::text ".
                'WHERE licencia_categoria IS NOT NULL'
            );
        }
    }

    public function down(): void
    {
        foreach (['contratistas_externos', 'contratistas_internos'] as $tabla) {
            DB::statement(
                "UPDATE {$tabla} SET licencia_categoria = (licencia_categoria::jsonb ->> 0) ".
                "WHERE licencia_categoria IS NOT NULL AND licencia_categoria <> ''"
            );

            Schema::table($tabla, function (Blueprint $table): void {
                $table->string('licencia_categoria', 10)->nullable()->change();
            });
        }
    }
};
