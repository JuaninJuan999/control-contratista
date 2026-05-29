<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;

class VehiculoDocumentoStorage
{
    public static function guardar(int $vehiculoId, ?UploadedFile $archivo): ?string
    {
        if (! $archivo instanceof UploadedFile) {
            return null;
        }

        return $archivo->store("vehiculos/{$vehiculoId}", 'public');
    }

    public static function urlPublica(?string $ruta): ?string
    {
        if ($ruta === null || $ruta === '') {
            return null;
        }

        return '/storage/'.ltrim(str_replace('\\', '/', $ruta), '/');
    }
}
