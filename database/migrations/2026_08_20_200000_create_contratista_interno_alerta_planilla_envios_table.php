<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contratista_interno_alerta_planilla_envios', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('contratista_interno_id')->constrained('contratistas_internos')->cascadeOnDelete();
            $table->string('canal', 32);
            $table->date('vigencia_hasta');
            $table->date('fecha_envio');
            $table->timestamp('created_at')->useCurrent();

            $table->unique(
                ['contratista_interno_id', 'canal', 'vigencia_hasta', 'fecha_envio'],
                'contratista_interno_alerta_planilla_unica'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contratista_interno_alerta_planilla_envios');
    }
};
