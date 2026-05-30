<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_usabilidad_sesiones', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamp('iniciada_at');
            $table->timestamp('ultima_actividad_at');
            $table->timestamp('finalizada_at')->nullable();
            $table->unsignedInteger('segundos_activos')->default(0);
            $table->timestamps();

            $table->index(['user_id', 'finalizada_at']);
            $table->index('iniciada_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_usabilidad_sesiones');
    }
};
