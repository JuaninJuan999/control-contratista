<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vehiculos', function (Blueprint $table): void {
            $table->boolean('inspeccion_sanitaria')->default(false)->after('inspeccion_sanitaria_archivo');
            $table->date('inspeccion_sanitaria_fin')->nullable()->after('inspeccion_sanitaria');
        });
    }

    public function down(): void
    {
        Schema::table('vehiculos', function (Blueprint $table): void {
            $table->dropColumn(['inspeccion_sanitaria', 'inspeccion_sanitaria_fin']);
        });
    }
};
