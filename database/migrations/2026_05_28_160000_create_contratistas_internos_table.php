<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('contratistas_internos', function (Blueprint $table) {
            $table->id();
            $table->string('nombres_apellidos');
            $table->string('tipo_documento', 10);
            $table->string('numero_documento', 32);
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->string('arl', 120);
            $table->json('meses_por_anio')->nullable();
            $table->timestamps();

            $table->unique(['tipo_documento', 'numero_documento']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contratistas_internos');
    }
};
