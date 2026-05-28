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
        Schema::table('empresas', function (Blueprint $table) {
            $table->string('telefono', 50)->nullable()->after('nit');
            $table->json('correos')->nullable()->after('telefono');
            $table->date('limite')->nullable()->after('correos');
            $table->string('planilla', 255)->nullable()->after('limite');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('empresas', function (Blueprint $table) {
            $table->dropColumn(['telefono', 'correos', 'limite', 'planilla']);
        });
    }
};
