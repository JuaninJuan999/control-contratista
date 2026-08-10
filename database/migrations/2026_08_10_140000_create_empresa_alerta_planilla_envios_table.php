<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('empresa_alerta_planilla_envios', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('empresa_id')->constrained()->cascadeOnDelete();
            $table->string('canal', 20);
            $table->date('vigencia_hasta');
            $table->date('fecha_envio');
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['empresa_id', 'canal', 'vigencia_hasta', 'fecha_envio'], 'empresa_alerta_planilla_unica');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('empresa_alerta_planilla_envios');
    }
};
