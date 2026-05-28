<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('nombre')->nullable()->after('name');
            $table->string('apellido')->nullable()->after('nombre');
        });

        foreach (DB::table('users')->orderBy('id')->get() as $user) {
            DB::table('users')->where('id', $user->id)->update([
                'nombre' => $user->name,
                'apellido' => '',
            ]);
        }

        Schema::table('users', function (Blueprint $table): void {
            $table->dropUnique(['email']);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->unique('email');
            $table->dropColumn(['nombre', 'apellido']);
        });
    }
};
