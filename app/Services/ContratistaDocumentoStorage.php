<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;

class ContratistaDocumentoStorage
{
    /**
     * @param  array<string, UploadedFile|null>  $archivos
     * @return array<string, string>
     */
    public static function guardarPara(string $tipo, int $contratistaId, array $archivos): array
    {
        $rutas = [];

        foreach ($archivos as $campo => $archivo) {
            if (! $archivo instanceof UploadedFile) {
                continue;
            }

            $rutas[$campo] = $archivo->store(
                "contratistas/{$tipo}/{$contratistaId}",
                'public'
            );
        }

        return $rutas;
    }

    public static function urlPublica(?string $ruta): ?string
    {
        if ($ruta === null || $ruta === '') {
            return null;
        }

        return '/storage/'.ltrim(str_replace('\\', '/', $ruta), '/');
    }
}
