<?php

use App\Models\Empresa;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('contratistas_externos', function (Blueprint $table) {
            $table->foreignId('empresa_id')->nullable()->after('numero_documento')->constrained('empresas')->restrictOnDelete();
        });

        foreach (DB::table('contratistas_externos')->whereNotNull('empresa')->get() as $row) {
            $nombre = trim((string) $row->empresa);
            if ($nombre === '') {
                continue;
            }

            $empresa = Empresa::query()->whereRaw('LOWER(nombre) = ?', [mb_strtolower($nombre, 'UTF-8')])->first();

            if ($empresa === null) {
                $empresa = Empresa::query()->create([
                    'nombre' => $nombre,
                    'nit' => null,
                ]);
            }

            DB::table('contratistas_externos')->where('id', $row->id)->update(['empresa_id' => $empresa->id]);
        }

        Schema::table('contratistas_externos', function (Blueprint $table) {
            $table->dropColumn('empresa');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contratistas_externos', function (Blueprint $table) {
            $table->string('empresa', 255)->nullable()->after('numero_documento');
        });

        foreach (DB::table('contratistas_externos')->whereNotNull('empresa_id')->get() as $row) {
            $nombre = DB::table('empresas')->where('id', $row->empresa_id)->value('nombre');
            DB::table('contratistas_externos')->where('id', $row->id)->update(['empresa' => $nombre]);
        }

        Schema::table('contratistas_externos', function (Blueprint $table) {
            $table->dropForeign(['empresa_id']);
            $table->dropColumn('empresa_id');
        });
    }
};
