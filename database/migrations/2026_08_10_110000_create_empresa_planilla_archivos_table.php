<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('empresa_planilla_archivos', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('empresa_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('periodo_anio')->nullable();
            $table->unsignedTinyInteger('periodo_mes')->nullable();
            $table->string('archivo');
            $table->string('nombre_original')->nullable();
            $table->string('mime', 120)->nullable();
            $table->unsignedBigInteger('tamano_bytes')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();

            $table->index(['empresa_id', 'periodo_anio', 'periodo_mes']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('empresa_planilla_archivos');
    }
};
