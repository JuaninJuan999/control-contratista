<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contratista_interno_planilla_archivos', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('contratista_interno_id')->constrained('contratistas_internos')->cascadeOnDelete();
            $table->unsignedSmallInteger('periodo_anio')->nullable();
            $table->unsignedTinyInteger('periodo_mes')->nullable();
            $table->date('vigencia_hasta')->nullable();
            $table->string('archivo');
            $table->string('nombre_original')->nullable();
            $table->string('mime', 120)->nullable();
            $table->unsignedBigInteger('tamano_bytes')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();

            $table->unique(['contratista_interno_id', 'vigencia_hasta'], 'ci_planilla_vigencia_unique');
            $table->index(['contratista_interno_id', 'periodo_anio', 'periodo_mes']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contratista_interno_planilla_archivos');
    }
};
