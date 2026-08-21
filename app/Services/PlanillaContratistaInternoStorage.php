<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class PlanillaContratistaInternoStorage
{
    public static function guardar(int $contratistaInternoId, UploadedFile $archivo): string
    {
        return $archivo->store("planillas/internos/{$contratistaInternoId}", 'public');
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
