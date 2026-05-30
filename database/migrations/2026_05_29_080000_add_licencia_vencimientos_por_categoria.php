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
                $table->json('licencia_vencimientos')->nullable()->after('licencia_categoria');
            });

            $registros = DB::table($tabla)
                ->whereNotNull('licencia_vencimiento')
                ->get(['id', 'licencia_categoria', 'licencia_vencimiento']);

            foreach ($registros as $registro) {
                $categorias = json_decode($registro->licencia_categoria ?? '[]', true);
                if (! is_array($categorias)) {
                    $categorias = $registro->licencia_categoria ? [$registro->licencia_categoria] : [];
                }

                $vencimientos = [];
                foreach ($categorias as $categoria) {
                    if (is_string($categoria) && $categoria !== '') {
                        $vencimientos[$categoria] = $registro->licencia_vencimiento;
                    }
                }

                DB::table($tabla)->where('id', $registro->id)->update([
                    'licencia_vencimientos' => $vencimientos === [] ? null : json_encode($vencimientos),
                ]);
            }

            Schema::table($tabla, function (Blueprint $table): void {
                $table->dropColumn('licencia_vencimiento');
            });
        }
    }

    public function down(): void
    {
        foreach (['contratistas_externos', 'contratistas_internos'] as $tabla) {
            Schema::table($tabla, function (Blueprint $table): void {
                $table->date('licencia_vencimiento')->nullable()->after('licencia_categoria');
            });

            $registros = DB::table($tabla)
                ->whereNotNull('licencia_vencimientos')
                ->get(['id', 'licencia_vencimientos']);

            foreach ($registros as $registro) {
                $vencimientos = json_decode($registro->licencia_vencimientos ?? '{}', true);
                $fecha = is_array($vencimientos) && $vencimientos !== []
                    ? reset($vencimientos)
                    : null;

                DB::table($tabla)->where('id', $registro->id)->update([
                    'licencia_vencimiento' => $fecha,
                ]);
            }

            Schema::table($tabla, function (Blueprint $table): void {
                $table->dropColumn('licencia_vencimientos');
            });
        }
    }
};
