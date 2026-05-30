<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ContratistaDocumentoStorage
{
    /**
     * @param  array<string, UploadedFile|null>  $archivos
     * @return array<string, string>
     */
    public static function guardarPara(string $tipo, int $contratistaId, array $archivos): array
    {
        return self::reemplazarPara($tipo, $contratistaId, $archivos);
    }

    /**
     * @param  array<string, UploadedFile|null>  $archivos
     * @return array<string, string>
     */
    public static function reemplazarPara(string $tipo, int $contratistaId, array $archivos, ?Model $contratista = null): array
    {
        $rutas = [];

        foreach ($archivos as $campo => $archivo) {
            if (! $archivo instanceof UploadedFile) {
                continue;
            }

            if ($contratista !== null) {
                self::eliminar($contratista->{$campo} ?? null);
            }

            $rutas[$campo] = $archivo->store(
                "contratistas/{$tipo}/{$contratistaId}",
                'public'
            );
        }

        return $rutas;
    }

    public static function eliminar(?string $ruta): void
    {
        if ($ruta === null || $ruta === '') {
            return;
        }

        Storage::disk('public')->delete($ruta);
    }

    public static function urlPublica(?string $ruta): ?string
    {
        if ($ruta === null || $ruta === '') {
            return null;
        }

        return '/storage/'.ltrim(str_replace('\\', '/', $ruta), '/');
    }
}
