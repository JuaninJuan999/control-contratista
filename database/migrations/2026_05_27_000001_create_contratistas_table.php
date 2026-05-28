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
        Schema::create('contratistas_externos', function (Blueprint $table) {
            $table->id();
            $table->string('nombres_apellidos');
            $table->string('tipo_documento', 10);
            $table->string('numero_documento', 32);
            $table->string('empresa', 255)->nullable();
            $table->date('fecha_ultima_ir');
            $table->unsignedSmallInteger('vigencia_dias')->default(365);
            $table->date('fecha_vencimiento');
            $table->timestamps();

            $table->unique(['tipo_documento', 'numero_documento']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contratistas_externos');
    }
};
