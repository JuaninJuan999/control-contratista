<?php

use App\Support\UserRol;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('rol', 30)->default(UserRol::CONSULTA)->after('activo');
        });

        DB::table('users')->update(['rol' => UserRol::SUPERADMIN]);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn('rol');
        });
    }
};
